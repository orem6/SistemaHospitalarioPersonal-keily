<?php

namespace App\Domain\LabResults\Exception;

final class MuestraNoEncontradaException extends DomainRuleException
{
    public function __construct(int $muestraId)
    {
        parent::__construct("No existe una muestra con el identificador {$muestraId} en este hospital.");
    }
}
