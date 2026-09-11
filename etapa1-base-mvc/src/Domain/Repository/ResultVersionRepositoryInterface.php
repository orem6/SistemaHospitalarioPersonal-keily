<?php

declare(strict_types=1);

namespace LabResults\Domain\Repository;

use LabResults\Domain\Model\ResultVersion;

/**
 * Puerto del dominio para versiones de resultado.
 * Contrato clave: guardar() SIEMPRE inserta una fila nueva;
 * jamás existe una operación de actualización o borrado,
 * porque las versiones son inmutables.
 */
interface ResultVersionRepositoryInterface
{
    public function guardar(ResultVersion $version): int;

    public function findCurrentBySampleId(string $sampleId): ?ResultVersion;

    /** @return ResultVersion[] ordenadas por número de versión ascendente */
    public function listarPorSampleId(string $sampleId): array;

    public function findById(int $id): ?ResultVersion;
}
