<?php

namespace App\Services\LabResults;

use App\Models\LabResult;
use App\Services\LabResults\Contracts\ResultValidator;

/**
 * Valida la coherencia del contenido de un resultado antes de almacenarlo
 * o corregirlo (UC-06). Única responsabilidad: validación de datos.
 */
class StrictResultValidator implements ResultValidator
{
    public function assertValidValues(?string $numericValue, ?string $textValue): void
    {
        $errors = [];

        $hasNumeric = $numericValue !== null && $numericValue !== '';
        $hasText = $textValue !== null && $textValue !== '';

        if (! $hasNumeric && ! $hasText) {
            $errors[] = 'Debe proporcionarse un valor numérico o un valor textual.';
        }

        if ($hasNumeric && $hasText) {
            $errors[] = 'No puede combinarse un valor numérico con un valor textual en el mismo resultado.';
        }

        if ($hasNumeric && ! is_numeric($numericValue)) {
            $errors[] = 'El valor numérico no es un número válido.';
        }

        if ($hasText && mb_strlen($textValue) > 500) {
            $errors[] = 'El valor textual no puede superar 500 caracteres.';
        }

        if ($errors !== []) {
            throw new \App\Services\LabResults\Exceptions\ResultValidationException($errors);
        }
    }
}