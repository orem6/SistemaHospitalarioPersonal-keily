<?php

namespace App\Services\LabResults\Contracts;

use App\Models\LabResult;
use App\Services\LabResults\ValueObjects\ResultInput;

/**
 * UC-02 — Registrar resultado de laboratorio (cliente: Técnico de Laboratorio).
 *
 * Contrato de escritura para la captura de un resultado pendiente. No expone
 * corrección, publicación ni consultas de solo lectura: el técnico usa este
 * contrato únicamente para registrar.
 */
interface ResultEntryWriter
{
    /**
     * @throws \App\Services\LabResults\Exceptions\ResultValidationException
     * @throws \App\Services\LabResults\Exceptions\OrderItemNotPendingException
     */
    public function enter(string $tenantId, ResultInput $input, int $enteredByUserId): LabResult;
}