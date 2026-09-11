<?php

declare(strict_types=1);

/*
 * Genera el documento Word (.docx) entregable de la PRIMERA ETAPA.
 * No requiere librerías externas: construye el paquete OOXML con ZipArchive.
 *
 * Uso: php tools/make-docx.php
 * Salida: ../Documento-Etapa1-Base-MVC-ASII19.docx (raíz del repositorio)
 */

require __DIR__ . '/../src/autoload.php';

$rutaRaiz = dirname(__DIR__, 2); // raíz del repositorio
$rutaSalida = $rutaRaiz . '/Documento-Etapa1-Base-MVC-ASII19.docx';
$rutaEvidenciaDemo = __DIR__ . '/../evidencias/demo-casos-obligatorios.txt';
$rutaEvidenciaTests = __DIR__ . '/../evidencias/pruebas-phpunit.txt';

// ------------------------------------------------------------------ utilidades

function esc(string $texto): string
{
    // Saneamiento defensivo: garantiza UTF-8 válido y descarta caracteres de control.
    $texto = mb_convert_encoding($texto, 'UTF-8', 'UTF-8');
    $texto = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/u', '', $texto) ?? '';

    return htmlspecialchars($texto, ENT_XML1 | ENT_QUOTES, 'UTF-8');
}

final class DocxBuilder
{
    /** @var string[] */
    private array $cuerpo = [];

    public function tituloPortada(string $linea1, string $linea2 = ''): void
    {
        $this->parrafo($linea1, ['sz' => 56, 'b' => true], centrado: true, espacioDespues: 240);
        if ($linea2 !== '') {
            $this->parrafo($linea2, ['sz' => 32, 'b' => true, 'color' => '1E3A5F'], centrado: true);
        }
    }

    public function h1(string $t): void
    {
        $this->parrafo($t, ['sz' => 36, 'b' => true, 'color' => '1E3A5F'], espacioAntes: 320, espacioDespues: 160, bordeInferior: true);
    }

    public function h2(string $t): void
    {
        $this->parrafo($t, ['sz' => 28, 'b' => true, 'color' => '334155'], espacioAntes: 240, espacioDespues: 120);
    }

    public function p(string $t, bool $negrita = false): void
    {
        $this->parrafo($t, $negrita ? ['b' => true] : [], espacioDespues: 120);
    }

    public function bullet(string $t): void
    {
        $this->parrafo('•  ' . $t, [], sangriaIzq: 360, espacioDespues: 60);
    }

    public function codigo(string $bloque): void
    {
        foreach (preg_split('/\R/', rtrim($bloque)) as $linea) {
            $this->parrafo($linea === '' ? ' ' : $linea, ['fuente' => 'Consolas', 'sz' => 18], sombreado: 'F1F5F9', espacioDespues: 0);
        }
        $this->espacio();
    }

    public function tabla(array $cabecera, array $filas): void
    {
        $xml = '<w:tbl><w:tblPr><w:tblW w:w="5000" w:type="pct"/><w:tblBorders>'
            . '<w:top w:val="single" w:sz="4" w:color="94A3B8"/><w:left w:val="single" w:sz="4" w:color="94A3B8"/>'
            . '<w:bottom w:val="single" w:sz="4" w:color="94A3B8"/><w:right w:val="single" w:sz="4" w:color="94A3B8"/>'
            . '<w:insideH w:val="single" w:sz="4" w:color="CBD5E1"/><w:insideV w:val="single" w:sz="4" w:color="CBD5E1"/>'
            . '</w:tblBorders></w:tblPr>';

        $xml .= '<w:tr>';
        foreach ($cabecera as $celda) {
            $xml .= '<w:tc><w:tcPr><w:shd w:val="clear" w:fill="E2E8F0"/></w:tcPr>'
                . '<w:p><w:pPr><w:spacing w:after="40"/></w:pPr>'
                . '<w:r><w:rPr><w:b/><w:sz w:val="20"/></w:rPr><w:t xml:space="preserve">' . esc($celda) . '</w:t></w:r></w:p></w:tc>';
        }
        $xml .= '</w:tr>';

        foreach ($filas as $fila) {
            $xml .= '<w:tr>';
            foreach ($fila as $celda) {
                $xml .= '<w:tc><w:p><w:pPr><w:spacing w:after="40"/></w:pPr>'
                    . '<w:r><w:rPr><w:sz w:val="20"/></w:rPr><w:t xml:space="preserve">' . esc($celda) . '</w:t></w:r></w:p></w:tc>';
            }
            $xml .= '</w:tr>';
        }

        $this->cuerpo[] = $xml . '</w:tbl>';
        $this->espacio();
    }

