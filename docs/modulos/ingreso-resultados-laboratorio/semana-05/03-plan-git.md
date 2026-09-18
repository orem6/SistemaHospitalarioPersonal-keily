# Semana 5: Plan Git manual

- **Issue:** `#19`
- **Rama actual:** `feature/asii-19-semana-05-api-integracion-orem6`
- **Base:** `origin/develop` (`8a1c08a`)
- **Worktree:** `shi-asii-19-semana-05-api-integracion`

## PR manual propuesto

El limite es 400 lineas modificadas por PR. La documentacion de Semana 5 suma 314 lineas modificadas, por lo que cabe en un unico PR coherente:

| PR | Base -> destino | Archivos | Limite |
|---|---|---|---|
| Semana 5 | `feature/asii-19-semana-05-api-integracion-orem6` -> `develop` | indice, contrato, integracion, plan Git y diagrama | 314 |

La rama ya parte de `origin/develop`. El titulo sugerido usa `Refs #19`, no `Fixes`, porque es un entregable documental. La restauracion del soporte de pruebas y la correccion global de configuracion no pertenecen a este PR y deben revisarse por separado.

## Verificacion obligatoria antes de cada commit

```powershell
git add <solo-archivos-del-bloque>
git diff --cached --numstat
git diff --cached --stat
git diff --cached --check
```

No crear PR hacia `main`, no hacer force push y no incluir archivos de otros estudiantes. Tras la revision manual, abrir cada PR contra `develop` con el texto `Refs #19`.
