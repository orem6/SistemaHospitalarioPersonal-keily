<?php

namespace App\Services\LabResults;

use App\Models\LabOrderItem;
use App\Models\LabResult;
use App\Models\Sample;
use App\Services\LabResults\Concerns\ComputesResultFlags;
use App\Services\LabResults\Contracts\ResultEntryWriter;
use App\Services\LabResults\Contracts\ResultValidator;
use App\Services\LabResults\Exceptions\OrderItemNotPendingException;
use App\Services\LabResults\ValueObjects\ResultInput;

/**
 * UC-02 — Captura de un resultado de laboratorio. Depende únicamente del
 * contrato de validación (UC-06); no conoce corrección ni publicación.
 */
class LabResultEntryService implements ResultEntryWriter
{
    use ComputesResultFlags;

    public function __construct(private readonly ResultValidator $validator)
    {
    }

    public function enter(string $tenantId, ResultInput $input, int $enteredByUserId): LabResult
    {
        $this->validator->assertValidValues($input->numericValue, $input->textValue);

        $item = LabOrderItem::query()
            ->whereKey($input->labOrderItemId)
            ->whereHas('labOrder', fn ($query) => $query->where('tenant_id', $tenantId))
            ->with('labTest')
            ->first();

        if ($item === null || ! in_array($item->status, ['pendiente', 'muestra_recibida', 'en_proceso'], true)) {
            throw new OrderItemNotPendingException($input->labOrderItemId);
        }

        if ($item->result()->exists()) {
            throw new OrderItemNotPendingException($input->labOrderItemId);
        }

        $sample = Sample::query()
            ->whereKey($input->sampleId)
            ->where('tenant_id', $tenantId)
            ->where('lab_order_id', $item->lab_order_id)
            ->first();

        if ($sample === null) {
            throw new OrderItemNotPendingException($input->labOrderItemId);
        }

        $numeric = $input->numericValue !== null && $input->numericValue !== ''
            ? (float) $input->numericValue
            : null;

        $result = new LabResult();
        $result->fill([
            'tenant_id' => $tenantId,
            'lab_order_item_id' => $item->id,
            'sample_id' => $sample->id,
            'entered_by' => $enteredByUserId,
            'numeric_value' => $numeric,
            'text_value' => $input->textValue !== null && $input->textValue !== '' ? $input->textValue : null,
            'is_critical' => $this->isCritical($item->labTest, $numeric),
            'is_abnormal' => $this->isAbnormal($item->labTest, $numeric),
            'resulted_at' => now(),
        ]);
        $result->save();

        $item->update(['status' => 'resultado_listo']);

        return $result;
    }
}