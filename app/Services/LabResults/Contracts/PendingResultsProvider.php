<?php

namespace App\Services\LabResults\Contracts;

use App\Services\LabResults\ValueObjects\PendingResultDetail;

/**
 * UC-01 — Consultar resultados pendientes (cliente: Técnico de Laboratorio).
 *
 * Contrato de solo lectura que expone únicamente los exámenes pendientes
 * de captura dentro del tenant autenticado.
 */
interface PendingResultsProvider
{
    /**
     * @return list<PendingResultDetail> Exámenes sin resultado capturado aún.
     */
    public function listPendingForEntry(string $tenantId): array;
}