<?php

namespace App\Domain\LabResults\Exception;

/**
 * EVIDENCIA OBLIGATORIA 3: protege el caso de corregir algo inexistente;
 * la corrección válida crea una NUEVA versión conservando la anterior.
 */
final class ResultadoInexistenteException extends DomainRuleException
{
    public function __construct(int $muestraId)
    {
        parent::__construct(
            "La muestra {$muestraId} no tiene un resultado vigente que pueda corregirse."
        );
    }
}
