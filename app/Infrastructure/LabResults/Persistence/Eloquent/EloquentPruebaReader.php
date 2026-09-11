<?php

namespace App\Infrastructure\LabResults\Persistence\Eloquent;

use App\Domain\LabResults\Contract\PruebaReaderInterface;
use App\Domain\LabResults\Model\PruebaDefinition;
use App\Domain\LabResults\Model\TipoResultado;
use App\Models\LabTest;

/**
 * Adaptador Eloquent/PostgreSQL del catálogo de pruebas.
 */
final class EloquentPruebaReader implements PruebaReaderInterface
{
    public function obtenerPorId(int $pruebaId): ?PruebaDefinition
    {
        /** @var LabTest|null $test */
        $test = LabTest::query()->find($pruebaId);

        if ($test === null) {
            return null;
        }

        $tipo = $test->result_type
            ?? TipoResultado::Numerico->value;

        return new PruebaDefinition(
            id: (int) $test->getKey(),
            code: (string) ($test->code ?? $test->getKey()),
            name: (string) $test->name,
            tipoEsperado: TipoResultado::tryFrom((string) $tipo) ?? TipoResultado::Numerico,
            unidad: $test->unit,
        );
    }
}
