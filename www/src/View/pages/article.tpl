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
                <a href="/category/{$cat->id}">#{$cat->title|escape:'html'}</a> |
            {/foreach}
            <span>📅 {$article->createdAt|truncate:10:'':''}</span>
            <span>👁 {$article->viewsCount} просмотров</span>
            <button
                id="btn-edit-article"
                class="btn-edit"
                type="button"
                aria-label="Редактировать статью"
                title="Редактировать"
            >✏️</button>
            <button
                id="btn-delete-article"
                class="btn-delete"
                type="button"
                aria-label="Удалить статью"
                data-article-id="{$article->id}"
                title="Удалить статью"
            >✕</button>
        </div>

        <h1 class="article-hero__title">{$article->title|escape:'html'}</h1>
        <p class="article-hero__desc">{$article->description|escape:'html'}</p>

        {if $articleCategories}
            <div class="tags">
                {foreach $articleCategories as $cat}
                    <a href="/category/{$cat->id}" class="tag tag-light">
                        {$cat->title|escape:'html'}
                    </a>
                {/foreach}
            </div>
        {/if}

        <img
            class="article-hero__img"
            src="{$article->getImage('original')}"
            alt="{$article->title|escape:'html'}"
            width="1200"
            height="500"
        >
    </div>
</div>

<div class="container">
    <div class="article-layout">

        <article class="article-content">
            <p>{$article->content|escape:'html'|nl2br}</p>
        </article>

        <aside class="sidebar">

            {if $articleCategories}
                <div class="sidebar__widget">
                    <div class="sidebar__title">Категории</div>
                    <div class="tags">
                        {foreach $articleCategories as $cat}
                            <a href="/category/{$cat->id}" class="tag tag-green">
                                {$cat->title|escape:'html'}
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
                            <a href="/article/{$sim->id}" class="similar-item">
                                <img
                                    class="similar-img"
                                    src="{$sim->getImage('preview')}"
                                    alt="{$sim->title|escape:'html'}"
                                    loading="lazy"
                                    width="140"
                                    height="100"
                                >
                                <div>
                                    <div class="similar-title">{$sim->title|escape:'html'}</div>
                                    <div class="similar-date">📅 {$sim->createdAt|truncate:10:'':''}</div>
                                </div>
                            </a>
                        {/foreach}
                    </div>
                </div>
            {/if}

        </aside>
    </div>
</div>

<div id="modal-edit-article" class="modal" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="modal-edit-title">
    <div class="modal__overlay" data-modal-close></div>
    <div class="modal__window">
        <div class="modal__header">
            <h2 id="modal-edit-title">Редактировать статью</h2>
            <button class="modal__close" type="button" data-modal-close aria-label="Закрыть">✕</button>
        </div>
        <form id="form-edit-article" class="modal__form" enctype="multipart/form-data" novalidate>
            <input type="hidden" name="id" value="{$article->id}">

            <div class="form-group">
                <label for="e-title">Заголовок *</label>
                <input id="e-title" type="text" name="title" value="{$article->title|escape:'html'}" required>
            </div>
            <div class="form-group">
                <label for="e-description">Краткое описание *</label>
                <input id="e-description" type="text" name="description" value="{$article->description|escape:'html'}" required>
            </div>
            <div class="form-group">
                <label for="e-content">Контент *</label>
                <textarea id="e-content" name="content" rows="6" required>{$article->content|escape:'html'}</textarea>
            </div>

            <div class="form-group">
                <label>Изображение</label>
                <div class="image-source-toggle">
                    <label><input type="radio" name="image_type" value="keep" checked> Оставить текущее</label>
                    <label><input type="radio" name="image_type" value="url"> Новый URL</label>
                    <label><input type="radio" name="image_type" value="file"> Загрузить файл</label>
                </div>
                <div id="edit-image-url-section" style="display:none;">
                    <div class="url-input-wrap">
                        <input
                            id="e-image-url"
                            type="url"
                            name="image_url"
                            placeholder="https://picsum.photos/seed/forest"
                            value="{$article->imageUrl|escape:'html'}"
                            data-tooltip="Если указать URL вида https://picsum.photos/seed/… — изображение автоматически подгоняется под три размера: превью (140×100), миниатюра (360×200) и оригинал (1200×500)."
                        >
                    </div>
                </div>
                <div id="edit-image-file-section" style="display:none;">
                    <input type="file" name="image_file" accept="image/*">
                </div>
                {if $article->imageUrl}
                    <div id="edit-current-image" class="current-image-preview">
                        <span class="current-image-preview__label">Текущее:</span>
                        <img src="{$article->getImage('thumbnail')}" alt="Текущее изображение">
                    </div>
                {/if}
            </div>

            <div class="form-group">
                <label>Категории *</label>
                {if $allCategories}
                    <div class="categories-grid">
                        {foreach $allCategories as $cat}
                            <label class="checkbox-label">
                                <input type="checkbox" name="categories[]" value="{$cat->id}"{if in_array($cat->id, $articleCategoryIds)} checked{/if}>
                                {$cat->title|escape:'html'}
                            </label>
                        {/foreach}
                    </div>
                {/if}
            </div>

            <button type="submit" class="btn btn-primary" style="align-self:flex-start;">Сохранить</button>
        </form>
    </div>
</div>

<div id="modal-delete-confirm" class="modal" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="modal-delete-title">
    <div class="modal__overlay" data-modal-close></div>
    <div class="modal__window modal__window--sm">
        <div class="modal__header">
            <h2 id="modal-delete-title">Удалить статью?</h2>
            <button class="modal__close" type="button" data-modal-close aria-label="Закрыть">✕</button>
        </div>
        <div class="modal__body">
            <p class="modal__confirm-text">Это действие необратимо. Статья будет удалена навсегда.</p>
            <div class="modal__confirm-actions">
                <button id="btn-confirm-delete" class="btn btn-danger" type="button">Да, удалить</button>
                <button class="btn btn-outline" type="button" data-modal-close>Нет, отмена</button>
            </div>
        </div>
    </div>
</div>

{/if}
