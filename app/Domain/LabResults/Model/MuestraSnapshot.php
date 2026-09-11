<?php

namespace App\Domain\LabResults\Model;

/**
 * Proyección inmutable de la muestra que necesita el módulo.
 * La persistencia real (Eloquent/PostgreSQL) vive en Infrastructure;
 * el dominio solo conoce este snapshot.
 */
final readonly class MuestraSnapshot
{
    public function __construct(
        public int $id,
        public string $tenantId,
        public string $barcode,
        public ?int $pruebaId,
        public EstadoAceptacion $estadoAceptacion,
        public ?string $motivoRechazo,
        public ?string $collectadaEn,
    ) {
    }
}
