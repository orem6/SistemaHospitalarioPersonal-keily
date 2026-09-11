# 5 · Conclusión y bibliografía

## Conclusión

La primera etapa deja operativa la base/MVC del módulo *Ingreso de resultados
de laboratorio* en PHP 8.2 vanilla, con las reglas centrales encapsuladas en la
capa Domain y verificadas por una suite automatizada en verde (11 pruebas,
39 aserciones):

- solo muestras ACEPTADAS admiten resultados; las rechazadas producen un
  rechazo controlado y trazable;
- el tipo y la unidad se validan contra el catálogo local; los valores
  inválidos no se persisten;
- cada corrección crea una versión nueva (n + 1) ligada a la anterior, que se
  conserva intacta: la tabla `lab_result_versions` es append-only
  (sin UPDATE/DELETE en el código).

La separación por capas y los puertos `Domain/Repository/*Interface` permiten
que la segunda etapa incorpore el Repository Pattern sobre PostgreSQL dentro
del proyecto SHI Laravel sin reescribir reglas de negocio.

## Bibliografía

- PHP Documentation Group. *PHP Manual — PDO (PHP Data Objects)*. https://www.php.net/manual/es/book.pdo.php
- PHP Documentation Group. *PHP Manual — Enums*. https://www.php.net/manual/es/language.enumerations.php
- Fowler, M. *Patterns of Enterprise Application Architecture*. Addison-Wesley, 2002. (Repository, Layering)
- Martin, R. C. *Clean Architecture: A Craftsman's Guide to Software Structure and Design*. Prentice Hall, 2017.
- The Open Group. *TOEGLI — Domain-Driven Design reference* (Evans, E. *Domain-Driven Design*, Addison-Wesley, 2003).
- PHPUnit. *Documentation*. https://docs.phpunit.de/en/11.5/
- SQLite Consortium. *SQLite Documentation*. https://www.sqlite.org/docs.html
