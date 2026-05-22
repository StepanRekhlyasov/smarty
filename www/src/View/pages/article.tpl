{if !$article}
    <div class="container">
        <div class="not-found" style="padding: 100px 0;">
            <div class="not-found__icon">📄</div>
            <h2>Статья не найдена</h2>
            <p>Попробуйте вернуться на <a href="/" style="color:#2d6a4f;">главную страницу</a>.</p>
        </div>
    </div>
{else}

<div class="article-hero">
    <div class="container">

        <div class="article-meta-bar">
            <a href="/">← Главная</a> |
            {foreach $articleCategories as $cat}
                <a href="/category/{$cat.id}">#{$cat.title|escape:'html'}</a> |
            {/foreach}
            <span>📅 {$article.created_at|truncate:10:'':''}</span>
            <span>👁 {$article.views_count} просмотров</span>
        </div>

        <h1 class="article-hero__title">{$article.title|escape:'html'}</h1>
        <p class="article-hero__desc">{$article.description|escape:'html'}</p>

        {if $articleCategories}
            <div class="tags">
                {foreach $articleCategories as $cat}
                    <a href="/category/{$cat.id}" class="tag tag-light">
                        {$cat.title|escape:'html'}
                    </a>
                {/foreach}
            </div>
        {/if}

        <img
            class="article-hero__img"
            src="{$article.image_url}"
            alt="{$article.title|escape:'html'}"
            width="1200"
            height="500"
        >
    </div>
</div>

<div class="container">
    <div class="article-layout">

        <article class="article-content">
            <p>{$article.content|escape:'html'|nl2br}</p>
        </article>

        <aside class="sidebar">

            {if $articleCategories}
                <div class="sidebar__widget">
                    <div class="sidebar__title">Категории</div>
                    <div class="tags">
                        {foreach $articleCategories as $cat}
                            <a href="/category/{$cat.id}" class="tag tag-green">
                                {$cat.title|escape:'html'}
                            </a>
                        {/foreach}
                    </div>
                </div>
            {/if}

            {if $similar}
                <div class="sidebar__widget">
                    <div class="sidebar__title">Похожие статьи</div>
                    <div class="similar-list">
                        {foreach $similar as $sim}
                            <a href="/article/{$sim.id}" class="similar-item">
                                <img
                                    class="similar-img"
                                    src="{$sim.preview_image_url}"
                                    alt="{$sim.title|escape:'html'}"
                                    loading="lazy"
                                    width="140"
                                    height="100"
                                >
                                <div>
                                    <div class="similar-title">{$sim.title|escape:'html'}</div>
                                    <div class="similar-date">📅 {$sim.created_at|truncate:10:'':''}</div>
                                </div>
                            </a>
                        {/foreach}
                    </div>
                </div>
            {/if}

        </aside>
    </div>
</div>

{/if}
