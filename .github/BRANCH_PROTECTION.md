# Protección de ramas — ASII 2026

Este repositorio usa un flujo académico controlado:

```text
main      ← entrega estable, solo administradores académicos
develop   ← integración del curso
feature/* ← worktree individual por estudiante
```

## Reglas que deben configurarse en GitHub

En `Settings → Branches → Branch protection rules`:

### `main`

- Require a pull request before merging.
- Require review from Code Owners.
- Restrict who can push: `rortizs`, `Josuemart555`, `joshuacirilo`.
- Do not allow force pushes.
- Do not allow deletions.

### `develop`

- Require a pull request before merging.
- Require at least 1 approval.
- Do not allow force pushes.
- Do not allow deletions.

## Regla para estudiantes

Los estudiantes trabajan únicamente en ramas `feature/asii-XX-slug-usuario` creadas desde un worktree basado en `origin/develop`.

Ningún estudiante debe trabajar directo sobre `main` ni mezclar más de un módulo en una rama.
