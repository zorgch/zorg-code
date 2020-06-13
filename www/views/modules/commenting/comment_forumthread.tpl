{nocache}
{if $user->id > 0 && $comment_data.id|in_array:$comments_unread}
 	{assign var=comment_color value=$color.newcomment}
 	{comment_mark_read comment_id=$comment_data.id user_id=$user->id}
{elseif $user->id === $comment_data.user_id}{assign var=comment_color value=$color.owncomment}
{else}{assign var=comment_color value=$color.background}
{/if}
{/nocache}
<table class="forum" ($is_thread ? 'itemid="{$smarty.const.SITE_URL}{comment_get_link board='.$rs['board'].' thread_id='.$rs['thread_id'].'}" itemscope itemtype="http://schema.org/DiscussionForumPosting"' : 'itemprop="articleSection" itemscope itemtype="http://schema.org/Comment"')>
	<tr>
		<td class="border forum" style="width: 100%;">
			<table style="table-layout: fixed;background-color: #{comment_colorfade depth=0 color=$comment_color};">
				<tr class="tiny">
					<td class="forum comment meta left" style="width: 70%;">
						<div style="display: none;" itemscope itemtype="http://schema.org/Organization" itemprop="publisher"><span style="display: none;" itemprop="name">{$smarty.const.SITE_HOSTNAME}</span></div>
						<a href="{comment_get_link board=$comment_data.board parent_id=$comment_data.id id=$comment_data.id thread_id=$comment_data.id}" name="{$comment_data.id}" itemprop="url">#{$comment_data.id}</a>
						&nbsp;by&nbsp;<span ($is_thread ? 'author' : 'contributor') itemscope itemtype="http://schema.org/Person">{$comment_data.user_id|userpage:0}</span>
						&nbsp;@&nbsp;<meta itemprop="datePublished" content="{$comment_data.date|date_format:'%Y-%m-%d'}">{$comment_data.date|datename}
						{if $comment_data.date_edited > 0}, edited @ <meta itemprop="dateModified" content="{$comment_data.date_edited|date_format:'%Y-%m-%d-T%H:00'}">{$comment_data.date_edited|datename}{/if}
						<!--googleoff: all-->
						<a href="#top">- nach oben -</a> 
					</td>
					<td class="forum comment meta" style="width: 15%; text-align: right; white-space: nowrap;">
					{if $user->id > 0}
						{* Subscribe / Unsubscribe *}
						{if $comment_data.id|in_array:$comments_subscribed}
							<a class="hide-mobile" href="/actions/commenting.php?do=unsubscribe&board={$comment_data.board}&comment_id={$comment_data.id}&url={$request.url|base64_encode}">[unsubscribe]</a>
						{else}
							<a class="hide-mobile" href="/actions/commenting.php?do=subscribe&board={$comment_data.board}&comment_id={$comment_data.id}&url={$request.url|base64_encode}">[subscribe]</a>
						{/if}
						{* Edit Comment *}
						{if $user->id === $comment_data.user_id}<a href="/forum.php?layout=edit&parent_id={$comment_data.parent_id}&id={$comment_data.id}&url={$request.url|base64_encode}">[edit]</a>{/if}
					</td>
					<td class="forum comment meta right" style="width: 15%; text-align: right;">
						<label for="replyfor-{$comment_data.id}" style="white-space: nowrap;margin-right: 2px;">
							<input type="radio" class="replybutton" name="parent_id" id="replyfor-{$comment_data.id}" onClick="reply()" value="{$comment_data.id}"{if $smarty.get.parent_id === $comment_data.id} checked="checked"{/if} />
							<span class="hide-mobile">&nbsp;reply</span>
						</label>
					{/if}
					<!--googleon: all-->
					</td>
				</tr>
				<tr>
					<span itemprop="headline" content="{show_thread_link|nohtml board=$comment_data.board thread_id=$comment_data.id}"></span>
					<td class="forum comment" colspan="3" itemprop="{if $is_thread}articleBody{else}text{/if}">
						{if !$comment_data.error}
							{$comment_data.text|format_comment}
						{else}
							{*nocache}{assign var="error" value="['type'=>'warn', 'title'=>'ERROR', 'message'=>`$comment_data.error`']"*}{*assign_array var="error" value="array('type'=>'warn', 'title'=>'ERROR', 'message'=>$comment_data.error')"*}
							{*include file="file:layout/elements/block_error.tpl"}{/nocache*}
							{nocache}{error msg=$comment_data.error}{/nocache}
						{/if}			
						{if $comment_data.numchildposts > 0}
						<span itemprop="interactionStatistic" itemscope itemtype="http://schema.org/InteractionCounter">
							<link itemprop="interactionType" href="http://schema.org/CommentAction" />
							<span itemprop="userInteractionCount" content="{$comment_data.numchildposts}"></span>
						</span>
						<span itemprop="commentCount" content="{$comment_data.numchildposts}"></span>
						{/if}
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>