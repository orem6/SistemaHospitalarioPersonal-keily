<?php

declare(strict_types=1);

namespace LabResults\Domain\Exception;

final class TipoResultadoInvalidoException extends DomainRuleException
{
    public static function porqueNoCorrespondeALaPrueba(
        string $tipoRecibido,
        string $nombrePrueba,
        string $tipoEsperado,
    ): self {
        return new self(
            "El tipo de resultado '{$tipoRecibido}' no es válido para la prueba '{$nombrePrueba}'; "
            . "esta prueba requiere resultados de tipo {$tipoEsperado}."
        );
    }
}