    public function saltoPagina(): void
    {
        $this->cuerpo[] = '<w:p><w:r><w:br w:type="page"/></w:r></w:p>';
    }

    public function espacio(): void
    {
        $this->cuerpo[] = '<w:p/>';
    }

    private function parrafo(
        string $texto,
        array $rpr,
        bool $centrado = false,
        int $sangriaIzq = 0,
        int $espacioAntes = 0,
        int $espacioDespues = 120,
        bool $bordeInferior = false,
        ?string $sombreado = null,
    ): void {
        $ppr = '<w:pPr>';
        if ($centrado) {
            $ppr .= '<w:jc w:val="center"/>';
        }
        if ($sangriaIzq > 0) {
            $ppr .= '<w:ind w:left="' . $sangriaIzq . '"/>';
        }
        $ppr .= '<w:spacing w:before="' . $espacioAntes . '" w:after="' . $espacioDespues . '"/>';
        if ($bordeInferior) {
            $ppr .= '<w:pBdr><w:bottom w:val="single" w:sz="8" w:space="2" w:color="1E3A5F"/></w:pBdr>';
        }
        if ($sombreado !== null) {
            $ppr .= '<w:shd w:val="clear" w:fill="' . $sombreado . '"/>';
        }
        $ppr .= '</w:pPr>';

        $runProps = '';
        if (!empty($rpr['b'])) {
            $runProps .= '<w:b/>';
        }
        $fuente = $rpr['fuente'] ?? null;
        if ($fuente !== null) {
            $runProps .= '<w:rFonts w:ascii="' . $fuente . '" w:hAnsi="' . $fuente . '" w:cs="' . $fuente . '"/>';
        }
        $sz = $rpr['sz'] ?? 22;
        $runProps .= '<w:sz w:val="' . $sz . '"/><w:szCs w:val="' . $sz . '"/>';
        if (!empty($rpr['color'])) {
            $runProps .= '<w:color w:val="' . $rpr['color'] . '"/>';
        }

        $this->cuerpo[] = '<w:p>' . $ppr . '<w:r><w:rPr>' . $runProps . '</w:rPr>'
            . '<w:t xml:space="preserve">' . esc($texto) . '</w:t></w:r></w:p>';
    }

    public function documentoXml(): string
    {
        $sectPr = '<w:sectPr><w:pgSz w:w="11906" w:h="16838"/>'
            . '<w:pgMar w:top="1440" w:right="1440" w:bottom="1440" w:left="1440"/></w:sectPr>';

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">'
            . '<w:body>' . implode('', $this->cuerpo) . $sectPr . '</w:body></w:document>';
    }
}

// ------------------------------------------------------------------ contenido

$d = new DocxBuilder();

// ---------- PORTADA ----------
$d->espacio(); $d->espacio(); $d->espacio(); $d->espacio();
$d->tituloPortada(
    'SISTEMA HOSPITALARIO INTEGRAL (SHI)',
    'MÓDULO: INGRESO DE RESULTADOS DE LABORATORIO'
);
$d->espacio();
$d->p('PRIMERA ETAPA — Construcción de la base/MVC en PHP 8.2 vanilla', true);
$d->p('Arquitectura por capas: Presentation · Application · Domain · Persistence');
$d->espacio(); $d->espacio();
$d->tabla(['Campo', 'Detalle'], [
    ['Estudiante', 'Keily Fabiola Orellana Marroquín'],
    ['GitHub', 'orem6'],
    ['Rama de trabajo', 'feature/asii-19-ingreso-de-resultados-de-laboratorio-orem6'],
    ['Flujo asignado', 'Captura y corrección controlada de resultados pendientes'],
    ['Tecnología', 'PHP 8.2+ vanilla, PDO + SQLite, PHPUnit 11 (sin frameworks)'],
    ['Fecha', '21 de agosto de 2026'],
]);
$d->saltoPagina();

