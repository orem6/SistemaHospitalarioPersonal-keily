<?php

namespace App\Http\Controllers\Api\V1\LabResults;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Services\LabResults\Contracts\ResultEntryWriter;
use App\Services\LabResults\Exceptions\OrderItemNotPendingException;
use App\Services\LabResults\Exceptions\ResultValidationException;
use App\Services\LabResults\ValueObjects\ResultInput;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LabResultEntryController extends Controller
{
    public function __construct(private readonly ResultEntryWriter $entry)
    {
    }

    public function store(Request $request): JsonResponse
    {
        /** @var Tenant $tenant */
        $tenant = $request->attributes->get('tenant');

        $validated = $request->validate([
            'lab_order_item_id' => ['required', 'integer'],
            'sample_id' => ['required', 'integer'],
            'numeric_value' => ['nullable', 'string'],
            'text_value' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $result = $this->entry->enter(
                $tenant->id,
                new ResultInput(
                    labOrderItemId: (int) $validated['lab_order_item_id'],
                    sampleId: (int) $validated['sample_id'],
                    numericValue: $validated['numeric_value'] ?? null,
                    textValue: $validated['text_value'] ?? null,
                ),
                (int) Auth::guard('api')->id(),
            );
        } catch (ResultValidationException $exception) {
            return response()->json([
                'message' => 'La información del resultado no es válida.',
                'errors' => $exception->errors(),
            ], 422);
        } catch (OrderItemNotPendingException $exception) {
            return response()->json([
                'message' => 'El ítem de la orden no está pendiente de captura o no pertenece al tenant.',
                'lab_order_item_id' => $exception->getOrderItemId(),
            ], 409);
        }

        return response()->json([
            'data' => $result->load('labOrderItem.labTest', 'labOrderItem.labOrder'),
        ], 201);
    }
}