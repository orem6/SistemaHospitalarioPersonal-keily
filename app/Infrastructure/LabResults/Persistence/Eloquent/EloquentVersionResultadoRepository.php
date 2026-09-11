<?php

namespace App\Infrastructure\LabResults\Persistence\Eloquent;

use App\Domain\LabResults\Contract\VersionResultadoRepositoryInterface;
use App\Domain\LabResults\Model\ContenidoResultado;
use App\Domain\LabResults\Model\TipoResultado;
use App\Domain\LabResults\Model\VersionResultado;
use App\Models\LabResultVersion;

/**
 * Adaptador Eloquent/PostgreSQL del repositorio de versiones.
 *
 * GARANTÍA DE INMUTABILIDAD (Repository Pattern real):
 * esta clase solo ejecuta INSERT y SELECT. No existe ninguna
 * operación UPDATE ni DELETE sobre lab_result_versions, de modo que
 * una corrección jamás puede sobrescribir la versión anterior.
 */
final class EloquentVersionResultadoRepository implements VersionResultadoRepositoryInterface
{
    public function registrar(VersionResultado $version): int
    {
        /** @var LabResultVersion $fila */
        $fila = LabResultVersion::query()->create([
            'tenant_id' => $version->tenantId,
            'sample_id' => $version->muestraId,
            'lab_test_id' => $version->pruebaId,
            'version_number' => $version->numeroVersion,
            'result_type' => $version->contenido->tipo->value,
            'numeric_value' => $version->contenido->valorNumerico,
            'text_value' => $version->contenido->valorTexto,
            'unit' => $version->contenido->unidad,
            'is_abnormal' => $version->esAnormal,
            'is_critical' => $version->esCritico,
            'corrected_from_version' => $version->corrigeAVersion,
            'correction_reason' => $version->motivoCorreccion,
            'resulted_at' => $version->resultadaEn,
        ]);

        return (int) $fila->getKey();
    }

    public function vigentePorMuestra(int $muestraId): ?VersionResultado
    {
        /** @var LabResultVersion|null $fila */
        $fila = LabResultVersion::query()
            ->where('sample_id', $muestraId)
            ->orderByDesc('version_number')
            ->first();

        return $fila === null ? null : $this->mapear($fila);
    }

    public function historialPorMuestra(int $muestraId): array
    {
        return LabResultVersion::query()
            ->where('sample_id', $muestraId)
            ->orderBy('version_number')
            ->get()
            ->map($this->mapear(...))
            ->all();
    }

    public function obtenerPorId(int $id): ?VersionResultado
    {
        /** @var LabResultVersion|null $fila */
        $fila = LabResultVersion::query()->find($id);

        return $fila === null ? null : $this->mapear($fila);
    }

    private function mapear(LabResultVersion $fila): VersionResultado
    {
        return new VersionResultado(
            id: (int) $fila->getKey(),
            muestraId: (int) $fila->sample_id,
            pruebaId: (int) $fila->lab_test_id,
            tenantId: (string) $fila->tenant_id,
            numeroVersion: (int) $fila->version_number,
            contenido: ContenidoResultado::desdeAlmacenamiento(
                TipoResultado::from((string) $fila->result_type),
                $fila->numeric_value !== null ? (float) $fila->numeric_value : null,
                $fila->text_value,
                $fila->unit,
            ),
            corrigeAVersion: $fila->corrected_from_version,
            motivoCorreccion: $fila->correction_reason,
            esAnormal: (bool) $fila->is_abnormal,
            esCritico: (bool) $fila->is_critical,
            resultadaEn: $fila->resulted_at?->toDateTimeString(),
        );
    }
}
