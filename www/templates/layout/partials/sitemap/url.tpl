{foreach from=$sitemapItems item=entry}	<url>
		<loc>{$entry.url}</loc>
{if $entry.lastmod neq ''}		<lastmod>{$entry.lastmod}</lastmod>{/if}
{if $entry.changefreq neq ''}		<changefreq>{$entry.changefreq}</changefreq>{/if}
	</url>
{/foreach}