# Escenario B: Conexion limitada tras correccion

1. TecnicoLab completa nuevo valor y motivo, revisa dialogo de confirmacion y envia PATCH.
2. Si la conexion falla antes de conocer respuesta, aparece estado incierto, no "fallo definitivo".
3. Se conserva borrador solo en memoria de la pantalla; no se promete cola/offline ni sincronizacion productiva.
4. La accion primaria es Consultar historial. Si v(n+1) existe, no se reintenta; si no existe, el usuario decide reenviar.

**Riesgo:** PATCH no es idempotente; reintentar ciegamente puede crear otra version. **Criterio:** no hay reintento automatico ni boton "Reintentar" como accion principal cuando el estado es incierto.
