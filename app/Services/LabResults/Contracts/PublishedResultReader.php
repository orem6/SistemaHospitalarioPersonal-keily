<?php

namespace App\Services\LabResults\Contracts;

use App\Services\LabResults\ValueObjects\PublishedResultDetail;

/**
 * UC-05 — Consultar resultado publicado (cliente: Médico).
 *
 * Contrato de solo lectura. El médico nunca ve escritura, corrección ni
 * publicación; únicamente accede a resultados ya publicados de su tenant.
 */
interface PublishedResultReader
{
    public function findPublishedForTenant(string $tenantId, int $labResultId): ?PublishedResultDetail;
}