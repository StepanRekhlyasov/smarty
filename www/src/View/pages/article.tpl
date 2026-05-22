{if !$article}
<p>Article not found</p>
{else}
<h1>{$article.title|escape:'html'}</h1>
<p>Article ID: {$article.id|escape:'html'}</p>
<p>Article Content: {$article.content|escape:'html'}</p>
<p>Article Description: {$article.description|escape:'html'}</p>
{if $article.imageUrl}
<p>Article Image URL: {$article.imageUrl|escape:'html'}</p>
{/if}
<p>Article Created At: {$article.createdAt|escape:'html'}</p>
{/if}
