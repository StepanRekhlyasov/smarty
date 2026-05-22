{if !$category}
<p>Category not found</p>
{else}
<p>Category ID: {$category.id|escape:'html'}</p>
<p>Category Title: {$category.title|escape:'html'}</p>
<p>Category Description: {$category.description|escape:'html'}</p>
<p>Category Created At: {$category.created_at|escape:'html'}</p>
{/if}
