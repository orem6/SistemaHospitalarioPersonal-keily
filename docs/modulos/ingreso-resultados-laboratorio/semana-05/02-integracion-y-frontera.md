# Semana 5: Integracion cliente-servidor y frontera de servicio

**Base:** contrato real de `routes/api.php`, `LabResultV2Controller` y adaptadores ASII-19 en `origin/develop` (`8a1c08a`). Este documento extiende, no duplica, `ESPECIFICACION.md` y `adr/ADR-001-arquitectura.md`.

## Cliente-servidor actual

```text
Cliente JWT + X-Tenant-ID
  -> API / LabResultV2Controller
  -> Use case Application
  -> Politicas, Value Objects y puertos Domain
  -> Repository interface
  -> Adaptadores Eloquent + DB::transaction
  -> PostgreSQL (samples, lab_tests, lab_result_versions)
```

El cliente obtiene JWT mediante el flujo de autenticacion existente y envia el tenant en cada solicitud. `TenantMiddleware` resuelve el tenant y lo publica como `currentTenant`; el JWT autentica. En ingreso, correccion e inicio de cola, Spatie exige `TecnicoLab`. El controller valida forma HTTP, crea el command con tenant y actor, y transforma excepciones de dominio a JSON. Los casos de uso encapsulan lectura, validacion y escritura; `EloquentTransactionManager` usa una transaccion local.

El aislamiento se realiza en la cola mediante `tenant_id` y para operaciones por id comparando el tenant de la muestra con el del request. Las versiones se consultan por muestra despues de esa comprobacion. El cliente debe mostrar errores de validacion sin exponer detalles internos y no incluir valores clinicos completos en telemetria de navegador.

## Propiedad de datos

| Dato | Propietario actual | Uso de ASII-19 | Frontera futura |
|---|---|---|---|
| Tenant y usuario | identidad/plataforma SHI | referencia y autorizacion | externo; conservar `tenant_id` y `user_id` logicos |
| Paciente e historia clinica | pacientes/EMR | indirecto por orden | externo; no copiar datos identificables |
| Orden y sus items | laboratorio clinico existente | origen de `Sample` | referencia externa si se separa |
| Muestra y aceptacion | recepcion de muestras | precondicion y referencia de captura | contrato/snapshot externo, no propiedad de resultados |
| Catalogo `lab_tests` | catalogo de laboratorio | tipo y unidad canonica | lectura sincronica o replica versionada |
| `lab_result_versions` | ASII-19 | fuente de verdad append-only | propiedad natural del posible servicio |

No hay comunicacion de red entre estos componentes hoy: comparten proceso y base de datos del monolito. La FK de `samples` apunta a ordenes, y ordenes a paciente, historia, nota y usuario; por ello separar resultados sin contratos para muestra, catalogo e identidad romperia el flujo.

## Consistencia y resiliencia

Ingreso y correccion ejecutan lectura, politicas e `INSERT` en una unica transaccion local. La unicidad `(sample_id, version_number)` protege la secuencia de versiones a nivel de base. La lectura de vigente seguida de insercion sigue pudiendo competir entre dos solicitudes concurrentes: no hay bloqueo pesimista, version esperada ni clave de idempotencia. El cliente no debe reintentar automaticamente `POST` o `PATCH` tras timeout; primero debe consultar historial. Las lecturas son consistentes con la base local al completar la transaccion.

No se publican eventos, no hay outbox, colas ni llamadas HTTP remotas en ASII-19. Si en el futuro se notifica una correccion o resultado critico, el evento debe generarse despues del commit mediante outbox, incluir `event_id`, `tenant_id`, `sample_id`, `result_version_id`, `version_number` y fecha, y usar consumidores idempotentes. La entrega puede ser al menos una vez; los valores clinicos completos no deben ir en logs ni en eventos que no los necesiten.

Propuesta no implementada para dependencias futuras: timeout finito, reintentos solo en lectura o entrega asincrona idempotente, backoff, limite de intentos y alerta ante cola atrasada. Una falla al consultar muestra/catalogo en una separacion futura debe impedir la escritura, pues aceptar un resultado sin precondicion o unidad valida viola consistencia clinica. Para proyecciones, notificaciones y analitica si es aceptable consistencia eventual; para captura, correccion, tenant y version vigente no lo es.

