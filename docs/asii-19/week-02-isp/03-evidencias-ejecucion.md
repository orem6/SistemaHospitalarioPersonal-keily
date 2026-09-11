# ASII-19 — Evidencias de Ejecución y Git

---

# 1. Propósito

Este documento recopila las evidencias de la implementación del módulo **Ingreso de resultados de laboratorio** correspondiente a la Semana 2: rutas expuestas, resultados de la suite de pruebas y estado Git del worktree.

---

# 2. Rutas expuestas (backend Laravel)

El módulo se registra bajo `/api/v1/lab-results` con los siguientes endpoints:

| Método | Ruta | Roles | Caso de Uso |
|---|---|---|---|
| `GET` | `/api/v1/lab-results/pending` | `TecnicoLab` | UC-01 |
| `POST` | `/api/v1/lab-results` | `TecnicoLab` | UC-02 |
| `PATCH` | `/api/v1/lab-results/{result}/correct` | `TecnicoLab` | UC-03 |
| `POST` | `/api/v1/lab-results/{result}/publish` | `TecnicoLab` | UC-04 |
| `GET` | `/api/v1/lab-results/{result}` | `Médico` \| `Admin` | UC-05 |

La verificación de la ruta se realizó con `php artisan route:list`, confirmando que las cinco rutas del módulo existen con sus middlewares (`tenant`, `jwt.auth` y `role`).

---

# 3. Estructura de archivos implementada

```
app/
├─ Http/Controllers/Api/V1/LabResults/
│   ├─ PendingResultsController.php        (UC-01)
│   ├─ LabResultEntryController.php        (UC-02)
│   ├─ LabResultCorrectionController.php   (UC-03)
│   ├─ LabResultPublishController.php      (UC-04)
│   └─ PublishedResultsController.php      (UC-05)
├─ Services/LabResults/
│   ├─ Contracts/                          (6 contratos ISP)
│   │   ├─ PendingResultsProvider.php
│   │   ├─ ResultEntryWriter.php
│   │   ├─ PendingResultCorrector.php
│   │   ├─ ResultPublisher.php
│   │   ├─ PublishedResultReader.php
│   │   └─ ResultValidator.php
│   ├─ ValueObjects/                       (entradas y salidas tipadas)
│   ├─ Exceptions/                         (excepciones de dominio)
│   ├─ Concerns/ComputesResultFlags.php
│   ├─ EloquentPendingResultsProvider.php
│   ├─ StrictResultValidator.php
│   ├─ LabResultEntryService.php
│   ├─ LabResultCorrectionService.php
│   ├─ ResultPublicationService.php
│   └─ EloquentPublishedResultReader.php
├─ Models/
│   ├─ LabResult.php                       (editado: ciclo de vida + relaciones)
│   ├─ LabOrderItem.php                    (relación orderItem)
│   ├─ Sample.php                          (relación sample)
│   ├─ LabTest.php
│   └─ LabResultCorrection.php             (trazabilidad)
└─ Providers/LabResultsServiceProvider.php (bindings de contratos)
```

```
database/
├─ migrations/2026_04_26_135000_add_results_lifecycle_to_lab_results.php
└─ seeders/LabResultsScenarioSeeder.php

tests/
├─ Unit/LabResults/IspSegregationTest.php
└─ Feature/LabResults/
    ├─ InteractsWithLabResults.php
    ├─ PendingConsultationTest.php
    ├─ ResultEntryTest.php
    ├─ CorrectionAndPublishTest.php
    └─ PublishedConsultationTest.php
```

---

# 4. Evidencia de ejecución: suite de pruebas

## 4.1. Comando ejecutado

```
php artisan test
```

## 4.2. Resultado final (31 pruebas)

