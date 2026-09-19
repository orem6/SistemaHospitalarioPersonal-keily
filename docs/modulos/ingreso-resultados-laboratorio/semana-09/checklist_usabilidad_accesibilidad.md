# Semana 9: Checklist de usabilidad y accesibilidad

**Escala:** OK = evidenciado en Semana 8; Hallazgo = requiere correccion; No evaluable = wireframe textual no permite comprobarlo.

| Area revisada | Estado | Evidencia o limite |
|---|---|---|
| Visibilidad de carga, vacio y exito | OK | `estados_y_mensajes.md` y wireframes 01/03 |
| Lenguaje clinico y flujo versionado | OK | tipo, unidad y "creara v(n+1)" en wireframes |
| Control del usuario | Hallazgo | no se define salida/retroceso durante llamada en curso |
| Consistencia de mensajes API | Hallazgo | no hay patron de resumen global para errores multiples |
| Prevencion de error | Hallazgo | confirmacion no exige relectura de motivo de correccion |
| Reconocimiento antes que recuerdo | OK | pendientes muestra prueba, tipo esperado y unidad |
| Eficiencia por teclado | Hallazgo | Tab se menciona, pero no orden ni atajos verificables |
| Diseno minimalista y privacidad | OK | sin paciente/historia; datos minimizados |
| Recuperacion de error | OK parcial | conserva formulario; falta distinguir timeout de 409 |
| Ayuda contextual | Hallazgo | ayuda solo aparece en pendientes, no en motivo/unidad |
| Labels e instrucciones | OK parcial | se declaran labels; falta nombre accesible de acciones iconicas futuras |
| Foco y foco visible | Hallazgo | no se define indicador visible ni retorno de foco tras dialogo |
| Contraste y no solo color | Hallazgo | se exige texto/icono, pero no contraste medible |
| Errores identificables | OK parcial | hay texto asociado; falta resumen anunciable para multiples errores |
| Confirmacion | Hallazgo | captura confirma; correccion no tiene wireframe de confirmacion separado |

No se declara conformidad WCAG completa: los wireframes textuales no permiten medir contraste, semantica HTML ni comportamiento real de lector de pantalla.
