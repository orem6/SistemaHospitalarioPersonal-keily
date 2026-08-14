<?php

namespace App\Services\LabResults\Contracts;

/**
 * UC-06 — Validar información (cliente: Sistema / validación previa).
 *
 * Contrato de validación usado por la captura y la corrección antes de
 * almacenar. Las demás responsabilidades (persistir, corregir, publicar)
 * no le pertenecen a este contrato.
 */
interface ResultValidator
{
    /**
     * @throws \App\Services\LabResults\Exceptions\ResultValidationException
     */
    public function assertValidValues(?string $numericValue, ?string $textValue): void;
}