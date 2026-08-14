# ASII-19 — Requerimientos Funcionales, No Funcionales y Criterios de Aceptación

**Curso:** Análisis de Sistemas II (ASII) — 2026
**Sistema:** Sistema Hospitalario Integrado (HIS)
**Estudiante:** Keily Fabiola Orellana Marroquín (`orem6`)
**Rama de trabajo:** `feature/asii-19-ingreso-de-resultados-de-laboratorio-orem6`
**Fuente de Referencia SOLID:** [MVP Cluster — Diseño de Software 2](https://mvpcluster.com/diseno-de-software-2/)

---

## 1. Requerimientos Funcionales (RF)

Los **Requerimientos Funcionales** definen los comportamientos, operaciones y servicios que el módulo **Ingreso de resultados de laboratorio** debe proveer dentro del Sistema Hospitalario Integrado.

| Código | Requerimiento Funcional | Descripción Detallada | Prioridad | Caso de Uso Relacionado |
|---|---|---|---|:---:|
| **RF-ASII19-01** | **Consulta de resultados pendientes** | El sistema debe permitir al Técnico de Laboratorio consultar los exámenes de laboratorio que aún no tienen resultado registrado, únicamente dentro de su tenant. | **Alta** | `UC-01` |
| **RF-ASII19-02** | **Registro de resultado de laboratorio** | El sistema debe permitir al Técnico de Laboratorio registrar el valor numérico o textual de un examen pendiente, validando la información antes de almacenarla. | **Crítica** | `UC-02`, `UC-06` |
| **RF-ASII19-03** | **Corrección controlada de resultados** | El sistema debe permitir corregir un resultado **solo si aún no ha sido publicado**, dejando trazabilidad de cada cambio (valor anterior, valor nuevo y motivo). | **Alta** | `UC-03` |
| **RF-ASII19-04** | **Publicación de resultados** | El sistema debe permitir publicar un resultado ya registrado para que esté disponible a la consulta médica. Un resultado publicado no puede corregirse ni republicarse. | **Alta** | `UC-04` |
| **RF-ASII19-05** | **Consulta de resultados publicados** | El sistema debe permitir al Médico (o Admin) consultar únicamente resultados publicados del tenant, denegando acceso a resultados aún no publicados o de otro tenant. | **Alta** | `UC-05` |
| **RF-ASII19-06** | **Validación de información** | El sistema debe validar la consistencia e integridad de los datos ingresados (existencia de la orden, unicidad de captura, valor obligatorio, formato numérico) antes de persistir. | **Crítica** | `UC-06` |
| **RF-ASII19-07** | **Aislamiento por tenant** | Todas las consultas y operaciones del módulo deben ejecutarse sobre el tenant autenticado; un tenant no puede leer ni modificar datos de otro. | **Crítica** | `UC-01` a `UC-06` |

---

## 2. Requerimientos No Funcionales (RNF)

Los **Requerimientos No Funcionales** establecen los atributos de calidad y restricciones técnicas del módulo.

| Código | Requerimiento No Funcional | Descripción y Métricas Objetivo | Categoría |
|---|---|---|---|:---:|
| **RNF-ASII19-01** | **Aislamiento estricto por tenant** | Todas las consultas del módulo filtran por `tenant_id`; un usuario de un tenant no puede visualizar ni modificar resultados de otro tenant (verificado con pruebas de aislamiento cruzado). | **Seguridad** |
| **RNF-ASII19-02** | **Control de acceso por rol** | Las rutas de captura, corrección y publicación requieren el rol `TecnicoLab`; las rutas de consulta de publicados requieren `Médico` o `Admin`. Acceso no autorizado responde `403`. | **Seguridad** |
| **RNF-ASII19-03** | **Mantenibilidad y Modularidad (SOLID/ISP)** | Cada cliente del módulo (controlador) depende de **un único contrato enfocado** en su responsabilidad. La segregación de interfaces permite agregar o cambiar implementaciones sin afectar a los clientes existentes. | **Arquitectura** |
| **RNF-ASII19-04** | **Trazabilidad de correcciones** | Cada corrección debe registrar la fecha, el usuario, el campo, el valor anterior, el valor nuevo y el motivo, manteniendo el historial íntegro del resultado. | **Fiabilidad** |
| **RNF-ASII19-05** | **Disponibilidad y atomicidad de pruebas** | El módulo debe ser verificable con una suite de pruebas automatizadas que use una base de datos en memoria (`sqlite :memory:`), sin depender de credenciales reales. | **Fiabilidad** |
| **RNF-ASII19-06** | **Rendimiento** | Las consultas del módulo deben usar índices y eager loading apropiados para evitar N+1 en la consulta de pendientes y publicados. | **Rendimiento** |

---

## 3. Criterios de Aceptación (Gherkin BDD / Checklist)

### 3.1. Criterios de Aceptación para RF-ASII19-01: Consulta de resultados pendientes
* **Escenario 1: El técnico consulta los pendientes de su tenant**
  * **Dado** que un Técnico de Laboratorio autenticado pertenece al tenant "Lab Resultados Demo",
  * **Cuando** envía una solicitud `GET /api/v1/lab-results/pending` con su token JWT válido,
  * **Entonces** el sistema responde con HTTP status `200 OK` y la lista de exámenes pendientes de su tenant.

* **Escenario 2: El técnico no ve pendientes de otros tenants**
  * **Dado** que existen pendientes en otro tenant distinto,
  * **Cuando** el técnico consulta `GET /api/v1/lab-results/pending`,
  * **Entonces** la respuesta **no** contiene ningún elemento del otro tenant.

* **Escenario 3: Un médico no puede consultar pendientes**
  * **Dado** que un usuario con rol `Médico` (sin rol `TecnicoLab`),
  * **Cuando** envía una solicitud `GET /api/v1/lab-results/pending`,
  * **Entonces** el sistema responde con HTTP status `403 Forbidden`.

---

### 3.2. Criterios de Aceptación para RF-ASII19-02 y RF-ASII19-06: Registro y validación de resultados
* **Escenario 1: Captura exitosa de un resultado numérico**
  * **Dado** que existe un examen pendiente con id `X` en el tenant del técnico,
  * **Cuando** envía una solicitud `POST /api/v1/lab-results` con `{"lab_order_item_id": X, "sample_id": Y, "numeric_value": "95"}`,
  * **Entonces** el sistema responde con HTTP status `201 Created` y el resultado queda almacenado.

* **Escenario 2: Captura sin valor numérico ni textual**
  * **Dado** que el técnico envía la solicitud sin `numeric_value` ni `text_value`,
  * **Cuando** el sistema valida la información,
  * **Entonces** responde con HTTP status `422 Unprocessable Entity` y un mensaje de error de validación.

* **Escenario 3: Captura duplicada del mismo examen**
  * **Dado** que el examen pendiente `X` ya tiene un resultado registrado,
  * **Cuando** el técnico intenta registrar un nuevo resultado para `X`,
  * **Entonces** el sistema responde con HTTP status `409 Conflict`.

* **Escenario 4: Examen inexistente**
  * **Dado** que el `lab_order_item_id` no existe en el tenant,
  * **Cuando** el técnico envía la captura,
  * **Entonces** el sistema responde con HTTP status `404 Not Found`.

---

### 3.3. Criterios de Aceptación para RF-ASII19-03: Corrección controlada
* **Escenario 1: Corrección exitosa de un resultado pendiente**
  * **Dado** que un resultado pendiente tiene el valor `95.0000`,
  * **Cuando** el técnico envía `PATCH /api/v1/lab-results/{id}/correct` con `{"numeric_value": "85", "reason": "Redigitación del valor."}`,
  * **Entonces** el sistema responde con HTTP status `200 OK`, el valor queda en `85.0000` y se registra una corrección con valor anterior, nuevo y motivo.

* **Escenario 2: Corrección sin cambios**
  * **Dado** que el técnico envía un valor idéntico al actual,
  * **Cuando** el sistema procesa la corrección,
  * **Entonces** responde con HTTP status `422 Unprocessable Entity`.

* **Escenario 3: Corrección de un resultado publicado**
  * **Dado** que un resultado ya fue publicado,
  * **Cuando** el técnico intenta corregirlo,
  * **Entonces** el sistema responde con HTTP status `409 Conflict`.

---

### 3.4. Criterios de Aceptación para RF-ASII19-04: Publicación de resultados
* **Escenario 1: Publicación exitosa**
  * **Dado** que un resultado está registrado y no publicado,
  * **Cuando** el técnico envía `POST /api/v1/lab-results/{id}/publish`,
  * **Entonces** el sistema responde con HTTP status `200 OK`, y el resultado queda con `published_at` y `published_by` registrados.

* **Escenario 2: Republicación rechazada**
  * **Dado** que el resultado ya está publicado,
  * **Cuando** el técnico intenta publicarlo nuevamente,
  * **Entonces** el sistema responde con HTTP status `409 Conflict`.

---

### 3.5. Criterios de Aceptación para RF-ASII19-05: Consulta de resultados publicados
* **Escenario 1: El médico consulta un resultado publicado**
  * **Dado** que un resultado del tenant está publicado,
  * **Cuando** el médico envía `GET /api/v1/lab-results/{id}` con su token válido,
  * **Entonces** el sistema responde con HTTP status `200 OK` y los datos del resultado.

* **Escenario 2: El médico no puede consultar un resultado no publicado**
  * **Dado** que un resultado aún no está publicado,
  * **Cuando** el médico envía `GET /api/v1/lab-results/{id}`,
  * **Entonces** el sistema responde con HTTP status `404 Not Found`.

* **Escenario 3: Lectura de un resultado de otro tenant**
  * **Dado** que el resultado `id` pertenece a otro tenant,
  * **Cuando** el médico consulta `GET /api/v1/lab-results/{id}`,
  * **Entonces** el sistema responde con HTTP status `404 Not Found` (sin filtrar datos ajenos).

* **Escenario 4: El técnico no puede usar el endpoint de lectura de publicados**
  * **Dado** que un usuario tiene rol `TecnicoLab` (sin `Médico` ni `Admin`),
  * **Cuando** envía `GET /api/v1/lab-results/{id}`,
  * **Entonces** el sistema responde con HTTP status `403 Forbidden`.

---

## 4. Trazabilidad con la Semana 1

| Caso de Uso (Semana 1) | Contrato ISP (Semana 2) | Endpoint |
|---|---|---|
| UC-01 Consultar pendientes | `PendingResultsProvider` | `GET /api/v1/lab-results/pending` |
| UC-02 Registrar resultado | `ResultEntryWriter` | `POST /api/v1/lab-results` |
| UC-03 Corregir pendiente | `PendingResultCorrector` | `PATCH /api/v1/lab-results/{result}/correct` |
| UC-04 Publicar resultado | `ResultPublisher` | `POST /api/v1/lab-results/{result}/publish` |
| UC-05 Consultar publicado | `PublishedResultReader` | `GET /api/v1/lab-results/{result}` |
| UC-06 Validar información | `ResultValidator` | (usado por captura y corrección) |

---

## 5. Estado de cumplimiento

| Requisito / Entregable | Criterio de Cumplimiento | Estado |
|---|---|---|:---:|
| **Requerimientos Funcionales (RF)** | 7 RFs definidos con código, descripción, prioridad y caso de uso asociado. | Completado |
| **Requerimientos No Funcionales (RNF)** | 6 RNFs definidos en categorías de Seguridad, Arquitectura, Fiabilidad y Rendimiento. | Completado |
| **Criterios de Aceptación** | Escenarios BDD en formato *Given/When/Then* para cada flujo del módulo. | Completado |
| **Verificación automatizada** | Suite de 31 pruebas (unitarias ISP + de integración) verificando los criterios. | Completado |