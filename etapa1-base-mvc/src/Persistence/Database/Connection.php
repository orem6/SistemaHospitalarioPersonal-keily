<?php

declare(strict_types=1);

namespace LabResults\Persistence\Database;

use PDO;

/**
 * Conexión única de la capa Persistence.
 * Encapsula PDO; ninguna otra capa conoce el driver.
 */
final class Connection
{
    private function __construct(private readonly PDO $pdo)
    {
    }

    public static function connect(string $sqlitePath): self
    {
        $directorio = dirname($sqlitePath);
        if (!is_dir($directorio)) {
            mkdir($directorio, 0777, true);
        }

        $pdo = new PDO('sqlite:' . $sqlitePath, null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);

        $pdo->exec('PRAGMA foreign_keys = ON');

        return new self($pdo);
    }

    public function pdo(): PDO
    {
        return $this->pdo;
    }
}
