<?php

declare(strict_types=1);

namespace LabResults\Domain\Exception;

use LabResults\Domain\Model\Sample;

/**
 * Muestra en estado PENDIENTE: todavía no fue aceptada,
 * por lo tanto tampoco admite resultados.
 */
final class MuestraNoAceptadaException extends DomainRuleException
{
    public function __construct(Sample $muestra)
    {
        parent::__construct(
            "La muestra {$muestra->barcode} está en estado {$muestra->status->value}; "
            . 'solo las muestras ACEPTADAS permiten ingresar resultados.'
        );
    }
}
