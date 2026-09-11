# ADR-001: Arquitectura del módulo en el proyecto SHI (Etapa 2)

- **Estado:** Aceptado
- **Fecha:** 2026-08-21
- **Decisora:** Keily Fabiola Orellana Marroquín (`orem6`)
- **Módulo:** Ingreso de resultados de laboratorio (ASII-19)

## Contexto

La Etapa 1 entregó un prototipo funcional en PHP 8.2 vanilla con capas Presentation/Application/Domain/Persistence. La Etapa 2 exige evolucionarlo dentro del proyecto real SHI (Laravel 12, multi-tenant con `X-Tenant-ID`, JWT, Spatie Permission) manteniendo:

1. la regla central: **solo muestras aceptadas admiten resultados**;
2. validación de **tipo/unidad** contra el catálogo;
3. correcciones como **versiones nuevas** sin sobrescribir la anterior;
4. los endpoints ISP de la semana 2 operativos (no romper a otros estudiantes).

Restricciones adicionales: sin ramas/worktrees/PRs paralelos; datos 100% ficticios; referencia a CENTRAL solo como UUID lógico; pruebas reproducibles.

## Decisión

Se adopta **Repository Pattern con puertos definidos por el dominio** y una migración aditiva:

1. **Puertos en Domain** (`app/Domain/LabResults/Contract`): `MuestraReaderInterface`, `PruebaReaderInterface`, `VersionResultadoRepositoryInterface`. Representan *solo lo que el módulo necesita* (lectura de muestras, catálogo, registro/consulta de versiones) — no un CRUD genérico.
2. **Adaptadores en Infrastructure** (`app/Infrastructure/LabResults/Persistence/Eloquent`): implementan los puertos con Eloquent. `EloquentVersionResultadoRepository` expone únicamente `registrar` (INSERT) y consultas — **la inmutabilidad es estructural**: no existe vía de código para actualizar/borrar versiones.
3. **Casos de uso en Application** orquestan transacción + políticas + repositorios mediante Commands (DTOs crudos); el parseo y las reglas viven en Domain.
4. **Versión vigente = MAX(version_number)**: se elimina la necesidad de una columna `is_current` que pueda desincronizarse; el historial es la fuente de verdad.
5. **Rutas paralelas `/api/v1/lab-results/v2`** con controller propio (`LabResultV2Controller`): coexistencia con la semana 2; cero regresión (31 pruebas previas intactas).
6. **Migración única reversible** (`add_acceptance_and_versioned_results_to_laboratory`): tabla `lab_result_versions` + columnas aditivas en `samples` (`acceptance_status`, `rejection_reason`, `lab_test_id`) y `lab_tests` (`code`, `result_type`). No se altera el enum `status` existente.
7. **Multi-tenant:** el tenant llega por middleware al contenedor (`currentTenant`); los adaptadores filtran por `tenant_id`; los casos de uso verifican pertenencia antes de actuar.
8. **Portabilidad PostgreSQL:** SQL estándar (sin tipos exclusivos de SQLite); la suite corre en SQLite `:memory:` para CI local y el mismo esquema aplica en producción PostgreSQL.

## Alternativas consideradas

| Alternativa | Por qué se descartó |
|---|---|
| Reemplazar los endpoints ISP semana 2 | Rompería contratos usados por otros flujos/evidencias; alto riesgo de regresión. |
| Columna `is_current` para la versión vigente | Estado derivado propenso a corrupción; MAX(version_number) es equivalente y siempre consistente. |
| UPDATE sobre `lab_results` para corregir | Viola la regla central: destruye trazabilidad e historial clínico. |
| FK remota hacia CENTRAL | Acoplamiento entre bases; se usa UUID lógico sin integridad referencial remota. |
| Repositorio genérico CRUD | Expone operaciones que el dominio no debe permitir (p. ej. borrar versiones). |

## Consecuencias

**Positivas**
- Reglas de negocio testeable sin base de datos (9 pruebas unitarias puras).
- Imposibilidad técnica de sobrescribir historial.
- Coexistencia pacífica con el trabajo previo; regresión cero demostrada.
- Cambiar Eloquent por otro mecanismo (DBAL, API) no toca Domain/Application.

**Responsabilidades que introduce**
- Toda nueva operación sobre versiones debe añadirse primero al puerto de Domain y evaluarse si rompe el append-only.
- Los adaptadores deben seguir libres de reglas de negocio.

**Cumplimiento verificado:** suite completa `46 pruebas / 148 aserciones PASS`.
