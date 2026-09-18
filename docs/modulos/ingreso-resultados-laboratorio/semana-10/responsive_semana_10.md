# Semana 10: Propuesta responsive

## Jerarquia movil 320-430 px

1. Barcode abreviado y prueba identifican la muestra sin datos de paciente.
2. Tipo esperado y unidad canonica aparecen antes del campo de valor.
3. Valor, unidad y accion principal ocupan una sola columna; rango no se muestra porque API v2 no lo entrega.
4. Historial y acciones secundarias se ubican despues del guardar o en enlace separado.
5. Correccion siempre muestra vigente, nuevo contenido, unidad y motivo antes de confirmar.

La accion primaria ocupa ancho completo, tiene objetivo tactil minimo de 44 x 44 px y no depende de hover. Guardar se deshabilita mientras la solicitud esta en curso; Cancelar conserva navegacion predecible.

## Relacion con Semana 9

- P0 H-01: confirmacion movil de correccion resume datos y permite cancelar sin PATCH.
- P0 H-05: ante respuesta incierta no hay reintento automatico; se ofrece historial.
- P1 H-02/H-06: foco visible, orden vertical y Escape en dialogo cuando hay teclado.
- P1 H-03: resumen de errores arriba enlaza al primer campo invalido.
