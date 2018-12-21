{strip}{if $show_profilepic === 'true'}<div class="profilepic">{/if}
	{if $show_profile_link === 'true'}<a href="/user/{$username_link}" itemprop="url">{/if}
		{if $show_profilepic === 'true' && $profilepic_imgsrc !== ''}
			<img src="{$profilepic_imgsrc}" alt="{$username} Profile-Pic" title="{$username}" itemprop="image">
		{/if}
		{if $show_username === 'true'}
			{if $show_profilepic === 'true'}<br>{/if}
			<span itemprop="name">{$username}</span>
		{/if}
	{if $show_profile_link === 'true'}</a>{/if}
{if $show_profilepic === 'true'}</div>{/if}{/strip}