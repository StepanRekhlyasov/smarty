<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

require __DIR__ . '/../../vendor/autoload.php';

spl_autoload_register(static function (string $class): void {
    $prefix  = 'Smarty\\';
    $baseDir = __DIR__ . '/../../src/';

    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $file = $baseDir . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';

    if (is_file($file)) {
        require $file;
    }
}, prepend: true);

use Smarty\Controllers\DatabaseController;

DatabaseController::connect();
$db = \Smarty\Controllers\DatabaseController::db();

// ── 1. Validate uploaded file ─────────────────────────────────────────────────

if (!isset($_FILES['json_file']) || $_FILES['json_file']['error'] !== UPLOAD_ERR_OK) {
    $uploadErrors = [
        UPLOAD_ERR_INI_SIZE   => 'Файл превышает upload_max_filesize.',
        UPLOAD_ERR_FORM_SIZE  => 'Файл превышает MAX_FILE_SIZE формы.',
        UPLOAD_ERR_PARTIAL    => 'Файл загружен частично.',
        UPLOAD_ERR_NO_FILE    => 'Файл не выбран.',
        UPLOAD_ERR_NO_TMP_DIR => 'Нет временной папки.',
        UPLOAD_ERR_CANT_WRITE => 'Не удалось записать файл.',
    ];
    $code    = $_FILES['json_file']['error'] ?? UPLOAD_ERR_NO_FILE;
    $message = $uploadErrors[$code] ?? 'Ошибка загрузки файла.';
    http_response_code(422);
    echo json_encode(['success' => false, 'error' => $message]);
    exit;
}

$file = $_FILES['json_file'];

$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if ($ext !== 'json') {
    http_response_code(422);
    echo json_encode(['success' => false, 'error' => 'Допускается только файл формата .json']);
    exit;
}

if ($file['size'] > 5 * 1024 * 1024) {
    http_response_code(422);
    echo json_encode(['success' => false, 'error' => 'Размер файла не должен превышать 5 МБ']);
    exit;
}

$raw = file_get_contents($file['tmp_name']);
if ($raw === false) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Не удалось прочитать файл']);
    exit;
}

$data = json_decode($raw, true);
if (!is_array($data) || json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(422);
    echo json_encode(['success' => false, 'error' => 'Невалидный JSON: ' . json_last_error_msg()]);
    exit;
}

if (empty($data)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'error' => 'JSON-файл пустой']);
    exit;
}

// ── 2. Validate and split by type ─────────────────────────────────────────────

$categories = [];
$articles   = [];
$errors     = [];

foreach ($data as $index => $item) {
    $lineLabel = 'Запись #' . ($index + 1);

    if (!is_array($item)) {
        $errors[] = "{$lineLabel}: ожидается объект";
        continue;
    }

    $type = isset($item['type']) ? trim((string) $item['type']) : '';
    if (!in_array($type, ['category', 'article'], true)) {
        $errors[] = "{$lineLabel}: поле «type» должно быть «category» или «article»";
        continue;
    }

    if ($type === 'category') {
        if (empty($item['title']) || trim((string) $item['title']) === '') {
            $errors[] = "{$lineLabel} (category): поле «title» обязательно";
            continue;
        }
        $categories[] = [
            'title'       => trim((string) $item['title']),
            'description' => isset($item['description']) ? trim((string) $item['description']) : '',
        ];
    } else {
        $missingFields = [];
        foreach (['title', 'description', 'content'] as $required) {
            if (empty($item[$required]) || trim((string) $item[$required]) === '') {
                $missingFields[] = "«{$required}»";
            }
        }
        if ($missingFields) {
            $errors[] = "{$lineLabel} (article): отсутствуют обязательные поля: " . implode(', ', $missingFields);
            continue;
        }

        if (empty($item['categories']) || !is_array($item['categories']) || count($item['categories']) === 0) {
            $errors[] = "{$lineLabel} (article): поле «categories» должно быть непустым массивом названий категорий";
            continue;
        }

        if (isset($item['image_url']) && !is_string($item['image_url'])) {
            $errors[] = "{$lineLabel} (article): поле «image_url» должно быть строкой";
            continue;
        }

        $articles[] = [
            'title'       => trim((string) $item['title']),
            'description' => trim((string) $item['description']),
            'content'     => trim((string) $item['content']),
            'image_url'   => isset($item['image_url']) ? trim((string) $item['image_url']) : null,
            'categories'  => array_map('strval', $item['categories']),
        ];
    }
}

if (!empty($errors)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'error' => 'Ошибки валидации', 'details' => $errors]);
    exit;
}

// ── 3. Pre-check: all article categories must exist after import ──────────────

// Collect all category titles that will be available after import
$stmt = $db->query('SELECT title FROM categories');
$existingTitles = array_flip($stmt->fetchAll(\PDO::FETCH_COLUMN));

$newTitles = [];
foreach ($categories as $cat) {
    $newTitles[$cat['title']] = true;
}

$allAvailableTitles = array_merge($existingTitles, $newTitles);

$missingCategoryRefs = [];
foreach ($articles as $i => $article) {
    foreach ($article['categories'] as $catTitle) {
        if (!isset($allAvailableTitles[$catTitle])) {
            $missingCategoryRefs[] = "Статья «{$article['title']}»: категория «{$catTitle}» не существует и не создаётся в этом файле";
        }
    }
}

if (!empty($missingCategoryRefs)) {
    http_response_code(422);
    echo json_encode([
        'success' => false,
        'error'   => 'Статьи ссылаются на несуществующие категории',
        'details' => $missingCategoryRefs,
    ]);
    exit;
}

// ── 4. Import inside a transaction ───────────────────────────────────────────

$db->beginTransaction();

try {
    // 4a. Insert categories, build title → id map
    $titleToId = [];

    // Load existing
    $stmt = $db->query('SELECT id, title FROM categories');
    foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $row) {
        $titleToId[$row['title']] = (int) $row['id'];
    }

    $createdCategories = 0;
    foreach ($categories as $cat) {
        if (isset($titleToId[$cat['title']])) {
            continue; // already exists
        }
        $stmt = $db->prepare('INSERT INTO categories (title, description) VALUES (?, ?)');
        $stmt->execute([$cat['title'], $cat['description']]);
        $titleToId[$cat['title']] = (int) $db->lastInsertId();
        $createdCategories++;
    }

    // 4b. Insert articles
    $createdArticles = 0;
    foreach ($articles as $article) {
        $stmt = $db->prepare(
            'INSERT INTO articles (title, content, description, image_url) VALUES (?, ?, ?, ?)',
        );
        $stmt->execute([
            $article['title'],
            $article['content'],
            $article['description'],
            $article['image_url'] ?: null,
        ]);
        $articleId = (int) $db->lastInsertId();

        foreach ($article['categories'] as $catTitle) {
            $catId = $titleToId[$catTitle];
            $stmt  = $db->prepare(
                'INSERT IGNORE INTO articles_categories (article_id, category_id) VALUES (?, ?)',
            );
            $stmt->execute([$articleId, $catId]);
        }

        $createdArticles++;
    }

    $db->commit();

    echo json_encode([
        'success'            => true,
        'created_categories' => $createdCategories,
        'created_articles'   => $createdArticles,
    ]);
} catch (\Throwable $e) {
    $db->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Ошибка при записи в базу данных: ' . $e->getMessage()]);
}
