<div style="background: linear-gradient(135deg, #1b4332 0%, #2d6a4f 60%, #52b788 100%); color: #fff; padding: 80px 0 64px; text-align: center;">
    <div class="container">
        <div style="font-size:13px;font-weight:600;letter-spacing:1.2px;text-transform:uppercase;color:#b7e4c7;margin-bottom:14px;">
            Открывай природу вместе с нами
        </div>
        <h1 style="font-family:Georgia,serif;font-size:clamp(36px,5vw,62px);font-weight:700;line-height:1.15;margin-bottom:18px;letter-spacing:-.5px;">
            Мир вокруг нас
        </h1>
        <p style="font-size:18px;opacity:.85;max-width:500px;margin:0 auto 32px;line-height:1.7;">
            Истории о морях, горах, лесах и пустынях — в каждой статье живёт природа.
        </p>
        <div class="action-bar">
            <button id="btn-create-article" class="btn btn-primary" type="button">
                ✏️ Создать статью
            </button>
            <button id="btn-create-category" class="btn" style="background:rgba(255,255,255,.15);color:#fff;border:2px solid rgba(255,255,255,.45);" type="button">
                🗂 Создать категорию
            </button>
            <button id="btn-upload-data" class="btn" style="background:rgba(255,255,255,.15);color:#fff;border:2px solid rgba(255,255,255,.45);" type="button">
                📥 Загрузить данные
            </button>
        </div>
    </div>
</div>

{* ── Modal: Загрузить данные ───────────────────────────────── *}
<div id="modal-upload" class="modal" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="modal-upload-title">
    <div class="modal__overlay" data-modal-close></div>
    <div class="modal__window modal__window--sm">
        <div class="modal__header">
            <h2 id="modal-upload-title">Загрузить данные</h2>
            <button class="modal__close" type="button" data-modal-close aria-label="Закрыть">✕</button>
        </div>
        <form id="form-upload" class="modal__form" enctype="multipart/form-data" novalidate>
            <div class="form-group">
                <label for="upload-file">JSON-файл *</label>
                <input id="upload-file" type="file" name="json_file" accept=".json,application/json" required>
                <div class="upload-hint">
                    Структура записи: <code>{ldelim}"type":"article"|"category", ...поля...{rdelim}</code><br>
                    Статьи ссылаются на категории по полю <code>"categories": ["Название"]</code><br>
                    Категории обрабатываются первыми.
                    <a href="/mock-data.json" download style="color:#2d6a4f;font-weight:600;">Скачать пример файла</a>
                </div>
            </div>
            <button type="submit" class="btn btn-primary" style="align-self:flex-start;">Загрузить</button>
        </form>
        <div id="upload-result" style="display:none;" class="modal__form" style="padding-top:0;"></div>
    </div>
</div>

