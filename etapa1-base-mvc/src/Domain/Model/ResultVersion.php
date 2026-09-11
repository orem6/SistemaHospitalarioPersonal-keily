<?php

declare(strict_types=1);

namespace LabResults\Domain\Model;

/**
 * Versión inmutable de un resultado de laboratorio.
 *
 * REGLA CENTRAL DEL MÓDULO:
 * una corrección NUNCA sobrescribe la versión anterior; se crea
 * una fila nueva con version_number + 1 y corrected_from_version
 * apuntando a la versión que origina la corrección.
 */
final readonly class ResultVersion
{
    public function __construct(
        public ?int $id,
        public string $sampleId,
        public int $testId,
        public string $tenantId,
        public int $versionNumber,
        public ContenidoResultado $contenido,
        public ?int $correctedFromVersion,
        public ?string $correctionReason,
        public ?string $capturedAt,
    ) {
    }

    /** Captura inicial: siempre es la versión 1. */
    public static function capturaInicial(Sample $muestra, ContenidoResultado $contenido): self
    {
        return new self(
            null,
            $muestra->id,
            $muestra->testId,
            $muestra->tenantId,
            1,
            $contenido,
            null,
            null,
            null
        );
    }

    /**
     * Corrección controlada: crea una versión nueva (n + 1)
     * conservando intacta la versión anterior.
     */
    public static function correccion(
        Sample $muestra,
        ResultVersion $versionAnterior,
        ContenidoResultado $nuevoContenido,
        string $motivo,
    ): self {
        return new self(
            null,
            $muestra->id,
            $muestra->testId,
            $muestra->tenantId,
            $versionAnterior->versionNumber + 1,
            $nuevoContenido,
            $versionAnterior->versionNumber,
            trim($motivo),
            null
        );
    }

    public function esCorreccion(): bool
    {
        return $this->correctedFromVersion !== null;
    }
}
