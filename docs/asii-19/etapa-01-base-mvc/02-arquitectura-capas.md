# 2 · Arquitectura por capas

```
Presentation  (public/index.php, Controller, Views, bin/demo.php)
      ↓  comandos (DTOs)
Application   (UseCases: Ingresar, Corregir, ListarPendientes, ConsultarHistorial)
      ↓  puertos
Domain        (entidades, VOs, enums, políticas, validador, excepciones, contratos)
      ↑ implementa
Persistence   (PDO + consultas preparadas; repositorios concretos)
```

| Capa | Responsabilidad | Prohibido |
|---|---|---|
| Presentation | recibir entrada, validar presencia/formato básico, invocar casos de uso, presentar | SQL, reglas de negocio |
| Application | coordinar el caso de uso y su flujo, orquestar dominio vía contratos | SQL, HTML |
| Domain | REGLAS CENTRALES: muestra aceptada, tipo/unidad válidos, versionado inmutable | conocer PDO/HTTP |
| Persistence | persistir con PDO + prepared statements; mapear filas → entidades | contener reglas de negocio |

## Reglas de negocio y dónde viven

| Regla | Ubicación |
|---|---|
| Solo muestras ACEPTADAS admiten resultados | `Domain/Policy/ResultEntryPolicy` |
| Muestra rechazada ⇒ excepción `MuestraRechazadaException` | `Domain/Exception` |
| Tipo debe coincidir con la definición de la prueba | `Domain/Service/ResultContentValidator` |
| Unidad obligatoria y canónica para NUMERICO | `ResultContentValidator` |
| TEXTO no lleva unidad; texto no vacío ≤ 500 chars | `Domain/Model/ContenidoResultado` |
| Corrección = versión nueva (n+1) con motivo | `Domain/Model/ResultVersion::correccion` |
| Versiones inmutables (append-only) | `Persistence/.../PdoResultVersionRepository` (solo INSERT/SELECT; **sin UPDATE ni DELETE**) |

## Composition Root

`src/AppContainer.php` ensambla manualmente las dependencias: es el único punto
donde se conectan dominio y persistencia. Esto permite que la segunda etapa
sustituya los adaptadores PDO por repositorios Eloquent/PostgreSQL sin tocar
los casos de uso.

## Modelo de datos mínimo

- `lab_test_definitions` — catálogo local ficticio (código, nombre,
  `result_type` NUMERICO|TEXTO, unidad canónica).
- `samples` — muestra local (`id` UUID, `tenant_id`, `patient_ref` como
  UUID lógico CENTRAL sin FK remota, barcode único, estado
  PENDIENTE|ACEPTADA|RECHAZADA).
- `lab_result_versions` — filas inmutables por versión:
  `UNIQUE(sample_id, version_number)`, `corrected_from_version`,
  `correction_reason`. Sin CHECK de negocio en SQL: esas reglas pertenecen al dominio.
