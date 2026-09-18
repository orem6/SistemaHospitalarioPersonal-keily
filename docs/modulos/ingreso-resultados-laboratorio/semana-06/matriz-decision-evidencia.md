# Semana 6: Matriz decision -> evidencia

| Decision | RF/RNF o criterio | Codigo o documento real | Prueba o evidencia |
|---|---|---|---|
| Pendiente = aceptada sin version | RF-01, RF-07 | `EloquentMuestraReader::pendientesDeCaptura()` | `VersionedResultsFlowTest::tecnico_ve_solo_muestras_aceptadas_sin_resultado` |
| Solo contenido valido entra al dominio | RF-02, RF-06 | `ContenidoResultado::crear()`, `ValidadorContenidoResultado::validar()` | `DomainRulesTest::test_contenido_rechaza_formas_invalidas` |
| Tipo y unidad vienen del catalogo | RF-02/RF-06 | `EloquentPruebaReader`, `PruebaDefinition` | `VersionedResultsFlowTest::ingreso_valida_tipo_y_unidad_contra_la_prueba` |
| Captura inicial no duplica resultado | Criterio duplicado -> 409 | `PoliticaIngresoResultado::assertSinResultadoVigente()` | `VersionedResultsFlowTest::flujo_feliz_ingreso_correccion_conserva_versiones` |
| Correccion es append-only | RF-03, RNF-04 | `VersionResultado::correccion()`, repositorio sin UPDATE/DELETE | mismo flujo: v1 permanece y v2 apunta a v1 |
| Motivo y cambio real son obligatorios | RF-03 | `PoliticaCorreccion`, validacion del controller | caso `SIN_CAMBIOS` y validacion de motivo corto |
| Tenant limita lectura y escritura | RF-07, RNF-01 | `TenantMiddleware`, comparacion en casos de uso | `historial_esta_isolado_por_tenant` |
| Captura/correccion requieren rol | RNF-02 | `routes/api.php`: `role:TecnicoLab,api` | middleware declarado en ruta; pruebas usan tecnico ficticio |
| Controller no conoce Eloquent | RNF-03, DIP | `LabResultV2Controller` inyecta use cases | `LabResultsServiceProvider` enlaza puertos a adaptadores |
| Escritura es atomica local | RNF-05 | `EloquentTransactionManager::ejecutar()` usa `DB::transaction` | codigo revisado; no hay prueba de carrera dedicada |
| API real se mantiene separada del legado ISP | Compatibilidad | `routes/api.php` prefijo `lab-results/v2` | `01-contrato-api.md` secciones 1-4 |
| No extraer microservicio sin medicion | RNF de resiliencia futuro | `semana-05/02-integracion-y-frontera.md` | comparacion monolito/servicio y criterios de decision |

## Lectura de la matriz

La Semana 1 y el ISP previo describen publicacion y consulta medica como antecedentes. La defensa del flujo v2 no afirma que esas operaciones sean rutas v2: se limita a pendientes, captura, correccion e historial, tal como `routes/api.php` implementa hoy.
