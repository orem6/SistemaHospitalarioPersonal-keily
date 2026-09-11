<?php
/**
 * Vista: formulario de corrección controlada.
 * Variables esperadas: $historial = ['muestra'=>Sample,'prueba'=>?LabTestDefinition,'versiones'=>[],'vigente'=>?ResultVersion]
 */
$titulo = 'Corregir resultado';
$muestra = $historial['muestra'];
$prueba = $historial['prueba'];
$vigente = $historial['vigente'];

$tipoActual = $vigente?->contenido->tipo->value ?? ($prueba?->resultType->value ?? 'NUMERICO');
$valorNumericoActual = $vigente?->contenido->valorNumerico;
$valorTextoActual = $vigente?->contenido->valorTexto;
$unidadActual = $vigente?->contenido->unidad ?? $prueba?->unit;
?>
<a class="volver" href="?action=pendientes">&larr; Volver a pendientes</a>

<h1>Corrección controlada — <?= htmlspecialchars($muestra->barcode, ENT_QUOTES, 'UTF-8') ?></h1>

<?php if ($vigente === null): ?>
    <p>Esta muestra no tiene un resultado vigente que se pueda corregir.</p>
<?php else: ?>
<p>Versión vigente: <strong>v<?= $vigente->versionNumber ?></strong>
 · La corrección creará la versión <strong>v<?= $vigente->versionNumber + 1 ?></strong>
 · La versión anterior <strong>NUNCA</strong> se sobrescribe ni se elimina.</p>

<form method="post" action="?action=corregir">
    <input type="hidden" name="muestra_id" value="<?= htmlspecialchars($muestra->id, ENT_QUOTES, 'UTF-8') ?>">

    <label for="motivo">Motivo de la corrección (obligatorio)</label>
    <input type="text" name="motivo" id="motivo" placeholder="Ej.: Error de transcripción en la captura">

    <label for="tipo">Tipo de resultado</label>
    <select name="tipo" id="tipo" required>
        <option value="NUMERICO" <?= $tipoActual === 'NUMERICO' ? 'selected' : '' ?>>NUMERICO</option>
        <option value="TEXTO" <?= $tipoActual === 'TEXTO' ? 'selected' : '' ?>>TEXTO</option>
    </select>

    <label for="valor_numerico">Valor numérico (solo para NUMERICO)</label>
    <input type="text" name="valor_numerico" id="valor_numerico"
           value="<?= $valorNumericoActual === null ? '' : htmlspecialchars((string) $valorNumericoActual, ENT_QUOTES, 'UTF-8') ?>">

    <label for="unidad">Unidad (obligatoria para NUMERICO)</label>
    <input type="text" name="unidad" id="unidad"
           value="<?= htmlspecialchars((string) $unidadActual, ENT_QUOTES, 'UTF-8') ?>">

    <label for="valor_texto">Valor textual (solo para TEXTO)</label>
    <input type="text" name="valor_texto" id="valor_texto"
           value="<?= htmlspecialchars((string) $valorTextoActual, ENT_QUOTES, 'UTF-8') ?>">

    <button type="submit">Registrar corrección (nueva versión)</button>
</form>

<p><a href="?action=historial&amp;muestra=<?= urlencode($muestra->id) ?>">Ver historial de versiones</a></p>
<?php endif; ?>
</main></body></html>
