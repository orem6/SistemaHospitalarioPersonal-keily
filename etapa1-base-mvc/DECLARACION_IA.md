# DECLARACIÓN DE USO DE INTELIGENCIA ARTIFICIAL

## ASII-19 · Ingreso de resultados de laboratorio — PRIMERA ETAPA (base/MVC, PHP vanilla)

**Estudiante:** KEILY FABIOLA ORELLANA MARROQUÍN
**GitHub:** `orem6`
**Rama de trabajo:** `feature/asii-19-ingreso-de-resultados-de-laboratorio-orem6`
**Fecha:** 21 de agosto de 2026

> Nota: existe otra declaración en la raíz del repositorio correspondiente a la
> Semana 2 (ISP). Este documento cubre exclusivamente esta primera etapa.

---

## 1. Herramienta de IA utilizada

| Campo | Detalle |
|---|---|
| Herramienta | opencode (asistente de código por línea de comandos) |
| Modelo | ox-alpha |
| Uso principal | Implementación de la base/MVC vanilla, pruebas y documentación |

## 2. Propósito

1. Construir el módulo **Ingreso de resultados de laboratorio** en PHP 8.2
   vanilla con capas Presentation/Application/Domain/Persistence.
2. Implementar las reglas centrales asignadas (muestra aceptada/rechazada,
   tipo/unidad válidos, corrección versionada sin sobrescribir).
3. Crear pruebas automatizadas y evidencia reproducible.
4. Redactar documentación Markdown y el documento Word (.docx) entregable.

## 3. Prompts utilizados (resumen)

1. "Actúa como desarrollador senior… primera etapa… PHP 8.2+ vanilla…
   capas Presentation/Application/Domain/Persistence…" (asignación completa,
   incluye reglas de Git sin ramas/worktrees, casos obligatorios, pruebas,
   documentación y DECLARACION_IA.md).
2. Instrucción intermedia: "sigues trabajando" / "continúa" para mantener el
   avance por pasos.
3. Solicitud final: generar además un documento .docx con las indicaciones.

## 4. Contenido generado por IA

- `etapa1-base-mvc/` completo: `src/Domain`, `src/Application`,
  `src/Persistence`, `src/Presentation` (controlador + vistas),
  `public/index.php`, `bin/demo.php`, `bin/smoke-web.php`,
  `database/schema.sql`, `database/seed.php`, `phpunit.xml.dist`.
- Pruebas: `tests/Domain/ReglasDominioTest.php`,
  `tests/Integration/FlujoIngresoCorreccionTest.php` (+ soporte).
- Evidencias capturadas: `etapa1-base-mvc/evidencias/*.txt`.
- Documentación: `docs/asii-19/etapa-01-base-mvc/*.md`, este archivo y el
  generador del documento `.docx` (`etapa1-base-mvc/tools/make-docx.php`)
  junto al documento generado en la raíz.

## 5. Contenido modificado por IA

- Ningún archivo preexistente del repositorio fue modificado ni eliminado.
  Toda la etapa vive en carpetas nuevas (`etapa1-base-mvc/`,
  `docs/asii-19/etapa-01-base-mvc/`) y en el documento Word generado.
- Se conservó intacto el `DECLARACION_IA.md` raíz de la Semana 2.

## 6. Errores encontrados durante el desarrollo

1. **Evidencia enmascarada (CASO 3):** los intentos con valor/unidad inválidos
   recaían sobre una muestra que ya tenía resultado vigente, por lo que la
   excepción mostrada era "ya tiene resultado vigente" y no la de valor
   inválido. *Corrección:* se agregó la muestra ficticia `DEM-BAR-0004`
   (ACEPTADA, sin resultado) para demostrar limpiamente CASO 3a/3b.
2. **Constructor privado del VO `ContenidoResultado`:** el repositorio PDO no
   podía reconstruir entidades desde filas. *Corrección:* se añadió el método
   estático `desdeAlmacenamiento()` para rehidratación sin re-validación.
3. **Smoke test web desde PowerShell:** falló por escapado de variables
   superglobales en `-r`. *Corrección:* script `bin/smoke-web.php`.

## 7. Validación realizada durante la sesión (por la herramienta, verificable)

Los siguientes comandos fueron ejecutados y su salida quedó registrada:

- `php -v` → PHP 8.2.31.
- Lint sintáctico de todos los archivos PHP de la etapa → 0 errores.
- `php bin/demo.php` → los 4 casos obligatorios en verde; v1 conservada tras v2
  (ver `evidencias/demo-casos-obligatorios.txt`).
- PHPUnit → **11 pruebas, 39 aserciones, OK**
  (ver `evidencias/pruebas-phpunit.txt`).
- Smoke test de las vistas web (pendientes e historial) → render correcto.
- Revisión de datos: todos ficticios; sin secretos ni datos clínicos reales;
  referencias CENTRAL solo como UUID lógico sin FK remota.

## 8. Validación humana pendiente (estudiante)

La validación final del trabajo corresponde a la estudiante y queda pendiente
de ejecución/documentación por ella. Lista sugerida:

- [ ] Ejecutar `php bin/demo.php` y revisar la salida.
- [ ] Ejecutar la suite PHPUnit y confirmar 11/11.
- [ ] Levantar `php -S localhost:8080 -t public` y recorrer la interfaz web.
- [ ] Revisar el documento `.docx` y ajustar portada/formatos si la cátedra lo exige.
- [ ] Revisar `git status` / `git diff` antes de hacer commit manual.

La responsabilidad final sobre integridad y coherencia del trabajo es de la estudiante.
