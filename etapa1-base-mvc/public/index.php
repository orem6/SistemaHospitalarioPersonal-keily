<?php

declare(strict_types=1);

/**
 * ASII-19 · Primera etapa — Front Controller web (MVC, sin framework).
 * Enrutamiento simple por query string: ?action=...
 *
 * Ejecutar: php -S localhost:8080 -t public  (desde etapa1-base-mvc/)
 */

require __DIR__ . '/../src/autoload.php';

use LabResults\AppContainer;
use LabResults\Domain\Exception\DomainRuleException;
use LabResults\Persistence\Database\DemoSeeder;
use LabResults\Persistence\Database\SchemaInstaller;

$rutaBaseDatos = __DIR__ . '/../database/lab_results.sqlite';
$pdo = SchemaInstaller::install($rutaBaseDatos);
DemoSeeder::seed($pdo); // datos ficticios idempotentes

$contenedor = AppContainer::conSqlite($rutaBaseDatos);
$controlador = new LabResults\Presentation\Controller\LabResultsController($contenedor);

$action = (string) ($_GET['action'] ?? 'pendientes');

try {
    match ($action) {
        'pendientes' => $controlador->pendientes(),
        'form-ingreso' => $controlador->formularioIngreso((string) ($_GET['muestra'] ?? '')),
        'ingresar' => $controlador->ingresar($_POST),
        'form-correccion' => $controlador->formularioCorreccion((string) ($_GET['muestra'] ?? '')),
        'corregir' => $controlador->corregir($_POST),
        'historial' => $controlador->historial((string) ($_GET['muestra'] ?? '')),
        default => $controlador->pendientes(),
    };
} catch (DomainRuleException $excepcion) {
    http_response_code(422);
    $mensaje = htmlspecialchars($excepcion->getMessage(), ENT_QUOTES, 'UTF-8');
    echo "<!doctype html><meta charset='utf-8'><title>Regla de negocio</title>"
        . "<body style='font-family:sans-serif;background:#fff3f3;padding:2rem'>"
        . "<h1 style='color:#b91c1c'>Rechazo controlado por regla de negocio</h1>"
        . "<p>{$mensaje}</p><a href='?action=pendientes'>&larr; Volver</a></body>";
}
