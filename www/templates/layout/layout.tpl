{if $sun == 'up' || $smarty.get.sun == 'up'}{assign var=daytime value=day}{else}{assign var=daytime value=night}{/if}
{include file='file:layout/head.tpl'}
{if !$smarty.get.tpleditor}
	{if $request.tpl}
		{include file=$request._tpl}
	{elseif $smarty.get.word}
		{include file=$request._word}
	{else}
		{include file=$tplroot.id}
	{/if}
{else}
	{if $user->typ >= $smarty.const.USER_USER}
		{include file='file:layout/pages/tpleditor.tpl'}
	{else}
		{assign_array var=error value="array('type'=>'warn', 'title'=>'Nice try', 'message'=>'Permissions denied, du Sack!', 'dismissable'=>'false')"} 
		{include file='file:layout/elements/block_error.tpl'}
	{/if}
{/if}
{include file='file:layout/footer.tpl'}