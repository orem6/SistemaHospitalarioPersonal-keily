<?php

namespace App\Application\LabResults\UseCase;

use App\Domain\LabResults\Contract\MuestraReaderInterface;
use App\Domain\LabResults\Contract\PruebaReaderInterface;
use App\Domain\LabResults\Model\MuestraSnapshot;
use App\Domain\LabResults\Model\PruebaDefinition;

/**
 * Cola de trabajo: muestras ACEPTADAS sin resultado capturado.
 */
final class ListarResultadosPendientesUseCase
{
    public function __construct(
        private readonly MuestraReaderInterface $muestras,
        private readonly PruebaReaderInterface $pruebas,
    ) {
    }

    /**
     * @return array<int, array{
     *     muestra_id: int, barcode: string, prueba_id: ?int,
     *     prueba_nombre: ?string, tipo_esperado: ?string,
     *     unidad_canonica: ?string, collectada_en: ?string
     * }>
     */
    public function ejecutar(): array
    {
        /** @var MuestraSnapshot[] $pendientes */
        $pendientes = $this->muestras->pendientesDeCaptura();

        return array_map(function (MuestraSnapshot $muestra): array {
            $prueba = $muestra->pruebaId !== null
                ? $this->pruebas->obtenerPorId($muestra->pruebaId)
                : null;

            return [
                'muestra_id' => $muestra->id,
                'barcode' => $muestra->barcode,
                'prueba_id' => $muestra->pruebaId,
                'prueba_nombre' => $prueba?->name,
                'tipo_esperado' => $prueba?->tipoEsperado->value,
                'unidad_canonica' => $prueba?->unidad,
                'collectada_en' => $muestra->collectadaEn,
            ];
        }, $pendientes);
    }
}
