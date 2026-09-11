<?php

namespace App\Services\LabResults\Exceptions;

use RuntimeException;

/**
 * Un resultado publicado no puede corregirse ni republicarse directamente
 * (regla de negocio de la semana 1).
 */
class ResultAlreadyPublishedException extends RuntimeException
{
    public function __construct(private readonly int $labResultId)
    {
        parent::__construct("El resultado {$labResultId} ya fue publicado.");
    }

    public function getLabResultId(): int
    {
        return $this->labResultId;
    }
}