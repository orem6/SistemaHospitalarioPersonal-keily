<?php
/**
 * Vista: cola de resultados pendientes + estado de todas las muestras.
 * Variables esperadas: $pendientes (array), $todas (Sample[]).
 */
$titulo = 'Resultados pendientes';
?>
<h1>Resultados pendientes de captura</h1>
<p>Muestras <strong>ACEPTADAS</strong> sin resultado capturado. Las muestras rechazadas o pendientes no aparecen aquí porque no admiten resultados.</p>

<?php if ($pendientes === []): ?>
    <p>No hay resultados pendientes.</p>
<?php else: ?>
<table>
    <tr>
        <th>Barcode</th><th>Prueba</th><th>Tipo esperado</th><th>Unidad canónica</th><th>Recolectada</th><th></th>
    </tr>
    <?php foreach ($pendientes as $item): ?>
    <tr>
        <td><?= htmlspecialchars($item['barcode'], ENT_QUOTES, 'UTF-8') ?></td>
        <td><?= htmlspecialchars($item['prueba_nombre'], ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars($item['prueba_codigo'], ENT_QUOTES, 'UTF-8') ?>)</td>
        <td><?= htmlspecialchars($item['tipo_esperado'], ENT_QUOTES, 'UTF-8') ?></td>
        <td><?= htmlspecialchars((string) $item['unidad_canonica'], ENT_QUOTES, 'UTF-8') ?></td>
        <td><?= htmlspecialchars((string) $item['collected_at'], ENT_QUOTES, 'UTF-8') ?></td>
        <td><a href="?action=form-ingreso&amp;muestra=<?= urlencode($item['muestra_id']) ?>">Capturar resultado</a></td>
    </tr>
    <?php endforeach; ?>
</table>
<?php endif; ?>

<h1 style="margin-top:2rem">Todas las muestras</h1>
<table>
    <tr><th>Barcode</th><th>Estado</th><th>Motivo de rechazo</th><th>Paciente (UUID lógico CENTRAL)</th><th>Historial</th></tr>
    <?php foreach ($todas as $muestra): ?>
    <tr>
        <td><?= htmlspecialchars($muestra->barcode, ENT_QUOTES, 'UTF-8') ?></td>
        <td class="estado-<?= $muestra->status->value ?>"><?= $muestra->status->value ?></td>
        <td><?= htmlspecialchars((string) $muestra->rejectionReason, ENT_QUOTES, 'UTF-8') ?></td>
        <td><code><?= htmlspecialchars($muestra->patientRef, ENT_QUOTES, 'UTF-8') ?></code></td>
        <td><a href="?action=historial&amp;muestra=<?= urlencode($muestra->id) ?>">Ver versiones</a></td>
    </tr>
    <?php endforeach; ?>
</table>
</main></body></html>
