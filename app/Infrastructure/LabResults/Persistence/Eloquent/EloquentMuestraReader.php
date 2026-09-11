<?php

namespace App\Infrastructure\LabResults\Persistence\Eloquent;

use App\Domain\LabResults\Contract\MuestraReaderInterface;
use App\Domain\LabResults\Model\EstadoAceptacion;
use App\Domain\LabResults\Model\MuestraSnapshot;
use App\Models\Sample;

/**
 * Adaptador Eloquent/PostgreSQL del puerto MuestraReaderInterface.
 * Solo consultas; aquí NO viven reglas de negocio.
 *
 * El tenant proviene del contexto resuelto por TenantMiddleware
 * (misma convención que el resto del proyecto).
 */
final class EloquentMuestraReader implements MuestraReaderInterface
{
    public function obtenerPorId(int $muestraId): ?MuestraSnapshot
    {
        /** @var Sample|null $sample */
        $sample = Sample::query()->find($muestraId);

        return $sample === null ? null : $this->mapear($sample);
    }

    public function pendientesDeCaptura(): array
    {
        $tenantId = $this->tenantActual();

        return Sample::query()
            ->where('tenant_id', $tenantId)
            ->where('acceptance_status', EstadoAceptacion::Aceptada->value)
            ->whereDoesntHave('resultVersions')
            ->orderBy('collected_at')
            ->get()
            ->map($this->mapear(...))
            ->all();
    }

    private function tenantActual(): string
    {
        /** @var \App\Models\Tenant|null $tenant */
        $tenant = app()->bound('currentTenant') ? app('currentTenant') : null;

        if ($tenant === null) {
            throw new \RuntimeException('No hay un tenant en el contexto (X-Tenant-ID).');
        }

        return (string) $tenant->getKey();
    }

    private function mapear(Sample $sample): MuestraSnapshot
    {
        return new MuestraSnapshot(
            id: (int) $sample->getKey(),
            tenantId: (string) $sample->tenant_id,
            barcode: (string) $sample->barcode,
            pruebaId: $sample->lab_test_id !== null ? (int) $sample->lab_test_id : null,
            estadoAceptacion: EstadoAceptacion::desdeValor($sample->acceptance_status),
            motivoRechazo: $sample->rejection_reason,
            collectadaEn: $sample->collected_at?->toDateTimeString(),
        );
    }
}
