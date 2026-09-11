<?php

namespace App\Domain\LabResults\Model;

/**
 * Versión INMUTABLE de un resultado de laboratorio.
 *
 * REGLA CENTRAL DEL MÓDULO:
 * una corrección NUNCA sobrescribe la versión anterior; se crea
 * una fila nueva con version_number + 1 y corrige_a_version apuntando
 * a la versión que origina la corrección. El historial completo
 * permanece disponible.
 */
final readonly class VersionResultado
{
    public function __construct(
        public ?int $id,
        public int $muestraId,
        public int $pruebaId,
        public string $tenantId,
        public int $numeroVersion,
        public ContenidoResultado $contenido,
        public ?int $corrigeAVersion,
        public ?string $motivoCorreccion,
        public bool $esAnormal,
        public bool $esCritico,
        public ?string $resultadaEn,
    ) {
    }

    /** Captura inicial: siempre es la versión 1. */
    public static function capturaInicial(MuestraSnapshot $muestra, ContenidoResultado $contenido, string $resultadaEn): self
    {
        return new self(
            null,
            $muestra->id,
            (int) $muestra->pruebaId,
            $muestra->tenantId,
            1,
            $contenido,
            null,
            null,
            false,
            false,
            $resultadaEn,
        );
    }

    /**
     * Corrección controlada: crea la versión n + 1 conservando
     * intacta la versión anterior.
     */
    public static function correccion(
        MuestraSnapshot $muestra,
        self $versionAnterior,
        ContenidoResultado $nuevoContenido,
        string $motivo,
        string $resultadaEn,
    ): self {
        return new self(
            null,
            $muestra->id,
            $versionAnterior->pruebaId,
            $muestra->tenantId,
            $versionAnterior->numeroVersion + 1,
            $nuevoContenido,
            $versionAnterior->numeroVersion,
            trim($motivo),
            false,
            false,
            $resultadaEn,
        );
    }

    public function esCorreccion(): bool
    {
        return $this->corrigeAVersion !== null;
    }
}