```
PASS  Tests\Unit\ExampleTest
PASS  Tests\Unit\LabResults\IspSegregationTest          (11 pruebas, reflexión ISP)
PASS  Tests\Feature\ExampleTest
PASS  Tests\Feature\LabResults\CorrectionAndPublishTest
PASS  Tests\Feature\LabResults\PendingConsultationTest
PASS  Tests\Feature\LabResults\PublishedConsultationTest
PASS  Tests\Feature\LabResults\ResultEntryTest

Tests:    31 passed (83 assertions)
Duration: 2.82s
```

## 4.3. Cobertura de criterios verificada por los tests

| Test | Criterios de aceptación verificados |
|---|---|
| `PendingConsultationTest` | UC-01: lista pendientes del tenant, aislamiento entre tenants, `403` a médico. |
| `ResultEntryTest` | UC-02/UC-06: captura exitosa, rechazo sin valor, ítem inexistente, duplicado, `403` a recepcionista. |
| `CorrectionAndPublishTest` | UC-03/UC-04: corrección con trazabilidad, corrección sin cambios, corrección de publicado, republicación, publicación exitosa. |
| `PublishedConsultationTest` | UC-05: lectura de publicado, rechazo de no publicado, aislamiento cruzado, `403` a técnico. |
| `IspSegregationTest` | ISP: cada controlador depende de un único contrato; cada contrato expone un único método. |

## 4.4. Correcciones aplicadas durante la validación

| Problema detectado | Causa | Solución |
|---|---|---|
| Fatal en middleware `JwtAuth` en tests | Colisión de nombres `use ...facades\JWTAuth` + `class JwtAuth` (PHP insensitive a mayúsculas) | Alias del facade a `JwtAuthFacade` en `app/Http/Middleware/JwtAuth.php` |
| `Tests\Feature\ExampleTest` fallaba | Faltaba `public/build/manifest.json` (assets no compilados) | `npm run build` (genera `public/build/`) |
| 11 pruebas fallaban con "Call to undefined relationship [labOrderItem]" | Faltaban las relaciones `labOrderItem()` y `sample()` en `LabResult` | Relaciones `BelongsTo` agregadas al modelo |
| Test de publicación fallaba | `assertJsonMissingPath` verifica ausencia, no presencia | Se ajustó la aserción para validar que `published_at` existe y es no nulo |

Resultado de la suite corregida **antes** del ajuste final: `1 failed, 30 passed`. Después de la corrección de las relaciones del modelo: `31 passed (83 assertions)`.

---

# 5. Evidencia Git

## 5.1. Estado del worktree

La rama de trabajo es:

```
feature/asii-19-ingreso-de-resultados-de-laboratorio-orem6
```

El repositorio compartido con el docente es **https://github.com/orem6/SistemaHospitalarioPersonal-keily.git** (remoto `individual`); el repositorio del equipo es **https://github.com/compilations-teams/sistema-hospitalario-integrado-SistenasII-2026.git** (remoto `origin`).

El worktree local contiene los archivos nuevos y las modificaciones de la implementación. El último commit existente del worktree es de la Semana 1 (diagramas UML); los cambios de la Semana 2 están en el árbol de trabajo y **no han sido commiteados ni empujados** a repositorios remotos.

## 5.2. Archivos sin seguimiento (nuevos) — principales

```
app/Services/LabResults/...
app/Http/Controllers/Api/V1/LabResults/...
app/Providers/LabResultsServiceProvider.php
database/migrations/..._add_results_lifecycle_to_lab_results.php
database/seeders/LabResultsScenarioSeeder.php
tests/Feature/LabResults/...
tests/Unit/LabResults/...
docs/asii-19/week-02-isp/...
DECLARACION_IA.md
```

## 5.3. Archivos modificados — principales

```
app/Models/LabResult.php
app/Models/LabOrder.php
app/Http/Middleware/JwtAuth.php
routes/api.php
bootstrap/app.php
phpunit.xml
```

---

# 6. Notas

- Todos los datos de seeders y pruebas son ficticios (`@demo.local`).
- No se incluyen secretos, claves reales ni información clínica real.
- El proceso de subida al repositorio remoto lo realizará el estudiante de forma manual; la guía paso a paso se describe en la entrega final.