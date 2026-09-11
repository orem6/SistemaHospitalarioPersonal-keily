<?php

declare(strict_types=1);

namespace LabResults\Application\UseCase;

use LabResults\Application\Command\IngresarResultadoCommand;
use LabResults\Domain\Exception\MuestraNoEncontradaException;
use LabResults\Domain\Model\ContenidoResultado;
use LabResults\Domain\Model\ResultVersion;
use LabResults\Domain\Policy\ResultEntryPolicy;
use LabResults\Domain\Repository\LabTestRepositoryInterface;
use LabResults\Domain\Repository\ResultVersionRepositoryInterface;
use LabResults\Domain\Repository\SampleRepositoryInterface;
use LabResults\Domain\Service\ResultContentValidator;

/**
 * CASO 1 del flujo — Captura de un resultado:
 * muestra aceptada -> validación tipo/unidad -> versión 1 almacenada.
 *
 * Coordina el dominio; no contiene reglas de negocio ni SQL.
 */
final class IngresarResultadoUseCase
{
    public function __construct(
        private readonly SampleRepositoryInterface $muestras,
        private readonly LabTestRepositoryInterface $pruebas,
        private readonly ResultVersionRepositoryInterface $resultados,
        private readonly ResultContentValidator $validador,
    ) {
    }

    public function ejecutar(IngresarResultadoCommand $comando): ResultVersion
    {
        $muestra = $this->muestras->findById($comando->muestraId)
            ?? throw new MuestraNoEncontradaException($comando->muestraId);

        // Regla central: solo muestras aceptadas (rechazada => excepción controlada).
        ResultEntryPolicy::assertMuestraPermiteIngreso($muestra);

        // La captura inicial solo procede si la muestra está pendiente de captura.
        $vigente = $this->resultados->findCurrentBySampleId($muestra->id);
        ResultEntryPolicy::assertSinResultadoVigente($vigente, $muestra->barcode);

        $definicion = $this->pruebas->findById($muestra->testId);
        if ($definicion === null) {
            throw new \RuntimeException('La definición de la prueba no existe en el catálogo local.');
        }

        // Validación de contenido: parseo + reglas de tipo/unidad del dominio.
        $contenido = ContenidoResultado::crear(
            $comando->tipo,
            $comando->valorNumerico,
            $comando->valorTexto,
            $comando->unidad
        );
        $this->validador->validar($definicion, $contenido);

        // Persistencia como versión 1 inmutable.
        $version = ResultVersion::capturaInicial($muestra, $contenido);
        $id = $this->resultados->guardar($version);

        return $this->resultados->findById($id) ?? $version;
    }
}
