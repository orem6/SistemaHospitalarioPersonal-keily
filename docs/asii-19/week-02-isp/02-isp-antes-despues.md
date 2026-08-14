# ASII-19 — Aplicación del Principio ISP (Antes / Después)

---

# 1. Propósito

Este documento describe la aplicación del **Principio de Segregación de Interfaces (ISP)** sobre el módulo **Ingreso de resultados de laboratorio**, mostrando el diseño inicial (interfaz monolítica) y el diseño final (contratos segregados por responsabilidad).

> Fuente oficial del curso: [MVP Cluster — Diseño de Software 2](https://mvpcluster.com/diseno-de-software-2/)

El ISP dispone que **ninguna clase debe verse forzada a depender de métodos que no utiliza**. En lugar de exponer una interfaz grande con todos los métodos de todos los casos de uso, se definen contratos pequeños y enfocados, cada uno con una única responsabilidad.

---

# 2. Diseño ANTES (violación de ISP)

## 2.1. Descripción

En un diseño inicial se modeló una única interfaz de gestión de resultados con todos los métodos necesarios para los seis casos de uso del módulo:

```php
<?php

namespace App\Services\LabResults;

interface LaboratoryResultManager
{
    public function pending(string $tenantId): array;

    public function register(array $input): void;

    public function correct(array $input): void;

    public function publish(string $tenantId, int $resultId, int $userId): void;

    public function showPublished(string $tenantId, int $resultId): array;

    public function validate(array $input): void;
}
```

## 2.2. Problemas de este diseño

- **Dependencias innecesarias:** los seis clientes (controladores por caso de uso) dependen de los seis métodos, aunque solo utilizan uno.
- **Acoplamiento:** una modificación en la firma de un método (ej. `validate`) obliga a recompilar y reajustar los seis clientes.
- **Dificultad de sustitución:** no se puede crear una implementación parcial ni probar cada responsabilidad de forma aislada.
- **Menor mantenibilidad:** el contrato cambia por múltiples razones (una por responsabilidad), violando también el SRP a nivel de interfaz.

## 2.3. Diagrama ANTES

Diagrama de clases representando la interfaz monolítica:

```
docs/asii-19/week-02-isp/diagrams/isp-antes.puml
```

---

# 3. Diseño DESPUÉS (aplicando ISP)

## 3.1. Contratos segregados

Se reemplazó la interfaz monolítica por **seis contratos**, uno por responsabilidad/caso de uso:

```php
<?php

namespace App\Services\LabResults\Contracts;

// UC-01 — Consulta de resultados pendientes
interface PendingResultsProvider
{
    public function pendingFor(string $tenantId): array;
}

// UC-02 — Registro de resultado de laboratorio
interface ResultEntryWriter
{
    public function create(ResultInput $input): PublishedResultDetail;
}

// UC-03 — Corrección controlada de resultados pendientes
interface PendingResultCorrector
{
    public function correct(ResultCorrectionInput $input): PublishedResultDetail;
}

// UC-04 — Publicación de resultados
interface ResultPublisher
{
    public function publish(string $tenantId, int $resultId, int $userId): PublishedResultDetail;
}

// UC-05 — Consulta de resultados publicados
interface PublishedResultReader
{
    public function find(string $tenantId, int $resultId): PublishedResultDetail;
}

// UC-06 — Validación de información
interface ResultValidator
{
    public function validateForEntry(array $payload): ResultInput;
}
```

## 3.2. Asignación de responsabilidades

| Contrato | Responsabilidad única | Cliente que depende de él |
|---|---|---|
| `PendingResultsProvider` | Proveer exámenes pendientes del tenant. | `PendingResultsController` |
| `ResultEntryWriter` | Registrar resultado e indicar si es crítico/anormal. | `LabResultEntryController` |
| `PendingResultCorrector` | Corregir y trazar un resultado no publicado. | `LabResultCorrectionController` |
| `ResultPublisher` | Publicar un resultado registrado. | `LabResultPublishController` |
| `PublishedResultReader` | Leer un resultado publicado del tenant. | `PublishedResultsController` |
| `ResultValidator` | Validar entrada antes de persistir. | `ResultEntryWriter` / `PendingResultCorrector` |

Cada controlador inyecta **únicamente** el contrato que corresponde a su caso de uso:

```php
<?php

namespace App\Http\Controllers\Api\V1\LabResults;

use App\Services\LabResults\Contracts\ResultPublisher;
use Illuminate\Http\JsonResponse;

class LabResultPublishController extends Controller
{
    public function __construct(private readonly ResultPublisher $publisher)
    {
    }

    public function store(Request $request, int $result): JsonResponse
    {
        $published = $this->publisher->publish($tenant->id, $result, $userId);

        return response()->json(['data' => $published->load(...)]);
    }
}
```

## 3.3. Implementaciones concretas

| Contrato | Implementación |
|---|---|
| `PendingResultsProvider` | `EloquentPendingResultsProvider` |
| `ResultEntryWriter` | `LabResultEntryService` |
| `PendingResultCorrector` | `LabResultCorrectionService` |
| `ResultPublisher` | `ResultPublicationService` |
| `PublishedResultReader` | `EloquentPublishedResultReader` |
| `ResultValidator` | `StrictResultValidator` |

Los enlaces de cada contrato con su implementación se registran en `App\Providers\LabResultsServiceProvider` y son resueltos por el contenedor IoC de Laravel (inyección de dependencias).

## 3.4. Diagrama DESPUÉS

Diagrama de clases con los contratos segregados:

```
docs/asii-19/week-02-isp/diagrams/isp-despues.puml
```

---

# 4. Beneficios obtenidos

- **Cliente minimalista:** cada controlador ve solo los métodos que usa (sin métodos fantasma).
- **Sustituibilidad:** las implementaciones (Eloquent) pueden reemplazarse por mocks o adaptadores sin tocar los controladores (también refuerza DIP).
- **Pruebas unitarias:** se puede evaluar aisladamente cada contrato; la suite incluye un test de reflexión (`IspSegregationTest`) que verifica que cada controlador inyecta un único contrato y que cada contrato expone un único método.
- **Evolución cerrada al cambio:** agregar un nuevo ajuste al registro (ej. `markAsCritical`) se hace en un contrato específico sin alterar la publicación ni la lectura.

---

# 5. Verificación automatizada del ISP

El archivo `tests/Unit/LabResults/IspSegregationTest.php` verifica por reflexión:

1. Que cada uno de los cinco controladores del módulo depende de **un único** contrato segregado.
2. Que cada uno de los seis contratos expone **un único método** enfocado en su responsabilidad.

Resultado de la suite (ver evidencia completa en `03-evidencias-ejecucion.md`):

```
Tests:    31 passed (83 assertions)
```

---

# 6. Límites de esta evidencia

- Los datos utilizados en pruebas y seeders son **ficticios**.
- No se incluyen secretos ni credenciales reales.
- El módulo no fue publicado en repositorios remotos; todo el trabajo reside en el worktree local.