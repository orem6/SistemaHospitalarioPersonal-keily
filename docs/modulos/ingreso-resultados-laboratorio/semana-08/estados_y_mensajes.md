# Semana 8: Estados y mensajes

| Estado | Mensaje y accion recuperable |
|---|---|
| Carga pendientes | "Cargando muestras pendientes..."; deshabilitar recarga duplicada y conservar foco. |
| Vacio | "No hay muestras aceptadas pendientes."; permitir recargar. |
| Valor invalido | "Ingrese un numero valido." o "Ingrese texto no vacio."; foco al campo. |
| Unidad incompatible | "La unidad debe coincidir con la unidad canonica de la prueba."; no inventar conversion. |
| Tipo incompatible | "El tipo de resultado no coincide con la prueba."; mostrar tipo esperado. |
| Motivo invalido | "El motivo debe tener entre 10 y 500 caracteres."; conservar contenido. |
| Sin cambios | "La correccion debe cambiar el contenido vigente."; volver a valor. |
| Duplicado 409 | "Ya existe una version inicial. Consulte historial antes de reintentar." |
| No encontrado 404 | "La muestra no existe o no pertenece al tenant actual."; volver a pendientes. |
| No autorizado | "No puede realizar esta accion."; no revelar existencia del recurso. |
| Fallo 500/red | "No se pudo completar la solicitud. Revise conexion y reintente."; mantener formulario local. |

La interfaz no muestra rangos de referencia ni clasifica anormal/critico: el contrato v2 entrega tipo y unidad, y crea banderas en falso; no hay calculo de rango expuesto.
