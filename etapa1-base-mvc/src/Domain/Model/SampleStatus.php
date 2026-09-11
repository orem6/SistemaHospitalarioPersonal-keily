<?php

declare(strict_types=1);

namespace LabResults\Domain\Model;

/**
 * Estados posibles de una muestra dentro del módulo.
 *
 * REGLA CENTRAL DEL MÓDULO:
 * solo el estado ACEPTADA permite ingresar resultados.
 */
enum SampleStatus: string
{
    case Pendiente = 'PENDIENTE';
    case Aceptada = 'ACEPTADA';
    case Rechazada = 'RECHAZADA';

    public function permiteIngresarResultados(): bool
    {
        return $this === self::Aceptada;
    }
}
