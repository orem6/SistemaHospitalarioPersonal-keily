<?php

namespace App\Domain\LabResults\Model;

/**
 * Definición de la prueba de laboratorio que el módulo necesita:
 * tipo de resultado esperado y unidad canónica para la validación.
 */
final readonly class PruebaDefinition
{
    public function __construct(
        public int $id,
        public string $code,
        public string $name,
        public TipoResultado $tipoEsperado,
        public ?string $unidad,
    ) {
    }
}