// ---------- ÍNDICE ----------
$d->h1('Índice');
foreach ([
    '1. Introducción',
    '2. Desarrollo',
    '3. Explicación de la arquitectura',
    '4. Explicación del flujo',
    '5. Pruebas',
    '6. Evidencia',
    '7. Conclusión',
    '8. Bibliografía',
    'Anexo A · Declaración de uso de IA (referencia)',
] as $i => $item) {
    $d->bullet($item);
}
$d->saltoPagina();

// ---------- INTRODUCCIÓN ----------
$d->h1('1. Introducción');
$d->p('El Sistema Hospitalario Integral (SHI) se desarrolla de forma federada por módulos verticales. Esta primera etapa construye la base/MVC del módulo “Ingreso de resultados de laboratorio” utilizando exclusivamente PHP 8.2+ vanilla, sin frameworks, con una separación estricta en cuatro capas: Presentation, Application, Domain y Persistence.');
$d->p('El flujo asignado es la captura y corrección controlada de resultados pendientes. La regla central obligatoria del módulo establece que solo se ingresan resultados para muestras aceptadas; se valida el tipo y la unidad del resultado; y toda corrección genera una nueva versión sin sobrescribir ni eliminar la anterior.');
$d->h2('Alcance');
$d->bullet('Captura de resultados sobre muestras con estado ACEPTADA pendientes de captura.');
$d->bullet('Validación de coherencia entre tipo de resultado, valor y unidad contra el catálogo local de pruebas.');
$d->bullet('Corrección versionada: cada corrección inserta la versión n+1 ligada a la anterior mediante corrected_from_version y un motivo obligatorio.');
$d->bullet('Historial completo de versiones siempre disponible.');
$d->h2('Fuera de alcance');
$d->p('Pacientes, citas, medicamentos, admisiones, expedientes, órdenes de laboratorio completas, recepción/aceptación de muestras, validación por bioquímico, RAG/CAG, dashboard, QA general y cualquier otro módulo del SHI. Ninguna funcionalidad ajena al flujo asignado fue implementada.');
$d->h2('Límite de datos');
$d->p('El hospital realiza la escritura clínica/local. Toda referencia hacia un sistema CENTRAL es únicamente un UUID lógico (patient_ref y tenant_id) sin clave foránea remota. Todos los datos utilizados son ficticios: códigos de barras DEM-BAR-0001…0004, UUIDs de demostración y valores numéricos arbitrarios.');
$d->saltoPagina();

// ---------- DESARROLLO ----------
$d->h1('2. Desarrollo');
$d->p('La solución vive en la carpeta etapa1-base-mvc/ del repositorio y se organiza así:');
$d->codigo(<<<TXT
etapa1-base-mvc/
├── bin/demo.php              CLI con los CASOS 1–4 obligatorios (evidencia)
├── database/schema.sql       DDL mínimo: prueba, muestra, versiones de resultado
├── database/seed.php         Datos 100% ficticios (idempotente)
├── public/index.php          Front controller web (Presentation)
├── src/autoload.php          Autoloader PSR-4 propio (sin Composer)
├── src/AppContainer.php      Composition Root
├── src/Domain/               Reglas centrales (sin PDO ni SQL)
├── src/Application/          Casos de uso (ingresar, corregir, listar, historial)
├── src/Persistence/          PDO + consultas preparadas
├── src/Presentation/         Controller + Views (MVC)
└── tests/                    PHPUnit: dominio e integración
TXT);
$d->h2('Modelo de datos mínimo');
$d->bullet('lab_test_definitions: catálogo local ficticio (código, nombre, result_type NUMERICO|TEXTO, unidad canónica).');
$d->bullet('samples: muestra local con id UUID, tenant_id y patient_ref (UUID lógico CENTRAL, sin FK remota), barcode único y estado PENDIENTE | ACEPTADA | RECHAZADA.');
$d->bullet('lab_result_versions: filas inmutables por versión; UNIQUE(sample_id, version_number); columnas corrected_from_version y correction_reason. Las reglas de negocio NO se trasladaron a CHECK de SQL: pertenecen al dominio.');
$d->saltoPagina();

