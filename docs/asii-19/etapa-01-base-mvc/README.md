# ASII-19 · Ingreso de resultados de laboratorio — PRIMERA ETAPA (base/MVC, PHP vanilla)

**Estudiante:** Keily Fabiola Orellana Marroquín · **GitHub:** `orem6`
**Flujo asignado:** Captura y corrección controlada de resultados pendientes.

## Índice

1. [Introducción y alcance](01-introduccion-alcance.md)
2. [Arquitectura por capas](02-arquitectura-capas.md)
3. [Flujo y reglas del módulo](03-flujo-reglas.md)
4. [Pruebas y evidencias](04-pruebas-evidencias.md)
5. [Conclusión y bibliografía](05-conclusion-bibliografia.md)
6. [DECLARACION_IA.md](../../etapa1-base-mvc/DECLARACION_IA.md)

## Ejecución rápida

```bash
cd etapa1-base-mvc

# Demo CLI con los 4 casos obligatorios (evidencia reproducible)
php bin/demo.php

# Pruebas automatizadas (PHPUnit 11)
php ../vendor/phpunit/phpunit/phpunit -c phpunit.xml.dist

# Interfaz web (MVC mínimo, servidor embebido de PHP)
php -S localhost:8080 -t public
```

## Estructura

```
etapa1-base-mvc/
├── bin/demo.php              # CLI: CASOS 1–4 obligatorios (evidencia)
├── database/
│   ├── schema.sql            # DDL mínimo (prueba, muestra, versiones)
│   └── seed.php              # Datos 100% ficticios
├── public/index.php          # Front controller web (Presentation)
├── src/
│   ├── autoload.php          # PSR-4 sin Composer
│   ├── AppContainer.php      # Composition Root
│   ├── Domain/               # Reglas centrales (sin PDO ni SQL)
│   ├── Application/          # Casos de uso (ingresar, corregir, listar, historial)
│   ├── Persistence/          # PDO + consultas preparadas (solo INSERT/SELECT en versiones)
│   └── Presentation/         # Controller + Views (MVC)
├── tests/                    # PHPUnit: dominio + integración
└── evidencias/               # Salidas capturadas (demo y pruebas)
```

## Preparado para la segunda etapa

Los contratos (`Domain/Repository/*Interface`) son los puertos que la etapa 2
reemplazará/adaptará con el **Repository Pattern sobre PostgreSQL** dentro del
proyecto SHI Laravel, sin reescribir las reglas de negocio.
