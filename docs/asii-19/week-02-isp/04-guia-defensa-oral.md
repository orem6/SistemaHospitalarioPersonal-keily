# ASII-19 — Guía de Defensa Oral (Semana 2)

---

# 1. Objetivo de la defensa

Demostrar la comprensión de la aplicación del **Principio de Segregación de Interfaces (ISP)** sobre el módulo **Ingreso de resultados de laboratorio**, y sustentar con evidencia verificable (código y pruebas) el diseño implementado.

---

# 2. Resumen de tu historia (30 segundos)

> "El módulo registra, corrige y publica resultados de laboratorio pendientes. En la Semana 1 modelé los casos de uso; en la Semana 2 lo implementé en Laravel aplicando el Principio ISP: en lugar de una interfaz monolítica con seis métodos, definí seis contratos pequeños, uno por caso de uso. Cada controlador inyecta solo el contrato que necesita, y lo verifico con una suite de 31 pruebas."

---

# 3. Posibles preguntas y respuestas

## P3.1 ¿Qué es el Principio ISP?
> "El Principio de Segregación de Interfaces dice que ningún cliente debe verse forzado a depender de métodos que no utiliza. Es mejor tener interfaces pequeñas y específicas que una interfaz grande con métodos innecesarios."

**Exactamente:** la definición oficial está en la fuente del curso: [MVP Cluster — Diseño de Software 2](https://mvpcluster.com/diseno-de-software-2/).

## P3.2 ¿Cómo se aplica ISP en este módulo?
> "La interfaz monolítica `LaboratoryResultManager` tenía seis métodos: pendientes, registrar, corregir, publicar, leer publicado y validar. La reemplacé por seis contratos: `PendingResultsProvider`, `ResultEntryWriter`, `PendingResultCorrector`, `ResultPublisher`, `PublishedResultReader` y `ResultValidator`. Cada controlador del módulo inyecta únicamente su contrato."

## P3.3 ¿Qué problema resolvió? (antes vs después)
> "Antes, el controlador de publicación dependía de métodos que no usaba (validar, corregir, leer). Si cambiaba la firma de un método, se afectaban los seis clientes. Después, cada cliente depende solo de lo que usa; se puede sustituir o probar cada responsabilidad de forma aislada y el contrato cambia por una única razón."

## P3.4 ¿Cuál es la diferencia entre ISP y SRP?
> "El SRP se enfoca en clases: cada clase tiene una única razón para cambiar. El ISP se enfoca en el *cliente* de las interfaces: el cliente no debe conocer métodos que no usa. Un diseño cumple ISP cuando cada cliente depende de una interfaz que contiene exactamente lo que necesita."

## P3.5 ¿Cómo se compone o inyecta la dependencia?
> "En Laravel, el `LabResultsServiceProvider` registra cada contrato con su implementación en el contenedor IoC. Los controladores reciben el contrato en su constructor mediante inyección de dependencias; el contenedor resuelve la implementación. Así, los controladores dependen de abstracciones (también refuerza el DIP)."

## P3.6 ¿Cómo se prueba que se cumplió ISP?
> "Con el test `IspSegregationTest`, que usa reflexión: verifica que cada controlador tenga un único parámetro de contrato en el constructor, y que cada contrato exponga un único método. Además, la suite de integración valida el comportamiento por rol y por tenant."

## P3.7 ¿Qué pasa si el controlador necesita publicar Y leer?
> "No pasa nada: un controlador puede inyectar más de un contrato. Lo importante es que cada contrato siga siendo pequeño y enfocado. La segregación no limita la cantidad de interfaces que un cliente usa; limita que una interfaz contenga métodos innecesarios."

## P3.8 ¿Por qué usar Value Objects y excepciones de dominio?
> "Los `ValueObjects` (`ResultInput`, `PublishedResultDetail`) tipifican la entrada y salida de los servicios, evitando arrays 'mágicos'. Las excepciones de dominio (`ResultAlreadyPublishedException`, etc.) codifican las reglas de negocio y permiten al controlador traducirlas a códigos HTTP (`409`, `404`, `422`)."

---

# 4. Estructura sugerida para la presentación (5–7 min)

| Minuto | Contenido | Evidencia a mostrar |
|---|---|---|
| 0–1 | Contexto: módulo, casos de uso (Semana 1) | Diagramas de la Semana 1 |
| 1–2 | Problema: interfaz monolítica (diseño ANTES) | `isp-antes.puml` |
| 2–4 | Solución: contratos segregados (DESPUÉS) | `isp-despues.puml` + código |
| 4–5 | Implementación: controladores, provider, excepciones | Rutas y controladores |
| 5–6 | Verificación: suite de pruebas y reflexión ISP | `php artisan test` |
| 6–7 | Cierre: beneficios, límites y próximos pasos | Evidencia Git |

---

# 5. Puntos clave a mencionar

1. **ISP + DIP juntos:** los controladores dependen de interfaces, no de implementaciones concretas.
2. **Reglas de negocio por rol:** captura/corrección/publicación solo `TecnicoLab`; lectura solo `Médico|Admin`.
3. **Aislamiento por tenant:** verificado con pruebas de lectura cruzada.
4. **Trazabilidad:** cada corrección guarda valor anterior, nuevo y motivo.
5. **Datos ficticios:** todos los seeders y pruebas usan datos de demostración.

---

# 6. Errores comunes a evitar

- No confundir ISP con "muchas clases" — es "contratos enfocados".
- No decir que el ISP fue inventado en la actividad: atribuirlo a la fuente oficial del curso.
- No omitir la evidencia: respaldar cada afirmación con el archivo y la prueba correspondiente.