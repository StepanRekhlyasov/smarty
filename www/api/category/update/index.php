<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

require __DIR__ . '/../../../vendor/autoload.php';

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

$id          = (int) ($_POST['id'] ?? 0);
$title       = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');

if ($id <= 0) {
    http_response_code(422);
    echo json_encode(['success' => false, 'error' => 'Некорректный идентификатор категории']);
    exit;
}

if ($title === '') {
    http_response_code(422);
    echo json_encode(['success' => false, 'error' => 'Поле «Название» обязательно']);
    exit;
}

try {
    $controller = new CategoryController();
    $controller->update($id, $title, $description);

    echo json_encode(['success' => true]);
} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Внутренняя ошибка сервера']);
}
