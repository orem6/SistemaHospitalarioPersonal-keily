<?php

declare(strict_types=1);

namespace LabResults\Domain\Policy;

use LabResults\Domain\Exception\MotivoCorreccionInvalidoException;
use LabResults\Domain\Exception\ResultadoInexistenteException;
use LabResults\Domain\Model\ResultVersion;

/**
 * REGLA CENTRAL DEL MÓDULO:
 * una corrección controlada requiere un resultado vigente y un motivo.
 */
final class CorrectionPolicy
{
    public static function assertMotivoValido(?string $motivo): void
    {
        if (trim((string) $motivo) === '') {
            throw new MotivoCorreccionInvalidoException();
        }
    }

    public static function assertExisteResultadoActual(?ResultVersion $vigente, string $muestraId): void
    {
        if ($vigente === null) {
            throw new ResultadoInexistenteException($muestraId);
        }
    }
}
