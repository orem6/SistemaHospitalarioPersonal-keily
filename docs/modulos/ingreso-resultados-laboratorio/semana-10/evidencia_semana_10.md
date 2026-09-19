# Semana 10: Evidencia

## Evidencia de base

| Afirmacion | Fuente verificable |
|---|---|
| Cola para `TecnicoLab`, captura y correccion | `routes/api.php` lineas 49-59: JWT, tenant y `role:TecnicoLab,api`. |
| Historial no es exclusivo de tecnico | `routes/api.php` lineas 61-63: solo grupo JWT/tenant. |
| Datos de pendientes | `LabResultV2Controller::pendientes()` y contrato Semana 5: `muestra_id`, barcode, prueba, tipo esperado, unidad canonica y fecha de recoleccion. |
| Versionado y respuesta de exito | `LabResultV2Controller::store()` y `corregir()`: `201`; correccion conserva la version anterior y devuelve numero de version. |
| Riesgo de duplicacion | Contrato Semana 5, lineas 141 y 169-171: PATCH no es idempotente, sin `Idempotency-Key` ni control de concurrencia. |
| Rango y banderas | Contrato Semana 5, lineas 46 y 48-66: no hay rango; banderas no son entrada y se crean en `false`. |

## Evidencia entregada y pendiente de ejecucion

La propuesta contiene cinco wireframes Salt editables, cuatro rangos responsive y dos escenarios trazables al contrato. No modifica API ni frontend productivo, por lo que no afirma pruebas de dispositivos o lector de pantalla que no se ejecutaron.

Para cerrar evidencia de implementacion futura se requiere comprobar en un viewport real de 320 px y 430 px: ausencia de scroll horizontal, objetivos de 44 px, contraste/foco, orden Tab y retorno del dialogo; probar `200` vacio, carga, `201`, `422`, `404`, `401/403` y timeout/`500`; y verificar que, tras una respuesta incierta, Historial evita enviar otra correccion cuando ya existe v(n+1). Los datos de la evidencia deben ser sinteticos y no capturar valores, motivos ni barcodes completos en telemetria.
