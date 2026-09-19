# Semana 6: Cambio practico defendido

## Cambio propuesto

**Cada version de resultado debe persistir obligatoriamente el autor autenticado que la capturo o corrigio.** El cliente no puede enviar ni reemplazar ese autor.

## Por que es una variacion real

El sistema ya conserva `version_number`, `corrige_a_version`, `correction_reason` y `resulted_at`. Tambien los commands reciben `ingresadoPor` o `corregidoPor`. Sin embargo, `VersionResultado` no tiene ese atributo y `EloquentVersionResultadoRepository::registrar()` no escribe `entered_by`, aunque la migracion y el modelo lo contemplan nullable. No se propone repetir versionado; se completa la trazabilidad de autor.

## Impacto por capa

| Capa | Cambio defendible |
|---|---|
| Domain | Agregar `enteredBy` readonly a `VersionResultado`; exigir entero positivo al crear v1/v(n+1). |
| Application | Pasar `IngresarResultadoCommand::$ingresadoPor` y `CorregirResultadoCommand::$corregidoPor` a los factories. El actor ya viene del JWT. |
| Persistence | Mapear `entered_by` en `registrar()` y al rehidratar. Mantener columna nullable para filas historicas ya existentes. |
| Controller/API | Sin nuevo campo de request: `actor($request)` sigue siendo la unica fuente. La respuesta podria exponer solo un id si la politica clinica lo permite. |
| Seguridad/tenant | Verificar usuario autenticado; no confiar en un `user_id` enviado por cliente. El tenant de muestra sigue siendo la frontera de acceso. |
| Pruebas | Assert de `entered_by` en v1 y v2; intento con body que incluya autor no lo altera; aislamiento tenant sin regresion. |

## Compatibilidad y riesgos

- Es aditivo: no cambia URI, payload ni semantica de versiones previas.
- La migracion actual permite nulo; una restriccion `NOT NULL` se evaluaria despues de completar datos historicos, no en el mismo cambio.
- El id de usuario es dato de auditoria: no se debe registrar el valor clinico completo en logs solo para asociarlo.
- Este cambio no resuelve por si solo reintentos ni carreras concurrentes; esas brechas permanecen documentadas en Semana 5.

## Respuesta corta para defensa

"No agregaria el autor al JSON. Ya obtengo el actor del JWT; lo propago por Application y lo persisto con la version append-only. Asi evito suplantacion, mantengo tenant y no rompo clientes existentes."
