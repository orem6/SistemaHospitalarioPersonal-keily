<?php

namespace App\Domain\LabResults\Exception;

final class ResultadoYaRegistradoException extends DomainRuleException
{
    public function __construct(string $barcode)
    {
        parent::__construct(
            "La muestra '{$barcode}' ya tiene un resultado vigente; "
            . 'utilice la corrección controlada para generar una nueva versión.'
        );
    }
}
