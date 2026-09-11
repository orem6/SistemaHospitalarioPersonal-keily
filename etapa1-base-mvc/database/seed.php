<?php

declare(strict_types=1);

/*
 * ASII-19 · Primera etapa — Datos semilla 100% FICTICIOS.
 * Uso: php database/seed.php [ruta_sqlite] [--fresh]
 */

require __DIR__ . '/../src/autoload.php';

use LabResults\Persistence\Database\DemoSeeder;
use LabResults\Persistence\Database\SchemaInstaller;

$ruta = $argv[1] ?? __DIR__ . '/lab_results.sqlite';
$fresh = in_array('--fresh', $argv, true);

if ($fresh && is_file($ruta)) {
    unlink($ruta);
}

$pdo = SchemaInstaller::install($ruta);
DemoSeeder::seed($pdo);

echo "Base lista en {$ruta}" . PHP_EOL;
