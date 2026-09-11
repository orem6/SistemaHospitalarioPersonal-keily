<?php

namespace App\Domain\LabResults\Contract;

use App\Domain\LabResults\Model\VersionResultado;

/**
 * Puerto del dominio para resultados VERSIONADOS.
 *
 * Repository Pattern — operaciones del módulo:
 *  - registrar un resultado (siempre INSERTA una versión nueva);
 *  - obtener la versión vigente;
 *  - obtener el historial completo de versiones;
 *  - obtener una versión puntual.
 *
 * GARANTÍA: no existe operación de actualización ni borrado;
 * las filas son inmutables (append-only).
 */
interface VersionResultadoRepositoryInterface
{
    public function registrar(VersionResultado $version): int;

    public function vigentePorMuestra(int $muestraId): ?VersionResultado;

    /** @return VersionResultado[] ordenadas por numeroVersion ascendente */
    public function historialPorMuestra(int $muestraId): array;

    public function obtenerPorId(int $id): ?VersionResultado;
}
