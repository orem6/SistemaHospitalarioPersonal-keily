<?php

namespace App\Http\Controllers\Api\V1\LabResults;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Services\LabResults\Contracts\ResultPublisher;
use App\Services\LabResults\Exceptions\LabResultNotFoundException;
use App\Services\LabResults\Exceptions\ResultAlreadyPublishedException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LabResultPublishController extends Controller
{
    public function __construct(private readonly ResultPublisher $publisher)
    {
    }

    public function store(Request $request, int $result): JsonResponse
    {
        /** @var Tenant $tenant */
        $tenant = $request->attributes->get('tenant');

        try {
            $published = $this->publisher->publish(
                $tenant->id,
                $result,
                (int) Auth::guard('api')->id(),
            );
        } catch (LabResultNotFoundException) {
            return response()->json(['message' => 'El resultado no existe.'], 404);
        } catch (ResultAlreadyPublishedException) {
            return response()->json([
                'message' => 'El resultado ya fue publicado.',
            ], 409);
        }

        return response()->json([
            'data' => $published->load('labOrderItem.labTest', 'labOrderItem.labOrder'),
        ]);
    }
}