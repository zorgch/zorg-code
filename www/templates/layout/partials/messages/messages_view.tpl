{if $error != ''}
<div class="alert" onclick="this.style.display='none';"><span class="closebtn">&times;</span>
	<strong>{$error}</strong>
</div>
{else}
<h2 style="text-align:left">{$messagedetails.subject}</h2>
<table class="border" width="100%">
	<tr style="background-color:{$smarty.const.TABLEBACKGROUNDCOLOR}">
		<td align="left">
			<b>Date</b>
		</td>
		<td align="left">
			{$messagedetails.date|datename}
		</td>
	</tr>
	<tr style="background-color:{$smarty.const.TABLEBACKGROUNDCOLOR}">
		<td align="left">
			<b>From</b>
		</td>
		<td align="left">
			{$messagedetails.from_user_id|userpage:1}
		</td>
	</tr>
	<tr style="background-color:{$smarty.const.TABLEBACKGROUNDCOLOR}">
		<td align="left">
			<b>To</b>
		</td>
		<td align="left">
			{section name="recipient" loop=$recipientslist}
				{$recipientslist[recipient]|userpage:0}
			{/section}
		</td>
	</tr>
	<tr>
		<td align="left" colspan="2">
			{$messagedetails.text|nl2br}
		</td>
	</tr>
	<tr height="30" style="background-color:{$smarty.const.TABLEBACKGROUNDCOLOR}">
		<td align="left" width="80">
			{$prevmessage_url}
			{$nextmessage_url}
		</td>
		<td align="right" width="80%">
			{$deletemessage_html}
		</td>
	</tr>
</table>
{/if}