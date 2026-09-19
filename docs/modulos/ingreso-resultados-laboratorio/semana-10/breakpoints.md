# Semana 10: Breakpoints

| Rango | Decision |
|---|---|
| 320-359 px, movil pequeno | Una columna; estado, barcode y prueba en lineas separadas; tarjetas en vez de tabla; todos los botones a ancho completo. El dialogo ocupa el ancho con margen lateral de 16 px. |
| 360-430 px, movil estandar/grande | Una columna; tipo y unidad informativa pueden compartir fila, nunca los controles de accion. Se mantiene la misma jerarquia y objetivos de 44 px. |
| 431-767 px | Se conserva la composicion movil y las tarjetas; no se introduce informacion clinica adicional ni acciones por hover. |
| >=768 px | Fuera del entregable movil: pendientes puede recuperar tabla, pero conserva las mismas columnas permitidas y confirma la correccion en dialogo. |

El contenido no se oculta por breakpoint salvo la redistribucion visual. No hay scroll horizontal a 320 px, ni zoom bloqueado. Teclado, foco visible, contraste y mensajes conservan el mismo comportamiento en todos los rangos.
