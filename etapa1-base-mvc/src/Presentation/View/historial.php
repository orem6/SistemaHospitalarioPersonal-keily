<?php
/**
 * Vista: historial de versiones (evidencia de corrección versionada).
 * Variables esperadas: $historial = ['muestra','prueba','versiones'=>ResultVersion[],'vigente'=>?ResultVersion]
 */
$titulo = 'Historial de versiones';
$muestra = $historial['muestra'];
$prueba = $historial['prueba'];
$versiones = $historial['versiones'];
$vigente = $historial['vigente'];
?>
<a class="volver" href="?action=pendientes">&larr; Volver a pendientes</a>

<h1>Historial de versiones — <?= htmlspecialchars($muestra->barcode, ENT_QUOTES, 'UTF-8') ?></h1>
<p>
    Prueba: <strong><?= htmlspecialchars($prueba?->name ?? '?', ENT_QUOTES, 'UTF-8') ?></strong>
    · Estado de la muestra: <strong class="estado-<?= $muestra->status->value ?>"><?= $muestra->status->value ?></strong>
    <?php if ($vigente !== null): ?>
        · Versión vigente: <strong>v<?= $vigente->versionNumber ?></strong>
    <?php else: ?>
        · Sin resultado capturado
    <?php endif; ?>
</p>

<?php if ($versiones === []): ?>
<p>No hay versiones registradas.</p>
<?php else: ?>
<table>
    <tr>
        <th>Versión</th><th>Tipo</th><th>Valor numérico</th><th>Unidad</th><th>Texto</th>
        <th>Origen</th><th>Motivo</th><th>Capturada</th><th>Estado</th>
    </tr>
    <?php foreach ($versiones as $v): ?>
    <tr class="<?= $vigente !== null && $v->versionNumber === $vigente->versionNumber ? 'vigente' : 'anterior' ?>">
        <td>v<?= $v->versionNumber ?></td>
        <td><?= $v->contenido->tipo->value ?></td>
        <td><?= $v->contenido->valorNumerico === null ? '—' : $v->contenido->valorNumerico ?></td>
        <td><?= htmlspecialchars((string) $v->contenido->unidad, ENT_QUOTES, 'UTF-8') ?: '—' ?></td>
        <td><?= htmlspecialchars((string) $v->contenido->valorTexto, ENT_QUOTES, 'UTF-8') ?: '—' ?></td>
        <td><?= $v->esCorreccion() ? 'Corrige a v' . $v->correctedFromVersion : 'Captura inicial' ?></td>
        <td><?= htmlspecialchars((string) $v->correctionReason, ENT_QUOTES, 'UTF-8') ?: '—' ?></td>
        <td><?= htmlspecialchars((string) $v->capturedAt, ENT_QUOTES, 'UTF-8') ?></td>
        <td><?= $vigente !== null && $v->versionNumber === $vigente->versionNumber ? 'VIGENTE' : 'conservada' ?></td>
    </tr>
    <?php endforeach; ?>
</table>
<?php endif; ?>

<?php if ($vigente !== null): ?>
<p style="margin-top:1.5rem"><a href="?action=form-correccion&amp;muestra=<?= urlencode($muestra->id) ?>">Corregir resultado (crea nueva versión)</a></p>
<?php endif; ?>
</main></body></html>
