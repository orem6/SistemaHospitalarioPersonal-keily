# Semana 7: Refactor antes/despues

## Diagnostico real

El mayor punto de concentracion esta en `app/Http/Controllers/LabResults/LabResultV2Controller.php`: `store()` y `corregir()` repiten la traduccion de request a command para tipo, valor numerico, texto y unidad; el controller tambien serializa versiones y traduce excepciones. Este acoplamiento es de **Presentation**, no mezcla SQL ni reglas clinicas: Domain y Application ya estan separados por puertos.

## Antes

| Responsabilidad concentrada | Dependencia | Riesgo al crecer |
|---|---|---|
| Validacion de forma HTTP | `Request::validate` | diferencia accidental entre captura y correccion |
| Mapeo JSON -> command | campos de contenido | nuevos endpoints repiten conversiones |
| Serializacion y errores | `VersionResultado`, excepciones | respuestas inconsistentes si se agregan operaciones |

## Despues preventivo, no implementado

Extraer en Presentation un `LabResultPayloadMapper` y un `LabResultResponseMapper` cuando exista una tercera operacion que use el mismo contenido. El primero recibe datos ya validados y construye commands; el segundo serializa `VersionResultado` y errores. Controller conservaria autenticacion, tenant, invocacion del use case y codigo HTTP.

| Beneficio | Trade-off |
|---|---|
| Un solo lugar para formato JSON y mapeo repetido | dos clases adicionales para solo dos escrituras hoy |
| Domain/Application permanecen sin HTTP | no corrige una regla clinica ni cambia persistencia |
| Menor riesgo al agregar reanalisis o importacion | requiere pruebas de contrato de respuesta |

## Decision

No se aplica cambio de codigo en Semana 7. Extraer ahora seria refactor artificial: las dos operaciones tienen validaciones distintas (`sample_id` frente a `motivo`) y el controller sigue siendo pequeno. La condicion medible para extraer es una tercera ruta con el mismo payload o divergencias repetidas detectadas en pruebas de contrato.

**Evidencia:** controller lineas 39-98; `IngresarResultadoUseCase`, `CorregirResultadoUseCase`, `EloquentVersionResultadoRepository` y `LabResultsServiceProvider`.
