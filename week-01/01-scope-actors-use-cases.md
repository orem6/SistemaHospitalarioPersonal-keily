# ASII-19 — Alcance, actores y casos de uso

---

# 1. Identificación de la actividad

| Campo | Información |
|--------|-------------|
| Estudiante | KEILY FABIOLA ORELLANA MARROQUÍN |
| GitHub | `orem6` |
| Módulo oficial | Ingreso de resultados de laboratorio |
| Actividad adaptada | Captura y corrección controlada de resultados pendientes |
| Semana | 1 |
| Tipo de evidencia | Análisis, UML y trazabilidad inicial |

---

# 2. Contexto del repositorio

El Sistema Hospitalario Integrado (SHI) proporciona la infraestructura base para la autenticación, control de acceso y administración de usuarios. El módulo **Ingreso de resultados de laboratorio** corresponde a un diseño académico que modela el proceso de captura y corrección controlada de resultados pendientes.

Es importante diferenciar entre las funcionalidades existentes del sistema y las funcionalidades propuestas para este módulo.

| Elemento | Estado verificado | Uso en ASII-19 |
|----------|-------------------|----------------|
| Autenticación | Existente | Control de acceso |
| Roles de usuario | Existentes | Determinan permisos |
| Gestión de resultados de laboratorio | Diseño propuesto | Módulo modelado |
| Captura de resultados | Diseño propuesto | Proceso principal |
| Corrección controlada | Diseño propuesto | Proceso principal |

---

# 3. Problema que se modela

Actualmente se requiere modelar el proceso mediante el cual el personal del laboratorio registra los resultados pendientes de los exámenes realizados. Además, el proceso debe permitir la corrección controlada de aquellos resultados que requieran modificaciones antes de ser publicados para consulta médica.

---

# 4. Objetivo del módulo

Modelar el proceso de captura y corrección controlada de resultados pendientes, garantizando que únicamente el personal autorizado pueda registrar, modificar y publicar resultados de laboratorio de forma segura y controlada.

---

# 5. Límite del sistema

El módulo comprende las siguientes actividades:

- Consulta de resultados pendientes.
- Registro de resultados.
- Corrección de resultados.
- Validación de información.
- Publicación de resultados.

No incluye la toma de muestras, procesamiento del examen ni la consulta clínica del paciente.

---

# 6. Alcance incluido

El módulo permite:

- Consultar resultados pendientes.
- Registrar resultados de laboratorio.
- Corregir resultados antes de su publicación.
- Validar la información ingresada.
- Publicar resultados para su consulta.

---

# 7. Fuera de alcance

No forman parte de esta actividad:

- Gestión de pacientes.
- Gestión de médicos.
- Procesamiento de muestras.
- Facturación.
- Inventario del laboratorio.
- Desarrollo de interfaces o código fuente.


# 8. Actores

| Actor | Tipo | Estado | Responsabilidad |
|-------|------|--------|-----------------|
| Técnico de laboratorio | Principal | Propuesto | Registrar y corregir resultados pendientes. |
| Médico | Secundario | Existente | Consultar resultados publicados. |
| Sistema Hospitalario | Sistema | Existente | Validar información y almacenar resultados. |
| Base de Datos | Sistema | Existente | Almacenar los resultados de laboratorio. |



# 9. Reglas de negocio

- Solo el técnico de laboratorio puede registrar resultados pendientes.
- Solo pueden corregirse resultados que aún no hayan sido publicados.
- Todo resultado debe validarse antes de almacenarse.
- Cada modificación debe quedar registrada.
- El médico únicamente puede consultar resultados publicados.
- Los resultados publicados no pueden modificarse directamente sin un nuevo proceso de corrección.

# 10. Procesos principales

Los procesos principales del módulo son:

1. Consultar resultados pendientes.
2. Registrar resultados de laboratorio.
3. Validar la información.
4. Corregir resultados cuando sea necesario.
5. Publicar resultados.
6. Consultar resultados publicados.


# 11. Catálogo de Casos de Uso

