<?php

namespace App\Domain\LabResults\Contract;

use App\Domain\LabResults\Model\PruebaDefinition;

/**
 * Puerto del dominio: catálogo de pruebas para validar tipo/unidad.
 */
interface PruebaReaderInterface
{
    public function obtenerPorId(int $pruebaId): ?PruebaDefinition;
}
