<?php

declare(strict_types=1);

namespace LabResults\Domain\Exception;

/**
 * Corrección idéntica al resultado vigente: no tiene sentido
 * crear una versión nueva con exactamente el mismo contenido.
 */
final class ResultadoSinCambiosException extends DomainRuleException
{
    public function __construct()
    {
        parent::__construct(
            'La corrección es idéntica al resultado vigente; no se creará una versión nueva sin cambios.'
        );
    }
}
