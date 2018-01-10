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

   {include file='file:tpleditor.html'}

{/if}

{include file='file:layout/footer.tpl'}
