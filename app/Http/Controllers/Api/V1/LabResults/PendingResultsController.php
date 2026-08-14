<?php

namespace App\Http\Controllers\Api\V1\LabResults;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Services\LabResults\Contracts\PendingResultsProvider;
use App\Services\LabResults\ValueObjects\PendingResultDetail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PendingResultsController extends Controller
{
    public function __construct(private readonly PendingResultsProvider $pendingResults)
    {
    }

    public function index(Request $request): JsonResponse
    {
        /** @var Tenant $tenant */
        $tenant = $request->attributes->get('tenant');

        $pending = array_map(
            fn (PendingResultDetail $detail) => $detail->toArray(),
            $this->pendingResults->listPendingForEntry($tenant->id)
        );

        return response()->json(['data' => $pending]);
    }
}