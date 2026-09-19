# ASII-19: Defensa de arquitectura - Semana 6

- **Estudiante:** Keily Fabiola Orellana Marroquin (`orem6`)
- **Flujo defendido:** captura y correccion controlada de resultados pendientes.

---

## 1. Problema, alcance y actores

**Mensaje:** un resultado solo se captura para una muestra aceptada y su correccion conserva evidencia clinica.

- TecnicoLab: consulta pendientes, ingresa y corrige.
- Sistema: valida tipo, valor, unidad, tenant y version vigente.
- La muestra, orden, paciente y catalogo son dependencias; no son propiedad de la version.
- Alcance v2: pendientes, captura, correccion e historial. Publicacion pertenece al flujo ISP previo, no a estas cuatro rutas v2.

**Evidencia:** `docs/asii-19/week-01/01-scope-actors-use-cases.md`; `docs/modulos/ingreso-resultados-laboratorio/ESPECIFICACION.md` secciones 3 y 4.

---

## 2. RF, RNF y aceptacion verificable

**Mensaje:** las reglas clinicas se convierten en criterios observables y pruebas.

- RF-01: pendientes del tenant; solo `ACCEPTADA` y sin version.
- RF-02/RF-06: captura numerica o textual, validada contra tipo y unidad de la prueba.
- RF-03: correccion con motivo, sin sobrescribir v(n).
- RNF: aislamiento tenant, control de rol, trazabilidad y prueba automatizada.

**Evidencia:** `docs/asii-19/week-02-isp/01-rf-rnf-criterios-aceptacion.md`; `tests/Feature/LabResults/VersionedResultsFlowTest.php`.

---

## 3. Arquitectura en capas

**Mensaje:** el controlador adapta HTTP; las reglas no viven en Eloquent.

```text
API -> Controller -> Application use case -> Domain -> port -> Eloquent -> DB
```

- Presentation: `LabResultV2Controller` valida forma y traduce errores.
- Application: orquesta transaccion y comandos.
- Domain: `ContenidoResultado`, politicas y excepciones tipadas.
- Infrastructure: adaptadores Eloquent; el provider resuelve dependencias.

**Evidencia:** `app/Http/Controllers/LabResults/LabResultV2Controller.php`; `app/Providers/LabResultsServiceProvider.php`; `docs/modulos/ingreso-resultados-laboratorio/uml/componentes.puml`.

---

## 4. Repository Pattern, DIP e ISP

**Mensaje:** los casos de uso dependen de puertos pequenos, no de la base de datos.

- `MuestraReaderInterface`: muestra y cola pendiente.
- `PruebaReaderInterface`: definicion de tipo y unidad.
- `VersionResultadoRepositoryInterface`: insertar y consultar versiones.
- `EloquentVersionResultadoRepository` solo realiza `INSERT` y `SELECT`: append-only estructural.

**Evidencia:** `app/Domain/LabResults/Contract/`; `app/Infrastructure/LabResults/Persistence/Eloquent/`; `docs/modulos/ingreso-resultados-laboratorio/adr/ADR-001-arquitectura.md`.

---

## 5. Captura y correccion versionada

**Mensaje:** v1 captura; cada correccion inserta v(n+1), no actualiza la evidencia previa.

1. Se verifica muestra del tenant y estado `ACEPTADA`.
2. Se valida contenido y unidad contra la prueba.
3. La captura crea version 1; duplicado responde `409`.
4. La correccion exige motivo de 10 a 500 caracteres y contenido distinto.
5. `version_number` y `corrige_a_version` conservan la cadena de auditoria.

**Evidencia:** `IngresarResultadoUseCase.php`, `CorregirResultadoUseCase.php`, `VersionResultado.php`, `secuencia_correccion.puml`.

---

## 6. Contrato API, seguridad y tenant

**Mensaje:** el contrato real es `/api/v1/lab-results/v2`; seguridad y tenant son parte de cada llamada.

| Operacion | Ruta | Acceso real |
|---|---|---|
| Pendientes | `GET /pendientes` | JWT + `TecnicoLab` |
| Captura | `POST /` | JWT + `TecnicoLab` |
| Correccion | `PATCH /muestras/{sampleId}/correccion` | JWT + `TecnicoLab` |
| Historial | `GET /historial/{sampleId}` | JWT; sin middleware de rol actual |

- Cabeceras: `Authorization: Bearer` y `X-Tenant-ID`.
- Un id de muestra ajeno al tenant responde como `404`.

**Evidencia:** `routes/api.php` lineas 48-63; `docs/modulos/ingreso-resultados-laboratorio/semana-05/01-contrato-api.md`.

---

## 7. Decisiones, riesgos y frontera de servicio

**Mensaje:** el monolito modular es la decision actual; extraer un servicio hoy aumentaria el riesgo clinico.

- Transaccion local fuerte para captura/correccion.
- No hay eventos, outbox ni llamadas remotas implementadas.
- Riesgos documentados: historial sin rol, autor no persistido, sin idempotencia ni control de carrera.
- `lab_result_versions` es candidato a propiedad futura, pero muestra, orden, paciente, catalogo, usuario y tenant seguirian siendo referencias externas.

**Evidencia:** `docs/modulos/ingreso-resultados-laboratorio/semana-05/02-integracion-y-frontera.md`.

---

## 8. Cambio practico defendido

**Mensaje:** exigir autor persistido por version completa la trazabilidad sin cambiar el contrato clinico.

- Ya existen version, motivo y fecha; el command recibe el actor.
- Brecha real: `VersionResultado` no guarda actor y el repositorio no inserta `entered_by`.
- Cambio propuesto: incluir `enteredBy` inmutable en la entidad y persistirlo desde el usuario JWT, nunca desde el body.
- Mantiene versiones existentes compatibles con `entered_by` nullable; agrega pruebas de actor, tenant y correccion.

**Evidencia:** `app/Application/LabResults/Command/`; `VersionResultado.php`; `EloquentVersionResultadoRepository.php`; `semana-06/cambio-practico.md`.
