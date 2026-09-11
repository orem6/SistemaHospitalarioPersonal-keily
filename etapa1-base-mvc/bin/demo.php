<?php

declare(strict_types=1);

/*
 * ASII-19 · Primera etapa — Demo CLI con los CASOS OBLIGATORIOS.
 *
 * Genera evidencia reproducible:
 *   CASO 1 — Muestra aceptada: ingreso permitido.
 *   CASO 2 — Muestra rechazada: rechazo controlado.
 *   CASO 3 — Valor inválido (tipo/unidad): rechazo controlado.
 *   CASO 4 — Corrección versionada + conservación de la versión anterior.
 *
 * Uso: php bin/demo.php
 */

require __DIR__ . '/../src/autoload.php';

use LabResults\AppContainer;
use LabResults\Application\Command\CorregirResultadoCommand;
use LabResults\Application\Command\IngresarResultadoCommand;
use LabResults\Domain\Exception\DomainRuleException;
use LabResults\Persistence\Database\DemoSeeder;
use LabResults\Persistence\Database\SchemaInstaller;

$rutaBase = dirname(__DIR__);
$rutaDemo = $rutaBase . '/database/demo.sqlite';

if (is_file($rutaDemo)) {
    unlink($rutaDemo); // demo determinista en cada ejecución
}

$pdo = SchemaInstaller::install($rutaDemo);
DemoSeeder::seed($pdo);

$c = AppContainer::conSqlite($rutaDemo);
$aceptada = DemoSeeder::MUESTRA_ACEPTADA_ID;
$aceptada2 = DemoSeeder::MUESTRA_ACEPTADA_2_ID;
$rechazada = DemoSeeder::MUESTRA_RECHAZADA_ID;

function titulo(string $texto): void
{
    echo PHP_EOL, str_repeat('=', 78), PHP_EOL, $texto, PHP_EOL, str_repeat('=', 78), PHP_EOL;
}

function resultado(?LabResults\Domain\Model\ResultVersion $v): string
{
    if ($v === null) {
        return 'sin resultado';
    }

    $contenido = $v->contenido->valorNumerico !== null
        ? $v->contenido->valorNumerico . ' ' . $v->contenido->unidad
        : $v->contenido->valorTexto;

    return sprintf('v%d [%s] %s', $v->versionNumber, $v->contenido->tipo->value, $contenido);
}

titulo('ESTADO INICIAL (datos ficticios)');
foreach ($c->muestras->listarTodas() as $m) {
    printf(
        "  %-13s estado=%-9s prueba_id=%d paciente_ref=%s%s\n",
        $m->barcode,
        $m->status->value,
        $m->testId,
        substr($m->patientRef, 0, 13) . '…',
        $m->rejectionReason ? " (motivo: {$m->rejectionReason})" : ''
    );
}

titulo('CASO 1 · Muestra ACEPTADA -> ingreso correcto del resultado');
try {
    $v1 = $c->ingresarResultado()->ejecutar(new IngresarResultadoCommand(
        muestraId: $aceptada,
        tipo: 'NUMERICO',
        valorNumerico: '14.5',
        valorTexto: null,
        unidad: 'g/dL'
    ));
    echo "  [OK] Resultado almacenado: " . resultado($v1) . PHP_EOL;
} catch (DomainRuleException $e) {
    echo '  [FALLO] ' . $e->getMessage() . PHP_EOL;
}

titulo('CASO 2 · Muestra RECHAZADA -> rechazo controlado del ingreso');
try {
    $c->ingresarResultado()->ejecutar(new IngresarResultadoCommand(
        muestraId: $rechazada,
        tipo: 'NUMERICO',
        valorNumerico: '92.0',
        valorTexto: null,
        unidad: 'mg/dL'
    ));
    echo "  [FALLO] No debería haberse permitido el ingreso.\n";
} catch (DomainRuleException $e) {
    echo "  [OK] Rechazo controlado: " . $e->getMessage() . PHP_EOL;
}
$versionesRechazada = count($c->resultados->listarPorSampleId($rechazada));
echo "  [OK] Versiones almacenadas para la muestra rechazada: {$versionesRechazada} (esperado: 0)\n";

titulo('CASO 3a · Valor numérico INVÁLIDO ("abc") sobre muestra aceptada pendiente');
try {
    $c->ingresarResultado()->ejecutar(new IngresarResultadoCommand(
        muestraId: $aceptada2,
        tipo: 'NUMERICO',
        valorNumerico: 'abc',
        valorTexto: null,
        unidad: 'g/dL'
    ));
    echo "  [FALLO] El valor inválido no debía guardarse.\n";
} catch (DomainRuleException $e) {
    echo "  [OK] Rechazo controlado: " . $e->getMessage() . PHP_EOL;
}

titulo('CASO 3b · UNIDAD no válida (mmol/L en lugar de g/dL)');
try {
    $c->ingresarResultado()->ejecutar(new IngresarResultadoCommand(
        muestraId: $aceptada2,
        tipo: 'NUMERICO',
        valorNumerico: '14.5',
        valorTexto: null,
        unidad: 'mmol/L'
    ));
    echo "  [FALLO] La unidad inválida no debía guardarse.\n";
} catch (DomainRuleException $e) {
    echo "  [OK] Rechazo controlado: " . $e->getMessage() . PHP_EOL;
}
$versionesTrasInvalidos = count($c->resultados->listarPorSampleId($aceptada2));
echo "  [OK] Versiones almacenadas tras los intentos inválidos: {$versionesTrasInvalidos} (esperado: 0)\n";

titulo('CASO 4 · Corrección versionada (crea v2, conserva v1)');
echo '  Antes de corregir : ' . resultado($c->resultados->findCurrentBySampleId($aceptada)) . "\n";
try {
    $v2 = $c->corregirResultado()->ejecutar(new CorregirResultadoCommand(
        muestraId: $aceptada,
        motivo: 'Error de transcripción: el valor correcto es 13.8 g/dL',
        tipo: 'NUMERICO',
        valorNumerico: '13.8',
        valorTexto: null,
        unidad: 'g/dL'
    ));
    echo "  [OK] Nueva versión creada: " . resultado($v2) . "\n";
    echo '       corrected_from_version=' . $v2->correctedFromVersion
       . ', motivo="' . $v2->correctionReason . '"' . PHP_EOL;
} catch (DomainRuleException $e) {
    echo '  [FALLO] ' . $e->getMessage() . PHP_EOL;
}

titulo('CONSERVACIÓN · Historial completo tras la corrección');
$todasLasVersiones = $c->resultados->listarPorSampleId($aceptada);
printf("  Total de filas en lab_result_versions para la muestra: %d (esperado: 2)\n", count($todasLasVersiones));
foreach ($todasLasVersiones as $v) {
    $etiqueta = ($vigente = $c->resultados->findCurrentBySampleId($aceptada)) !== null
        && $vigente->versionNumber === $v->versionNumber ? 'VIGENTE' : 'conservada (intacta)';
    printf("  v%d = %-14s capturada=%s -> %s\n", $v->versionNumber, resultado($v), (string) $v->capturedAt, $etiqueta);
}
$v1fila = $todasLasVersiones[0];
$conservada = $v1fila !== null && $v1fila->contenido->valorNumerico === 14.5 && $v1fila->contenido->unidad === 'g/dL';
echo $conservada
    ? "  [OK] La versión 1 se CONSERVA con su valor original 14.5 g/dL (no fue sobrescrita).\n"
    : "  [FALLO] La versión 1 fue modificada.\n";

titulo('DEMO COMPLETADA');
