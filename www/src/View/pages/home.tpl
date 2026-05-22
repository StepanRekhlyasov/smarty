<div style="background: linear-gradient(135deg, #1b4332 0%, #2d6a4f 60%, #52b788 100%); color: #fff; padding: 80px 0 64px; text-align: center;">
    <div class="container">
        <div style="font-size:13px;font-weight:600;letter-spacing:1.2px;text-transform:uppercase;color:#b7e4c7;margin-bottom:14px;">
            Открывай природу вместе с нами
        </div>
        <h1 style="font-family:Georgia,serif;font-size:clamp(36px,5vw,62px);font-weight:700;line-height:1.15;margin-bottom:18px;letter-spacing:-.5px;">
            Мир вокруг нас
        </h1>
        <p style="font-size:18px;opacity:.85;max-width:500px;margin:0 auto;line-height:1.7;">
            Истории о морях, горах, лесах и пустынях — в каждой статье живёт природа.
        </p>
    </div>
</div>

<div style="padding: 60px 0 72px;">
    <div class="container">

        {foreach $sections as $section}

            <section style="margin-bottom: 60px;">
                <div class="section-header">
                    <div>
                        <h2 class="section-title">{$section.category.title|escape:'html'|capitalize}</h2>
                        {if $section.category.description}
                            <p class="section-desc">{$section.category.description|escape:'html'|truncate:100:'…':false}</p>
                        {/if}
                    </div>
                    <a href="/category/{$section.category.id}" class="btn btn-outline">
                        Все статьи →
                    </a>
                </div>

                <div class="articles-grid">
                    {foreach $section.articles as $article}
                        <a href="/article/{$article.id}" class="card">
                            <img
                                class="card__img"
                                src="{$article.image_url}/360/200"
                                alt="{$article.title|escape:'html'}"
                                loading="lazy"
                            >
                            <div class="card__body">
                                <div class="card__meta">
                                    <span class="card__meta-item">
                                        📅 {$article.created_at|truncate:10:'':''}
                                    </span>
                                    <span class="card__meta-item">
                                        👁 {$article.views_count}
                                    </span>
                                </div>
                                <div class="card__title">{$article.title|escape:'html'}</div>
                                <div class="card__desc">{$article.description|escape:'html'}</div>
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
