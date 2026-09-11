# 3 · Flujo y reglas del módulo

## Flujo principal (captura)

```
Muestra ACEPTADA
  → aparece en "resultados pendientes" (aceptada + sin resultado)
  → captura del resultado (tipo, valor, unidad)
  → validación de dominio (coherencia con la definición de la prueba)
  → INSERT en lab_result_versions como versión 1 (inmutable)
```

## Flujo de corrección

```
Resultado existente (versión n vigente)
  → solicitud de corrección (motivo obligatorio)
  → validación del nuevo contenido
  → INSERT versión n+1 con corrected_from_version = n
  → la versión n permanece intacta y consultable
```

## Casos obligatorios implementados

| # | Caso | Resultado esperado | Evidencia |
|---|---|---|---|
| 1 | Muestra aceptada + valores válidos | Versión 1 almacenada | `demo-casos-obligatorios.txt` § CASO 1 |
| 2 | Muestra rechazada | `MuestraRechazadaException`, 0 versiones | § CASO 2 |
| 3a | Valor numérico `"abc"` | `ValorNumericoInvalidoException`, 0 versiones | § CASO 3a |
| 3b | Unidad `mmol/L` (canónica `g/dL`) | `UnidadInvalidaException`, 0 versiones | § CASO 3b |
| 4 | Corrección sobre v1 | v2 creada (`corrected_from_version=1`), v1 conservada | § CASO 4 + CONSERVACIÓN |

## Decisiones de diseño documentadas

- **Sin flag `is_current`:** la versión vigente es simplemente el máximo
  `version_number`; así las filas son 100% inmutables (nunca se actualizan).
- **Corrección idéntica se rechaza** (`ResultadoSinCambiosException`): evita
  ruido de versiones sin cambios reales.
- **La corrección no exige re-verificar estado de la muestra:** opera sobre un
  resultado ya existente; la política de estado aplica a la *captura inicial*.
- **Referencias CENTRAL = UUID lógico** (`patient_ref`), sin FK remota: los
  datos clínicos son propiedad del hospital.
