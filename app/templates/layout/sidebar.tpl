{if $tplroot.sidebar_tpl neq '' || isset($sidebarHtml) && $sidebarHtml neq ''}
	</main>
	<aside class="sidebar">
		{if $tplroot.sidebar_tpl neq ''}
			{include file="tpl:`$tplroot.sidebar_tpl`"}
		{else}
			{$sidebarHtml}
		{/if}
	</aside>
{else}
	</main>
{/if}
