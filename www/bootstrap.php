<?php

spl_autoload_register(static function (string $class): void {
    $prefix = 'Smarty\\';
    $baseDir = __DIR__ . '/src/';

    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relativeClass = substr($class, strlen($prefix));
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (is_file($file)) {
        require $file;
    }
});

use Smarty\Config\DatabaseConfig;
use Smarty\Controllers\RouterController;

$db = DatabaseConfig::connect();
$routes = require __DIR__ . '/src/routes.php';
$router = new RouterController($routes);

$router->dispatch($_SERVER['REQUEST_URI']);