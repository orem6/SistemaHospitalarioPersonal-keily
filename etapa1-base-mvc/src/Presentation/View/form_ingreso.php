<?php
/**
 * Vista: formulario de captura de resultado.
 * Variables esperadas: $seleccionada (array|null), $pendientes (array).
 */
$titulo = 'Capturar resultado';
?>
<a class="volver" href="?action=pendientes">&larr; Volver a pendientes</a>

<?php if ($seleccionada === null): ?>
    <p>La muestra no está disponible para captura (debe estar ACEPTADA y sin resultado).</p>
    <table>
        <tr><th>Barcode</th><th>Prueba</th><th></th></tr>
        <?php foreach ($pendientes as $item): ?>
        <tr>
            <td><?= htmlspecialchars($item['barcode'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars($item['prueba_nombre'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><a href="?action=form-ingreso&amp;muestra=<?= urlencode($item['muestra_id']) ?>">Seleccionar</a></td>
        </tr>
        <?php endforeach; ?>
    </table>
<?php else: ?>
<h1>Capturar resultado — <?= htmlspecialchars($seleccionada['barcode'], ENT_QUOTES, 'UTF-8') ?></h1>
<p>Prueba: <strong><?= htmlspecialchars($seleccionada['prueba_nombre'], ENT_QUOTES, 'UTF-8') ?></strong>
   · Tipo esperado: <strong><?= htmlspecialchars($seleccionada['tipo_esperado'], ENT_QUOTES, 'UTF-8') ?></strong>
   · Unidad canónica: <strong><?= htmlspecialchars((string) $seleccionada['unidad_canonica'], ENT_QUOTES, 'UTF-8') ?></strong></p>

<form method="post" action="?action=ingresar">
    <input type="hidden" name="muestra_id" value="<?= htmlspecialchars($seleccionada['muestra_id'], ENT_QUOTES, 'UTF-8') ?>">

    <label for="tipo">Tipo de resultado</label>
    <select name="tipo" id="tipo" required>
        <option value="NUMERICO">NUMERICO</option>
        <option value="TEXTO">TEXTO</option>
    </select>

    <label for="valor_numerico">Valor numérico (solo para tipo NUMERICO)</label>
    <input type="text" name="valor_numerico" id="valor_numerico" placeholder="Ej.: 14.5">

    <label for="unidad">Unidad (obligatoria para NUMERICO; use la unidad canónica)</label>
    <input type="text" name="unidad" id="unidad" placeholder="Ej.: <?= htmlspecialchars((string) $seleccionada['unidad_canonica'], ENT_QUOTES, 'UTF-8') ?>">

    <label for="valor_texto">Valor textual (solo para tipo TEXTO)</label>
    <input type="text" name="valor_texto" id="valor_texto" placeholder="Ej.: Sin crecimiento bacteriano">

    <button type="submit">Guardar resultado (versión 1)</button>
</form>
<?php endif; ?>
</main></body></html>