// ---------- ARQUITECTURA ----------
$d->h1('3. Explicación de la arquitectura');
$d->codigo(<<<TXT
Presentation  (index.php, LabResultsController, Views, bin/demo.php)
      ↓ comandos (DTOs inmutables)
Application   (IngresarResultadoUseCase, CorregirResultadoUseCase,
               ListarResultadosPendientesUseCase, ConsultarHistorialUseCase)
      ↓ puertos (interfaces de repositorio definidas en Domain)
Domain        (Sample, ResultVersion, ContenidoResultado, enums,
               políticas, ResultContentValidator, excepciones, contratos)
      ↑ implementación concreta
Persistence   (Connection PDO, PdoSampleRepository, PdoLabTestRepository,
               PdoResultVersionRepository — solo consultas preparadas)
TXT);
$d->tabla(['Capa', 'Responsabilidad', 'Prohibiciones'], [
    ['Presentation', 'Recibir entrada, validar presencia/formato básico, invocar casos de uso, presentar resultados.', 'SQL y reglas centrales de negocio.'],
    ['Application', 'Coordinar el caso de uso y su flujo usando las abstracciones (contratos) del dominio.', 'SQL y HTML.'],
    ['Domain', 'Reglas centrales: muestra aceptada, tipo/unidad válidos, corrección versionada inmutable.', 'Conocer PDO o HTTP.'],
    ['Persistence', 'Persistir con PDO y prepared statements; mapear filas a entidades.', 'Contener reglas de negocio.'],
]);
$d->h2('Reglas del dominio y su ubicación exacta');
$d->tabla(['Regla', 'Clase responsable'], [
    ['Solo muestras ACEPTADAS admiten resultados', 'Domain\\Policy\\ResultEntryPolicy'],
    ['Muestra rechazada ⇒ rechazo controlado', 'Domain\\Exception\\MuestraRechazadaException'],
    ['Tipo de resultado válido según la prueba', 'Domain\\Service\\ResultContentValidator'],
    ['Unidad válida/canónica cuando corresponde', 'ResultContentValidator + ContenidoResultado'],
    ['Corrección crea versión n+1 con motivo', 'Domain\\Model\\ResultVersion::correccion()'],
    ['Versiones inmutables (append-only)', 'PdoResultVersionRepository: solo INSERT/SELECT, sin UPDATE/DELETE'],
]);
$d->p('El Composition Root (AppContainer) es el único punto que conecta dominio con persistencia. Esto deja la base lista para la segunda etapa: los adaptadores PDO podrán sustituirse por repositorios Eloquent/PostgreSQL dentro del SHI Laravel sin reescribir reglas de negocio.', true);
$d->saltoPagina();

// ---------- FLUJO ----------
$d->h1('4. Explicación del flujo');
$d->h2('Camino principal (captura)');
$d->codigo(<<<TXT
Muestra aceptada
  → resultado pendiente (ACEPTADA sin resultado → cola de trabajo)
  → captura del resultado (tipo, valor, unidad)
  → validación de tipo/unidad (ResultContentValidator)
  → almacenamiento como versión 1 inmutable
TXT);
$d->h2('Corrección controlada');
$d->codigo(<<<TXT
Resultado existente (versión n vigente)
  → solicitud de corrección (motivo obligatorio)
  → creación de nueva versión n+1 (corrected_from_version = n)
  → conservación de la versión anterior (nunca se sobrescribe ni elimina)
TXT);
$d->p('Garantía técnica de conservación: el repositorio de versiones no expone ninguna operación UPDATE o DELETE; una corrección es siempre un INSERT adicional. Además, si la corrección es idéntica al resultado vigente, el dominio la rechaza (ResultadoSinCambiosException) para evitar ruido de versiones.');
$d->saltoPagina();

