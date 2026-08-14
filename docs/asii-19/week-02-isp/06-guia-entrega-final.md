# ASII-19 — GUÍA DE ENTREGA FINAL (PASO A PASO)

**Estudiante:** Keily Fabiola Orellana Marroquín (`orem6`)
**Semana:** 2 — Principio ISP
**Fecha:** 13 de agosto de 2026

---

## CONTEXTO

Tu implementación y documentación ya están **completas en el worktree local**. Lo que falta es:

1. Hacer commit y push del trabajo.
2. Registrar el hash del commit en la portada (reemplazar `PENDIENTE`).
3. Armar el documento final en **PDF o DOCX** (portada, índice, introducción, desarrollo, conclusión, bibliografía).
4. Verificar la lista de comprobación final.

---

## PASO 1 — REVISAR TU ESTADO

Abre la terminal en el worktree y verifica que solo estén los archivos esperados:

```powershell
git status
```

Verás 6 archivos modificados (`JwtAuth.php`, `LabOrder.php`, `LabResult.php`, `bootstrap/app.php`, `phpunit.xml`, `routes/api.php`) y los archivos nuevos (controllers, services, migrations, seeders, tests, docs, `DECLARACION_IA.md`).

> Si aparece algo extra (archivos .env, caché, vendor), NO lo agregues. Solo agrega los archivos de la lista.

---

## PASO 2 — AGREGAR Y COMMITEAR

```powershell
git add -A
```

Verifica qué quedó "en stage":

```powershell
git status
```

Crea el commit (usa el mensaje que ya acordamos):

```powershell
git commit -m "ASII-19: implementa ingreso de resultados de laboratorio con principio ISP"
```

Si quieres crear también una **etiqueta** (tag) para que sea fácil identificar el punto evaluado:

```powershell
git tag v1-asii19-isp
```

---

## PASO 3 — SUBIR AL REPOSITORIO

Tu repo personal compartido es el remoto **`individual`** (`https://github.com/orem6/SistemaHospitalarioPersonal-keily.git`). El remoto `nuevo` apunta a un repositorio que **no existe** (`SistemaHospitalario-keily.git`), así que **no lo uses**.

Sube tu rama al repo personal:

```powershell
git push -u individual feature/asii-19-ingreso-de-resultados-de-laboratorio-orem6
```

Sube también tu rama al repo del equipo (origin) si tienes permisos:

```powershell
git push origin feature/asii-19-ingreso-de-resultados-de-laboratorio-orem6
```

> Si el push a `origin` da "permission denied", no es un problema: la entrega se hace contra tu repo compartido `individual`. En `origin` el flujo obligatorio es **PR hacia `develop`**, nunca hacia `main` (ver `docs/worktree-guide.md`).

Sube la etiqueta si la creaste:

```powershell
git push individual --tags
```

> La URL de tu repo compartido ya está registrada en la portada del README.

---

## PASO 4 — REGISTRAR EL COMMIT EN LA PORTADA

1. Obtén el hash corto de tu commit:

   ```powershell
   git log --oneline -3
   ```

2. Abre `docs/asii-19/week-02-isp/README.md` y reemplaza la línea:

   ```
   **Commit evaluado / etiqueta:** `PENDIENTE` ...
   ```

   por el hash real:

   ```
   **Commit evaluado / etiqueta:** `abcd123` (ver enlace: https://github.com/orem6/SistemaHospitalarioPersonal-keily/commit/abcd123)
   ```

3. Actualiza `03-evidencias-ejecucion.md` en la sección "Evidencia Git" con el hash del commit y el `git log --oneline` real.

4. Commit y push de la corrección:

   ```powershell
   git add -A
   git commit -m "ASII-19: registra hash de commit en portada y evidencia git"
   git push individual feature/asii-19-ingreso-de-resultados-de-laboratorio-orem6
   ```

---

## PASO 5 — ARMAR EL DOCUMENTO FINAL (PDF o DOCX)

El documento final DEBE tener: **portada, índice actualizado, introducción, desarrollo, conclusión y bibliografía**.

