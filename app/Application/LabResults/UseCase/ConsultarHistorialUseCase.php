<?php

namespace App\Application\LabResults\UseCase;

use App\Domain\LabResults\Contract\MuestraReaderInterface;
use App\Domain\LabResults\Exception\MuestraNoEncontradaException;
use App\Domain\LabResults\Model\PruebaDefinition;
use App\Domain\LabResults\Model\VersionResultado;

/**
 * Historial completo de versiones de una muestra
 * (regla 5: la historia permanece disponible).
 */
final class ConsultarHistorialUseCase
{
    public function __construct(
        private readonly MuestraReaderInterface $muestras,
        private readonly \App\Domain\LabResults\Contract\VersionResultadoRepositoryInterface $versiones,
        private readonly \App\Domain\LabResults\Contract\PruebaReaderInterface $pruebas,
    ) {
    }

    /**
     * @return array{muestra_id: int, barcode: string, prueba: ?PruebaDefinition, vigente: ?VersionResultado, versiones: VersionResultado[]}
     */
    public function ejecutar(string $tenantId, int $muestraId): array
    {
        $muestra = $this->muestras->obtenerPorId($muestraId);

        if ($muestra === null || $muestra->tenantId !== $tenantId) {
            throw new MuestraNoEncontradaException($muestraId);
        }

        return [
            'muestra_id' => $muestra->id,
            'barcode' => $muestra->barcode,
            'prueba' => $muestra->pruebaId !== null ? $this->pruebas->obtenerPorId($muestra->pruebaId) : null,
            'vigente' => $this->versiones->vigentePorMuestra($muestra->id),
            'versiones' => $this->versiones->historialPorMuestra($muestra->id),
        ];
    }
}
