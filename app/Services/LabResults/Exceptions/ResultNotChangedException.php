<?php

namespace App\Services\LabResults\Exceptions;

use RuntimeException;

/**
 * No hay cambios que registrar en la corrección (UC-03).
 */
class ResultNotChangedException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('No se detectaron cambios sobre el resultado.');
    }
}