<?php

namespace App\Http\Controllers\Api\V1\LabResults;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Services\LabResults\Contracts\PendingResultCorrector;
use App\Services\LabResults\Exceptions\LabResultNotFoundException;
use App\Services\LabResults\Exceptions\ResultAlreadyPublishedException;
use App\Services\LabResults\Exceptions\ResultNotChangedException;
use App\Services\LabResults\Exceptions\ResultValidationException;
use App\Services\LabResults\ValueObjects\ResultCorrectionInput;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LabResultCorrectionController extends Controller
{
    public function __construct(private readonly PendingResultCorrector $corrector)
    {
    }

    public function update(Request $request, int $result): JsonResponse
    {
        /** @var Tenant $tenant */
        $tenant = $request->attributes->get('tenant');

        $validated = $request->validate([
            'numeric_value' => ['nullable', 'string'],
            'text_value' => ['nullable', 'string', 'max:500'],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $updated = $this->corrector->correct(
                $tenant->id,
                new ResultCorrectionInput(
                    labResultId: $result,
                    numericValue: $validated['numeric_value'] ?? null,
                    textValue: $validated['text_value'] ?? null,
                    reason: $validated['reason'] ?? null,
                ),
                (int) Auth::guard('api')->id(),
            );
        } catch (LabResultNotFoundException) {
            return response()->json(['message' => 'El resultado no existe.'], 404);
        } catch (ResultAlreadyPublishedException) {
            return response()->json([
                'message' => 'Un resultado publicado no puede corregirse directamente. Requiere un nuevo proceso.',
            ], 409);
        } catch (ResultNotChangedException) {
            return response()->json([
                'message' => 'No se detectaron cambios sobre el resultado.',
            ], 422);
        } catch (ResultValidationException $exception) {
            return response()->json([
                'message' => 'La información corregida no es válida.',
                'errors' => $exception->errors(),
            ], 422);
        }

        return response()->json([
            'data' => $updated->load('corrections', 'labOrderItem.labTest'),
        ]);
    }
}