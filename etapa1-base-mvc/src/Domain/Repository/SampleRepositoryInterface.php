<?php

declare(strict_types=1);

namespace LabResults\Domain\Repository;

use LabResults\Domain\Model\Sample;

/**
 * Puerto del dominio: el caso de uso NO conoce PDO ni SQL.
 * La implementación concreta vive en la capa Persistence.
 */
interface SampleRepositoryInterface
{
    public function findById(string $id): ?Sample;

    /**
     * Resultados pendientes de captura = muestras ACEPTADAS
     * que todavía no tienen ninguna versión de resultado.
     *
     * @return Sample[]
     */
    public function listarPendientesDeCaptura(): array;

    /** @return Sample[] */
    public function listarTodas(): array;
}
