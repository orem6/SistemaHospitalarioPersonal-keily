<?php

namespace App\Domain\LabResults\Exception;

final class UnidadInvalidaException extends DomainRuleException
{
    public static function porqueNoCoincide(string $unidadRecibida, string $nombrePrueba, string $unidadEsperada): self
    {
        return new self(
            "La unidad '{$unidadRecibida}' no es válida para la prueba '{$nombrePrueba}'; "
            . "la unidad canónica es {$unidadEsperada}."
        );
    }

    public static function porqueEsObligatoria(string $nombrePrueba): self
    {
        return new self(
            "La unidad es obligatoria para resultados numéricos de la prueba '{$nombrePrueba}'."
        );
    }
}
