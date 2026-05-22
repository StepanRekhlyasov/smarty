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
use Smarty\Controllers\ArticleController;

DatabaseController::connect();

$id          = (int) ($_POST['id'] ?? 0);
$title       = trim($_POST['title'] ?? '');
$content     = trim($_POST['content'] ?? '');
$description = trim($_POST['description'] ?? '');
$imageType   = $_POST['image_type'] ?? 'keep';
$imageUrl    = trim($_POST['image_url'] ?? '');
$categories  = array_filter(array_map('intval', (array) ($_POST['categories'] ?? [])));

if ($id <= 0) {
    http_response_code(422);
    echo json_encode(['success' => false, 'error' => 'Некорректный идентификатор статьи']);
    exit;
}

if ($title === '' || $content === '' || $description === '') {
    http_response_code(422);
    echo json_encode(['success' => false, 'error' => 'Поля «Заголовок», «Описание» и «Контент» обязательны']);
    exit;
}

if (empty($categories)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'error' => 'Выберите хотя бы одну категорию']);
    exit;
}

$finalImageUrl  = null;
$imageChanged   = false;

if ($imageType === 'file' && isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
    $file         = $_FILES['image_file'];
    $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $mime         = mime_content_type($file['tmp_name']);

    if (!in_array($mime, $allowedMimes, true)) {
        http_response_code(422);
        echo json_encode(['success' => false, 'error' => 'Недопустимый формат изображения']);
        exit;
    }

    $ext         = pathinfo($file['name'], PATHINFO_EXTENSION);
    $newFilename = bin2hex(random_bytes(12)) . '.' . strtolower($ext);
    $uploadDir   = __DIR__ . '/../../../uploads/';
    $destination = $uploadDir . $newFilename;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Не удалось сохранить файл']);
        exit;
    }

    $finalImageUrl = '/uploads/' . $newFilename;
    $imageChanged  = true;
} elseif ($imageType === 'url') {
    $finalImageUrl = $imageUrl !== '' ? $imageUrl : null;
    $imageChanged  = true;
}
// $imageType === 'keep' — ничего не меняем

try {
    $controller = new ArticleController();
    $controller->update($id, $title, $content, $description, $finalImageUrl, $imageChanged);
    $controller->syncCategories($id, array_values($categories));

    echo json_encode(['success' => true]);
} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Внутренняя ошибка сервера']);
}
