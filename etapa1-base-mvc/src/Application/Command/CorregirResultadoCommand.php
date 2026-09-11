<?php

declare(strict_types=1);

namespace LabResults\Application\Command;

/**
 * Entrada del caso de uso "Corregir resultado".
 * El motivo es obligatorio para la trazabilidad de la versión nueva.
 */
final readonly class CorregirResultadoCommand
{
    public function __construct(
        public string $muestraId,
        public string $motivo,
        public string $tipo,
        public ?string $valorNumerico,
        public ?string $valorTexto,
        public ?string $unidad,
    ) {
    }
}
