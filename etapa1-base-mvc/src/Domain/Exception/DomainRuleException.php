<?php

declare(strict_types=1);

namespace LabResults\Domain\Exception;

/**
 * Base de todas las violaciones a reglas de negocio del módulo.
 */
abstract class DomainRuleException extends \RuntimeException
{
}
