<?php

namespace App\Services\LabResults\Concerns;

use App\Models\LabTest;

/**
 * Cálculo de banderas de interpretación a partir de los metadatos del
 * catálogo de pruebas (integración con ASII-17: rangos normales/críticos).
 */
trait ComputesResultFlags
{
    private function isAbnormal(?LabTest $test, ?float $numeric): bool
    {
        if ($numeric === null || $test === null || $test->reference_min === null || $test->reference_max === null) {
            return false;
        }

        return $numeric < (float) $test->reference_min || $numeric > (float) $test->reference_max;
    }

    private function isCritical(?LabTest $test, ?float $numeric): bool
    {
        if ($numeric === null || $test === null) {
            return false;
        }

        if ($test->critical_min !== null && $numeric < (float) $test->critical_min) {
            return true;
        }

        if ($test->critical_max !== null && $numeric > (float) $test->critical_max) {
            return true;
        }

        return false;
    }
}