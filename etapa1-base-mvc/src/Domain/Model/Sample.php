<?php

declare(strict_types=1);

namespace LabResults\Domain\Model;

/**
 * Muestra clínica del hospital local.
 *
 * tenant_id y patientRef son UUID lógicos:
 * patientRef apunta a un sistema CENTRAL únicamente como referencia,
 * sin clave foránea remota (propiedad de datos: HOSPITAL).
 */
final readonly class Sample
{
    public function __construct(
        public string $id,
        public string $tenantId,
        public string $patientRef,
        public string $barcode,
        public int $testId,
        public SampleStatus $status,
        public ?string $rejectionReason,
        public ?string $collectedAt,
    ) {
    }
}
