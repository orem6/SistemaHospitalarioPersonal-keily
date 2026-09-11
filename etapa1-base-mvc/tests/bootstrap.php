<?php

declare(strict_types=1);

require __DIR__ . '/../src/autoload.php';
require_once __DIR__ . '/Support/TestDatabase.php';

use LabResults\Tests\Support\TestDatabase;

// Cada prueba trabaja sobre su propia base SQLite temporal.
if (!function_exists('lab_test_container')) {
    function lab_test_container(): LabResults\AppContainer
    {
        return TestDatabase::crear();
    }
}
