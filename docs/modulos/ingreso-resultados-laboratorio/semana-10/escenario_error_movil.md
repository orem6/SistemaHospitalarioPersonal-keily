# Escenario B: Conexion limitada tras correccion

1. `TecnicoLab` completa nuevo valor y motivo (10-500), revisa vigente, nuevo contenido, unidad y motivo en el dialogo y envia un unico `PATCH`.
2. Si hay timeout, desconexion o `500` sin cuerpo que confirme la escritura, aparece **Solicitud incierta**, no "fallo definitivo". Se conserva el borrador solo en memoria de esa pantalla; no se promete cola offline ni sincronizacion.
3. La accion primaria es Consultar historial. Si el historial muestra v(n+1) con el contenido enviado, se informa que la correccion ya se registro y no se reintenta. Si no aparece, el usuario puede volver a editar y enviar conscientemente.
4. Si la respuesta es conocida, se trata segun contrato: `422 SIN_CAMBIOS` mantiene el formulario y explica que debe diferir de vigente; otro `422` muestra resumen y foco; `404 MUESTRA_NO_ENCONTRADA` vuelve a Pendientes sin revelar datos; `401`/`403` piden autenticar o muestran acceso no autorizado. Un `201` anuncia la version creada.

**Riesgo:** PATCH no es idempotente y no expone `Idempotency-Key` ni control de concurrencia; reintentar ciegamente puede crear otra version. **Criterio:** no hay reintento automatico ni boton "Reintentar" como accion principal cuando el estado es incierto. Un fallo conocido antes de enviar puede ofrecer volver a editar, nunca duplicar el envio en curso.
