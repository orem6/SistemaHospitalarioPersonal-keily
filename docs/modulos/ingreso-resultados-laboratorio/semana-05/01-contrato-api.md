# Semana 5: Contrato API real

- **Modulo:** ASII-19 - Ingreso de resultados de laboratorio
- **Responsable:** Keily Fabiola Orellana Marroquin (`orem6`)
- **Base auditada:** `origin/develop` en `8a1c08a`
- **Alcance:** flujo versionado Etapa 2. No sustituye los endpoints de otros modulos ni los ISP previos.

## Base del contrato

`bootstrap/app.php` aplica el prefijo `api/v1` a `routes/api.php`. El grupo ASII-19 usa el prefijo adicional `lab-results/v2`; por tanto, la base real es:

```text
/api/v1/lab-results/v2
```

Todos los endpoints requieren `X-Tenant-ID` y JWT. El middleware `tenant` rechaza cabecera ausente con `400` y tenant inexistente con `404`; `jwt.auth` autentica al usuario. `TecnicoLab` se exige solo donde se indica en la tabla. No hay query parameters ni paginacion implementados.

| Metodo | URI | Proposito | Autorizacion real | Idempotencia |
|---|---|---|---|---|
| GET | `/pendientes` | Cola de muestras aceptadas sin version | JWT + rol `TecnicoLab` | Si, lectura |
| POST | `/` | Crea la version inicial | JWT + rol `TecnicoLab` | No; repeticion devuelve `409` |
| PATCH | `/muestras/{sampleId}/correccion` | Agrega version n+1 | JWT + rol `TecnicoLab` | No; repeticion puede crear otra version |
| GET | `/historial/{sampleId}` | Lee vigente e historial | Solo JWT | Si, lectura |

`sampleId` debe ser entero positivo por la restriccion de ruta `whereNumber`; `sample_id` se valida como `required|integer`, sin minimo explicito. Cada muestra se resuelve y se compara con el tenant del encabezado. Una muestra ajena se presenta como `404 MUESTRA_NO_ENCONTRADA`.

## Esquemas compartidos

### Cabeceras

```http
Accept: application/json
Authorization: Bearer <jwt>
X-Tenant-ID: <uuid-del-tenant>
```

### Contenido de resultado

| Campo | Tipo | Validacion HTTP | Regla de dominio |
|---|---|---|---|
| `result_type` | string | requerido, maximo 10 | `NUMERICO` o `TEXTO`; debe coincidir con la prueba |
| `numeric_value` | number o string numerico | opcional, `numeric` | obligatorio, finito y valido para `NUMERICO` |
| `text_value` | string | opcional, maximo 500 | no vacio y sin unidad para `TEXTO` |
| `unit` | string | opcional, maximo 20 | debe ser la unidad canonica de la prueba; requerida cuando la prueba numerica la define |

El valor clinico, unidad, tipo, banderas `es_anormal`/`es_critico`, fecha y motivo de correccion son datos clinicos. La API no acepta las banderas: el dominio las crea en `false` en esta version.

### Version serializada

```json
{
  "id": 9,
  "version_number": 2,
  "muestra_id": 5,
  "contenido": {
    "tipo": "NUMERICO",
    "valor_numerico": 15.1,
    "valor_texto": null,
    "unidad": "g/dL"
  },
  "corrige_a_version": 1,
  "motivo_correccion": "Error de transcripcion del analizador.",
  "es_anormal": false,
  "es_critico": false,
  "resulted_at": "2026-09-17 10:30:00"
}
```

## 1. Consultar pendientes

```http
GET /api/v1/lab-results/v2/pendientes
```

No lleva cuerpo, parametros de ruta ni query. Devuelve solamente muestras del tenant actual con `acceptance_status = ACEPTADA` y sin filas en `lab_result_versions`, ordenadas por `collected_at`.

```json
{
  "data": [{
    "muestra_id": 5,
    "barcode": "BC-EVD-000002",
    "prueba_id": 3,
    "prueba_nombre": "Hemoglobina",
    "tipo_esperado": "NUMERICO",
    "unidad_canonica": "g/dL",
    "collectada_en": "2026-09-17 09:00:00"
  }]
}
```

Responde `200`; la cola vacia es `200` con `data: []`. Tambien puede devolver `400`, `404`, `401` o `403` por middleware. La implementacion no define codigos de error de negocio para esta lectura.

## 2. Ingresar resultado

```http
POST /api/v1/lab-results/v2
Content-Type: application/json

{
  "sample_id": 5,
  "result_type": "NUMERICO",
  "numeric_value": "13.5",
  "unit": "g/dL"
}
```

