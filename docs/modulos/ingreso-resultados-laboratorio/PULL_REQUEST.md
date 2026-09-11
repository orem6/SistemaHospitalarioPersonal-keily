# Texto propuesto para el Pull Request

> Este repositorio de curso no usa ramas ni PRs reales (restricción de la entrega); este texto queda listo para pegarse en la plataforma que lo requiera (GitHub Classroom, Moodle, etc.).

---

**Título:**

```
ASII-19 (orem6): Etapa 2 — resultados de laboratorio versionados con Repository Pattern (Laravel + PostgreSQL-ready)
```

**Descripción:**

## Qué incluye

- **Flujo versionado v2** en rutas paralelas (`/api/v1/lab-results/v2`), sin tocar los endpoints ISP de la semana 2:
  - `GET /pendientes` — muestras ACEPTADAS sin resultado del tenant actual.
  - `POST /` — ingreso del resultado (versión 1) con validación de tipo/unidad contra el catálogo.
  - `PATCH /muestras/{id}/correccion` — corrección como versión n+1 con motivo obligatorio (min. 10 caracteres).
  - `GET /historial/{id}` — versión vigente + historial completo inmutable.
- **Repository Pattern real:** puertos en Domain (`MuestraReaderInterface`, `PruebaReaderInterface`, `VersionResultadoRepositoryInterface`) implementados por adaptadores Eloquent en Infrastructure; bindings en `LabResultsServiceProvider`.
- **Append-only garantizado por diseño:** el repositorio de versiones solo expone INSERT/SELECT; la vigente es `MAX(version_number)`; correcciones apuntan a `corrected_from_version`.
- **Regla central protegida:** solo muestras `ACEPTADA` admiten resultados (`PoliticaIngresoResultado`); rechazada → `422 MUESTRA_RECHAZADA`.
- **Excepciones de dominio tipadas** mapeadas a códigos HTTP estables (`TIPO_RESULTADO_INVALIDO`, `UNIDAD_INVALIDA`, `RESULTADO_YA_REGISTRADO` 409, `SIN_CAMBIOS`, `RESULTADO_INEXISTENTE`, …).
- **Migración única reversible y aditiva:** tabla `lab_result_versions` + columnas nuevas en `samples` (`acceptance_status`, `rejection_reason`, `lab_test_id`) y `lab_tests` (`code`, `result_type`). Compatible PostgreSQL/SQLite.
- **Seeder ampliado sin romper semana 2:** tercer tenant ficticio exclusivo para evidencias (muestra rechazada + muestra aceptada para corrección).

## Arquitectura

Presentation → Application (UseCases + Commands) → Domain (políticas, validador, VO inmutables, puertos, excepciones) ← Infrastructure (adaptadores Eloquent). Dependency Inversion vía service provider; ISP: cada caso de uso recibe solo sus puertos.

## Pruebas

- **46 pruebas / 148 aserciones PASS** (`php artisan test`, SQLite en memoria).
- Nuevas: 9 unitarias de dominio puro + 6 feature del flujo v2 (incluyen las 3 evidencias obligatorias).
- Regresión cero: las 31 pruebas previas siguen pasando sin modificaciones.

## Documentación

`docs/modulos/ingreso-resultados-laboratorio/`: `ESPECIFICACION.md`, `adr/ADR-001-arquitectura.md`, `EVIDENCIA.md` (comandos reproducibles), `DECLARACION_IA.md` y `uml/` con 7 diagramas PlantUML editables (casos de uso, clases, 2 secuencias, componentes, ER y estados).

## Datos

100 % ficticios; referencia a CENTRAL solo como UUID lógico (sin FK remota).
