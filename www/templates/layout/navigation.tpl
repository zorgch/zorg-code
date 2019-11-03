<nav class="navigation">
	{* MyMenu is always loaded! *}{if $user->mymenu neq ''}{include file="tpl:`$user->mymenu`"}{/if}
	{if $tplroot.menus neq ''}
		{foreach from=$tplroot.menus item=menu name=navmenus}
			{*if $menu != ''}<nobr>{$notification}{if $smarty.foreach.notifications.last == false} | {/if}</nobr>{/if*}
			{*include file="tpl:`$menu`"*}
			{menu name=$menu}
		{/foreach}
	{elseif $tpl_menus neq ''}
		{foreach from=$tpl_menus item=menu name=navmenus}
			{menu name=$name}
		{/foreach}
	{/if}
</nav>