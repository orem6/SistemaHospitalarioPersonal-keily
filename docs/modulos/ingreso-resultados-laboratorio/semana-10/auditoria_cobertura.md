# Semana 10: Auditoria de cobertura

## Revision de artefactos preexistentes

| Archivo revisado | Ya satisfecho | Incompleto o faltante detectado | Mejora aplicada / evidencia necesaria |
|---|---|---|---|
| `responsive_semana_10.md` | Priorizaba barcode, prueba, valor, unidad y confirmacion; reconocia ausencia de rangos. | No definia las pantallas, estados, orden completo de teclado, contraste, rol real de historial ni significado exacto de pendiente. | Se amplia con cinco pantallas, accesibilidad, privacidad, carga, exito y la limitacion de API. Falta validacion en dispositivo/AT al implementar. |
| `breakpoints.md` | Cubria 320-430 px, tarjetas y una columna. | No explicaba dialogo, distribucion intermedia, ausencia de scroll ni preservacion de contenido. | Se precisan cuatro rangos y restricciones verificables. Falta inspeccion visual a 320 y 430 px. |
| `escenario_camino_feliz.md` | Definia captura numerica y `201`. | No cubria carga, tipo texto, estado vacio, semantica de pendiente, prevencion de doble envio ni `422`. | Se completa el escenario contra contrato. Falta ejecucion E2E con datos sinteticos. |
| `escenario_error_movil.md` | Identificaba respuesta incierta y evitaba reintento automatico. | No separaba errores conocidos ni declaraba ausencia de idempotencia/control de concurrencia. | Se detallan `422`, `404`, `401/403`, `500` y la decision de historial. Falta simulacion de timeout real. |
| `evidencia_semana_10.md` | Declaraba contrato, cuatro wireframes y dos escenarios. | Era una afirmacion sin trazabilidad de rutas/campos y sin distinguir evidencia disponible de pendiente. | Se reemplaza por fuentes y plan de evidencia de implementacion. |
| `01-pendientes-movil.puml` | Tarjeta, vacio, rol y foco. | No explicitaba carga ni que pendiente es una etiqueta derivada. | Se actualiza la pantalla con ambos estados. |
| `02-captura-movil.puml` | Tipo, valor, unidad, error y acciones. | No diferenciaba `TEXTO`, unidad de solo lectura, carga ni resumen accesible. | Se actualiza con esas reglas. |
| `03-confirmar-correccion-movil.puml` | Resume versiones, unidad, motivo y Escape. | No indicaba retorno de foco ni bloqueo durante envio. | Se incorpora. |
| `04-error-historial-movil.puml` | Historial y solicitud incierta. | Mezclaba tratamiento de `404`/`422` sin acciones y no mostraba prevencion por contenido coincidente. | Se actualiza con ramificaciones recuperables. |

## Requisitos obligatorios y cierre

| Requisito | Cobertura Semana 10 | Estado |
|---|---|---|
| Propuesta responsive de 3-5 pantallas | Cinco wireframes Salt: pendientes, captura, correccion, confirmacion y recuperacion/historial. | Completo documentalmente. |
| Reglas de breakpoint | `breakpoints.md`, de 320 px a desktop fuera de alcance. | Completo documentalmente. |
| Dos escenarios moviles | Camino feliz y conexion limitada tras correccion. | Completo documentalmente. |
| Decisiones de contenido | Jerarquia, tipo, unidad, ausencia de rango, datos minimizados y semantica de pendiente. | Completo documentalmente. |
| Manejo de error | Carga, vacio, `201`, `422`, `404`, `401/403`, `500`/timeout y consulta de historial. | Completo documentalmente. |
| Coherencia S8/S9 | Cubre H-01 a H-06 sin cambiar contrato ni prometer offline. | Completo documentalmente. |
| API v2 real | Respeta rutas, rol de escritura, historial JWT, tipos y versionado append-only. | Completo documentalmente. |
| Evidencia de interfaz implementada | No existe frontend movil de esta entrega para ejecutar pruebas visuales, tactiles o de lector de pantalla. | Pendiente explicito. |

La propuesta no inventa rangos de referencia, diagnostico, selector de tenant, idempotencia, cola offline, ni un estado de resultado `pendiente`. Su defensa academica distingue esas limitaciones verificables de las decisiones UX que si pueden aplicarse en cliente.
