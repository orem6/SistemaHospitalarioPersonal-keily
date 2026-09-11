<?php

namespace App\Services\LabResults\ValueObjects;

/**
 * Vista de solo lectura para UC-01: exámenes pendientes de captura.
 */
final readonly class PendingResultDetail
{
    public function __construct(
        public int $labOrderItemId,
        public string $orderCode,
        public string $patientName,
        public string $testName,
        public string $priority,
        public ?string $sampleBarcode,
        public string $orderedAt,
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return get_object_vars($this);
    }
}