<?php

declare(strict_types=1);

namespace LabResults\Application\UseCase;

use LabResults\Application\Command\CorregirResultadoCommand;
use LabResults\Domain\Exception\MuestraNoEncontradaException;
use LabResults\Domain\Exception\ResultadoSinCambiosException;
use LabResults\Domain\Model\ContenidoResultado;
use LabResults\Domain\Model\ResultVersion;
use LabResults\Domain\Policy\CorrectionPolicy;
use LabResults\Domain\Repository\LabTestRepositoryInterface;
use LabResults\Domain\Repository\ResultVersionRepositoryInterface;
use LabResults\Domain\Repository\SampleRepositoryInterface;
use LabResults\Domain\Service\ResultContentValidator;

/**
 * CASO 4 del flujo — Corrección controlada:
 * resultado existente -> validación -> NUEVA versión (n + 1).
 * La versión anterior se conserva intacta (filas inmutables).
 */
final class CorregirResultadoUseCase
{
    public function __construct(
        private readonly SampleRepositoryInterface $muestras,
        private readonly LabTestRepositoryInterface $pruebas,
        private readonly ResultVersionRepositoryInterface $resultados,
        private readonly ResultContentValidator $validador,
    ) {
    }

    public function ejecutar(CorregirResultadoCommand $comando): ResultVersion
    {
        // El motivo es obligatorio para la trazabilidad.
        CorrectionPolicy::assertMotivoValido($comando->motivo);

        $muestra = $this->muestras->findById($comando->muestraId)
            ?? throw new MuestraNoEncontradaException($comando->muestraId);

        // Debe existir un resultado vigente que corregir.
        $vigente = $this->resultados->findCurrentBySampleId($muestra->id);
        CorrectionPolicy::assertExisteResultadoActual($vigente, $muestra->id);

        $definicion = $this->pruebas->findById($muestra->testId);
        if ($definicion === null) {
            throw new \RuntimeException('La definición de la prueba no existe en el catálogo local.');
        }

        $nuevoContenido = ContenidoResultado::crear(
            $comando->tipo,
            $comando->valorNumerico,
            $comando->valorTexto,
            $comando->unidad
        );
        $this->validador->validar($definicion, $nuevoContenido);

        if ($vigente->contenido->igualA($nuevoContenido)) {
            throw new ResultadoSinCambiosException();
        }

        // Nueva versión (n + 1) ligada a la anterior; la fila previa nunca se toca.
        $nueva = ResultVersion::correccion($muestra, $vigente, $nuevoContenido, $comando->motivo);
        $id = $this->resultados->guardar($nueva);

        return $this->resultados->findById($id) ?? $nueva;
    }
}
