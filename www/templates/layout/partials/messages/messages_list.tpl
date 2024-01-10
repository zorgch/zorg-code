<h2>Pers&ouml;nliche Nachrichten</h2>
{if $smarty.get.delete == 'done'}
<div class="alert warn" onclick="{literal}this.style.display='none';{/literal}{*this.style.opacity='0';setTimeout(function(){this.style.display='none';},600);*}"><span class="closebtn">&times;</span>
	<strong>Ausgew&auml;hlte Nachricht wurde gel&ouml;scht</strong>
</div>
{/if}
<form name="inboxform" method="POST" action="{$form_action}">
	<input type="hidden" name="url" value="{$form_url}">
	<style>
		table .messages > tbody > tr:nth-child(odd){ldelim}background-color:{$smarty.const.TABLEBACKGROUNDCOLOR}{rdelim}
		table .messages > tbody > .new{ldelim}background-color:{$smarty.const.NEWCOMMENTCOLOR} !important{rdelim}
		table .messages > tbody > .my{ldelim}background-color:{$smarty.const.OWNCOMMENTCOLOR} !important{rdelim}
	</style>
	<table class="border messages" width="100%">{assign var="cols_total" value=5}
		<thead>
			<tr>
				<th align="right" colspan="{$cols_total-2}">
					<h3>{if $box == 'inbox'}
						Empfangen / <a href="{get_changed_url change='box=outbox'}">Gesendet</a>
					{elseif $box == 'outbox'}
						<a href="{get_changed_url change='box=inbox'}">Empfangen</a> / Gesendet
					{/if}</h3>
				</th>
				<th align="right" colspan="{$cols_total-3}">
					<a href="{get_changed_url change="do=newmsg"}{*$newmsg_url*}"><button class="button primary" name="button_newMessage" style="float:right;">Neue Nachricht</button></a>
				</th>
			</tr>
			<tr>
				<th style="width:5%"><input class="button" onClick="selectAllMessages();" type="button" value="Alle"></th>
				<th style="width:20%"><a href="{get_changed_url change="sort=date&order=asc"}">Datum</a></th>
				<th style="width:35%;text-align:left;"><a href="{get_changed_url change="sort=subject"}">Message</a></th>
				<th style="width:15%"><a href="{get_changed_url change="sort=from_user_id"}">Sender</a></th>
				<th style="width:25%">Empf&auml;nger</th>
			</tr>
		</thead>
		<tbody>
		{section name='message' loop=$messages}
			<tr {if $messages[message].isread == 0}class="new"{elseif $messages[message].from_user_id == $user->id}class="my"{/if}>
				<td >
					<input name="message_id[]" type="checkbox" value="{$messages[message].id}" onclick="document.getElementById('do_messages_as_unread').disabled = false;document.getElementById('do_delete_messages').disabled = false">
				</td>
				<td>
					{$messages[message].date|datename}
				</td>
				<td style="text-align:left;">
					{if $messages[message].isread == 0}
						&#127381;
					{/if}
					<a href="/messagesystem.php?message_id={$messages[message].id}"><strong>{if $messages[message].subject != ''}{$messages[message].subject}{*$messages[message].subject|truncate:40:"&hellip;"*}{else}- no subject -{/if}</strong></a>
				</td>
				<td>
					{$messages[message].from_user_id|userpage:0}
				</td>
				<td>
					{assign var='recipients' value=','|explode:$messages[message].to_users}
					{section name="recipient" loop=$recipients}
						<span style="white-space:nowrap">{$recipients[recipient]|userpage:0}</span>
					{/section}
				</td>
			</tr>
			{sectionelse}
			<tr>
				<td align="center" colspan="{$cols_total}">
					<b>--- Postfach leer ---</b>
				</td>
			</tr>
		{/section}
		<tfoot>
			<tr>
				<td align="center" colspan="{$cols_total}">
					Pages: <strong>
					{section name='page' loop=$pages start=1}
						{if $smarty.section.page.index == $current_page}
							{$smarty.section.page.index}
						{else}
							<a href="{get_changed_url change="page=`$smarty.section.page.index`"}">{$smarty.section.page.index}</a>
						{/if}
					{sectionelse}
						0
					{/section}
					</strong>
				</td>
			</tr>
			<tr>
				<td align="left" colspan="{$cols_total}">
					<button type="submit" class="button alternate" id="do_mark_all_as_read" name="do" value="mark_all_as_read">ALLE als gelesen markieren</button>
					<button type="submit" class="button secondary" id="do_messages_as_unread" name="do" value="messages_as_unread" disabled>Markierte als ungelesen</button>
					<button type="submit" class="button danger" id="do_delete_messages" name="do" value="delete_messages" disabled>Markierte Nachrichten l&ouml;schen</button>
				</td>
			</tr>
		</tfoot>
	</table>
</form>
