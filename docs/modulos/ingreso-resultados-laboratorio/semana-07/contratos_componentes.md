# Semana 7: Componentes y contratos

## Componentes backend encontrados

| Componente | Entrada | Salida | Dependencias permitidas |
|---|---|---|---|
| `LabResultV2Controller` | HTTP, JWT, `X-Tenant-ID`, JSON | JSON y HTTP status | Use cases, `Request`, excepciones de dominio |
| `ListarResultadosPendientesUseCase` | tenant desde adaptador de muestra | lista de muestra/prueba | puertos de muestra y prueba |
| `IngresarResultadoUseCase` | `IngresarResultadoCommand` | `VersionResultado` v1 | puertos, validador, politica, transaccion |
| `CorregirResultadoUseCase` | `CorregirResultadoCommand` | `VersionResultado` v(n+1) | mismos puertos y politica de correccion |
| `ConsultarHistorialUseCase` | tenant, `sampleId` | vigente e historial | puertos de muestra, prueba y versiones |
| Domain | valores, unidad, muestra y prueba ya mapeados | entidad o excepcion tipada | PHP puro y contratos propios |
| Adaptadores Eloquent | contratos Domain/Application | filas mapeadas | modelos, Eloquent, DB |

Dependencias prohibidas: Domain no depende de HTTP, Laravel ni Eloquent; Controller no consulta SQL; Application no conoce modelos Eloquent concretos.

## UI conceptual desde el contrato existente

| Componente UI | Consume | Muestra o envia |
|---|---|---|
| `PendingResultsTable` | `GET /pendientes` | barcode, prueba, tipo esperado y unidad canonica |
| `ResultEntryForm` | `POST /` | `sample_id`, tipo, valor numerico/textual y unidad |
| `CorrectionForm` | `PATCH /muestras/{id}/correccion` | motivo y nuevo contenido, nunca version manual |
| `HistoryPanel` | `GET /historial/{id}` | vigente, versiones y motivo de correccion |
| `ValidationFeedback` | errores `422`, `409`, `404` | mensaje seguro, sin repetir valores clinicos en logs |

La UI no calcula rangos, no decide aceptacion y no asigna tenant: usa metadatos de pendientes y las respuestas del servidor.
