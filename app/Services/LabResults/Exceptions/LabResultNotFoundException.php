<?php

namespace App\Services\LabResults\Exceptions;

use RuntimeException;

/**
 * El resultado no existe dentro del tenant autenticado.
 */
class LabResultNotFoundException extends RuntimeException
{
    public function __construct(private readonly int $labResultId)
    {
        parent::__construct("El resultado {$labResultId} no existe.");
    }

    public function getLabResultId(): int
    {
        return $this->labResultId;
    }
}