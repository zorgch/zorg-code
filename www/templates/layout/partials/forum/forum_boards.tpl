{if $do === 'set_show_boards'}<form action="/actions/forum_setboards.php" method="POST" name="showboards">
	<input type="hidden" name="do" value="set_show_boards">{/if}
	{foreach name=boards_loop from=$boards item=board key=board_id}
		<label for="{$board.board}"><input type="checkbox" name="{if $do === 'set_show_boards'}forum_boards[]{else}forum_boards_unread[]{/if}" id="{$board.board}" value="{$board.board}" {if in_array($board.board, $boards_checked)}checked{/if}> {$board.title}</label>
	{/foreach}
{if $do === 'set_show_boards'}</form>{/if}