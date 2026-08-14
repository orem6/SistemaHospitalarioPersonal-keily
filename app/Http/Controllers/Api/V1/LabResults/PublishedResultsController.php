<?php

namespace App\Http\Controllers\Api\V1\LabResults;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Services\LabResults\Contracts\PublishedResultReader;
use App\Services\LabResults\ValueObjects\PublishedResultDetail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublishedResultsController extends Controller
{
    public function __construct(private readonly PublishedResultReader $publishedResults)
    {
    }

    public function show(Request $request, int $result): JsonResponse
    {
        /** @var Tenant $tenant */
        $tenant = $request->attributes->get('tenant');

        $detail = $this->publishedResults->findPublishedForTenant($tenant->id, $result);

        if ($detail === null) {
            return response()->json(['message' => 'El resultado publicado no existe o no está disponible.'], 404);
        }

        return response()->json([
            'data' => $detail instanceof PublishedResultDetail ? $detail->toArray() : $detail,
        ]);
    }
}