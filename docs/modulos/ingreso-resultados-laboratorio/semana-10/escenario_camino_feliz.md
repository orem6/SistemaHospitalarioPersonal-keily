# Escenario A: Camino feliz movil

1. `TecnicoLab` abre Pendientes. Mientras carga el GET ve tarjetas esqueleto; al recibir `200` ve una tarjeta con **Pendiente de captura**, barcode, prueba, tipo y unidad. Esa etiqueta significa muestra aceptada sin versiones, no un estado clinico de resultado.
2. Selecciona Capturar. Para una prueba `NUMERICO`, ingresa el valor con teclado numerico y ve la unidad canonica de solo lectura; para `TEXTO`, ingresa texto y no ve ni envia unidad. No se muestran rangos ni banderas porque v2 no los expone como datos interpretables.
3. La validacion local orienta, pero el servidor confirma tipo, valor y unidad con `POST`. Guardar se deshabilita y anuncia el progreso para evitar doble envio.
4. Si recibe `201`, se anuncia "Resultado ingresado como version 1" usando el numero recibido. Se ofrecen Pendientes o Historial; no se asume validacion clinica. Si la cola queda vacia, muestra "No hay muestras aceptadas pendientes" y Recargar.

**Criterio:** en 320 px no existe desplazamiento horizontal; Guardar tiene 44 px de alto minimo. Un `422` conserva las entradas validas, anuncia un resumen y permite ir al campo invalido.
