# Semana 8: Evidencia UX

## Base real

- `RoleSeeder.php`: existen `TecnicoLab`, `Medico`, `Admin`, `Enfermera`, `Recepcionista` y `Bioquimico`.
- `routes/api.php`: solo `TecnicoLab` accede a pendientes, captura y correccion v2; historial requiere JWT.
- `LabResultV2Controller.php`: valida tipo, valores, unidad y motivo; responde `201`, `409`, `422`, `404` y `500` segun caso.
- `01-contrato-api.md`: confirma rutas, tenant y que no hay paginacion ni `Idempotency-Key`.

## Entregable

- Flujo por rol en `user_flow.puml`.
- Cinco wireframes Salt editables: pendientes, captura, confirmacion, correccion e historial/error.
- Reglas y mensajes alineados al contrato, sin inventar rangos ni permisos.

## Validacion prevista

Los artefactos son documentales. La regresion relevante sigue siendo `DomainRulesTest` y `VersionedResultsFlowTest`; no se modifica codigo ni contrato de produccion.
