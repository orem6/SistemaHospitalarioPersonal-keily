<?php

declare(strict_types=1);

namespace LabResults\Domain\Exception;

use LabResults\Domain\Model\Sample;

/**
 * EVIDENCIA OBLIGATORIA 1 del módulo:
 * una muestra RECHAZADA no permite ingresar resultados.
 */
final class MuestraRechazadaException extends DomainRuleException
{
    public function __construct(Sample $muestra)
    {
        $motivo = $muestra->rejectionReason ?? 'sin motivo registrado';

        parent::__construct(
            "La muestra {$muestra->barcode} está RECHAZADA (motivo: {$motivo}); "
            . 'no se pueden ingresar resultados para muestras rechazadas.'
        );
    }
}
