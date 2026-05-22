<h1>{$title|escape:'html'}</h1>
<p>Home page</p>
<p><a href="/article/1">Example article #1</a></p>

<h2>Articles</h2>
<ul>
    {foreach $articles as $article}
        <li><a href="/article/{$article.id}">{$article.title|escape:'html'}</a></li>
    {/foreach}
</ul>

<h2>Categories</h2>
<ul>
    {foreach $categories as $category}
        <li><a href="/category/{$category.id}">{$category.title|escape:'html'}</a></li>
    {/foreach}
</ul>
