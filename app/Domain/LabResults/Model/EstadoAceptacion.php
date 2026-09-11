<?php

namespace App\Domain\LabResults\Model;

/**
 * Estado de aceptación de una muestra según lo registrado en el hospital.
 *
 * REGLA CENTRAL DEL MÓDULO:
 * solo el estado ACEPTADA permite ingresar resultados.
 * Pendiente cubre tanto "sin decisión registrada" (null) como cualquier
 * valor no reconocido, porque en ambos casos NO se permite capturar.
 */
enum EstadoAceptacion: string
{
    case Pendiente = 'PENDIENTE';
    case Aceptada = 'ACEPTADA';
    case Rechazada = 'RECHAZADA';

    public static function desdeValor(?string $valor): self
    {
        if ($valor === null || trim($valor) === '') {
            return self::Pendiente;
        }

        return self::tryFrom(strtoupper(trim($valor))) ?? self::Pendiente;
    }

    public function permiteIngresarResultados(): bool
    {
        return $this === self::Aceptada;
    }
}
