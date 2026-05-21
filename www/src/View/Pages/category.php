<?php

$category = $db->query('SELECT * FROM categories WHERE id = ' . $id)->fetch();


if (!$category) {
    echo 'Category not found';
    return;
}

?>
<h1><?= htmlspecialchars($category['title'], ENT_QUOTES, 'UTF-8') ?></h1>
<p>Category ID: <?= htmlspecialchars((string) ($category['id'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
<p>Category Description: <?= htmlspecialchars($category['description'], ENT_QUOTES, 'UTF-8') ?></p>
<p>Category Created At: <?= htmlspecialchars($category['created_at'], ENT_QUOTES, 'UTF-8') ?></p>






