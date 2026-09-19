# Semana 8: Reglas de interaccion

## Rol y navegacion

- `TecnicoLab` es el unico rol diseñado para pendientes, captura y correccion porque las rutas v2 lo exigen.
- Historial se modela para usuario JWT autenticado: la ruta real no tiene middleware de rol. La UI no debe afirmar que es exclusivo de tecnico.
- Cada llamada incluye JWT y `X-Tenant-ID`; no existe selector manual de tenant en estas pantallas.

## Validacion y confirmacion

| Momento | Regla UX basada en contrato |
|---|---|
| Captura | Mostrar tipo esperado y unidad canonica recibidos de pendientes; no calcular rangos ni banderas clinicas. |
| Numerico | Solicitar valor numerico; enviar unidad; asociar `422` al campo correspondiente. |
| Texto | Solicitar texto no vacio y ocultar/deshabilitar unidad; el servidor decide validez final. |
| Confirmar | Resumir muestra, tipo, valor y unidad; no revelar paciente ni historia clinica. |
| Correccion | Exigir motivo visible de 10-500 y explicar que crea v(n+1), no modifica v(n). |
| Exito | Tras `201`, anunciar version creada y ofrecer volver a pendientes o historial. |

## Accesibilidad y privacidad

- Todo input tiene label; Tab recorre acciones; foco pasa a resumen, exito o primer error.
- Error usa texto, icono y asociacion al campo, no solo color.
- No registrar valores clinicos completos en telemetria UX; minimizar a barcode, prueba y version cuando baste.
- No mostrar controles de correccion cuando no exista vigente; el servidor sigue siendo autoridad ante carrera o reintento.
