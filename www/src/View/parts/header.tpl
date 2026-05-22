<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{$title|escape:'html'} — Природный мир</title>
    <meta name="description" content="{$description|escape:'html'}">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --green:        #2d6a4f;
            --green-dark:   #1b4332;
            --green-light:  #52b788;
            --green-pale:   #d8f3dc;
            --green-subtle: #f0faf4;
            --cream:        #fafdf8;
            --brown:        #6b4226;
            --text:         #1a2e22;
            --text-muted:   #5a7a68;
            --border:       #c7e8d4;
            --white:        #ffffff;
            --shadow-sm:    0 1px 4px rgba(27,67,50,.07);
            --shadow:       0 4px 18px rgba(27,67,50,.11);
            --shadow-hover: 0 8px 32px rgba(27,67,50,.17);
            --radius:       14px;
            --radius-sm:    8px;
            --transition:   .2s ease;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', system-ui, sans-serif;
            background: var(--cream);
            color: var(--text);
            line-height: 1.7;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        a { color: inherit; }

        /* ── Header ── */
        .site-header {
            background: var(--green-dark);
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 14px rgba(0,0,0,.18);
        }
        .nav {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 64px;
        }
        .nav__logo {
            font-family: Georgia, 'Times New Roman', serif;
            font-size: 21px;
            font-weight: 700;
            color: var(--white);
            text-decoration: none;
            letter-spacing: .3px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .nav__logo-leaf { font-size: 22px; }
        .nav__logo span { color: var(--green-light); }

        /* ── Container ── */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 24px;
        }

        /* ── Buttons ── */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 10px 22px;
            border-radius: var(--radius-sm);
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            transition: all var(--transition);
            cursor: pointer;
            border: none;
            white-space: nowrap;
        }
        .btn-primary {
            background: var(--green);
            color: var(--white);
        }
        .btn-primary:hover {
            background: var(--green-dark);
            transform: translateY(-1px);
            box-shadow: var(--shadow);
        }
        .btn-outline {
            background: transparent;
            color: var(--green);
            border: 2px solid var(--green);
        }
        .btn-outline:hover {
            background: var(--green);
            color: var(--white);
        }

        /* ── Article card ── */
        .card {
            background: var(--white);
            border-radius: var(--radius);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            transition: transform var(--transition), box-shadow var(--transition);
            display: flex;
            flex-direction: column;
            text-decoration: none;
            color: inherit;
            border: 1px solid transparent;
        }
        .card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-hover);
            border-color: var(--border);
        }
        .card__img {
            width: 100%;
            aspect-ratio: 16/9;
            object-fit: cover;
            display: block;
            background: var(--green-pale);
        }
        .card__body {
            padding: 18px 20px 20px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        .card__meta {
            display: flex;
            align-items: center;
            gap: 14px;
            font-size: 12px;
            color: var(--text-muted);
            margin-bottom: 8px;
        }
        .card__meta-item { display: flex; align-items: center; gap: 4px; }
        .card__title {
            font-family: Georgia, serif;
            font-size: 16px;
            font-weight: 700;
            color: var(--text);
            margin-bottom: 8px;
            line-height: 1.4;
        }
        .card__desc {
            font-size: 13px;
            color: var(--text-muted);
            flex: 1;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            line-height: 1.55;
        }

        /* ── Cards grid ── */
        .articles-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 22px;
        }
        @media (max-width: 900px)  { .articles-grid { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 560px)  { .articles-grid { grid-template-columns: 1fr; } }

        /* ── Section header ── */
        .section-header {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            margin-bottom: 24px;
            gap: 16px;
            flex-wrap: wrap;
        }
        .section-title {
            font-family: Georgia, serif;
            font-size: 26px;
            font-weight: 700;
            color: var(--green-dark);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .section-title::before {
            content: '';
            display: inline-block;
            width: 4px;
            height: 26px;
            background: var(--green-light);
            border-radius: 2px;
            flex-shrink: 0;
        }
        .section-desc {
            font-size: 14px;
            color: var(--text-muted);
            margin-top: 6px;
            margin-left: 14px;
        }

        /* ── Page hero ── */
        .page-hero {
            background: linear-gradient(135deg, var(--green-dark) 0%, var(--green) 100%);
            color: var(--white);
            padding: 52px 0 48px;
        }
        .page-hero__eyebrow {
            font-size: 13px;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: var(--green-pale);
            margin-bottom: 12px;
        }
        .page-hero__title {
            font-family: Georgia, serif;
            font-size: clamp(28px, 4vw, 44px);
            font-weight: 700;
            line-height: 1.2;
            margin-bottom: 12px;
        }
        .page-hero__desc {
            font-size: 16px;
            opacity: .82;
            max-width: 600px;
            line-height: 1.65;
        }

        /* ── Toolbar ── */
        .toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 24px 0 20px;
            gap: 16px;
            flex-wrap: wrap;
            border-bottom: 1px solid var(--border);
            margin-bottom: 28px;
        }
        .toolbar__count {
            font-size: 14px;
            color: var(--text-muted);
        }
        .sort-group { display: flex; gap: 8px; }
        .sort-label {
            font-size: 13px;
            color: var(--text-muted);
            margin-right: 4px;
            align-self: center;
        }
        .sort-btn {
            padding: 7px 16px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            color: var(--green);
            border: 1.5px solid var(--border);
            transition: all var(--transition);
            background: var(--white);
        }
        .sort-btn:hover, .sort-btn.active {
            background: var(--green);
            color: var(--white);
            border-color: var(--green);
        }

        /* ── Pagination ── */
        .pagination {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 40px 0 16px;
        }
        .page-btn {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: var(--radius-sm);
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            color: var(--green);
            border: 1.5px solid var(--border);
            transition: all var(--transition);
            background: var(--white);
        }
        .page-btn:hover { border-color: var(--green); background: var(--green-subtle); }
        .page-btn.active { background: var(--green); color: var(--white); border-color: var(--green); }
        .page-btn.disabled { opacity: .35; pointer-events: none; }
        .page-btn--wide { width: auto; padding: 0 14px; }

        /* ── Tags / categories ── */
        .tags { display: flex; flex-wrap: wrap; gap: 8px; }
        .tag {
            padding: 4px 13px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 500;
            text-decoration: none;
            transition: all var(--transition);
        }
        .tag-light {
            background: rgba(255,255,255,.15);
            color: var(--white);
            border: 1px solid rgba(255,255,255,.3);
        }
        .tag-light:hover { background: rgba(255,255,255,.28); }
        .tag-green {
            background: var(--green-pale);
            color: var(--green-dark);
            border: 1px solid var(--border);
        }
        .tag-green:hover { background: var(--green); color: var(--white); border-color: var(--green); }

        /* ── Article detail ── */
        .article-hero {
            background: linear-gradient(160deg, var(--green-dark) 0%, #1e5c3b 100%);
            color: var(--white);
            padding: 52px 0 0;
        }
        .article-meta-bar {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 18px;
            font-size: 13px;
            color: rgba(255,255,255,.7);
            margin-bottom: 18px;
        }
        .article-meta-bar a { color: var(--green-pale); text-decoration: none; }
        .article-meta-bar a:hover { text-decoration: underline; }
        .article-hero__title {
            font-family: Georgia, serif;
            font-size: clamp(26px, 4.5vw, 48px);
            font-weight: 700;
            line-height: 1.2;
            margin-bottom: 16px;
        }
        .article-hero__desc {
            font-size: 17px;
            opacity: .85;
            line-height: 1.65;
            max-width: 680px;
            margin-bottom: 28px;
        }
        .article-hero__img {
            width: 100%;
            max-height: 460px;
            object-fit: cover;
            display: block;
            margin-top: 36px;
        }

        .article-layout {
            display: grid;
            grid-template-columns: 1fr 300px;
            gap: 48px;
            padding: 48px 0 72px;
            align-items: start;
        }
        @media (max-width: 860px) { .article-layout { grid-template-columns: 1fr; } }

        .article-content {
            font-size: 17px;
            line-height: 1.9;
            color: #222e28;
        }
        .article-content p { margin-bottom: 1.5em; }

        /* ── Sidebar ── */
        .sidebar__widget {
            background: var(--white);
            border-radius: var(--radius);
            padding: 22px 24px;
            box-shadow: var(--shadow-sm);
            margin-bottom: 24px;
            border: 1px solid var(--border);
        }
        .sidebar__title {
            font-family: Georgia, serif;
            font-size: 17px;
            font-weight: 700;
            color: var(--green-dark);
            margin-bottom: 16px;
            padding-bottom: 12px;
            border-bottom: 2px solid var(--green-pale);
        }

        /* ── Similar articles ── */
        .similar-list { display: flex; flex-direction: column; gap: 14px; }
        .similar-item {
            display: flex;
            gap: 12px;
            text-decoration: none;
            color: inherit;
            align-items: flex-start;
        }
        .similar-item:hover .similar-title { color: var(--green); }
        .similar-img {
            width: 68px;
            height: 52px;
            object-fit: cover;
            border-radius: var(--radius-sm);
            flex-shrink: 0;
            background: var(--green-pale);
        }
        .similar-title {
            font-size: 13px;
            font-weight: 600;
            line-height: 1.45;
            transition: color var(--transition);
        }
        .similar-date { font-size: 12px; color: var(--text-muted); margin-top: 4px; }

        /* ── Not found ── */
        .not-found {
            text-align: center;
            padding: 120px 24px;
        }
        .not-found__icon { font-size: 64px; margin-bottom: 20px; }
        .not-found h2 {
            font-family: Georgia, serif;
            font-size: 32px;
            color: var(--green-dark);
            margin-bottom: 12px;
        }
        .not-found p { color: var(--text-muted); font-size: 16px; }

        /* ── Footer ── */
        .site-footer {
            background: var(--green-dark);
            color: rgba(255,255,255,.65);
            padding: 52px 0 28px;
            margin-top: auto;
        }
        .footer-inner {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 40px;
            margin-bottom: 40px;
            flex-wrap: wrap;
        }
        .footer__brand {
            font-family: Georgia, serif;
            font-size: 19px;
            font-weight: 700;
            color: var(--white);
            margin-bottom: 8px;
        }
        .footer__brand span { color: var(--green-light); }
        .footer__tagline { font-size: 14px; max-width: 240px; line-height: 1.6; }
        .footer__bottom {
            border-top: 1px solid rgba(255,255,255,.1);
            padding-top: 22px;
            text-align: center;
            font-size: 13px;
        }

        /* ── Main content wrapper ── */
        main { flex: 1; }

        /* ── Divider ── */
        .section-divider {
            border: none;
            border-top: 1px solid var(--border);
        }
    </style>
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
