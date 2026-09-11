<?php

declare(strict_types=1);

namespace LabResults\Application\Command;

/**
 * Entrada del caso de uso "Ingresar resultado".
 * Contiene valores crudos; el parseo y las reglas viven en Domain.
 */
final readonly class IngresarResultadoCommand
{
    public function __construct(
        public string $muestraId,
        public string $tipo,
        public ?string $valorNumerico,
        public ?string $valorTexto,
        public ?string $unidad,
    ) {
    }
}
