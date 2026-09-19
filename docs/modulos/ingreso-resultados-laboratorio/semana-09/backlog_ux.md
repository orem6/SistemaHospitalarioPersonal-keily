# Semana 9: Backlog UX priorizado

| Prioridad | Item | Justificacion | Criterio de aceptacion |
|---|---|---|---|
| P0 | H-01 Confirmar correccion | Previene que una nueva version clinica se cree con valor/motivo equivocado. | Antes de PATCH se visualizan vigente, nuevo contenido, unidad y motivo; cancelar no llama API. |
| P0 | H-05 Recuperar solicitud incierta | Un timeout tras PATCH no permite asumir fracaso sin riesgo de v(n+2). | La UI ofrece historial y no reintento automatico cuando no conoce respuesta. |
| P1 | H-02 Contraste y foco | Frecuente en captura; afecta lectura y localizacion de error. | Auditoria visual verifica 4.5:1 texto y foco 3:1; foco visible en todos los controles. |
| P1 | H-03 Resumen de errores | Reduce omisiones cuando servidor devuelve varios 422. | Resumen anunciable lista campos; cada enlace mueve foco y conserva valores validos. |
| P1 | H-06 Teclado/dialogo | Afecta usuarios de teclado y confirmaciones. | Tab sigue orden visual; Escape cancela; foco retorna al invocador. |
| P2 | H-04 Ayuda unidad/motivo | Recuperable, pero mejora integridad y comprension. | Ayuda explica unidad segun tipo y que motivo queda en historial sin depender de hover. |

La prioridad combina integridad del resultado, frecuencia durante captura/correccion y capacidad de recuperacion. No se agrega calculo de rangos: el contrato actual no lo expone.
