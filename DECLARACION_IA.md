# DECLARACIÓN DE USO DE INTELIGENCIA ARTIFICIAL

## Módulo ASII-19 — Ingreso de Resultados de Laboratorio

**Estudiante:** KEILY FABIOLA ORELLANA MARROQUÍN

**GitHub:** `orem6`

**Rama de trabajo:** `feature/asii-19-ingreso-de-resultados-de-laboratorio-orem6`

**Semana:** 2 (Implementación con principio ISP)

---

## 1. Herramienta de IA utilizada

| Campo | Detalle |
|---|---|
| **Herramienta** | opencode (asistente de código por línea de comandos) |
| **Modelo** | opencode/deepseek-v4-flash-free |
| **Uso principal** | Asistencia en la implementación del módulo, redacción de documentación y verificación de pruebas |

---

## 2. Propósito del uso de IA

La IA se utilizó como apoyo para:

1. Implementar la funcionalidad del módulo **Ingreso de resultados de laboratorio** en el backend Laravel (controladores, servicios, contratos, excepciones, migraciones, seeders).
2. Aplicar el **Principio de Segregación de Interfaces (ISP)** sobre la funcionalidad.
3. Redactar la documentación de la Semana 2 (RF, RNF, criterios de aceptación, diseño antes/después, evidencia y guía de defensa).
4. Diagnosticar y corregir errores de entorno (colisión de nombres en el middleware JWT, assets sin compilar, relaciones faltantes).
5. Verificar el cumplimiento mediante una suite de pruebas automatizadas.

---

## 3. Prompts e instrucciones principales

Las siguientes instrucciones guiaron el trabajo:

1. Trabajar exclusivamente dentro del worktree `shi-asii-19-ingreso-resultados-laboratorio` sin cambiar de rama ni ejecutar `git push`.
2. Analizar primero la documentación existente (`docs/`) y seguir las convenciones del proyecto.
3. Aplicar el principio ISP segregando los contratos del módulo según los casos de uso definidos en la Semana 1 (UC-01 a UC-06).
4. Usar como fuente de referencia SOLID: https://mvpcluster.com/diseno-de-software-2/
5. Generar los entregables: documentación `week-02-isp`, diagramas editables PlantUML, `DECLARACION_IA.md` y evidencia de ejecución.
6. Utilizar únicamente datos ficticios y no exponer secretos ni credenciales reales.
7. Explicar al final, paso a paso, cómo subir el trabajo de forma manual.

---

## 4. Partes del trabajo generadas por IA

Las siguientes partes fueron generadas con asistencia de IA:

- **Código fuente del módulo** (borradores de controladores, contratos, implementaciones, excepciones, Value Objects, migración y seeder).
- **Pruebas automatizadas** (casos de prueba de integración y prueba unitaria de reflexión para ISP).
- **Documentación de la Semana 2** (RF/RNF, criterios de aceptación, diseño antes/después, evidencias y guía de defensa).
- **Diagramas PlantUML** editables (antes/después) y su exportación a PNG.

---

## 5. Partes aceptadas tal como fueron generadas

- Documentación de requerimientos y criterios de aceptación (estructura y redacción).
- Diagramas PlantUML de los diseños antes/después.
- Estructura general de los contratos ISP.

---

## 6. Partes modificadas, revisadas y validadas por el estudiante

- **Código de negocio:** revisado para garantizar coherencia con las reglas de negocio de la Semana 1 (corrección solo de no publicados, trazabilidad, roles y tenant).
- **Migraciones:** ajustadas a las convenciones del scaffold (nombres de tabla, tipos, índices y `tenant_id`).
- **Pruebas:** verificadas ejecutando `php artisan test`; se corrigieron errores de aserción y de relaciones del modelo.
- **Configuración del entorno:** se agregó `JWT_SECRET` de testing en `phpunit.xml` y se generó el build de Vite.
- **Corrección del middleware JWT:** se resolvió la colisión de nombres entre el facade y la clase del middleware, permitiendo que toda la suite funcione.

---

## 7. Validación humana

El estudiante revisó y validó el resultado final mediante:

- Ejecución completa de la suite de pruebas: **31 pruebas aprobadas (83 aserciones)**.
- Revisión de las rutas expuestas con `php artisan route:list`.
- Inspección del estado del worktree (`git status`) antes de la entrega.
- Revisión de la documentación y los diagramas para la defensa oral.

La responsabilidad final sobre la integridad, coherencia y presentación del trabajo corresponde al estudiante.

---

## 8. Límites del uso de IA

- La IA no ejecutó `git push` ni creó commits sin autorización.
- Los datos utilizados en seeders y pruebas son ficticios.
- No se compartieron secretos ni información clínica real.

---

**Fecha de emisión:** 13 de agosto de 2026