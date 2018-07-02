<h2>Nachricht {if $delete_message_id}beantworten{else}senden{/if}</h2>
{if $smarty.get.sent == 'successful'}
<div class="alert success" onclick="this.style.display='none';"><span class="closebtn">&times;</span>
	<strong>Nachricht gesendet!</strong>
</div>
{/if}
<table class="border" style="text-align:left; width:{$smarty.const.FORUMWIDTH};">
	<tbody>
	<form name="sendform" method="post" action="{$form_action}">
		<input type="hidden" name="action" value="sendmessage">
		<input type="hidden" name="url" value="{$form_url}">
		<tr>
			<td style="vertical-align:top; background-color:{$smarty.const.TABLEBACKGROUNDCOLOR};">
				<b>Betreff</b>
			</td>
			<td>
				<input type="text" class="text" style="width:100%;" maxlength="40" name="subject" tabindex="1" value="{$subject}">
			</td>
			<td rowspan="2" style="padding-left:5px;">
				{$userlist}
			</td>
		</tr>
		<tr>
			<td style="background-color:{$smarty.const.TABLEBACKGROUNDCOLOR};" rowspan="2">
				<b>Message</b>
			</td>
			<td>
				<textarea class="text" cols="90" name="text" rows="14" tabindex="2" wrap="hard" style="width:100%;">{$text}</textarea>
			</td>
		</tr>
		<tr style="font-size: x-small;">
			<td colspan="3" style="text-align:center">
				<a href="{$backlink_url}">Zur&uuml;ck</a>&nbsp;
				<input class="button" name="submit" tabindex="3" type="submit" value="Send">
				{if $delete_message_id}
					&nbsp;<input name="delete_message_id" tabindex="4" type="checkbox" value="{$delete_message_id}">
					obige Nachricht l&ouml;schen
				{/if}
			</td>
		</tr>
	</form>
	</tbody>
</table>