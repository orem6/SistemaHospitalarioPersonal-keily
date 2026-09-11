# Declaración de uso de herramientas de Inteligencia Artificial

**Estudiante:** Keily Fabiola Orellana Marroquín — carnet/cuenta `orem6`
**Curso:** Análisis de Sistemas e Informática II (ASII-19)
**Módulo:** Ingreso de resultados de laboratorio
**Fecha:** 21 de agosto de 2026

## 1. Declaración

Declaro que en el desarrollo de este módulo utilicé un asistente de programación basado en IA (CLI **opencode**, modelo *ox-alpha*) como herramienta de apoyo, bajo mi dirección continua. El diseño final, las decisiones de arquitectura documentadas en `ADR-001-arquitectura.md`, la revisión del código y la validación mediante pruebas son de mi responsabilidad; comprendo y puedo explicar cada parte entregada.

## 2. Alcance del uso de IA

| Actividad | Uso de IA |
|---|---|
| Diseño de reglas de negocio y políticas | Asistido; requisitos provienen del enunciado del curso |
| Generación de código (Domain/Application/Infrastructure/Presentation) | Co-escrito con asistencia; revisado y corregido por mí |
| Migraciones y seeder de datos ficticios | Asistido |
| Pruebas automatizadas (unit + feature) | Co-escritas con asistencia; ejecutadas y verificadas localmente |
| Diagramas PlantUML | Generados con asistencia a partir del código real |
| Documentación (ESPECIFICACION, ADR, EVIDENCIA) | Redactada con asistencia; datos y resultados tomados de ejecuciones reales |
| Ejecución de pruebas y corrección de fallos | Realizada por mí con apoyo del asistente (p. ej., alineación de contratos entre capas) |

## 3. Compromisos

1. **No inventé validaciones ni resultados:** cada regla citada en la documentación existe en el código (`app/Domain/LabResults/...`) y está cubierta por pruebas que pasan (`46 passed / 148 assertions`).
2. Los datos usados son 100 % ficticios.
3. No se utilizaron credenciales ni datos reales de pacientes o instituciones.
4. Entiendo las tecnologías empleadas: PHP 8.2, Laravel 12, Eloquent, Repository Pattern, JWT, multi-tenant por cabecera `X-Tenant-ID`, PHPUnit.
5. Estoy en capacidad de defender este trabajo en sustentación: explicar el flujo de versionamiento append-only, los puertos del dominio y el mapeo de errores HTTP.

## 4. Verificación

Cualquier afirmación de esta entrega puede reproducirse con:

```powershell
php artisan migrate:fresh --seed --force
php artisan test          # 46 passed (148 assertions)
```

y siguiendo los comandos paso a paso de `EVIDENCIA.md`.

---

**Firma:** Keily Fabiola Orellana Marroquín
