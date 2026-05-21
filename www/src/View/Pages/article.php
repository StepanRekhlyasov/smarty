<?php

$article = $db->query('SELECT * FROM articles WHERE id = ' . $id)->fetch();


if (!$article) {
    echo 'Article not found';
    return;
}

?>
<h1><?= htmlspecialchars($article['title'], ENT_QUOTES, 'UTF-8') ?></h1>
<p>Article ID: <?= htmlspecialchars((string) ($article['id'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
<p>Article Content: <?= htmlspecialchars($article['content'], ENT_QUOTES, 'UTF-8') ?></p>
<p>Article Description: <?= htmlspecialchars($article['description'], ENT_QUOTES, 'UTF-8') ?></p>
<p>Article Image URL: <?= htmlspecialchars($article['image_url'], ENT_QUOTES, 'UTF-8') ?></p>
<p>Article Created At: <?= htmlspecialchars($article['created_at'], ENT_QUOTES, 'UTF-8') ?></p>






