<?php

namespace App\Domain\LabResults\Exception;

final class ResultadoSinCambiosException extends DomainRuleException
{
    public function __construct()
    {
        parent::__construct(
            'La corrección es idéntica al resultado vigente; no se creará una versión nueva sin cambios.'
        );
    }
}
