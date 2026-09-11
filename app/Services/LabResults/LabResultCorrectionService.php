<?php

namespace App\Services\LabResults;

use App\Models\LabResult;
use App\Models\LabResultCorrection;
use App\Services\LabResults\Concerns\ComputesResultFlags;
use App\Services\LabResults\Contracts\PendingResultCorrector;
use App\Services\LabResults\Contracts\ResultValidator;
use App\Services\LabResults\Exceptions\LabResultNotFoundException;
use App\Services\LabResults\Exceptions\ResultAlreadyPublishedException;
use App\Services\LabResults\Exceptions\ResultNotChangedException;
use App\Services\LabResults\ValueObjects\ResultCorrectionInput;

/**
 * UC-03 — Corrección controlada de resultados pendientes. Solo corrige
 * resultados no publicados y deja trazabilidad en lab_result_corrections.
 */
class LabResultCorrectionService implements PendingResultCorrector
{
    use ComputesResultFlags;

    public function __construct(private readonly ResultValidator $validator)
    {
    }

    public function correct(string $tenantId, ResultCorrectionInput $input, int $correctedByUserId): LabResult
    {
        $this->validator->assertValidValues($input->numericValue, $input->textValue);

        $result = LabResult::query()
            ->where('tenant_id', $tenantId)
            ->whereKey($input->labResultId)
            ->with('labOrderItem.labTest')
            ->first();

        if ($result === null) {
            throw new LabResultNotFoundException($input->labResultId);
        }

        if ($result->isPublished()) {
            throw new ResultAlreadyPublishedException($input->labResultId);
        }

        $numeric = $input->numericValue !== null && $input->numericValue !== ''
            ? (float) $input->numericValue
            : null;
        $text = $input->textValue !== null && $input->textValue !== '' ? $input->textValue : null;

        $numericChanged = ($numeric !== null) !== ($result->numeric_value !== null)
            || ($numeric !== null && (float) $result->numeric_value !== $numeric);
        $textChanged = $text !== $result->text_value;

        if (! $numericChanged && ! $textChanged) {
            throw new ResultNotChangedException();
        }

        $corrections = [];

        if ($numericChanged) {
            $corrections[] = new LabResultCorrection([
                'tenant_id' => $tenantId,
                'lab_result_id' => $result->id,
                'corrected_by' => $correctedByUserId,
                'field' => 'numeric_value',
                'previous_value' => $result->numeric_value !== null ? (string) $result->numeric_value : null,
                'new_value' => $numeric !== null ? (string) $numeric : null,
                'reason' => $input->reason,
            ]);
        }

        if ($textChanged) {
            $corrections[] = new LabResultCorrection([
                'tenant_id' => $tenantId,
                'lab_result_id' => $result->id,
                'corrected_by' => $correctedByUserId,
                'field' => 'text_value',
                'previous_value' => $result->text_value,
                'new_value' => $text,
                'reason' => $input->reason,
            ]);
        }

        $result->numeric_value = $numeric;
        $result->text_value = $text;
        $result->is_abnormal = $this->isAbnormal($result->labOrderItem?->labTest, $numeric);
        $result->is_critical = $this->isCritical($result->labOrderItem?->labTest, $numeric);
        $result->save();

        foreach ($corrections as $correction) {
            $correction->save();
        }

        return $result->fresh();
    }
}