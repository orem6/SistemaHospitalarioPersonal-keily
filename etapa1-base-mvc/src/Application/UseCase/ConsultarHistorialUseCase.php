<?php

declare(strict_types=1);

namespace LabResults\Application\UseCase;

use LabResults\Domain\Exception\MuestraNoEncontradaException;
use LabResults\Domain\Model\LabTestDefinition;
use LabResults\Domain\Model\ResultVersion;
use LabResults\Domain\Model\Sample;
use LabResults\Domain\Repository\LabTestRepositoryInterface;
use LabResults\Domain\Repository\ResultVersionRepositoryInterface;
use LabResults\Domain\Repository\SampleRepositoryInterface;

/**
 * Historial completo de versiones de una muestra.
 * Soporta la evidencia "conservación de la versión anterior".
 */
final class ConsultarHistorialUseCase
{
    public function __construct(
        private readonly SampleRepositoryInterface $muestras,
        private readonly LabTestRepositoryInterface $pruebas,
        private readonly ResultVersionRepositoryInterface $resultados,
    ) {
    }

    /**
     * @return array{muestra: Sample, prueba: ?LabTestDefinition, versiones: ResultVersion[], vigente: ?ResultVersion}
     */
    public function ejecutar(string $muestraId): array
    {
        $muestra = $this->muestras->findById($muestraId)
            ?? throw new MuestraNoEncontradaException($muestraId);

        return [
            'muestra' => $muestra,
            'prueba' => $this->pruebas->findById($muestra->testId),
            'versiones' => $this->resultados->listarPorSampleId($muestra->id),
            'vigente' => $this->resultados->findCurrentBySampleId($muestra->id),
        ];
    }
}
