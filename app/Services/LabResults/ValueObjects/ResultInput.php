<?php

namespace App\Services\LabResults\ValueObjects;

/**
 * Datos inmutables para UC-02: captura de un resultado pendiente.
 */
final readonly class ResultInput
{
    public function __construct(
        public int $labOrderItemId,
        public int $sampleId,
        public ?string $numericValue,
        public ?string $textValue,
    ) {
    }
}