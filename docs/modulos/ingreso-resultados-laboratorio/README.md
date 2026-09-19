# Índice de artefactos — Etapa 2 y Semana 5 (orem6)

| Artefacto | Archivo |
|---|---|
| Especificación completa (portada, índice, arquitectura, API, pruebas) | [ESPECIFICACION.md](./ESPECIFICACION.md) |
| Decisión de arquitectura | [adr/ADR-001-arquitectura.md](./adr/ADR-001-arquitectura.md) |
| Evidencias obligatorias reproducibles | [EVIDENCIA.md](./EVIDENCIA.md) |
| Declaración de uso de IA | [DECLARACION_IA.md](./DECLARACION_IA.md) |
| Texto del Pull Request | [PULL_REQUEST.md](./PULL_REQUEST.md) |
| Semana 5: contrato API real | [semana-05/01-contrato-api.md](./semana-05/01-contrato-api.md) |
| Semana 5: integracion y frontera | [semana-05/02-integracion-y-frontera.md](./semana-05/02-integracion-y-frontera.md) |
| Semana 5: plan Git manual | [semana-05/03-plan-git.md](./semana-05/03-plan-git.md) |
| Semana 6: presentacion y defensa | [semana-06/presentacion.md](./semana-06/presentacion.md) |
| Semana 6: matriz de evidencia | [semana-06/matriz-decision-evidencia.md](./semana-06/matriz-decision-evidencia.md) |
| Semana 6: cambio practico | [semana-06/cambio-practico.md](./semana-06/cambio-practico.md) |
| Semana 6: guion y preguntas | [semana-06/guion-y-preguntas.md](./semana-06/guion-y-preguntas.md) |
| Semana 6: diagrama trazable | [semana-06/uml/trazabilidad-defensa.puml](./semana-06/uml/trazabilidad-defensa.puml) |

## Diagramas UML (`uml/`, fuentes PlantUML editables)

1. `casos_de_uso.puml` — actores y casos de uso del módulo.
2. `clases_dominio.puml` — modelo de dominio, políticas, validador y puertos.
3. `secuencia_ingreso.puml` — captura inicial (versión 1) con todos los caminos de error.
4. `secuencia_correccion.puml` — corrección versionada append-only + consulta de historial.
5. `componentes.puml` — capas Presentation/Application/Domain/Infrastructure y puertos.
6. `er_modelo.puml` — modelo de datos (tabla nueva + columnas aditivas).
7. `estados.puml` — máquina de estados muestra/resultado versionado.
8. `semana-05/uml/cliente-servidor-y-frontera.puml` — cliente-servidor y evaluacion de frontera futura.

Generar imágenes: `plantuml uml/*.puml` (requiere [PlantUML](https://plantuml.com/) o la extensión VS Code).

## Código relevante

- Casos de uso: `app/Application/LabResults/UseCase/`
- Comandos: `app/Application/LabResults/Command/`
- Dominio: `app/Domain/LabResults/{Model,Policy,Service,Contract,Exception}/`
- Infraestructura: `app/Infrastructure/LabResults/Persistence/Eloquent/`
- API v2: `app/Http/Controllers/LabResults/LabResultV2Controller.php` + `routes/api.php`
- Migración: `database/migrations/2026_08_21_000001_add_acceptance_and_versioned_results_to_laboratory.php`
- Seeder: `database/seeders/LabResultsScenarioSeeder.php`
- Pruebas: `tests/Unit/LabResults/DomainRulesTest.php`, `tests/Feature/LabResults/VersionedResultsFlowTest.php`
