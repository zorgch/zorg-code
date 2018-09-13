<table class="border" style="border-spacing:2px; border-collapse:collapse; width:204px; background-color:{$smarty.const.BACKGROUNDCOLOR}; margin:5px 0 10px 0;">
	<tr>
		<td align='left'>
			<small><b>{$poll.text}</b>{if $poll.state=="closed"}<span style="color:red;"> [closed]</span>{/if}<br>
			{$poll.user|userpage:0}, {$poll.date|datename}{if $poll.type=='member'} <nobr>- members only -</nobr>{/if}
			</small>
		</td>
	</tr>
	<tr>
		<td>
			<img src="/images/pixel_border.gif" height="1" width="100%">
		</td>
	</tr>
{* Poll is open for votes by user: *}
{if $user_has_vote_permission && !$poll.myvote && $poll.state=='open'}
	<form name="poll" method="post" action="{$form_action}">
		<input type="hidden" name="poll" value="{$poll.id}">
		{foreach name=answers_loop from=$answers item=answer key=answer_id}
		<tr>
			<td align="left">
				<table>
					<tr>
						<td align="left" valign="middle" width=10>
							<input type="radio" value="{$answer.id}" name="vote" onClick="document.location.href=\"{$action}&poll={$poll.id}&vote={$answer.id}\"">
						</td>
						<td align="left" valign="middle">
							<small>{$answer.text}</small>
						</td>
					</tr>
				</table>
			</td>
		</tr>
		{/foreach}
		<tr>
			<td align="center">
				<input type="submit" class="button" value=" vote ">
			</td>
		</tr>
	</form>
{* Display Poll with voting results: *}
{else}
	{foreach name=answers_loop from=$answers item=answer key=answer_id}
	<tr>
		<td>
			<img src="/images/spc.gif" height="2" width="1">
		</td>
	</tr>
	<tr>
		<td align="left">
			<small>
			{if $poll.myvote != $answer.id}{$answer.text} ({$answer.votes}){/if}
			{if $poll.myvote == $answer.id}<b>{$answer.text}</b> ({$answer.votes}){/if}
			{if $poll.type=='member'}: <i>
				{foreach name=voters_loop from=$voters[$answer.id] item=voter key=voter_index}
					{if $voter.user == $user->id}<b>{/if}{$voter.user|name}{if $voter.user == $user->id}</b>{/if}{if $smarty.foreach.voters_loop.last == false},{/if}
				{/foreach}
			</i>{/if}
			{if $poll.myvote == $answer.id && $poll.state=='open' && $user_has_vote_permission} <a href="{$answer.unvote_url}{*$poll_unvoteurl[$answer_id]*}">[unvote]</a>{/if}
			</small>
		</td>
	</tr>
	<tr>
		<td>
			<table cellspacing="0" cellpadding="0">
				<tr>
					<td style="background:url('/images/poll_bar.gif') repeat-x;">
						<img src="/images/spc.gif" height="6" width="{$answer.pollbar_size}">
					</td>
					<td>
						<img src="/images/spc.gif" height="1" width="{$answer.pollbar_space}">
					</td>
				</tr>
			</table>
		</td>
	</tr>
	{/foreach}
{/if}

{if ($poll.myvote=='1' && $poll.state=='open') || ($user->id==$poll.user && $user_has_vote_permission)}
	<tr>
		<td align="center">
			<small>
			{if $poll.state=='open' && $user->id==$poll.user}
				| <a href="/actions/poll_state.php?poll={$poll.id}&state=closed&{url_params}">close</a> |
			{elseif $poll.state == 'closed' && $user->id==$poll.user}
				| <a href="/actions/poll_state.php?poll={$poll.id}&state=open&{url_params}">open</a> |
			{/if}
			</small>
		</td>
	</tr>
{/if}
</table>