{* ── Modal: Создать статью ─────────────────────────────────── *}
<div id="modal-article" class="modal" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="modal-article-title">
    <div class="modal__overlay" data-modal-close></div>
    <div class="modal__window">
        <div class="modal__header">
            <h2 id="modal-article-title">Создать статью</h2>
            <button class="modal__close" type="button" data-modal-close aria-label="Закрыть">✕</button>
        </div>
        <form id="form-article" class="modal__form" enctype="multipart/form-data" novalidate>
            <div class="form-group">
                <label for="a-title">Заголовок *</label>
                <input id="a-title" type="text" name="title" placeholder="Заголовок статьи" required>
            </div>
            <div class="form-group">
                <label for="a-description">Краткое описание *</label>
                <input id="a-description" type="text" name="description" placeholder="Одним предложением" required>
            </div>
            <div class="form-group">
                <label for="a-content">Контент *</label>
                <textarea id="a-content" name="content" rows="6" placeholder="Текст статьи…" required></textarea>
            </div>

            <div class="form-group">
                <label>Изображение</label>
                <div class="image-source-toggle">
                    <label><input type="radio" name="image_type" value="url" checked> Ссылка на изображение</label>
                    <label><input type="radio" name="image_type" value="file"> Загрузить файл</label>
                </div>
                <div id="image-url-section">
                    <div class="url-input-wrap" style="position:relative;">
                        <input
                            id="a-image-url"
                            type="url"
                            name="image_url"
                            placeholder="https://picsum.photos/seed/forest"
                            data-tooltip="Если указать URL вида https://picsum.photos/seed/… — изображение автоматически подгоняется под три размера: превью (140×100), миниатюра (360×200) и оригинал (1200×500)."
                        >
                    </div>
                </div>
                <div id="image-file-section" style="display:none;">
                    <input type="file" name="image_file" accept="image/*">
                </div>
            </div>

            <div class="form-group">
                <label>Категории *</label>
                {if $allCategories}
                    <div class="categories-grid">
                        {foreach $allCategories as $cat}
                            <label class="checkbox-label">
                                <input type="checkbox" name="categories[]" value="{$cat->id}">
                                {$cat->title|escape:'html'}
                            </label>
                        {/foreach}
                    </div>
                {else}
                    <p style="font-size:13px;color:#5a7a68;">Категорий пока нет — создайте их отдельно.</p>
                {/if}
            </div>

            <button type="submit" class="btn btn-primary" style="align-self:flex-start;">Создать статью</button>
        </form>
    </div>
</div>

{* ── Modal: Создать категорию ──────────────────────────────── *}
<div id="modal-category" class="modal" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="modal-category-title">
    <div class="modal__overlay" data-modal-close></div>
    <div class="modal__window">
        <div class="modal__header">
            <h2 id="modal-category-title">Создать категорию</h2>
            <button class="modal__close" type="button" data-modal-close aria-label="Закрыть">✕</button>
        </div>
        <form id="form-category" class="modal__form" novalidate>
            <div class="form-group">
                <label for="c-title">Название *</label>
                <input id="c-title" type="text" name="title" placeholder="Название категории" required>
            </div>
            <div class="form-group">
                <label for="c-description">Описание</label>
                <textarea id="c-description" name="description" rows="3" placeholder="Краткое описание категории…"></textarea>
            </div>
            <button type="submit" class="btn btn-primary" style="align-self:flex-start;">Создать категорию</button>
        </form>
    </div>
</div>

<div style="padding: 60px 0 72px;">
    <div class="container">

        {foreach $sections as $section}

            <section style="margin-bottom: 60px;">
                <div class="section-header">
                    <div>
                        <h2 class="section-title">{$section.category->title|escape:'html'|capitalize}</h2>
                        {if $section.category->description}
                            <p class="section-desc">{$section.category->description|escape:'html'|truncate:100:'…':false}</p>
                        {/if}
                    </div>
                    <a href="/category/{$section.category->id}" class="btn btn-outline">
                        Все статьи →
                    </a>
                </div>

                <div class="articles-grid">
                    {foreach $section.articles as $article}
                        <a href="/article/{$article->id}" class="card">
                            <img
                                class="card__img"
                                src="{$article->getImage('thumbnail')}"
                                alt="{$article->title|escape:'html'}"
                                width="360"
                                height="200"
                                loading="lazy"
                            >
                            <div class="card__body">
                                <div class="card__meta">
                                    <span class="card__meta-item">
                                        📅 {$article->createdAt|truncate:10:'':''}
                                    </span>
                                    <span class="card__meta-item">
                                        👁 {$article->viewsCount}
                                    </span>
                                </div>
                                <div class="card__title">{$article->title|escape:'html'}</div>
                                <div class="card__desc">{$article->description|escape:'html'}</div>
                            </div>
                        </a>
                    {/foreach}
                </div>
            </section>

            {if !$section@last}
                <hr class="section-divider" style="margin-bottom: 60px;">
            {/if}

        {foreachelse}
            <div class="not-found">
                <div class="not-found__icon">🌱</div>
                <h2>Статьи ещё не добавлены</h2>
                <p>Скоро здесь появятся материалы о природе.</p>
            </div>
        {/foreach}

    </div>
</div>
