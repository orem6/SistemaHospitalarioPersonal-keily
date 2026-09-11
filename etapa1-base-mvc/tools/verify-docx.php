<?php

declare(strict_types=1);

// Verifica que el .docx generado sea un paquete OOXML válido y bien formado.
$ruta = $argv[1] ?? dirname(__DIR__, 2) . '/Documento-Etapa1-Base-MVC-ASII19.docx';

$zip = new ZipArchive();
if ($zip->open($ruta, ZipArchive::RDONLY) !== true) {
    fwrite(STDERR, "ZIP inválido\n");
    exit(1);
}

$partes = ['[Content_Types].xml', '_rels/.rels', 'word/document.xml', 'docProps/core.xml'];
foreach ($partes as $parte) {
    $contenido = $zip->getFromName($parte);
    if ($contenido === false) {
        fwrite(STDERR, "Falta la parte: {$parte}\n");
        exit(1);
    }
    simplexml_load_string($contenido);
    echo "OK · {$parte} (" . strlen($contenido) . " bytes)\n";
}

$xml = simplexml_load_string($zip->getFromName('word/document.xml'));
$parrafos = $xml->xpath('//w:p');
$tablas = $xml->xpath('//w:tbl');
echo "Párrafos: " . count($parrafos) . " · Tablas: " . count($tablas) . "\n";

// Muestra los títulos principales detectados.
foreach ($xml->xpath('//w:p') as $p) {
    $texto = trim((string) implode('', array_map(fn($n) => (string) $n, $p->xpath('.//w:t'))));
    if (preg_match('/^(SISTEMA|MÓDULO|PRIMERA|\d\. |Índice|Anexo)/u', $texto)) {
        echo "  - {$texto}\n";
    }
}
$zip->close();
