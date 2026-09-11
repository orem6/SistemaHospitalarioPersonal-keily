<?php

namespace App\Domain\LabResults\Policy;

use App\Domain\LabResults\Exception\MuestraNoAceptadaException;
use App\Domain\LabResults\Exception\MuestraRechazadaException;
use App\Domain\LabResults\Exception\ResultadoYaRegistradoException;
use App\Domain\LabResults\Model\EstadoAceptacion;
use App\Domain\LabResults\Model\MuestraSnapshot;
use App\Domain\LabResults\Model\VersionResultado;

/**
 * REGLA CENTRAL DEL MÓDULO:
 * solo se ingresan resultados para muestras ACEPTADAS.
 */
final class PoliticaIngresoResultado
{
    public static function assertMuestraPermiteIngreso(MuestraSnapshot $muestra): void
    {
        if ($muestra->estadoAceptacion->permiteIngresarResultados()) {
            return;
        }

        if ($muestra->estadoAceptacion === EstadoAceptacion::Rechazada) {
            throw new MuestraRechazadaException($muestra);
        }

        throw new MuestraNoAceptadaException($muestra);
    }

    /** La captura inicial solo procede si no existe resultado vigente. */
    public static function assertSinResultadoVigente(?VersionResultado $vigente, string $barcode): void
    {
        if ($vigente !== null) {
            throw new ResultadoYaRegistradoException($barcode);
        }
    }
}
