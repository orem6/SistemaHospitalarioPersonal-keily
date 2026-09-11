<?php

namespace App\Domain\LabResults\Service;

use App\Domain\LabResults\Exception\TipoResultadoInvalidoException;
use App\Domain\LabResults\Exception\UnidadInvalidaException;
use App\Domain\LabResults\Model\ContenidoResultado;
use App\Domain\LabResults\Model\PruebaDefinition;
use App\Domain\LabResults\Model\TipoResultado;

/**
 * REGLA CENTRAL DEL MÓDULO (EVIDENCIA 2 — valor inválido):
 * valida la coherencia del contenido con la definición de la prueba:
 * tipo correcto y unidad válida cuando corresponde.
 */
final class ValidadorContenidoResultado
{
    public function validar(PruebaDefinition $definicion, ContenidoResultado $contenido): void
    {
        if ($definicion->tipoEsperado !== $contenido->tipo) {
            throw TipoResultadoInvalidoException::porqueNoCorrespondeALaPrueba(
                $contenido->tipo->value,
                $definicion->name,
                $definicion->tipoEsperado->value
            );
        }

        if ($contenido->tipo === TipoResultado::Numerico) {
            $this->validarUnidadNumerica($definicion, $contenido);
        }
    }

    private function validarUnidadNumerica(PruebaDefinition $definicion, ContenidoResultado $contenido): void
    {
        if ($contenido->unidad === null) {
            throw UnidadInvalidaException::porqueEsObligatoria($definicion->name);
        }

        if ($definicion->unidad !== null && strcasecmp($contenido->unidad, $definicion->unidad) !== 0) {
            throw UnidadInvalidaException::porqueNoCoincide(
                $contenido->unidad,
                $definicion->name,
                $definicion->unidad
            );
        }
    }
}
