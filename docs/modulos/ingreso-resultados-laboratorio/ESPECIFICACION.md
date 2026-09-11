# Módulo: Ingreso de Resultados de Laboratorio — Etapa 2

**Estudiante:** Keily Fabiola Orellana Marroquín (`orem6`)
**Curso:** Análisis de Sistemas e Informática II (ASII-19)
**Proyecto:** SHI — Sistema Hospitalario Integrado (Laravel 12 + PostgreSQL)
**Fecha:** agosto de 2026

---

## Índice

1. [Introducción](#1-introducción)
2. [Objetivos](#2-objetivos)
3. [Alcance](#3-alcance)
4. [Reglas de negocio](#4-reglas-de-negocio)
5. [Arquitectura por capas](#5-arquitectura-por-capas)
6. [Modelo de datos](#6-modelo-de-datos)
7. [API — flujo versionado (v2)](#7-api--flujo-versionado-v2)
8. [Decisiones de arquitectura](#8-decisiones-de-arquitectura)
9. [Pruebas automatizadas](#9-pruebas-automatizadas)
10. [Evidencias obligatorias](#10-evidencias-obligatorias)
11. [Limitaciones y trabajo futuro](#11-limitaciones-y-trabajo-futuro)
12. [Bibliografía](#12-bibliografía)

---

## 1. Introducción

Este documento especifica la **segunda etapa** del módulo *Ingreso de resultados de laboratorio*: la evolución del prototipo funcional de la Etapa 1 (PHP 8.2 vanilla con MVC y capas) hacia el **proyecto SHI real**, construido sobre **Laravel 12** con soporte **PostgreSQL**, aplicando el patrón **Repository** con puertos (interfaces) definidos por las necesidades del módulo.

El módulo cubre desde que una muestra fue **aceptada en recepción** hasta que el técnico captura el resultado y, si corresponde, registra **correcciones versionadas** sin sobrescribir nunca la versión anterior.

## 2. Objetivos

### Objetivo general
Implementar el ingreso y corrección versionada de resultados de laboratorio dentro del sistema SHI, respetando la arquitectura por capas y el aislamiento multi-tenant existente.

### Objetivos específicos
1. Garantizar que **solo muestras aceptadas** admitan resultados (regla central).
2. Validar el **tipo de resultado** (numérico/texto) y la **unidad** contra el catálogo de la prueba.
3. Registrar cada corrección como **versión nueva** (append-only), conservando el historial completo.
4. Exponer el flujo mediante **casos de uso** (Application) desacoplados de Eloquent mediante **puertos en Domain** e implementaciones en Infrastructure.
5. Mantener intactos los endpoints ISP de la semana 2 (rutas paralelas `/v2`).

## 3. Alcance

| Incluye | No incluye |
|---|---|
| Cola de muestras aceptadas sin resultado | Recepción/aceptación de muestras (módulo semana 2) |
| Captura inicial del resultado (versión 1) | Publicación al médico (endpoint semana 2) |
| Correcciones versionadas con motivo obligatorio | Interpretación clínica o diagnósticos |
| Historial completo e inmutable de versiones | Órdenes de laboratorio (módulo de admisiones) |
| Validación tipo/unidad contra catálogo | Integración real con CENTRAL (solo UUID lógico) |

## 4. Reglas de negocio

| # | Regla | Dónde se aplica |
|---|---|---|
| R1 | Solo muestras con `acceptance_status = ACEPTADA` admiten resultados | `PoliticaIngresoResultado::assertMuestraPermiteIngreso()` |
| R2 | Un resultado numérico exige número finito válido; uno textual exige texto no vacío (≤500) y sin unidad | `ContenidoResultado::crear()` |
| R3 | Tipo y unidad deben coincidir con la definición de la prueba | `ValidadorContenidoResultado::validar()` |
| R4 | Una corrección crea la versión n+1 con motivo obligatorio; la anterior jamás se modifica ni elimina | `PoliticaCorreccion`, `VersionResultado::correccion()`, repositorio append-only |
| R5 | La versión vigente es la de mayor `version_number`; el historial permanece disponible | `EloquentVersionResultadoRepository::vigentePorMuestra()` |
| R6 | Todo acceso queda acotado al tenant del contexto (`X-Tenant-ID`) | `TenantMiddleware` + filtros por `tenant_id` |

Excepciones de dominio tipadas: `MuestraRechazadaException`, `MuestraNoAceptadaException`, `ResultadoYaRegistradoException`, `ResultadoInexistenteException`, `ResultadoSinCambiosException`, `MotivoCorreccionInvalidoException`, `TipoResultadoInvalidoException`, `UnidadInvalidaException` — todas heredan de `DomainRuleException`.

## 5. Arquitectura por capas

```
Presentation        app/Http/Controllers/LabResults/LabResultV2Controller   routes/api.php (/v2)
      │  Commands (DTOs crudos)            ▲ JSON / códigos de error
Application          app/Application/LabResults/
      │  UseCase: Ingresar · Corregir · ListarPendientes · ConsultarHistorial
      │  Contract: TransactionManagerInterface (puerto orquestación)
Domain               app/Domain/LabResults/
      │  Model: VersionResultado · ContenidoResultado · MuestraSnapshot · PruebaDefinition · EstadoAceptacion · TipoResultado
      │  Policy: PoliticaIngresoResultado · PoliticaCorreccion
      │  Service: ValidadorContenidoResultado
      │  Contract (PUERTOS): MuestraReaderInterface · PruebaReaderInterface · VersionResultadoRepositoryInterface
      │  Exception: DomainRuleException + subtipos
Infrastructure       app/Infrastructure/LabResults/Persistence/Eloquent/
         EloquentMuestraReader · EloquentPruebaReader ·
         EloquentVersionResultadoRepository (solo INSERT/SELECT) · EloquentTransactionManager
```

- **Dependency Inversion:** Application depende de interfaces de Domain; Infrastructure las implementa; el binding vive en `LabResultsServiceProvider`.
- **ISP:** cada caso de uso recibe solo los puertos que usa.
- Los adaptadores no contienen reglas de negocio; solo mapeo fila ↔ objeto de dominio.

Diagramas completos: [`uml/`](./uml/) (6 fuentes PlantUML editables).

## 6. Modelo de datos

Migración única y reversible: `2026_08_21_000001_add_acceptance_and_versioned_results_to_laboratory.php`

**Tabla nueva `lab_result_versions`** (append-only):

| Columna | Tipo | Notas |
|---|---|---|
| id | bigIncrements | PK |
| tenant_id | uuid | FK → tenants.id, índice compuesto |
| sample_id | foreignId | FK → samples.id (restrictOnDelete) |
| lab_test_id | foreignId nullable | FK → lab_tests.id |
| entered_by | foreignId | técnico responsable |
| version_number | unsignedInteger | ≥1; único por muestra |
| result_type | string(10) | NUMERICO \| TEXTO |
| numeric_value | decimal(12,3) nullable | obligatorio si NUMERICO |
| text_value | text nullable | obligatorio si TEXTO |
| unit | string(20) nullable | canónica de la prueba |
| is_abnormal / is_critical | boolean default false | banderas clínicas |
| corrected_from_version | unsignedInteger nullable | origen de la corrección |
| correction_reason | text nullable | motivo obligatorio si corrección |
| resulted_at | timestamp | momento de captura/corrección |
| timestamps | — | created_at / updated_at |

Índices únicos: `(tenant_id, sample_id, version_number)` y `(tenant_id, barcode-equivalente vía sample)`.

**Columnas aditivas en tablas existentes** (compatibles con la semana 2):
- `samples`: `acceptance_status` (string(10) nullable, index), `rejection_reason` (string(500) nullable), `lab_test_id` (FK).
- `lab_tests`: `code` (string(20) nullable), `result_type` (string(10) default `NUMERICO`).

Referencia a CENTRAL: se guarda como **UUID lógico** sin FK remota.

## 7. API — flujo versionado (v2)

Prefijo: `/api/v1/lab-results/v2` · Middleware: `tenant`, `jwt.auth`, rol `TecnicoLab` (historial también para consulta técnica).

| Método | Ruta | Descripción |
|---|---|---|
| GET | `/pendientes` | Muestras aceptadas sin resultado del tenant actual |
| POST | `/` | Ingresa resultado (crea versión 1) |
| PATCH | `/muestras/{sampleId}/correccion` | Corrige (crea versión n+1, motivo obligatorio min:10) |
| GET | `/historial/{sampleId}` | Versión vigente + historial completo |

**Ejemplo — captura inicial**

```http
POST /api/v1/lab-results/v2
X-Tenant-ID: 00000000-0000-4000-8000-0000000000a3
Authorization: Bearer <token>

{ "sample_id": 5, "result_type": "NUMERICO", "numeric_value": "13.5", "unit": "g/dL" }
```

```json
201 { "message": "Resultado ingresado como versión 1.",
      "data": { "id": 9, "version_number": 1, "contenido": { "tipo": "NUMERICO",
                "valor_numerico": 13.5, "unidad": "g/dL" }, ... } }
```

**Códigos de error controlados**

| HTTP | `error` | Causa |
|---|---|---|
| 404 | `MUESTRA_NO_ENCONTRADA` | id inexistente o de otro tenant |
| 422 | `MUESTRA_RECHAZADA` / `MUESTRA_NO_ACEPTADA` | R1 |
| 409 | `RESULTADO_YA_REGISTRADO` | captura duplicada |
| 422 | `RESULTADO_INEXISTENTE` | corrección sin versión previa |
| 422 | `SIN_CAMBIOS` | corrección idéntica a la vigente |
| 422 | `MOTIVO_INVALIDO` | motivo vacío (dominio) |
| 422 | `TIPO_RESULTADO_INVALIDO` / `UNIDAD_INVALIDA` | R3 |
| 422 | validaciones Laravel | formato de entrada |

## 8. Decisiones de arquitectura

Ver detalle completo en [`adr/ADR-001-arquitectura.md`](./adr/ADR-001-arquitectura.md). Resumen:

1. **Puertos en Domain, adaptadores en Infrastructure** (Repository Pattern real, no CRUD genérico).
2. **Append-only**: el repositorio no expone UPDATE/DELETE; la inmutabilidad es estructural.
3. **Vigente = MAX(version_number)**: sin columna `is_current` que pueda desincronizarse.
4. **Rutas paralelas `/v2`**: los endpoints ISP de la semana 2 quedan intactos.
5. **Migración aditiva reversible**: columnas nuevas en lugar de alterar el enum de estados.
6. **Driver-agnostic**: probado en SQLite en memoria; listo para PostgreSQL (mismo esquema SQL estándar).

## 9. Pruebas automatizadas

Suite completa: **46 pruebas / 148 aserciones — PASS** (`php artisan test`, SQLite `:memory:`).

Nuevas para esta etapa:

| Prueba | Cubre |
|---|---|
| `Unit/LabResults/DomainRulesTest` (9 casos) | VO `ContenidoResultado`, políticas, versionamiento puro |
| `Feature/LabResults/VersionedResultsFlowTest::tecnico_ve_solo_muestras_aceptadas_sin_resultado` | cola filtrada por estado y tenant |
| `…muestra_rechazada_no_admite_resultados` | **Evidencia 1** (R1) |
| `…ingreso_valida_tipo_y_unidad_contra_la_prueba` | **Evidencia 2** (R3): tipo, unidad distinta, unidad faltante |
| `…flujo_feliz_ingreso_correccion_conserva_versiones` | **Evidencia 3** (R4/R5) + duplicado 409 + SIN_CAMBIOS |
| `…correccion_requiere_version_previa` | corrección sin historial |
| `…historial_esta_isolado_por_tenant` | R6 |

Las **31 pruebas previas** de la semana 2 siguen pasando sin modificación (regresión cero).

## 10. Evidencias obligatorias

Instrucciones reproducibles (comandos, datos ficticios y salidas esperadas) en [`EVIDENCIA.md`](./EVIDENCIA.md):

1. **Muestra rechazada** → `422 MUESTRA_RECHAZADA`, cero filas creadas.
2. **Valor inválido** → `TIPO_RESULTADO_INVALIDO` y `UNIDAD_INVALIDA`.
3. **Corrección versionada** → v2 con `corrige_a_version=1`; historial conserva v1 intacta.

## 11. Limitaciones y trabajo futuro

- La publicación al médico continúa por el endpoint de la semana 2; unificar ambos flujos es trabajo futuro.
- `is_abnormal`/`is_critical` hoy son entrada del técnico; podrían derivarse automáticamente de los rangos de referencia del catálogo.
- Prueba de integración dedicada sobre PostgreSQL requiere un servicio activo; el esquema es portable y la suite corre en SQLite.

## 12. Bibliografía

- Laravel Documentation — *The PHP Framework For Web Artisans*. https://laravel.com/docs
- Martin, R. C. (2018). *Clean Architecture*. Prentice Hall.
- Fowler, M. (2002). *Patterns of Enterprise Application Architecture*. Addison-Wesley.
- Evans, E. (2003). *Domain-Driven Design*. Addison-Wesley.
- PostgreSQL Global Development Group. *PostgreSQL Documentation*. https://www.postgresql.org/docs/
