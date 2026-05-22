{if !$category}
    <div class="container">
        <div class="not-found" style="padding: 100px 0;">
            <div class="not-found__icon">🗺️</div>
            <h2>Категория не найдена</h2>
            <p>Попробуйте вернуться на <a href="/" style="color:#2d6a4f;">главную страницу</a>.</p>
        </div>
    </div>
{else}

<div class="page-hero">
    <div class="container">
        <div class="page-hero__eyebrow">Категория</div>
        <div class="page-hero__title-row">
            <h1 class="page-hero__title">{$category->title|escape:'html'|capitalize}</h1>
            <button
                id="btn-delete-category"
                class="btn-delete"
                type="button"
                aria-label="Удалить категорию"
                data-category-id="{$category->id}"
                title="Удалить категорию"
            >✕</button>
        </div>
        {if $category->description}
            <p class="page-hero__desc">{$category->description|escape:'html'}</p>
        {/if}
    </div>
</div>

<div style="padding-bottom: 64px;">
    <div class="container">

        <div class="toolbar">
            <span class="toolbar__count">
                Всего статей: <strong>{$totalCount}</strong>
            </span>
            <div style="display:flex;align-items:center;gap:8px;">
                <span class="sort-label">Сортировка:</span>
                <div class="sort-group">
                    <a href="/category/{$category->id}?sort=date&page=1"
                       class="sort-btn{if $sort == 'date'} active{/if}">
                        📅 По дате
                    </a>
                    <a href="/category/{$category->id}?sort=views&page=1"
                       class="sort-btn{if $sort == 'views'} active{/if}">
                        👁 По просмотрам
                    </a>
                </div>
            </div>
        </div>

        {if $articles}
            <div class="articles-grid">
                {foreach $articles as $article}
                    <a href="/article/{$article->id}" class="card">
                        <img
                            class="card__img"
                            src="{$article->getImage('thumbnail')}"
                            alt="{$article->title|escape:'html'}"
                            loading="lazy"
                        >
                        <div class="card__body">
                            <div class="card__meta">
                                <span class="card__meta-item">📅 {$article->createdAt|truncate:10:'':''}</span>
                                <span class="card__meta-item">👁 {$article->viewsCount}</span>
                            </div>
                            <div class="card__title">{$article->title|escape:'html'}</div>
                            <div class="card__desc">{$article->description|escape:'html'}</div>
                        </div>
                    </a>
                {/foreach}
            </div>

            {if $totalPages > 1}
                <nav class="pagination" aria-label="Пагинация">
                    {* Prev *}
                    {if $currentPage > 1}
                        <a href="/category/{$category->id}?sort={$sort}&page={$currentPage-1}"
                           class="page-btn page-btn--wide">← Назад</a>
                    {else}
                        <span class="page-btn page-btn--wide disabled">← Назад</span>
                    {/if}

                    {* Page numbers *}
                    {for $i=1 to $totalPages}
                        {if $i == $currentPage}
                            <span class="page-btn active">{$i}</span>
                        {else}
                            <a href="/category/{$category->id}?sort={$sort}&page={$i}"
                               class="page-btn">{$i}</a>
                        {/if}
                    {/for}

                    {* Next *}
                    {if $currentPage < $totalPages}
                        <a href="/category/{$category->id}?sort={$sort}&page={$currentPage+1}"
                           class="page-btn page-btn--wide">Вперёд →</a>
                    {else}
                        <span class="page-btn page-btn--wide disabled">Вперёд →</span>
                    {/if}
                </nav>
            {/if}

        {else}
            <div class="not-found" style="padding: 80px 0;">
                <div class="not-found__icon">📭</div>
                <h2>Статей пока нет</h2>
                <p>В этой категории ещё нет публикаций.</p>
            </div>
        {/if}

    </div>
</div>

<div id="modal-delete-confirm" class="modal" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="modal-delete-title">
    <div class="modal__overlay" data-modal-close></div>
    <div class="modal__window modal__window--sm">
        <div class="modal__header">
            <h2 id="modal-delete-title">Удалить категорию?</h2>
            <button class="modal__close" type="button" data-modal-close aria-label="Закрыть">✕</button>
        </div>
        <div class="modal__body">
            <div class="delete-warning">
                {if $exclusiveCount > 0}
                    <div class="delete-warning__item delete-warning__item--danger">
                        🗑 <strong>{$exclusiveCount} {if $exclusiveCount == 1}статья будет удалена навсегда{elseif $exclusiveCount >= 2 && $exclusiveCount <= 4}статьи будут удалены навсегда{else}статей будут удалены навсегда{/if}</strong>
                        — {if $exclusiveCount == 1}она прикреплена{else}они прикреплены{/if} <em>только</em> к этой категории и больше нигде не числяются.
                    </div>
                {/if}
                {if $sharedCount > 0}
                    <div class="delete-warning__item delete-warning__item--safe">
                        ✅ <strong>{$sharedCount} {if $sharedCount == 1}статья останется{elseif $sharedCount >= 2 && $sharedCount <= 4}статьи останутся{else}статей останутся{/if}</strong>
                        — {if $sharedCount == 1}она прикреплена{else}они прикреплены{/if} к другим категориям и удалены не будут.
                    </div>
                {/if}
                {if $exclusiveCount == 0 && $sharedCount == 0}
                    <div class="delete-warning__item delete-warning__item--safe">
                        ✅ В этой категории нет статей — она будет удалена без последствий.
                    </div>
                {/if}
            </div>
            <div class="modal__confirm-actions">
                <button id="btn-confirm-delete" class="btn btn-danger" type="button">Да, удалить</button>
                <button class="btn btn-outline" type="button" data-modal-close>Нет, отмена</button>
            </div>
        </div>
    </div>
</div>

{/if}
