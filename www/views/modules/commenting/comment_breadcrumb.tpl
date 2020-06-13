{nocache}{if $comments_top_additional}
<table class="border" itemscope itemtype="http://schema.org/BreadcrumbList">
	<tr>
		<td class="small">
			{counter start=0 print=false}
			{foreach name="levelup_loop" from=$parent_levelups item=levelup}
			<span itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">
				<a itemprop="item" href="{get_changed_url change="parent_id=`$levelup.comment_id`"}">
					<span itemprop="name">{counter} up</span>
					<span itemprop="position" content="{counter}"></span>
				</a>{if not $smarty.foreach.levelup_loop.last} | {/if}
			</span>
			{/foreach}
			{show_comment_thread_link board=$board thread_id=$thread_id}
		</td>
	</tr>
</table>
<table bgcolor="{$color.background}" class="border forum" style="table-layout:fixed;" width="100%">
	<tr>
		<td align="left" bgcolor="{$color.background}" valign="top"><nobr>
			<a href="{get_changed_url change="parent_id=`$comment_id`"}" class="small">^^^ Vorherige Posts ^^^</a>
		</td>
	</tr>
</table>
{/if}{nocache}