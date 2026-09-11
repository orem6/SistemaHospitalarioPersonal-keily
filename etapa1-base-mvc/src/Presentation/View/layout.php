<?php

declare(strict_types=1);

/**
 * Layout mínimo compartido por las vistas (CAPA PRESENTATION).
 * $titulo se define en cada vista antes de incluirla.
 */
$titulo = $titulo ?? 'Ingreso de resultados de laboratorio';
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title><?= htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8') ?></title>
<style>
    body { font-family: system-ui, sans-serif; margin: 0; background: #f8fafc; color: #0f172a; }
    header { background: #1e3a5f; color: #fff; padding: 1rem 2rem; }
    header small { display: block; opacity: .75; }
    main { max-width: 960px; margin: 2rem auto; padding: 0 1rem; }
    table { border-collapse: collapse; width: 100%; background: #fff; }
    th, td { border: 1px solid #cbd5e1; padding: .5rem .75rem; text-align: left; font-size: .92rem; }
    th { background: #e2e8f0; }
    .estado-ACEPTADA { color: #15803d; font-weight: 600; }
    .estado-RECHAZADA { color: #b91c1c; font-weight: 600; }
    .estado-PENDIENTE { color: #b45309; font-weight: 600; }
    .vigente { background: #ecfdf5; }
    .anterior { color: #64748b; }
    form { background: #fff; padding: 1.25rem; border: 1px solid #cbd5e1; }
    label { display: block; margin-top: .9rem; font-weight: 600; }
    input[type=text], select { width: 100%; max-width: 420px; padding: .45rem; }
    button { margin-top: 1.1rem; padding: .55rem 1.3rem; background: #1d4ed8; color: #fff; border: 0; cursor: pointer; }
    .ok { background: #dcfce7; border: 1px solid #15803d; padding: .75rem 1rem; margin-bottom: 1rem; }
    a.volver { display: inline-block; margin-bottom: 1rem; }
</style>
</head>
<body>
<header>
    <strong>SHI · Módulo: Ingreso de resultados de laboratorio</strong>
    <small>Primera etapa — base/MVC en PHP vanilla · Datos ficticios · Estudiante: Keily Fabiola Orellana Marroquín (orem6)</small>
</header>
<main>
<?php if (($_GET['ok'] ?? '') === 'ingreso'): ?>
    <p class="ok">Resultado capturado correctamente como versión 1.</p>
<?php elseif (($_GET['ok'] ?? '') === 'correccion'): ?>
    <p class="ok">Corrección registrada: se creó una NUEVA versión; la anterior quedó conservada.</p>
<?php endif; ?>
