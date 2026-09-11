<?php

namespace App\Application\LabResults\Command;

/**
 * Entrada del caso de uso "Corregir resultado".
 * El motivo es obligatorio para la trazabilidad de la versión nueva.
 */
final readonly class CorregirResultadoCommand
{
    public function __construct(
        public string $tenantId,
        public int $muestraId,
        public int $corregidoPor,
        public string $motivo,
        public string $tipo,
        public ?string $valorNumerico,
        public ?string $valorTexto,
        public ?string $unidad,
    ) {
    }
}
