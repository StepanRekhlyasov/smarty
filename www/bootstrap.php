<?php

require __DIR__ . '/vendor/autoload.php';

spl_autoload_register(static function (string $class): void {
    $prefix = 'Smarty\\';
    $baseDir = __DIR__ . '/src/';

    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $file = $baseDir . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';

    if (is_file($file)) {
        require $file;
    }
}, prepend: true);

use Smarty\Config\DatabaseConfig;
use Smarty\Controllers\RouterController;

$db = DatabaseConfig::connect();
$routes = require __DIR__ . '/src/routes.php';
$router = new RouterController($routes, $db);
$router->dispatch($_SERVER['REQUEST_URI']);