| ID | Caso de Uso | Actor Principal |
|----|-------------|-----------------|
| UC-01 | Consultar resultados pendientes | Técnico de laboratorio |
| UC-02 | Registrar resultado de laboratorio | Técnico de laboratorio |
| UC-03 | Corregir resultado pendiente | Técnico de laboratorio |
| UC-04 | Publicar resultado | Técnico de laboratorio |
| UC-05 | Consultar resultado publicado | Médico |
| UC-06 | Validar información | Sistema Hospitalario |

# 12. Descripción breve de los casos de uso

## UC-01 – Consultar resultados pendientes

**Objetivo:** Permitir al técnico visualizar los exámenes que aún no tienen resultados registrados.

**Actor principal:** Técnico de laboratorio.

**Precondición:** El usuario debe estar autenticado y tener permisos para acceder al módulo.

**Flujo principal:**

1. El técnico ingresa al módulo.
2. El sistema muestra la lista de resultados pendientes.
3. El técnico selecciona el examen correspondiente.

**Resultado:** Se visualizan los exámenes pendientes.

**Excepción:** No existen resultados pendientes para registrar.

---

## UC-02 – Registrar resultado de laboratorio

**Objetivo:** Registrar el resultado obtenido de un examen de laboratorio.

**Actor principal:** Técnico de laboratorio.

**Precondición:** Debe existir un examen pendiente.

**Flujo principal:**

1. Seleccionar el examen.
2. Ingresar el resultado.
3. Guardar la información.

**Resultado:** El resultado queda almacenado.

**Excepción:** El sistema detecta información incompleta o inválida.

---

## UC-03 – Corregir resultado pendiente

**Objetivo:** Permitir modificar un resultado antes de ser publicado.

**Actor principal:** Técnico de laboratorio.

**Precondición:** El resultado aún no ha sido publicado.

**Flujo principal:**

1. Seleccionar el resultado.
2. Realizar la corrección.
3. Guardar los cambios.

**Resultado:** El resultado queda actualizado.

**Excepción:** El resultado ya fue publicado.

---

## UC-04 – Publicar resultado

**Objetivo:** Publicar el resultado validado para que pueda ser consultado.

**Actor principal:** Técnico de laboratorio.

**Precondición:** El resultado debe estar validado.

**Flujo principal:**

1. Seleccionar el resultado.
2. Confirmar la publicación.
3. El sistema publica el resultado.

**Resultado:** El resultado queda disponible para consulta.

**Excepción:** Error durante la publicación.

---

## UC-05 – Consultar resultado publicado

**Objetivo:** Permitir al médico consultar resultados disponibles.

**Actor principal:** Médico.

**Precondición:** El resultado debe encontrarse publicado.

**Flujo principal:**

1. Buscar el paciente.
2. Consultar el resultado.
3. Visualizar la información.

**Resultado:** El médico consulta el resultado.

**Excepción:** El resultado aún no ha sido publicado.

---

## UC-06 – Validar información

**Objetivo:** Verificar que la información ingresada sea correcta antes de almacenarla.

**Actor principal:** Sistema Hospitalario.

**Precondición:** El técnico ha ingresado un resultado.

**Flujo principal:**

1. Revisar datos ingresados.
2. Verificar consistencia.
3. Confirmar el registro.

**Resultado:** Información validada.

**Excepción:** Existen errores de validación.

# 13. Diagrama UML de Casos de Uso

El diagrama de casos de uso correspondiente al módulo **Ingreso de resultados de laboratorio** se encuentra en el archivo:

```
diagrams/use-case-diagram.puml
```

Este diagrama representa los actores involucrados, los casos de uso principales y las relaciones existentes entre ellos.

# 14. Decisiones y supuestos de diseño

Para el modelado del módulo se establecieron los siguientes supuestos:

- Solo el Técnico de Laboratorio puede registrar y corregir resultados.
- El Médico únicamente consulta resultados publicados.
- Todo resultado debe ser validado antes de almacenarse.
- El sistema registra las modificaciones realizadas sobre los resultados.
- Los resultados publicados no pueden modificarse directamente sin un nuevo proceso de corrección.
- El módulo representa una propuesta de diseño académico y no una funcionalidad implementada actualmente.