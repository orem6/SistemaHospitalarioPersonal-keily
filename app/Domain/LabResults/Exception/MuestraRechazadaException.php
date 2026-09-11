<?php

namespace App\Domain\LabResults\Exception;

use App\Domain\LabResults\Model\MuestraSnapshot;

/**
 * EVIDENCIA OBLIGATORIA 1: una muestra rechazada no permite
 * ingresar resultados; el rechazo es controlado y trazable.
 */
final class MuestraRechazadaException extends DomainRuleException
{
    public function __construct(MuestraSnapshot $muestra)
    {
        $motivo = $muestra->motivoRechazo ?? 'sin motivo registrado';

        parent::__construct(
            "La muestra {$muestra->barcode} está RECHAZADA (motivo: {$motivo}); "
            . 'no se pueden ingresar resultados para muestras rechazadas.'
        );
    }
}
