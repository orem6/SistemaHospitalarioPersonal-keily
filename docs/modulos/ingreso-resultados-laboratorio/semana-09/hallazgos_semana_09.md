# Semana 9: Hallazgos

**Severidad:** P0 = integridad clinica o bloqueo; P1 = alto impacto/frecuencia; P2 = recuperable; P3 = mejora menor.

| ID | Pantalla | Problema y evidencia | Rol/impacto | Criterio | Correccion verificable | Pri. |
|---|---|---|---|---|---|---|
| H-01 | Correccion | No hay confirmacion separada que repita motivo y valor antes de `PATCH`; Semana 8 solo confirma que crea version. | TecnicoLab; correccion clinica equivocada. | Prevencion de errores, WCAG 3.3.4 contextual. | Dialogo resume v vigente, nuevo valor, unidad y motivo; Guardar requiere confirmacion explicita. | P0 |
| H-02 | Captura/correccion | No se define contraste minimo ni foco visible; Salt no prueba color. | TecnicoLab; puede perder campo activo/error. | WCAG 1.4.3, 2.4.7 a evaluar. | Especificar contraste 4.5:1 para texto normal y foco visible con 3:1 contra adyacente. | P1 |
| H-03 | Formularios | Errores por campo existen, pero no hay resumen anunciado cuando varios campos fallan. | TecnicoLab; reintentos lentos. | WCAG 3.3.1, 4.1.3 a evaluar. | Tras 422, foco en resumen `role=alert` con enlaces a cada campo invalido. | P1 |
| H-04 | Unidad/motivo | La ayuda contextual no explica por que unidad se bloquea en texto ni que motivo se conserva en historial. | TecnicoLab; unidad/motivo incorrectos. | Heuristica ayuda y correspondencia. | Texto de ayuda junto a campos, visible por foco y sin ocultar validacion. | P2 |
| H-05 | Confirmacion/error | El mensaje 500/red no distingue solicitud no enviada de respuesta perdida; reintentar puede crear correccion adicional. | TecnicoLab; duplicacion de version. | Recuperacion ante error. | Ofrecer "Consultar historial" antes de reintentar cuando estado de solicitud sea desconocido. | P0 |
| H-06 | Navegacion | Se menciona Tab, sin orden, retorno de foco ni Escape para confirmacion. | Teclado; bloqueo o contexto perdido. | WCAG 2.1.1, 2.4.3 a evaluar. | Orden documentado, Escape cancela dialogo y al cerrar foco vuelve al boton invocador. | P1 |

Estos hallazgos no cambian los codigos API: `409`, `422`, `404` y `500` siguen siendo las fuentes de feedback del controller real.
