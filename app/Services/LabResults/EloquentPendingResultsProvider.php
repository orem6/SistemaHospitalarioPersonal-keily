<?php

namespace App\Services\LabResults;

use App\Models\LabOrderItem;
use App\Services\LabResults\Contracts\PendingResultsProvider;
use App\Services\LabResults\ValueObjects\PendingResultDetail;

class EloquentPendingResultsProvider implements PendingResultsProvider
{
    /**
     * @return list<PendingResultDetail>
     */
    public function listPendingForEntry(string $tenantId): array
    {
        return LabOrderItem::query()
            ->whereIn('status', ['pendiente', 'muestra_recibida', 'en_proceso'])
            ->whereDoesntHave('result')
            ->whereHas('labOrder', fn ($query) => $query->where('tenant_id', $tenantId))
            ->with(['labOrder.patient:id,first_name,last_name', 'labTest:id,name'])
            ->orderBy('lab_order_id')
            ->get()
            ->map(function (LabOrderItem $item): PendingResultDetail {
                $order = $item->labOrder;

                return new PendingResultDetail(
                    labOrderItemId: $item->id,
                    orderCode: $order?->code ?? '',
                    patientName: $order?->patient?->full_name ?? '—',
                    testName: $item->labTest?->name ?? '—',
                    priority: $order?->priority ?? 'rutina',
                    sampleBarcode: $order?->samples()->first()?->barcode,
                    orderedAt: $order?->ordered_at?->toDateTimeString() ?? '',
                );
            })
            ->all();
    }
}