<?php

namespace App\Services\LabResults\Exceptions;

use InvalidArgumentException;

/**
 * La información del resultado no superó la validación previa al
 * almacenamiento (UC-06 "validar información").
 */
class ResultValidationException extends InvalidArgumentException
{
    /** @param list<string> $errors */
    public function __construct(private readonly array $errors)
    {
        parent::__construct('La información del resultado no es válida.');
    }

    /** @return list<string> */
    public function errors(): array
    {
        return $this->errors;
    }
}