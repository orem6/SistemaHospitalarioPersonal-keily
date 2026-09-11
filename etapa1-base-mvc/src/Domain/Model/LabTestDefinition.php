<?php

declare(strict_types=1);

namespace LabResults\Domain\Model;

/**
 * Definición (catálogo) de la prueba de laboratorio.
 * Determina el tipo de resultado esperado y la unidad canónica.
 */
final readonly class LabTestDefinition
{
    public function __construct(
        public int $id,
        public string $code,
        public string $name,
        public ResultType $resultType,
        public ?string $unit,
    ) {
    }
}
