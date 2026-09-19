# Evidencia: error critico recuperable

1. Desde Confirmar correccion, seleccione **Confirmar nueva version**.
2. El prototipo muestra **Solicitud incierta**: representa timeout o `500` despues de enviar PATCH, sin respuesta confirmatoria.
3. El mensaje explica que PATCH no es idempotente y no ofrece reintento automatico.
4. Seleccione **Consultar historial**: se muestra V2 existente y el estado indica que no debe reenviarse.

Esta recuperacion evita una version adicional. Se basa en la limitacion real: el contrato v2 no expone `Idempotency-Key` ni control de concurrencia.
