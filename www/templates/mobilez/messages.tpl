{*if $user->id > 0*}
	<div id="messages" class="background chat">
	{assign var=enableUserpics value=false}
	{foreach from=$query_result name=results item=result_row}
		{*if $result_row.text <> ""*} {* "Skip" empty messages ;-) *}
			{if $previousdate != $result_row.date|date_format:'%D' || $previousdate == ''}
				<div class="message date"><p>{$result_row.date|datename}</p></div>
				{assign var=previousdate value=$result_row.date|date_format:'%D'}
			{/if}
			{include file='file:mobilez/message.tpl'}
		{*/if*}
	{/foreach}
	</div>
	<div id="divLoadmore" class="button loadmore">
		<a name="btnLoadmore" id="btnLoadmore" href="#" class="ui-btn {$btnIconOptions} ui-shadow ui-corner-all ui-btn-icon-left ui-icon-refresh">Mehr anzeigen</a>
	</div>
{*else}
	<div class="background text">
		{include file='file:mobilez/motd.tpl'}
	</div>
{/if*}