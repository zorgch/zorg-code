{if $result_row.user_id == $user->id}
<div class="message me" data-id="{$result_row.date}">{$result_row.text}
	{if $enableUserpics}<div class="message me userpic">{$result_row.user_id|userpic2:0}</div>{/if}
</div>
{else}
{if $user->id > 0}
	{if $result_row.date > $user->currentlogin || $result_row.date > $user->lastlogin}{assign var=new value='new'}{assign var=$numNewMessages value=$numNewMessages++}{/if}
{/if}
<div class="message them {$new}" data-id="{$result_row.date}">
	{if $enableUserpics}<div class="message them userpic">{$result_row.user_id|userpic2:0}</div>{else}{$result_row.user_id|username}: {/if}{$result_row.text}
</div>
{/if}