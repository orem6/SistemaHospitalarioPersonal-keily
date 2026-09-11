<?php

declare(strict_types=1);

/*
 * Autoloader PSR-4 mínimo para la Primera Etapa (sin Composer ni frameworks).
 * Namespace raíz: LabResults\  ->  carpeta src/
 */

spl_autoload_register(static function (string $class): void {
    $prefix = 'LabResults\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relative = substr($class, strlen($prefix));
    $file = __DIR__ . '/' . str_replace('\\', '/', $relative) . '.php';

    if (is_file($file)) {
        require $file;
    }
});
