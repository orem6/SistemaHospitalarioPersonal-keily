<?php

namespace App\Services\LabResults\Contracts;

use App\Models\LabResult;
use App\Services\LabResults\ValueObjects\ResultCorrectionInput;

/**
 * UC-03 — Corregir resultado pendiente (cliente: Técnico de Laboratorio).
 *
 * Contrato de escritura acotado a la corrección controlada: solo resultados
 * aún no publicados y dejando trazabilidad de cada modificación.
 */
interface PendingResultCorrector
{
    /**
     * @throws \App\Services\LabResults\Exceptions\ResultValidationException
     * @throws \App\Services\LabResults\Exceptions\LabResultNotFoundException
     * @throws \App\Services\LabResults\Exceptions\ResultAlreadyPublishedException
     * @throws \App\Services\LabResults\Exceptions\ResultNotChangedException
     */
    public function correct(string $tenantId, ResultCorrectionInput $input, int $correctedByUserId): LabResult;
}