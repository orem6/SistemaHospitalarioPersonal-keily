# ASII-19 — Actividad, secuencia y trazabilidad

---

# 1. Propósito del documento

El presente documento describe el comportamiento del módulo **Ingreso de resultados de laboratorio** mediante los diagramas UML de Actividad y Secuencia, además de establecer la trazabilidad entre los casos de uso definidos durante la primera fase del análisis.

---

# 2. Relación con el alcance aprobado

Este documento complementa el análisis realizado en el archivo **01-scope-actors-use-cases.md**.

Los diagramas representan el flujo lógico del proceso de captura y corrección controlada de resultados pendientes, sin describir detalles de implementación ni estructura de base de datos.

---

# 3. Diagrama UML de Actividad

El diagrama de actividad se encuentra en:

```

diagrams/activity-diagram.puml

```

Este diagrama representa el flujo completo desde la consulta de resultados pendientes hasta la publicación del resultado validado.

---

# 4. Decisiones, excepciones y resultados

Durante el proceso pueden ocurrir las siguientes situaciones:

- El resultado ingresado es válido y continúa el flujo.
- El resultado contiene errores y requiere corrección.
- El resultado es publicado correctamente.
- El proceso finaliza mostrando los mensajes correspondientes.

---

# 5. Participantes del Diagrama de Secuencia

Los participantes involucrados son:

- Técnico de Laboratorio.
- Interfaz del Sistema.
- Módulo de Resultados de Laboratorio.
- Base de Datos.
- Médico.

Cada participante interviene únicamente durante las actividades que le corresponden dentro del proceso.

---

# 6. Diagrama UML de Secuencia

El código PlantUML correspondiente se encuentra en:

```

diagrams/sequence-diagram.puml

```

El diagrama representa el intercambio de mensajes entre los actores y el sistema durante la captura, validación, almacenamiento y consulta de resultados de laboratorio.

---

# 7. Validaciones realizadas

Durante el proceso el sistema realiza las siguientes validaciones:

- Existencia del resultado pendiente.
- Integridad de la información ingresada.
- Corrección de datos antes de publicar.
- Confirmación del almacenamiento.
- Disponibilidad del resultado para consulta médica.

---

# 8. Matriz de trazabilidad

| Caso de Uso | Diagrama de Actividad | Diagrama de Secuencia | Resultado |
|--------------|----------------------|-----------------------|-----------|
| UC-01 | Consulta de resultados pendientes | Consulta de resultados | Resultados pendientes mostrados |
| UC-02 | Registro de resultados | Registro de información | Resultado almacenado |
| UC-03 | Corrección de resultados | Corrección de información | Resultado actualizado |
| UC-04 | Publicación del resultado | Confirmación de publicación | Resultado disponible |
| UC-05 | Consulta del médico | Consulta del resultado | Resultado visualizado |
| UC-06 | Validación | Validación del sistema | Información verificada |

---

# 9. Verificación de consistencia

Se verificó que los actores, casos de uso y procesos definidos en el Diagrama de Casos de Uso mantienen consistencia con los Diagramas de Actividad y Secuencia.

Todos los diagramas representan el mismo proceso de negocio y utilizan la misma terminología.

---

# 10. Límites de esta evidencia

Esta documentación corresponde únicamente al análisis UML de la Semana 1.

No incluye implementación, desarrollo de interfaces, creación de base de datos ni programación del módulo.