// ---------- PRUEBAS ----------
$d->h1('5. Pruebas');
$d->p('Suite automatizada con PHPUnit 11 (11 pruebas / 39 aserciones, todas aprobadas). Comando de ejecución desde etapa1-base-mvc/:');
$d->codigo('php ../vendor/phpunit/phpunit/phpunit -c phpunit.xml.dist');
$d->h2('Casos cubiertos');
$d->tabla(['#', 'Prueba', 'Regla verificada'], [
    ['D1', 'test_muestra_rechazada_no_permite_ingresar_resultado', 'Muestra rechazada ⇒ rechazo controlado, 0 filas'],
    ['D2', 'test_valor_numerico_invalido_es_rechazado', 'Valor “abc” inválido'],
    ['D3', 'test_unidad_invalida_es_rechazada_y_no_se_persiste', 'Unidad no canónica rechazada'],
    ['D4', 'test_tipo_incompatible_con_la_prueba_es_rechazado', 'Tipo TEXTO sobre prueba NUMERICO'],
    ['D5', 'test_correccion_crea_version_nueva_sin_sobrescribir_la_anterior', 'v2 creada, v1 intacta'],
    ['D6', 'test_correccion_identica_al_vigente_es_rechazada', 'Sin cambios reales ⇒ sin versión'],
    ['I1', 'test_1_ingresa_resultado_para_muestra_aceptada', 'Flujo: pendiente → capturado'],
    ['I2', 'test_2_rechaza_resultado_para_muestra_rechazada', 'Flujo: rechazo controlado'],
    ['I3', 'test_3_rechaza_unidad_invalida', 'Flujo: valor inválido'],
    ['I4', 'test_4_correccion_genera_nueva_version', 'Flujo: versionado n+1'],
    ['I5', 'test_5_version_anterior_permanece_intacta', 'Conservación vs instantánea previa'],
]);
$d->saltoPagina();

// ---------- EVIDENCIA ----------
$d->h1('6. Evidencia');
$d->p('Las salidas siguientes corresponden a ejecuciones reales registradas durante la sesión de desarrollo (archivos en etapa1-base-mvc/evidencias/). Cada bloque se relaciona con una regla o criterio de aceptación.');

$d->h2('6.1 Muestra aceptada — ingreso correcto (CASO 1)');
$d->p('Regla: solo se ingresan resultados para muestras aceptadas.');
$demo = is_file($rutaEvidenciaDemo) ? file_get_contents($rutaEvidenciaDemo) : '(demo no disponible)';
$secciones = preg_split('/={78}\R/', (string) $demo);

$extraer = function (string $prefijo) use ($secciones): string {
    foreach ($secciones as $s) {
        if (str_starts_with(trim($s), $prefijo)) {
            return trim($s);
        }
    }
    return '(sección no encontrada)';
};

$d->codigo($extraer('ESTADO INICIAL'));
$d->codigo($extraer('CASO 1'));

$d->h2('6.2 Muestra rechazada — rechazo controlado (CASO 2)');
$d->p('Regla: una muestra rechazada no permite ingresar resultados (evidencia mínima 1).');
$d->codigo($extraer('CASO 2'));

$d->h2('6.3 Valor inválido — tipo/unidad (CASOS 3a y 3b)');
$d->p('Regla: se valida tipo/unidad; los valores inválidos no se persisten (evidencia mínima 2).');
$d->codigo($extraer('CASO 3a'));
$d->codigo($extraer('CASO 3b'));

$d->h2('6.4 Corrección versionada (CASO 4)');
$d->p('Regla: una corrección genera una nueva versión sin sobrescribir la anterior (evidencia mínima 3).');
$d->codigo($extraer('CASO 4'));

$d->h2('6.5 Conservación de la versión anterior');
$d->p('Regla: la versión anterior nunca se sobrescribe ni se elimina.');
$d->codigo($extraer('CONSERVACIÓN'));

$d->h2('6.6 Ejecución de pruebas');
$testsOut = is_file($rutaEvidenciaTests) ? trim((string) file_get_contents($rutaEvidenciaTests)) : '(pruebas no disponibles)';
$d->codigo($testsOut);
$d->saltoPagina();

// ---------- CONCLUSIÓN ----------
$d->h1('7. Conclusión');
$d->p('La primera etapa queda operativa y verificada: el módulo permite capturar resultados únicamente sobre muestras aceptadas, valida el tipo y la unidad contra el catálogo local, produce rechazos controlados ante muestras rechazadas o valores inválidos, y materializa cada corrección como una nueva versión conservando íntegra la anterior (tabla append-only sin UPDATE/DELETE).');
$d->p('La separación por capas y los contratos de repositorio definidos en el dominio permiten evolucionar directamente a la segunda etapa: Repository Pattern sobre PostgreSQL dentro del proyecto SHI Laravel, reutilizando las mismas reglas de negocio y criterios de aceptación.');
$d->h2('Trabajo futuro (segunda etapa)');
$d->bullet('Adaptar los puertos Domain\\Repository a repositorios Eloquent/PostgreSQL del SHI.');
$d->bullet('Migraciones reversibles, factories y seeders ficticios en PostgreSQL.');
$d->bullet('Pruebas de integración contra PostgreSQL y documentación ADR/UML.');

