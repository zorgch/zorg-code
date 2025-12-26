<form name="deleteform" method="post" action="{$form_action}">
	<input type="hidden" name="do" value="delete_messages">
	<input type="hidden" name="url" value="{$form_url}">
	<input type="hidden" name="message_id[]" value="{$message_id}">
	<input class="button" name="submit" type="submit" value="Nachricht l&ouml;schen">
</form>