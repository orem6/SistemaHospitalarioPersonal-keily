<?php

declare(strict_types=1);

namespace LabResults\Domain\Policy;

use LabResults\Domain\Exception\MuestraNoAceptadaException;
use LabResults\Domain\Exception\MuestraRechazadaException;
use LabResults\Domain\Exception\ResultadoYaRegistradoException;
use LabResults\Domain\Model\ResultVersion;
use LabResults\Domain\Model\Sample;
use LabResults\Domain\Model\SampleStatus;

/**
 * REGLA CENTRAL DEL MÓDULO:
 * solo se ingresan resultados para muestras ACEPTADAS.
 */
final class ResultEntryPolicy
{
    public static function assertMuestraPermiteIngreso(Sample $muestra): void
    {
        if ($muestra->status->permiteIngresarResultados()) {
            return;
        }

        if ($muestra->status === SampleStatus::Rechazada) {
            throw new MuestraRechazadaException($muestra);
        }

        throw new MuestraNoAceptadaException($muestra);
    }

    /** La captura inicial solo procede si no existe resultado vigente. */
    public static function assertSinResultadoVigente(?ResultVersion $vigente, string $barcode): void
    {
        if ($vigente !== null) {
            throw new ResultadoYaRegistradoException($barcode);
        }
    }
}
