<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{$title|escape:'html'} — Природный мир</title>
    <meta name="description" content="{$description|escape:'html'}">
    <link rel="stylesheet" href="/assets/style.php">
    {if $page === 'home'}
    <script src="/assets/js/home.js" defer></script>
    {/if}
    {if $page === 'article'}
    <script src="/assets/js/article.js" defer></script>
    {/if}
</head>
<body>

<header class="site-header">
    <nav class="nav">
        <a href="/" class="nav__logo">
            <span class="nav__logo-leaf">🌿</span>
            Природный<span>&nbsp;мир</span>
        </a>
    </nav>
</header>

<main>
