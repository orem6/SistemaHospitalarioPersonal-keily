<?php

namespace App\Domain\LabResults\Contract;

use App\Domain\LabResults\Model\MuestraSnapshot;

/**
 * Puerto del dominio: necesidades del módulo sobre las muestras.
 *
 * Repository Pattern — el contrato representa SOLO lo que este módulo
 * necesita (no un CRUD genérico):
 *  - verificar el estado de la muestra;
 *  - consultar resultados pendientes (aceptadas sin resultado).
 */
interface MuestraReaderInterface
{
    public function obtenerPorId(int $muestraId): ?MuestraSnapshot;

    /**
     * Muestras ACEPTADAS sin ninguna versión de resultado.
     *
     * @return MuestraSnapshot[]
     */
    public function pendientesDeCaptura(): array;
}
