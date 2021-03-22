{* set_show_boards:true = update Forum Subscriptions | :false = to update Unread-Subscriptions in User Profile *}
{if $do === 'set_show_boards'}<form action="/actions/forum_setboards.php" method="POST" name="showboards">
	<input type="hidden" name="do" value="set_show_boards">{/if}
	{foreach name=boards_loop from=$boards item=board key=board_id}
		<label for="{$board.board}"><input type="checkbox" name="{if $do === 'set_show_boards'}forum_boards[]{elseif $do === 'set_unread_boards'}forum_boards_unread[]{else}{$board.board}{/if}" id="{$board.board}" value="{$board.board}" {if in_array($board.board, $boards_checked)}checked{/if} {if $do === 'disable'}disabled{/if}> {$board.title}</label>
	{/foreach}
{if $do === 'set_show_boards'}<input class="button" type="submit" value="save">
</form>{/if}