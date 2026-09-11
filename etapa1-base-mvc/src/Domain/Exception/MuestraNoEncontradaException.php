<?php

declare(strict_types=1);

namespace LabResults\Domain\Exception;

final class MuestraNoEncontradaException extends DomainRuleException
{
    public function __construct(string $muestraId)
    {
        parent::__construct("No existe una muestra con el identificador '{$muestraId}'.");
    }
}
