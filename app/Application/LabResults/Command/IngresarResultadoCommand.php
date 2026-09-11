<?php

namespace App\Application\LabResults\Command;

/**
 * Entrada del caso de uso "Ingresar resultado" (valores crudos;
 * el parseo y las reglas viven en Domain).
 */
final readonly class IngresarResultadoCommand
{
    public function __construct(
        public string $tenantId,
        public int $muestraId,
        public int $ingresadoPor,
        public string $tipo,
        public ?string $valorNumerico,
        public ?string $valorTexto,
        public ?string $unidad,
    ) {
    }
}
