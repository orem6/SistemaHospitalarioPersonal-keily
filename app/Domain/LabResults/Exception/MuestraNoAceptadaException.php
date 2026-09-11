<?php

namespace App\Domain\LabResults\Exception;

use App\Domain\LabResults\Model\MuestraSnapshot;

/**
 * Muestra sin decisión de aceptación registrada (pendiente):
 * tampoco admite resultados.
 */
final class MuestraNoAceptadaException extends DomainRuleException
{
    public function __construct(MuestraSnapshot $muestra)
    {
        parent::__construct(
            "La muestra {$muestra->barcode} aún no está ACEPTADA; "
            . 'solo las muestras aceptadas permiten ingresar resultados.'
        );
    }
}
