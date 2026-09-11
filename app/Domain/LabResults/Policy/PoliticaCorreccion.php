<?php

namespace App\Domain\LabResults\Policy;

use App\Domain\LabResults\Exception\MotivoCorreccionInvalidoException;
use App\Domain\LabResults\Exception\ResultadoInexistenteException;
use App\Domain\LabResults\Model\VersionResultado;

/**
 * REGLA CENTRAL DEL MÓDULO:
 * una corrección controlada exige resultado vigente y motivo.
 */
final class PoliticaCorreccion
{
    public static function assertMotivoValido(?string $motivo): void
    {
        if (trim((string) $motivo) === '') {
            throw new MotivoCorreccionInvalidoException();
        }
    }

    public static function assertExisteResultadoActual(?VersionResultado $vigente, int $muestraId): void
    {
        if ($vigente === null) {
            throw new ResultadoInexistenteException($muestraId);
        }
    }
}
