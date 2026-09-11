<?php

declare(strict_types=1);

// Smoke test del front controller (solo para verificación manual).
$_SERVER['REQUEST_METHOD'] = 'GET';
parse_str($argv[1] ?? '', $_GET);
ob_start();
require __DIR__ . '/../public/index.php';
$salida = ob_get_clean();
echo 'HTTP render OK · bytes=' . strlen($salida) . PHP_EOL;
echo substr(strip_tags($salida), 0, 400) . PHP_EOL;
