<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

spl_autoload_register(static function (string $class): void {
    $prefix  = 'Smarty\\';
    $baseDir = __DIR__ . '/../../../src/';

    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $file = $baseDir . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';

    if (is_file($file)) {
        require $file;
    }
}, prepend: true);

use Smarty\Controllers\DatabaseController;
use Smarty\Controllers\CategoryController;

DatabaseController::connect();

$title       = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');

if ($title === '') {
    http_response_code(422);
    echo json_encode(['success' => false, 'error' => 'Поле «Название» обязательно']);
    exit;
}

try {
    $controller = new CategoryController();
    $categoryId = $controller->create($title, $description);

    echo json_encode(['success' => true, 'id' => $categoryId]);
} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Внутренняя ошибка сервера']);
}
