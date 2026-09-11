# EVIDENCIA — Módulo Ingreso de resultados de laboratorio (Etapa 2)

**Estudiante:** Keily Fabiola Orellana Marroquín (`orem6`)
Todos los datos son **ficticios** (tenants `*demo*`, correos `@demo.local`, códigos `BC-EVD-*` / `BC-DEMO-*`).

---

## 0. Preparación del entorno

```powershell
composer install
copy .env.example .env          # configurar DB (SQLite para pruebas o PostgreSQL)
php artisan key:generate
php artisan jwt:secret
php artisan migrate:fresh --seed --force     # crea esquema + datos demo
php artisan serve
```

Usuarios ficticios del escenario (`LabResultsScenarioSeeder`):

| Tenant (X-Tenant-ID) | Usuario | Rol | Uso |
|---|---|---|---|
| `…0000000000a3` (evidencias etapa 2) | `lab.evidencias@demo.local` | TecnicoLab | Evidencias 1–3 |
| `…0000000000a1` (laboratorio central) | `lab.resultados@demo.local` | TecnicoLab | Regresión semana 2 |
| Contraseña (todos) | `password` | — | — |

Muestras sembradas en el tenant de evidencias:

| Barcode | Estado aceptación | Prueba (tipo/unidad) |
|---|---|---|
| `BC-EVD-000001` | **RECHAZADA** (motivo: tubo mal rotulado y con coágulos) | Perfil Lipídico |
| `BC-EVD-000002` | **ACEPTADA** | Hemoglobina `NUMERICO` / `g/dL` |

### Obtener token JWT

```powershell
$login = Invoke-RestMethod -Method Post -Uri http://127.0.0.1:8000/api/v1/auth/login `
  -Headers @{ 'X-Tenant-ID' = '00000000-0000-4000-8000-0000000000a3'; 'Accept' = 'application/json' } `
  -ContentType 'application/json' `
  -Body '{"email":"lab.evidencias@demo.local","password":"password"}'

$H = @{ Authorization = "Bearer $($login.access_token ?? $login.token ?? $login.authorization.token)";
        'X-Tenant-ID' = '00000000-0000-4000-8000-0000000000a3';
        Accept = 'application/json' }
```

> Según la respuesta real de login, usar el campo que contenga el JWT.

Obtener el `sample_id` numérico de cada barcode:

```powershell
php artisan tinker --execute="print_r(App\Models\Sample::where('tenant_id','00000000-0000-4000-8000-0000000000a3')->get(['id','barcode','acceptance_status'])->toArray());"
```

---

## EVIDENCIA 1 — Muestra rechazada NO admite resultados

**Regla:** solo muestras ACEPTADAS admiten resultados.
**Cubre:** `VersionedResultsFlowTest::muestra_rechazada_no_admite_resultados`.

```powershell
# sample_id de BC-EVD-000001 (rechazada)
Invoke-RestMethod -Method Post -Uri http://127.0.0.1:8000/api/v1/lab-results/v2 `
  -Headers $H -ContentType 'application/json' `
  -Body '{"sample_id": <ID_BC_EVD_000001>, "result_type":"NUMERICO", "numeric_value":"150.0", "unit":"mg/dL"}'
```

**Resultado esperado (HTTP 422):**

```json
{ "error": "MUESTRA_RECHAZADA",
  "detail": "La muestra BC-EVD-000001 fue rechazada y no admite resultados." }
```

Verificación adicional: la tabla `lab_result_versions` sigue en 0 filas para esa muestra.

---

## EVIDENCIA 2 — Valor inválido (tipo y unidad contra la prueba)

**Regla:** tipo y unidad deben coincidir con la definición de la prueba (Hemoglobina: `NUMERICO`, `g/dL`).
**Cubre:** `VersionedResultsFlowTest::ingreso_valida_tipo_y_unidad_contra_la_prueba`.

**(a) Tipo incorrecto — TEXTO sobre prueba numérica:**

