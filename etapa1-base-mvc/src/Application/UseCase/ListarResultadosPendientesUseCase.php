<?php

declare(strict_types=1);

namespace LabResults\Application\UseCase;

use LabResults\Domain\Repository\LabTestRepositoryInterface;
use LabResults\Domain\Repository\SampleRepositoryInterface;

/**
 * Lista las muestras ACEPTADAS que aún no tienen resultado capturado:
 * la cola de trabajo del flujo "resultados pendientes".
 */
final class ListarResultadosPendientesUseCase
{
    public function __construct(
        private readonly SampleRepositoryInterface $muestras,
        private readonly LabTestRepositoryInterface $pruebas,
    ) {
    }

    /**
     * @return array<int, array{
     *     muestra_id: string, barcode: string, paciente_ref: string,
     *     prueba_id: int, prueba_codigo: string, prueba_nombre: string,
     *     tipo_esperado: string, unidad_canonica: ?string, collected_at: ?string
     * }>
     */
    public function ejecutar(): array
    {
        $pendientes = [];

        foreach ($this->muestras->listarPendientesDeCaptura() as $muestra) {
            $prueba = $this->pruebas->findById($muestra->testId);
            if ($prueba === null) {
                continue;
            }

            $pendientes[] = [
                'muestra_id' => $muestra->id,
                'barcode' => $muestra->barcode,
                'paciente_ref' => $muestra->patientRef,
                'prueba_id' => $prueba->id,
                'prueba_codigo' => $prueba->code,
                'prueba_nombre' => $prueba->name,
                'tipo_esperado' => $prueba->resultType->value,
                'unidad_canonica' => $prueba->unit,
                'collected_at' => $muestra->collectedAt,
            ];
        }

        return $pendientes;
    }
}
