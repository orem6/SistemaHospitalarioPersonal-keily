# Semana 10: Propuesta responsive

## Alcance y jerarquia movil (320-430 px)

La cola v2 no expone un estado de resultado `pendiente`: expone muestras `ACEPTADA` sin versiones. Por eso la etiqueta visible es **Pendiente de captura**, una descripcion de la cola, no un estado clinico persistido ni una afirmacion de validacion. La API tampoco entrega rangos de referencia; no se muestra rango, anormalidad ni criticidad. Aunque la respuesta serializa `es_anormal` y `es_critico`, el contrato real documenta que el dominio los crea en `false`, por lo que no son base para una interpretacion movil.

El orden de lectura y accion es:

1. Estado derivado de la cola, barcode abreviado y prueba. No se muestran nombre, expediente, fecha de nacimiento ni otros datos de paciente.
2. Tipo esperado y unidad canonica antes de pedir el valor. Para `TEXTO`, la unidad queda oculta y no se envia.
3. Valor y, solo en correccion, motivo. La unidad es de solo lectura cuando la prueba numerica la define; la UI no ofrece sustituir la unidad canonica.
4. Accion primaria unica: Capturar, Guardar resultado o Confirmar nueva version. Historial y Cancelar son secundarios y se muestran despues.
5. Antes del `PATCH`, vigente, nuevo contenido, unidad y motivo. Se explica que se crea v(n+1) y se conserva v(n).

## Cinco pantallas propuestas

| # | Pantalla y artefacto | Objetivo movil y contenido | Navegacion/estado |
|---|---|---|---|
| 1 | Pendientes (`01-pendientes-movil.puml`) | Tarjetas, no tabla horizontal: estado Pendiente de captura, barcode, prueba, tipo y unidad. | Capturar lleva a 2. `200 data: []` muestra vacio; carga usa tarjetas esqueleto sin datos clinicos y Recargar repite solo el GET. |
| 2 | Captura (`02-captura-movil.puml`) | Valor, tipo y unidad canonica; ayuda visible para la unidad. | Guardar hace `POST`; exito `201` lleva a 4 o vuelve a 1. Cancelar vuelve a 1 sin enviar. |
| 3 | Correccion (`05-correccion-movil.puml`) | Vigente consultada desde historial, nuevo valor/unidad y motivo de 10-500 caracteres. | Continuar abre 4 solo si la validacion local minima pasa; Volver a historial no escribe. |
| 4 | Confirmar correccion (`03-confirmar-correccion-movil.puml`) | Resumen de v(n), v(n+1), unidad y motivo, sin paciente. | Confirmar hace un solo `PATCH`; Volver a editar y Escape no escriben y devuelven foco al invocador. |
| 5 | Recuperacion e historial (`04-error-historial-movil.puml`) | Solicitud incierta y versiones devueltas por la API. | Consultar historial es primaria ante timeout/`500`; si existe el contenido creado no se reenvia. |

## Interaccion y accesibilidad

- Una columna, sin desplazamiento horizontal a 320 px. Las acciones ocupan todo el ancho disponible, tienen al menos 44 x 44 px y 8 px de separacion minima; no dependen de hover ni de gesto de arrastre.
- Cada control tiene `label`, instrucciones persistentes y error asociado mediante `aria-describedby`. El contraste es al menos 4.5:1 para texto normal; el indicador de foco tiene contraste de 3:1 con los colores adyacentes y no se elimina.
- El orden de Tab coincide con el orden visual: volver/navegacion, resumen, campos, accion primaria, acciones secundarias. En el dialogo se contiene el foco; inicia en el titulo, Escape cancela y al cerrarlo retorna al boton que lo abrio. El teclado numerico se solicita para valor numerico, pero no sustituye validacion ni etiquetas.
- Ante `422`, se conserva lo valido, se muestra un resumen con `role="alert"` al inicio y enlaces a cada campo invalido; el foco pasa al resumen y cada enlace mueve foco al campo. No se anuncia exito hasta recibir `201`.
- Durante `POST` o `PATCH`, el boton que envio se deshabilita, anuncia "Guardando resultado" y no se puede disparar otra escritura. Tras `201`, un mensaje de estado anuncia el numero de version recibido y ofrece Pendientes o Historial.
- El historial se etiqueta como disponible para el usuario autenticado, no como exclusivo de `TecnicoLab`: el endpoint real solo exige JWT. Las pantallas de cola, captura y correccion si son para `TecnicoLab`.
- No se persisten borradores fuera de la pantalla ni se envian valores, motivo o barcode a telemetria. El borrador en memoria se descarta al cerrar o recargar, salvo que el usuario permanezca en la recuperacion de la misma pantalla.

## Relacion con Semana 9

- P0 H-01: la pantalla 4 confirma valor, unidad, motivo y version anterior; cancelar no llama `PATCH`.
- P0 H-05: la pantalla 5 distingue respuesta conocida de incierta y prioriza historial, sin reintento automatico.
- P1 H-02/H-06: contraste, foco visible, orden de teclado, Escape y retorno de foco quedan especificados.
- P1 H-03: el resumen de `422` es anunciable, conserva valores y enlaza a cada error.
- P2 H-04: la ayuda visible explica unidad y permanencia del motivo en el historial.
