<?php

namespace App\Services\LabResults;

use App\Models\LabResult;
use App\Services\LabResults\Contracts\ResultPublisher;
use App\Services\LabResults\Exceptions\LabResultNotFoundException;
use App\Services\LabResults\Exceptions\ResultAlreadyPublishedException;

/**
 * UC-04 — Publicación de un resultado. Única responsabilidad: marcar como
 * publicado un resultado existente y no publicado del tenant.
 */
class ResultPublicationService implements ResultPublisher
{
    public function publish(string $tenantId, int $labResultId, int $publishedByUserId): LabResult
    {
        $result = LabResult::query()
            ->where('tenant_id', $tenantId)
            ->whereKey($labResultId)
            ->first();

        if ($result === null) {
            throw new LabResultNotFoundException($labResultId);
        }

        if ($result->isPublished()) {
            throw new ResultAlreadyPublishedException($labResultId);
        }

        $result->published_at = now();
        $result->published_by = $publishedByUserId;
        $result->save();

        return $result->fresh();
    }
}