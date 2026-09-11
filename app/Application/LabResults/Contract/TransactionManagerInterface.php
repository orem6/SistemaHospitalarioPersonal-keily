<?php

namespace App\Application\LabResults\Contract;

/**
 * Puerto para coordinar transacciones desde Application.
 * Infrastructure lo implementa con el mecanismo del framework
 * (DB::transaction), manteniendo los casos de uso desacoplados.
 */
interface TransactionManagerInterface
{
    /**
     * Ejecuta $operacion dentro de una transacción y devuelve su resultado.
     *
     * @template T
     *
     * @param  callable(): T  $operacion
     * @return T
     */
    public function ejecutar(callable $operacion): mixed;
}
