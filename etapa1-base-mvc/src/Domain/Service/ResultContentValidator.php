<?php

declare(strict_types=1);

namespace LabResults\Domain\Service;

use LabResults\Domain\Exception\TipoResultadoInvalidoException;
use LabResults\Domain\Exception\UnidadInvalidaException;
use LabResults\Domain\Model\ContenidoResultado;
use LabResults\Domain\Model\LabTestDefinition;
use LabResults\Domain\Model\ResultType;

/**
 * REGLA CENTRAL DEL MÓDULO (EVIDENCIA 2 — valor inválido):
 * valida que el contenido del resultado sea coherente con la
 * definición de la prueba: tipo correcto y unidad válida.
 */
final class ResultContentValidator
{
    public function validar(LabTestDefinition $definicion, ContenidoResultado $contenido): void
    {
        if ($definicion->resultType !== $contenido->tipo) {
            throw TipoResultadoInvalidoException::porqueNoCorrespondeALaPrueba(
                $contenido->tipo->value,
                $definicion->name,
                $definicion->resultType->value
            );
        }

        if ($contenido->tipo === ResultType::Numerico) {
            $this->validarUnidadNumerica($definicion, $contenido);
        }
    }

    private function validarUnidadNumerica(LabTestDefinition $definicion, ContenidoResultado $contenido): void
    {
        if ($contenido->unidad === null) {
            throw UnidadInvalidaException::porqueEsObligatoria($definicion->name);
        }

        if ($definicion->unit !== null && strcasecmp($contenido->unidad, $definicion->unit) !== 0) {
            throw UnidadInvalidaException::porqueNoCoincide(
                $contenido->unidad,
                $definicion->name,
                $definicion->unit
            );
        }
    }
}
