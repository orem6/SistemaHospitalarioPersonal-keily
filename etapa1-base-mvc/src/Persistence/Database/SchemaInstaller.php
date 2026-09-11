<?php

declare(strict_types=1);

namespace LabResults\Persistence\Database;

/**
 * Instala el esquema mínimo del módulo (idempotente).
 * El DDL vive en database/schema.sql.
 */
final class SchemaInstaller
{
    public static function install(string $sqlitePath): \PDO
    {
        $connection = Connection::connect($sqlitePath);

        $sql = file_get_contents(__DIR__ . '/../../../database/schema.sql');
        if ($sql === false) {
            throw new \RuntimeException('No se pudo leer database/schema.sql.');
        }

        $connection->pdo()->exec($sql);

        return $connection->pdo();
    }
}