```powershell
Invoke-RestMethod -Method Post -Uri http://127.0.0.1:8000/api/v1/lab-results/v2 `
  -Headers $H -ContentType 'application/json' `
  -Body '{"sample_id": <ID_BC_EVD_000002>, "result_type":"TEXTO", "text_value":"sin datos"}'
```

Esperado: `422 { "error": "TIPO_RESULTADO_INVALIDO", ... }`

**(b) Unidad distinta a la canónica:**

```powershell
... -Body '{"sample_id": <ID>, "result_type":"NUMERICO", "numeric_value":"14.0", "unit":"mmol/L"}'
```
Esperado: `422 { "error": "UNIDAD_INVALIDA", ... }`

**(c) Sin unidad (obligatoria en numéricos):**

```powershell
... -Body '{"sample_id": <ID>, "result_type":"NUMERICO", "numeric_value":"14.0"}'
```
Esperado: `422 { "error": "UNIDAD_INVALIDA", ... }`

En los tres casos **no se crean filas**.

---

## EVIDENCIA 3 — Corrección versionada conservando la versión anterior

**Reglas:** corrección = versión nueva n+1 con motivo; historial completo disponible.
**Cubre:** `VersionedResultsFlowTest::flujo_feliz_ingreso_correccion_conserva_versiones`.

**(1) Captura inicial correcta (versión 1):**

```powershell
Invoke-RestMethod -Method Post -Uri http://127.0.0.1:8000/api/v1/lab-results/v2 `
  -Headers $H -ContentType 'application/json' `
  -Body '{"sample_id": <ID_BC_EVD_000002>, "result_type":"NUMERICO", "numeric_value":"13.5", "unit":"g/dL"}'
```
Esperado: `201 { "data": { "version_number": 1, "contenido": { "valor_numerico": 13.5, ... } } }`

**(2) Corrección con motivo obligatorio (versión 2):**

```powershell
Invoke-RestMethod -Method Patch -Uri http://127.0.0.1:8000/api/v1/lab-results/v2/muestras/<ID>/correccion `
  -Headers $H -ContentType 'application/json' `
  -Body '{"motivo":"Error de transcripción del analizador ficticio.","result_type":"NUMERICO","numeric_value":"15.1","unit":"g/dL"}'
```
Esperado: `201 { "data": { "version_number": 2, "corrige_a_version": 1, ... } }`

**(3) El historial conserva AMBAS versiones intactas:**

```powershell
Invoke-RestMethod -Method Get -Uri http://127.0.0.1:8000/api/v1/lab-results/v2/historial/<ID> -Headers $H
```
Esperado (resumen):

```json
{ "data": {
    "vigente":   { "version_number": 2, "contenido": { "valor_numerico": 15.1 } },
    "versiones": [
      { "version_number": 1, "contenido": { "valor_numerico": 13.5 }, "corrige_a_version": null },
      { "version_number": 2, "contenido": { "valor_numerico": 15.1 }, "corrige_a_version": 1,
        "motivo_correccion": "Error de transcripción del analizador ficticio." }
    ] } }
```

La v1 conserva su valor original (13.5): **nunca fue sobrescrita** (append-only).

Casos complementarios cubiertos por la suite: duplicado inicial → `409 RESULTADO_YA_REGISTRADO`; corrección idéntica → `422 SIN_CAMBIOS`; corrección sin historial → `422 RESULTADO_INEXISTENTE`; intruso de otro tenant → `404 MUESTRA_NO_ENCONTRADA`.

---

## Ejecución automatizada completa

```powershell
$env:DB_CONNECTION='sqlite'; $env:DB_DATABASE=':memory:'
$env:JWT_SECRET='testing-secret'
php artisan test
```

Salida esperada (registrada en la ejecución de esta entrega):

```
PASS  Tests\Unit\LabResults\DomainRulesTest                    (9 pruebas)
PASS  Tests\Feature\LabResults\VersionedResultsFlowTest        (6 pruebas)
...
Tests:    46 passed (148 assertions)
Duration: ~2.6s
```

Las 31 pruebas de la semana 2 continúan en verde: **regresión cero**.