// ---------- BIBLIOGRAFÍA ----------
$d->h1('8. Bibliografía');
foreach ([
    'PHP Documentation Group. PHP Manual — PDO (PHP Data Objects). https://www.php.net/manual/es/book.pdo.php',
    'PHP Documentation Group. PHP Manual — Enums. https://www.php.net/manual/es/language.enumerations.php',
    'Fowler, M. (2002). Patterns of Enterprise Application Architecture. Addison-Wesley.',
    'Martin, R. C. (2017). Clean Architecture: A Craftsman’s Guide to Software Structure and Design. Prentice Hall.',
    'Evans, E. (2003). Domain-Driven Design: Tackling Complexity in the Heart of Software. Addison-Wesley.',
    'PHPUnit Project. The PHPUnit Testing Framework — Documentation. https://docs.phpunit.de/en/11.5/',
    'SQLite Consortium. SQLite Documentation. https://www.sqlite.org/docs.html',
    'OWASP Foundation. OWASP Cheat Sheet Series — Injection Prevention. https://cheatsheetseries.owasp.org/',
] as $ref) {
    $d->bullet($ref);
}
$d->saltoPagina();

// ---------- ANEXO A ----------
$d->h1('Anexo A · Declaración de uso de IA');
$d->p('La declaración completa se encuentra en etapa1-base-mvc/DECLARACION_IA.md. Resumen:');
$d->bullet('Herramienta: opencode (CLI); modelo ox-alpha.');
$d->bullet('Propósito: asistencia en implementación, pruebas y documentación de esta etapa.');
$d->bullet('Contenido generado: código de la etapa, pruebas, evidencias y documentación.');
$d->bullet('Errores encontrados y corregidos: documentados en la declaración (evidencia enmascarada en CASO 3, rehidratación del VO, smoke test web).');
$d->bullet('Validación humana: pendiente de ejecución y firma por la estudiante (lista de verificación incluida en la declaración).');

// ------------------------------------------------------------------ empaquetado

$contentTypes = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
    . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
    . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
    . '<Default Extension="xml" ContentType="application/xml"/>'
    . '<Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>'
    . '<Override PartName="/docProps/core.xml" ContentType="application/vnd.openxmlformats-package.core-properties+xml"/>'
    . '<Override PartName="/docProps/app.xml" ContentType="application/vnd.openxmlformats-officedocument.extended-properties+xml"/>'
    . '</Types>';

$rootRels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
    . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
    . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/>'
    . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties" Target="docProps/core.xml"/>'
    . '<Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/extended-properties" Target="docProps/app.xml"/>'
    . '</Relationships>';

$coreXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
    . '<cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties"'
    . ' xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:dcterms="http://purl.org/dc/terms/"'
    . ' xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">'
    . '<dc:title>SHI ASII-19 - Ingreso de resultados de laboratorio - Primera etapa base MVC</dc:title>'
    . '<dc:creator>Keily Fabiola Orellana Marroquin</dc:creator>'
    . '<cp:lastModifiedBy>Keily Fabiola Orellana Marroquin</cp:lastModifiedBy>'
    . '<dcterms:created xsi:type="dcterms:W3CDTF">2026-08-21T00:00:00Z</dcterms:created>'
    . '</cp:coreProperties>';

$appXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
    . '<Properties xmlns="http://schemas.openxmlformats.org/officeDocument/2006/extended-properties">'
    . '<Application>PHP vanilla generator</Application></Properties>';

if (is_file($rutaSalida)) {
    unlink($rutaSalida);
}

$zip = new ZipArchive();
if ($zip->open($rutaSalida, ZipArchive::CREATE) !== true) {
    fwrite(STDERR, "No se pudo crear el archivo ZIP.\n");
    exit(1);
}

$zip->addFromString('[Content_Types].xml', $contentTypes);
$zip->addFromString('_rels/.rels', $rootRels);
$zip->addFromString('docProps/core.xml', $coreXml);
$zip->addFromString('docProps/app.xml', $appXml);
$zip->addFromString('word/document.xml', $d->documentoXml());
$zip->close();

echo "Documento generado: {$rutaSalida} (" . filesize($rutaSalida) . " bytes)\n";
