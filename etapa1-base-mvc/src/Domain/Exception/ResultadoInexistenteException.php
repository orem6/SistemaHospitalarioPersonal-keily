<?php

declare(strict_types=1);

namespace LabResults\Domain\Exception;

/**
 * EVIDENCIA OBLIGATORIA 3 del módulo:
 * la corrección genera una NUEVA versión sin sobrescribir la anterior.
 * Esta excepción protege el caso de intentar corregir algo que no existe.
 */
final class ResultadoInexistenteException extends DomainRuleException
{
    public function __construct(string $muestraId)
    {
        parent::__construct(
            "La muestra '{$muestraId}' no tiene un resultado vigente que pueda corregirse."
        );
    }
}
