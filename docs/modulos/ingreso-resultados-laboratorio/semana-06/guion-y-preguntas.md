# Semana 6: Guion y preguntas de defensa

## Guion breve

1. "Defiendo el flujo v2 real: muestras aceptadas pendientes, captura, correccion versionada e historial."
2. "La capa HTTP valida forma; Application orquesta; Domain decide reglas; Infrastructure implementa puertos con Eloquent."
3. "La correccion no hace UPDATE: inserta v(n+1), conserva v(n) y enlaza `corrige_a_version`."
4. "El tenant entra por `X-Tenant-ID`; captura y correccion exigen `TecnicoLab`. El historial solo tiene JWT hoy, y lo reconozco como brecha."
5. "Repository Pattern evita que Application conozca Eloquent; el repositorio de versiones solo inserta o consulta."
6. "No extraigo microservicio porque aun hay transaccion local, dependencias compartidas y ausencia de metricas que compensen el costo distribuido."
7. "Mi cambio practico completa autor persistido, usando el actor JWT que ya existe sin ampliar el payload."

## Preguntas probables

| Pregunta | Respuesta basada en evidencia |
|---|---|
| Por que no se sobreescribe una correccion? | `VersionResultado::correccion()` crea n+1 y el repositorio no expone UPDATE/DELETE. |
| Que valida un resultado numerico? | Valor finito, tipo esperado y unidad canonica mediante `ContenidoResultado` y `ValidadorContenidoResultado`. |
| Como evitas ver datos de otro hospital? | Middleware resuelve tenant y los use cases comparan `muestra->tenantId`; el test de historial cruzado responde 404. |
| Que SOLID aplicas? | DIP por puertos y provider; ISP historico por contratos enfocados. La defensa v2 usa puertos por necesidad de caso de uso. |
| El medico puede consultar historial v2? | El codigo actual deja la ruta con JWT sin rol; es una brecha, no una capacidad que deba afirmarse como segura. |
| Por que no microservicio? | No hay mediciones ni contratos externos estables; captura requiere consistencia fuerte con muestra y catalogo. |
| Como implementarias autor obligatorio? | Propagar el actor ya autenticado a entidad y repositorio, sin aceptarlo desde el body. |
| Que prueba demuestra correccion? | `flujo_feliz_ingreso_correccion_conserva_versiones`: verifica v2, enlace a v1 y valor original intacto. |
