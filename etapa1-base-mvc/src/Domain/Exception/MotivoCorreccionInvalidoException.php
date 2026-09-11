<?php

declare(strict_types=1);

namespace LabResults\Domain\Exception;

final class MotivoCorreccionInvalidoException extends DomainRuleException
{
    public function __construct()
    {
        parent::__construct('El motivo de la corrección es obligatorio para mantener la trazabilidad.');
    }
}
