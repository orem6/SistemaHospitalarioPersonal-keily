# 4 · Pruebas y evidencias

## Cómo ejecutar

```bash
cd etapa1-base-mvc
php ../vendor/phpunit/phpunit/phpunit -c phpunit.xml.dist   # pruebas
php bin/demo.php                                            # demo de casos obligatorios
```

## Suite PHPUnit (11 pruebas, 39 aserciones — todas en verde)

### Dominio (`tests/Domain/ReglasDominioTest.php`)
1. `test_muestra_rechazada_no_permite_ingresar_resultado` — regla central + 0 filas persistidas.
2. `test_valor_numerico_invalido_es_rechazado` — valor `"abc"`.
3. `test_unidad_invalida_es_rechazada_y_no_se_persiste`.
4. `test_tipo_incompatible_con_la_prueba_es_rechazado` — TEXTO sobre prueba NUMERICO.
5. `test_correccion_crea_version_nueva_sin_sobrescribir_la_anterior`.
6. `test_correccion_identica_al_vigente_es_rechazada`.

### Integración del flujo vertical (`tests/Integration/FlujoIngresoCorreccionTest.php`)
1. Ingreso correcto para muestra aceptada (y ya no aparece como pendiente).
2. Rechazo controlado para muestra rechazada.
3. Rechazo de unidad inválida.
4. Corrección genera nueva versión (v2, ligada a v1).
5. La versión anterior permanece intacta (comparación contra instantánea previa).

## Evidencias capturadas

Archivos en `etapa1-base-mvc/evidencias/`:

| Archivo | Contenido | Regla/criterio asociado |
|---|---|---|
| `demo-casos-obligatorios.txt` | Ejecución completa de CASOS 1–4 + CONSERVACIÓN | Evidencias mínimas 1, 2, 3 de la asignación |
| `pruebas-phpunit.txt` | Salida íntegra de la suite | Cobertura de reglas |

Cada línea `[OK]` del demo se puede rastrear a una regla:
- CASO 1 → "solo muestras aceptadas admiten resultados".
- CASO 2 → "muestra rechazada ⇒ rechazo controlado".
- CASO 3a/3b → "tipo/unidad válidos".
- CASO 4 + CONSERVACIÓN → "corrección versionada sin sobrescribir".

## Verificaciones de integridad incluidas

- Tras cada intento inválido: `count(versiones) == 0`.
- Tras la corrección: `count == 2`, v1 con su valor original `14.5 g/dL`,
  v2 vigente con motivo registrado.
