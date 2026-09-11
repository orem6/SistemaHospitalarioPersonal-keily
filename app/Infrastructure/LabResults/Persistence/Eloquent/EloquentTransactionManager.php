<?php

namespace App\Infrastructure\LabResults\Persistence\Eloquent;

use App\Application\LabResults\Contract\TransactionManagerInterface;
use Illuminate\Support\Facades\DB;

/**
 * Adaptador concreto del puerto de transacciones usando el mecanismo
 * del framework (soporta PostgreSQL y SQLite).
 */
final class EloquentTransactionManager implements TransactionManagerInterface
{
    public function ejecutar(callable $operacion): mixed
    {
        return DB::transaction($operacion);
    }
}
