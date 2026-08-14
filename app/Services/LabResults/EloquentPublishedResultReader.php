<?php

namespace App\Services\LabResults;

use App\Models\LabResult;
use App\Services\LabResults\Contracts\PublishedResultReader;
use App\Services\LabResults\ValueObjects\PublishedResultDetail;

/**
 * UC-05 — Lectura de resultados publicados. El médico nunca recibe
 * resultados pendientes ni publicados de otro tenant.
 */
class EloquentPublishedResultReader implements PublishedResultReader
{
    public function findPublishedForTenant(string $tenantId, int $labResultId): ?PublishedResultDetail
    {
        $result = LabResult::query()
            ->where('tenant_id', $tenantId)
            ->whereKey($labResultId)
            ->whereNotNull('published_at')
            ->with(['labOrderItem.labTest', 'labOrderItem.labOrder'])
            ->first();

        if ($result === null) {
            return null;
        }

        return new PublishedResultDetail(
            labResultId: $result->id,
            orderCode: $result->labOrderItem?->labOrder?->code ?? '',
            testName: $result->labOrderItem?->labTest?->name ?? '',
            unit: $result->labOrderItem?->labTest?->unit,
            numericValue: $result->numeric_value !== null ? (string) $result->numeric_value : null,
            textValue: $result->text_value,
            isCritical: (bool) $result->is_critical,
            isAbnormal: (bool) $result->is_abnormal,
            resultedAt: $result->resulted_at?->toDateTimeString() ?? '',
            publishedAt: $result->published_at?->toDateTimeString() ?? '',
        );
    }
}