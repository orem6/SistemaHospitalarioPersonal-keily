# 1 · Introducción y alcance

## Introducción

El Sistema Hospitalario Integral (SHI) se construye de forma federada: cada
estudiante implementa un módulo vertical. En esta **primera etapa** se levanta la
**base/MVC** del módulo *Ingreso de resultados de laboratorio* en **PHP 8.2+
vanilla**, sin frameworks, con separación estricta en cuatro capas
(Presentation → Application → Domain → Persistence).

La segunda etapa evolucionará esta base hacia la arquitectura del proyecto SHI
(Laravel + PostgreSQL + Repository Pattern); por eso el dominio ya está aislado
detrás de contratos (`Domain/Repository/*Interface`) que no conocen PDO ni SQL.

## Alcance (módulo asignado)

- Flujo: **captura y corrección controlada de resultados pendientes**.
- Regla central: **solo muestras ACEPTADAS admiten resultados**.
- Validación de **tipo/unidad** contra el catálogo local de pruebas.
- **Corrección versionada**: cada corrección INSERTA una versión nueva
  (n + 1); la anterior nunca se sobrescribe ni elimina.

## Fuera de alcance

Pacientes, citas, medicamentos, admisiones, expedientes, órdenes completas,
recepción/aceptación de muestras, validación por bioquímico, RAG/CAG,
dashboard, QA general y cualquier otro módulo del SHI.

## Límite de datos

- El hospital escribe datos clínicos **localmente**.
- Toda referencia a un sistema CENTRAL es un **UUID lógico**
  (`patient_ref`, `tenant_id`) **sin clave foránea remota**.
- Todos los datos usados son **ficticios** (barcodes `DEM-BAR-*`,
  UUIDs de demostración, valores numéricos arbitrarios).
