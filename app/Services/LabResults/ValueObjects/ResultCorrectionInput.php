<?php

namespace App\Services\LabResults\ValueObjects;

/**
 * Datos inmutables para UC-03: corrección controlada de un resultado pendiente.
 */
final readonly class ResultCorrectionInput
{
    public function __construct(
        public int $labResultId,
        public ?string $numericValue,
        public ?string $textValue,
        public ?string $reason,
    ) {
    }
}