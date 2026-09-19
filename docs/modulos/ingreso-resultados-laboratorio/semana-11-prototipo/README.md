# Semana 11: Prototipo navegable ASII-19

## Decision tecnica

El repositorio posee Laravel, Vue 3 y Vite, pero el unico flujo reutilizable de resultados es `resources/js/modules/lab-results/` y pertenece a ASII-20: revisa, valida o rechaza resultados. No modela el contrato v2 de ASII-19 (captura y correccion append-only) y depende del runtime/autenticacion del producto. Para no duplicar el modulo ni modificar backend, el entregable es un HTML, CSS y JavaScript autocontenido en `prototipo/index.html`.

No consume API, persiste datos ni representa autenticacion real. Todos los datos son ficticios. Sus textos, roles y decisiones se derivan de `semana-08/`, `semana-09/`, `semana-10/` y `semana-05/01-contrato-api.md`.

## Ejecucion

Abra `prototipo/index.html` en un navegador o ejecute desde esta carpeta:

```powershell
& "C:\Program Files\Google\Chrome\Application\chrome.exe" "file:///C:/Users/keyor/Desktop/SistemaHospitalario/shi-asii-19-semana-11-prototipo/docs/modulos/ingreso-resultados-laboratorio/semana-11-prototipo/prototipo/index.html"
```

Rutas de evidencia reproducible: agregue `?vista=exito` o `?vista=incierto` a la URL. El control "Confirmar nueva version" simula una respuesta perdida posterior a PATCH, riesgo real del contrato; no afirma que el backend este disponible.

## Cobertura verificable

- Camino feliz: Pendientes > Capturar > Guardar resultado > Exito > Historial > Correccion > Confirmacion.
- Error critico: Confirmar > Solicitud incierta > Consultar historial > V2 existente; no se reintenta PATCH.
- Error de formulario: deje vacio el valor o un motivo menor de 10 caracteres; el resumen `role=alert` recibe foco y enlaza al campo.
- Desktop y movil: reduzca el viewport a 320-430 px. Las tarjetas, formularios y acciones pasan a una columna sin scroll horizontal.
- Accesibilidad: HTML semantico, labels, `aria-describedby`, foco visible, salto a contenido, orden natural Tab, Escape en confirmacion y mensajes de estado con texto.

## Limitaciones deliberadas

La API v2 no expone rangos de referencia. El prototipo muestra esta ausencia, no inventa rango, anormalidad o criticidad. Pendiente de captura significa muestra aceptada sin versiones, no estado clinico persistido. Historial exige JWT en la ruta real; pendientes, captura y correccion requieren `TecnicoLab`.
