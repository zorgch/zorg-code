<h2>Nachricht {if $delete_message_id}beantworten{else}senden{/if}</h2>
{if $smarty.get.sent == 'successful'}
	{assign_array var="error" value="array('type'=>'success', 'title'=>'Nachricht gesendet!')"}
	{include file="file:layout/elements/block_error.tpl"}
{/if}
<form name="sendform" method="post" action="{$form_action}" style="display: flex;white-space: nowrap;align-items: flex-start;align-content: flex-start;flex-wrap: wrap;">
	<input type="hidden" name="action" value="sendmessage">
	<input type="hidden" name="url" value="{$form_url}">
	<fieldset style="flex: 3;">
		<label style="display: flex;flex-direction: column;">
			Betreff
			<input type="text" class="text" maxlength="40" name="subject" tabindex="1" value="{if $delete_message_id}Re: {/if}{$subject|replace:'Re: ':''}">
		</label>
		<label style="display: flex;flex-direction: column;">
			Message
			<textarea class="text" name="text" rows="14" tabindex="2" wrap="hard">{$text}</textarea>
		</label>
	</fieldset>
	<fieldset style="flex: 1;">
		<label style="display: flex;flex-direction: column;">
			Senden an
			{$userlist}
		</label>
	</fieldset>
	<fieldset style="flex-basis: 100%;height: 0;width: 0;"></fieldset>
	<fieldset style="flex: 4;flex-direction: row;">
		<a href="{$backlink_url}">Zur&uuml;ck</a>&nbsp;
		<input type="submit" name="submit" class="button secondary" tabindex="3" value="Send">
		{if $delete_message_id}
			&nbsp;<input name="delete_message_id" tabindex="4" type="checkbox" value="{$delete_message_id}">
			obige Nachricht l&ouml;schen
		{/if}
	</fieldset>
</form>