El controlador asocia el usuario JWT al comando, pero la version persistida actual no guarda `entered_by`; esa brecha de trazabilidad se registra en el analisis de integracion. Para crear v1, la muestra debe existir en el tenant, estar aceptada, tener prueba asociada, carecer de version vigente y pasar las reglas de contenido/tipo/unidad.

```json
{
  "message": "Resultado ingresado como versión 1.",
  "data": { "id": 9, "version_number": 1, "muestra_id": 5, "contenido": { "tipo": "NUMERICO", "valor_numerico": 13.5, "valor_texto": null, "unidad": "g/dL" }, "corrige_a_version": null, "motivo_correccion": null, "es_anormal": false, "es_critico": false, "resulted_at": "2026-09-17 10:30:00" }
}
```

Responde `201`. Errores: `422` de validacion Laravel; `404 MUESTRA_NO_ENCONTRADA`; `422 MUESTRA_RECHAZADA`, `MUESTRA_NO_ACEPTADA`, `TIPO_RESULTADO_INVALIDO`, `UNIDAD_INVALIDA` o `REGLA_NEGOCIO`; `409 RESULTADO_YA_REGISTRADO`; y `500 ERROR_INTERNO`. Una repeticion posterior al exito no es idempotente: responde `409`; el cliente debe tratarla como posible exito previo solo tras consultar historial.

## 3. Corregir resultado

```http
PATCH /api/v1/lab-results/v2/muestras/5/correccion
Content-Type: application/json

{
  "motivo": "Error de transcripcion del analizador.",
  "result_type": "NUMERICO",
  "numeric_value": "15.1",
  "unit": "g/dL"
}
```

`motivo` es requerido, string de 10 a 500 caracteres. Los demas campos tienen las validaciones compartidas. Debe haber una version previa y el nuevo contenido debe diferir de la vigente; se inserta v(n+1), con `corrige_a_version` apuntando a n, sin modificar v(n).

```json
{
  "message": "Corrección registrada como versión 2; la versión anterior se conserva.",
  "data": { "id": 10, "version_number": 2, "muestra_id": 5, "contenido": { "tipo": "NUMERICO", "valor_numerico": 15.1, "valor_texto": null, "unidad": "g/dL" }, "corrige_a_version": 1, "motivo_correccion": "Error de transcripcion del analizador.", "es_anormal": false, "es_critico": false, "resulted_at": "2026-09-17 10:35:00" }
}
```

Responde `201`. Ademas de los errores de ingreso: `422 RESULTADO_INEXISTENTE`, `422 SIN_CAMBIOS` y `422 MOTIVO_INVALIDO`; la validacion HTTP del motivo tambien responde `422`. No es idempotente: reintentar tras una respuesta perdida puede crear una version adicional. No existe `Idempotency-Key` ni control de concurrencia expuesto.

## 4. Consultar historial y version vigente

```http
GET /api/v1/lab-results/v2/historial/5
```

No lleva cuerpo ni query. La muestra debe pertenecer al tenant del encabezado. La ruta solo exige JWT en el codigo actual, no rol `TecnicoLab`; por tanto, el contrato real no puede afirmar una restriccion por rol para esta operacion.

```json
{
  "data": {
    "muestra_id": 5,
    "barcode": "BC-EVD-000002",
    "prueba_id": 3,
    "prueba_nombre": "Hemoglobina",
    "vigente": { "version_number": 2, "contenido": { "tipo": "NUMERICO", "valor_numerico": 15.1, "valor_texto": null, "unidad": "g/dL" } },
    "versiones": [
      { "version_number": 1, "contenido": { "tipo": "NUMERICO", "valor_numerico": 13.5, "valor_texto": null, "unidad": "g/dL" }, "corrige_a_version": null },
      { "version_number": 2, "contenido": { "tipo": "NUMERICO", "valor_numerico": 15.1, "valor_texto": null, "unidad": "g/dL" }, "corrige_a_version": 1 }
    ]
  }
}
```

Responde `200`, incluso sin versiones (`vigente: null`, `versiones: []`), o `404 MUESTRA_NO_ENCONTRADA`, ademas de errores de middleware. La version vigente es la de mayor `version_number`.

## Errores transversales

Los errores de regla de negocio usan `{ "error": "CODIGO", "detail": "mensaje" }`. La validacion de Laravel usa su formato estandar `422`. El middleware de tenant devuelve `{ "message": "..." }`. No se debe inferir que una respuesta `500` contiene un valor clinico valido ni reintentar escrituras automaticamente sin una clave de idempotencia.
