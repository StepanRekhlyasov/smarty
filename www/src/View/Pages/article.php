<h1><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></h1>
<p>Article ID: <?= htmlspecialchars((string) ($id ?? ''), ENT_QUOTES, 'UTF-8') ?></p>