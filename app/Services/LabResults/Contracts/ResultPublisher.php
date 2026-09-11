<?php

namespace App\Services\LabResults\Contracts;

use App\Models\LabResult;

/**
 * UC-04 — Publicar resultado validado (cliente: Técnico de Laboratorio).
 *
 * Contrato de escritura que solo conoce cómo publicar un resultado; no
 * conoce captura ni corrección.
 */
interface ResultPublisher
{
    /**
     * @throws \App\Services\LabResults\Exceptions\LabResultNotFoundException
     * @throws \App\Services\LabResults\Exceptions\ResultAlreadyPublishedException
     */
    public function publish(string $tenantId, int $labResultId, int $publishedByUserId): LabResult;
}