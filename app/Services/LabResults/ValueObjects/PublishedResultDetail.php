<?php

namespace App\Services\LabResults\ValueObjects;

/**
 * Vista de solo lectura para UC-05: resultado publicado consultado por el médico.
 */
final readonly class PublishedResultDetail
{
    public function __construct(
        public int $labResultId,
        public string $orderCode,
        public string $testName,
        public ?string $unit,
        public ?string $numericValue,
        public ?string $textValue,
        public bool $isCritical,
        public bool $isAbnormal,
        public string $resultedAt,
        public string $publishedAt,
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return get_object_vars($this);
    }
}