## Seguridad y observabilidad

Roles existentes: `Admin`, `Medico`, `Enfermera`, `TecnicoLab`, `Recepcionista` y `Bioquimico`. ASII-19 v2 restringe cola, ingreso y correccion a `TecnicoLab`; historial tiene solamente JWT, una brecha respecto al principio de minimo privilegio. `Bioquimico` posee el permiso `lab.results.validate`, pero ese permiso pertenece a las rutas separadas de Modulo 20, no a las cuatro rutas v2 de ASII-19. La correccion exige motivo y agrega una version; no sobrescribe la anterior.

Debe registrarse, sin implementar infraestructura: request/correlation ID, timestamp, usuario autenticado, tenant, nombre de operacion, `sample_id`, `result_version_id` cuando exista, duracion, resultado/excepcion y codigo HTTP. Nunca registrar `numeric_value`, `text_value`, motivo libre completo, JWT, ni datos del paciente. Los logs deben permitir correlacionar una operacion con auditoria sin convertir el log en una copia de datos clinicos.

Brechas verificadas que requieren issue posterior antes de uso clinico ampliado:

1. El comando recibe el actor, pero `VersionResultado` no lo conserva y el repositorio no escribe `entered_by`; no hay trazabilidad persistida por version.
2. `GET /historial/{sampleId}` no usa middleware de rol; cualquier JWT autenticado del tenant puede leer valores clinicos de la muestra.
3. No existe `Idempotency-Key`, bloqueo de version ni prueba de carrera concurrente.
4. La consulta de prueba por id no filtra `tenant_id`; la muestra si se aisla, pero el catalogo debe comprobar pertenencia antes de una futura separacion.

## Evaluacion de frontera de microservicio

| Criterio medible | A. Monolito modular actual | B. Servicio de resultados futuro |
|---|---|---|
| Consistencia de captura | una transaccion DB local | exige API de muestra/catalogo, snapshots o sagas |
| Latencia/dependencias | cero salto de red | minimo dos dependencias remotas en escritura |
| Escalado y despliegue | escala con SHI | despliegue y escala independientes |
| Propiedad de versiones | clara dentro del modulo | clara y transferible |
| Operacion | logs y DB compartidos | observabilidad, outbox, DLQ y soporte adicionales |
| Riesgo actual | bajo; ya existe arquitectura por capas | alto; contratos y ownership aun incompletos |

La conclusion es mantener ASII-19 como modulo del monolito. Sus puertos ya permiten sustituir adaptadores, pero no justifican una extraccion: no se midieron necesidad de despliegue independiente, carga aislada, equipos autonomos ni limites de disponibilidad. La separacion se reconsidera solo si durante tres meses se observa al menos uno: necesidades de despliegue bloqueadas por SHI, volumen que exige escalado aislado, un equipo propietario sostenido, o integraciones externas que consuman resultados versionados. Antes deben cerrarse las cuatro brechas y acordar contratos versionados de muestra, catalogo, identidad y tenant.

## Migracion razonada, no implementada

1. Corregir trazabilidad, autorizacion de historial e idempotencia/concurrencia dentro del monolito; agregar pruebas de seguridad y carrera.
2. Definir contratos de lectura para muestra y catalogo, con `tenant_id` obligatorio, y publicar eventos mediante outbox despues del commit. Mantener la base actual como fuente de verdad.
3. Medir volumen, latencia p95, tasa de error, reintentos, profundidad de outbox y frecuencia de despliegues; decidir con los umbrales acordados, no por preferencia arquitectonica.
4. Solo si se cumple el criterio, replicar versiones append-only hacia una base propiedad del servicio, verificar conteos/hash por tenant y operar lectura en sombra.
5. Cambiar consumidores gradualmente, conservar rollback a la lectura monolitica y retirar la propiedad compartida solo despues de reconciliacion y observabilidad estable.

No se deben compartir escrituras entre bases ni mover paciente, orden, muestra, usuarios o tenants al servicio de resultados. Las referencias deben ser IDs estables con contratos explicitamente versionados.