### Opción A — Word (DOCX) manual (recomendada)
1. Crea un documento Word nuevo.
2. **Portada:** copia la portada e información del estudiante del `README.md` (agrega URL, rama y hash del commit).
3. **Índice:** listar secciones:
   - Introducción
   - 1. Requerimientos funcionales (de `01-rf-rnf...`)
   - 2. Requerimientos no funcionales
   - 3. Criterios de aceptación
   - 4. Diseño antes/después (de `02-isp-antes-despues.md`, inserta los diagramas PNG de `diagrams/images/`)
   - 5. Evidencias de ejecución (de `03-evidencias-ejecucion.md`)
   - 6. Conclusión y bibliografía (de `05-conclusion-bibliografia.md`)
4. **Introducción:** usa la sección "Objetivo" del README.
5. Cierra con **Conclusión y Bibliografía** del documento `05`.
6. Exporta como **PDF** desde Word (Archivo → Exportar → Crear PDF).

### Opción B — Convertir Markdown a PDF con Pandoc (avanzado)
Si tienes [Pandoc](https://pandoc.org) instalado, desde el worktree:

```powershell
pandoc docs/asii-19/week-02-isp/README.md `
  docs/asii-19/week-02-isp/01-rf-rnf-criterios-aceptacion.md `
  docs/asii-19/week-02-isp/02-isp-antes-despues.md `
  docs/asii-19/week-02-isp/03-evidencias-ejecucion.md `
  docs/asii-19/week-02-isp/05-conclusion-bibliografia.md `
  -o DocumentoFinal-ASII19-ISP.pdf --pdf-engine=pdflatex
```

> Las imágenes de los diagramas (`diagrams/images/*.png`) se agregan manualmente en la sección 4 en ambos métodos.

---

## PASO 6 — VERIFICAR LISTA DE COMPROBACIÓN FINAL

| ✔ | Item |
|---|---|
| ☐ | Portada con URL, rama y commit evaluado actualizados. |
| ☐ | Índice actualizado en el documento final. |
| ☐ | Consigna individual cumplida (ISP + RF/RNF + antes/después + evidencia). |
| ☐ | Repositorio compartido actualizado (push hecho). |
| ☐ | Evidencia Git (hash, `git log --oneline`, enlace al commit). |
| ☐ | Fuentes editables (`.puml`, `.md`) incluidas, no solo PNG. |
| ☐ | Datos ficticios (verificado: ningún secreto). |
| ☐ | `DECLARACION_IA.md` presente e incluida en el documento final (anexo). |
| ☐ | Guía de defensa oral leída y practicada (`04-guia-defensa-oral.md`). |
| ☐ | Sin texto oculto ni instrucciones adversariales. |

---

## PASO 7 — ENSAYAR LA DEFENSA ORAL

1. Lee `04-guia-defensa-oral.md` (preguntas tipo y estructura de 5–7 min).
2. Practica explicar el diagrama `isp-despues.png`: qué contrato usa cada controlador.
3. Prepárate para **modificar un elemento en vivo** (ej. agregar un método a un contrato y explicar el impacto).
4. Repasa estos 3 archivos antes de la defensa:
   - `app/Services/LabResults/Contracts/*.php`
   - `app/Http/Controllers/Api/V1/LabResults/*.php`
   - `app/Providers/LabResultsServiceProvider.php`

---

## RESUMEN DE COMANDOS EN UNA SOLA SECCIÓN

```powershell
git status
git add -A
git status
git commit -m "ASII-19: implementa ingreso de resultados de laboratorio con principio ISP"
git tag v1-asii19-isp
git push -u individual feature/asii-19-ingreso-de-resultados-de-laboratorio-orem6
git push origin feature/asii-19-ingreso-de-resultados-de-laboratorio-orem6
git push individual --tags
git log --oneline -3
```

Después: actualizar `README.md` y `03-evidencias-ejecucion.md` con el hash, re-committear, y armar el PDF/DOCX.

---

## NOTA IMPORTANTE

Si en cualquier paso Git te pide credenciales, usa tu **Personal Access Token** de GitHub como contraseña (no tu contraseña normal).

- **No uses el remoto `nuevo`** (apunta a `SistemaHospitalario-keily.git`, que no existe y provoca `Repository not found`).
- Tu repo personal compartido es **`individual`** (`SistemaHospitalarioPersonal-keily.git`).
- En `origin` el PR va hacia **`develop`**, nunca hacia `main` (regla del worktree-guide).