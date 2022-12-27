<?php
/**
 * Comments Template-Resources Handling
 * @package zorg\Forum
 */
/**
 * File includes
 * @include smarty.inc.php required
 * @include forum.inc.php required
 * @include usersystem.inc.php required
 */
require_once INCLUDES_DIR.'/smarty.inc.php';
require_once INCLUDES_DIR.'forum.inc.php';
//require_once INCLUDES_DIR.'usersystem.inc.php'; // DUPLICATE INCLUSION (already in smarty.inc.php)

/**
 * tpl resource - get timestamp
 *
 * @author [z]biko
 * @version 1.0
 * @since 1.0 function added
 */
function smartyresource_comments_get_timestamp($tpl_name, &$tpl_timestamp, &$smarty)
{
  /** comments werden nie automatisch kompiliert. immer nur manuell. */
  global $compile_comments;

  $tpl_timestamp = 0;
  return true;
}

/**
 * tpl resource - get secure
 * sicherheit des templates $tpl_name überprüfen
 *
 * @author [z]biko
 * @version 1.0
 * @since 1.0 function added
 */
function smartyresource_comments_get_secure($tpl_name, &$smarty_obj) {
  return true;
}

/**
 * tpl resource - get trusted
 * nicht verwendet; funktion muss aber existieren
 *
 * @author [z]biko
 * @version 1.0
 * @since 1.0 function added
 */
function smartyresource_comments_get_trusted($tpl_name, &$smarty_obj) {
  // elmatrichüd!
}

/**
 * tpl resource - comments get thread
 *
 * @author [z]biko
 * @version 1.0
 * @since 1.0 function added
 */
function smartyresource_comments_get_thread ($id, $board) {
	//if(!is_numeric($id)) echo '$id is not numeric!';
	if ($board == 'f') {
		//if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> smartyresource_comments_get_thread(): %s', __METHOD__, __LINE__, $board));
		return smartyresource_comments_get_commenttree($id, true);
	} else {
		//if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> smartyresource_comments_get_thread(): %s', __METHOD__, __LINE__, $board));
		return smartyresource_comments_get_childposts($id, $board);
	}
}

/**
 * tpl resource - comments get template
 *
 * Aufbau comments-resource:
 * - ID  z.B. comments:12345
 * -> holt comment 12345
 *
 * BOARD - ID  z.B. comments:b-123
 * -> holt thread 123 aus dem board b
 * boards können mit dem einzelnen character (aus table) angegeben werden.
 *
 * @author [z]biko
 * @version 2.1
 * @since 1.0 function added
 * @since 2.0 `26.10.2018` `IneX` various optimizations, structured html (schema.org)
 * @since 2.1 `22.01.2020` `IneX` Fix sizeof() to only be called when variable is an array, and therefore guarantee it's Countable (eliminating parsing warnings)
 *
 * @global object $db Globales Class-Object mit allen MySQL-Methoden um unser Template zu laden, und '$tpl_source' zuzuweisen
 */
function smartyresource_comments_get_template ($tpl_name, &$tpl_source, &$smarty) {
	global $db;

	$tpl_source = '';

	$name = explode('-', $tpl_name);
	//if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> smartyresource_comments_get_template($name): %s', __METHOD__, __LINE__, $tpl_name));
	if (is_array($name) && sizeof($name) == 1)
	{
		/** forum - commenttree holen */
		$tpl_source = smartyresource_comments_get_commenttree($name[0]);
		//if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> smartyresource_comments_get_commenttree(): %s', __METHOD__, __LINE__, $name[0]));
	} else {
		/** thread - thread holen */
		//$tpl_source = smartyresource_comments_get_thread(Comment::getParentid($name[1], 3), $name[0]);
		$tpl_source = smartyresource_comments_get_thread($name[1], $name[0]);
		//if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> smartyresource_comments_get_thread(): %s, %s', __METHOD__, __LINE__, $name[1], $name[0]));
	}

	return true;
}

/**
 * tpl resource - comments get navigation
 *
 * @author [z]biko
 * @author IneX
 * @version 2.0
 * @since 1.0 `[z]biko` function added
 * @since 2.0 `14.01.2019` `IneX` added schema.org tags
 *
 * @param integer $id Comment-ID
 * @param integer $thread_id
 * @param string $board
 * @global object $db Globales Class-Object mit allen MySQL-Methoden
 * @global object $smarty Globales Class-Object mit allen Smarty-Methoden
 * @return string
 */
function smartyresource_comments_get_navigation ($id, $thread_id, $board) {
	global $db, $smarty;

	$html = '<table class="border" width="100%" itemscope itemtype="http://schema.org/BreadcrumbList">
				<tr><td class="small">';

	$count = 1;
	$parent_id = $id;
	while ($parent_id > $thread_id) {
		$up_e = $db->query('SELECT * FROM comments WHERE id='.$parent_id, __FILE__, __LINE__, __FUNCTION__);
		$up = $db->fetch($up_e);

		$html .= '<span itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">';
			$html .= '<a itemprop="item" href="{get_changed_url change="parent_id='.$up['id'].'"}">';
				$html .= '<span itemprop="name">'.$count.' up</span>';
				$html .= '<span itemprop="position" content="'.$count.'"></span>';
			$html .= '</a> | ';
		$html .= '</span>';

		$parent_id = $up['parent_id'];
		$count++;
	}

	$html .= Comment::getLinkThread($board, $thread_id);

	$html .= '</td></tr></table>';

	$html .=
		'<table bgcolor="{$color.background}" class="border forum" style="table-layout:fixed;" width="100%">'
			.'<tr>'
				.'<td align="left" bgcolor="{$color.background}" valign="top"><nobr>'
					.'<a href="{get_changed_url change="parent_id='.$id.'"}">'
					.'<font size="4">^^^ Additional posts ^^^</font></a>'
				.'</td>'
			.'</tr>'
		.'</table>'
	;

	return $html;
	return $smarty->fetch('file:layout/partials/forum/comments_navigation');
}

/**
 * tpl resource - comments get comment-tree
 *
 * @author [z]biko
 * @author IneX
 * @version 3.2
 * @since 1.0 `[z]biko` function added
 * @since 2.0 `26.10.2018` `IneX` function code cleanup & optimized, added structured data (schema.org) and google-off/-on, added Thread-Switch
 * @since 3.0 `30.10.2018` `IneX` added check of $user->is_loggedin() to Query for Member-specific joins
 * @since 3.1 `14.01.2019` `IneX` fixed schema.org tags
 * @since 3.2 `22.01.2020` `IneX` Fix sizeof() to only be called when variable is an array, and therefore guarantee it's Countable (eliminating parsing warnings)
 *
 * @TODO ganzes HTML in ein Smarty TPL auslagern
 *
 * @see smartyresource_comments_get_navigation(), smartyresource_comments_get_childposts()
 * @see Comment::getLinkThread(), Comment::formatPost(), Comment::getNumChildposts()
 * @param integer $id Comment-ID
 * @param boolean $is_thread Switch to check if get_commenttree is for initial 'f' post (=Thread Comment) or not (any regular Comment) - Default: false
 * @global object $db Globales Class-Object mit allen MySQL-Methoden
 * @global object $user Globales Class-Object mit den User-Methoden & Variablen
 * @global object $smarty Globales Class-Object mit allen Smarty-Methoden
 * @return string
 */
function smartyresource_comments_get_commenttree ($id, $is_thread=false) {
	global $db, $user, $smarty;

	$sql = 'SELECT
				comments.*,
				UNIX_TIMESTAMP(comments.date) date,
				UNIX_TIMESTAMP(comments.date_edited) date_edited,
				user.clan_tag, user.username,
				count(c2.id) as numchildposts'
				.($user->is_loggedin() ? ', IF(ISNULL(cs.comment_id), 0, 1) AS issubscribed' : '').
			' FROM comments
			LEFT JOIN user ON (comments.user_id = user.id)
			LEFT JOIN comments as c2 ON (comments.id = c2.parent_id AND comments.board = c2.board)'
			.($user->is_loggedin() ? 'LEFT JOIN comments_subscriptions cs ON (comments.id = cs.id AND comments.board = cs.board AND cs.user_id = '.$user->id.')' : '').
			' WHERE comments.id = '.$id.' GROUP BY comments.id';
	$rs = $db->fetch($db->query($sql, __FILE__, __LINE__, __FUNCTION__));

	$html = '{if $comments_top_additional == 1}';
		$html .= smartyresource_comments_get_navigation($rs['id'], $rs['thread_id'], $rs['board']);
	$html .= '{else}';

			$html .=
				'<table class="forum"'.($is_thread ? 'itemid="{$smarty.const.SITE_URL}{comment_get_link board='.$rs['board'].' thread_id='.$rs['thread_id'].'}" itemscope itemtype="http://schema.org/DiscussionForumPosting"' : 'itemprop="articleSection" itemscope itemtype="http://schema.org/Comment"').'>'
			 	.'<tr>';

			$html .=
				'{foreach from=$hdepth item=it key=k}'.
					'{if is_array($hdepth) && $k == (sizeof($hdepth) - 1)}';
						if($rs['numchildposts'] > 0) {
					  		$html .=
					  			'<td class="threading {$it}">'
						    		.'<a class="threading switch collapse" onClick="showhide('.$rs['id'].', this)"></a>'
						    	.'</td>'
					    	;
					  	} else {
					  		$html .=
					  			'{if $it == "space"}'.
					  				'<td class="threading end"></td>'.
					  			'{else}'.
					  				'<td class="threading vertline"><span class="threading split"></span></td>'.
					  			'{/if}';
					  	}
			$html .=
					'{else}'.
						'<td class="threading {$it}"></td>'.
					'{/if}'.
				'{/foreach}';

			$html .=
			 '<td align="left" class="border forum">'
			 .'{if $user->id!=0 && in_array('.$rs['id'].', $comments_unread)}'
			 	.'{assign var=comment_color value=$color.newcomment}'
			 	.'{comment_mark_read comment_id="'.$rs['id'].'" user_id=$user->id}'
			 .'{elseif $user->id == '.$rs['user_id'].'}'
			  .'{assign var=comment_color value=$color.owncomment}'
			 .'{else}'
			 	.'{assign var=comment_color value=$color.background}'
			 .'{/if}'
			 .'{capture assign="sizeof_hdepth"}{sizeof array=$hdepth}{/capture}'
			 .'<table bgcolor="{comment_colorfade depth=$sizeof_hdepth color=$comment_color}" style="table-layout:fixed;" width="100%">'
			 .'<tr class="tiny">'
				.'<td class="forum comment meta left" style="width: {if $user->from_mobile}85%{else}70%{/if};">'
				.'<div style="display: none;" itemscope itemtype="http://schema.org/Organization" itemprop="publisher"><span style="display: none;" itemprop="name">{$smarty.const.SITE_HOSTNAME}</span></div>'
				.'<a href="{comment_get_link board='.$rs['board'].' parent_id='.$rs['parent_id'].' id='.$rs['id'].' thread_id='.$rs['thread_id'].'}" name="'.$rs['id'].'"'.($is_thread ? ' itemprop="url"' : '').'>'
				.'#'.$rs['id']
				.'</a>'
				.' by <span itemprop="'.($is_thread ? 'author' : 'contributor').'" itemscope itemtype="http://schema.org/Person">'.$user->userpagelink($rs['user_id'], $rs['clan_tag'], $rs['username'])
				.'</span> @ <meta itemprop="datePublished" content="{'.$rs['date'].'|date_format:"%Y-%m-%d"}">{datename date='.$rs['date'].'}'
			;

			if($rs['date_edited'] > 0) {
				$html .= ', edited @ <meta itemprop="dateModified" content="{'.$rs['date_edited'].'|date_format:"%Y-%m-%d-T%H:00"}">{datename date='.$rs['date_edited'].'}';
			}

			$html .= '<!--googleoff: all-->';
			$html .=
				' <a href="#top" class="dont-wrap">- {if $user->from_mobile}top{else}nach oben{/if} -</a> '
				.'</td><td class="forum comment meta dont-wrap align-right hide-mobile" style="width: 15%;">'
			;

			// Subscribe / Unsubscribe
			$html .= '{if $user->id > 0}'
						.'{if in_array('.$rs['id'].', $comments_subscribed)}
							<a href="/actions/commenting.php'
							.'?do=unsubscribe'
							.'&board='.$rs['board']
							.'&comment_id='.$rs['id']
							.'&url={$request.url|base64encodeurl}'
							.'">[unsubscribe]</a>
						{else}
							<a href="/actions/commenting.php?do=subscribe&board='.$rs['board'].'&comment_id='.$rs['id'].'&url={$request.url|base64encodeurl}">[subscribe]</a>
						{/if}
					{/if}';

			$html .= '{if $user->id == '.$rs['user_id'].'}'
				  		.'<a href="/forum.php?layout=edit&parent_id='.$rs['parent_id'].'&id='.$rs['id'].'&url={$request.url|base64encodeurl}">[edit]</a> '
				  	.'{/if}
				  	  {if $user->id != 0}'
				  		.'</td><td class="forum comment meta right align-right" style="width: 15%;">'
					  		.'<label for="replyfor-'.$rs['id'].'" class="dont-wrap" style="margin-right: 2px;">'
						  		.'<input type="radio" class="replybutton" name="parent_id" id="replyfor-'.$rs['id'].'" onClick="reply()" value="'.$rs['id'].'" '
						  		.'{if $smarty.get.parent_id == '.$rs['id'].'} checked="checked" {/if} /><span class="hide-mobile">&nbsp;reply</span></label>'
				  	.'{/if}';
			$html .= '<!--googleon: all-->';
			$html .= '</td></tr><tr>';

			($is_thread ? $html .= '<span itemprop="headline" content="'.remove_html(Comment::getLinkThread($rs['board'], $rs['thread_id'])).'"></span>' : '');
			$html .= '<td class="forum comment" colspan="3" itemprop="'.($is_thread ? 'articleBody' : 'text').'">';
			if (!$rs['error']) {
				$html .= Comment::formatPost($rs['text']);
			} else {
				$html .= '<b><font color="red">{literal}'.$rs['error'].'{/literal}</font></b>';
			}
			$replyCount = Comment::getNumChildposts($rs['board'], $rs['id']);
			if ($replyCount > 0) $html .= '<span itemprop="interactionStatistic" itemscope itemtype="http://schema.org/InteractionCounter">
						<link itemprop="interactionType" href="http://schema.org/CommentAction" />
						<span itemprop="userInteractionCount" content="'.$replyCount.'"></span>
					</span>';
			if ($replyCount > 0) $html .= '<span itemprop="commentCount" content="'.$replyCount.'"></span>';
			$html .= '</td></tr></table></td></tr></table>';
			$html .= '{/if}';

		if(!is_numeric($rs['id']))
		{
			echo sprintf('[ERROR] <%s:%d> $rs[id] is not numeric: "%d"', __FILE__, __LINE__, $rs['id']);
			exit;
			//$html .= '$rs[id] is not numeric! '.__FILE__.' Line: '.__LINE__;
		}
		$html .= '{if !$comments_no_childposts}';
			$html .= smartyresource_comments_get_childposts($rs['id'], $rs['board']);
		$html .= '{/if}';

		return $html;
}

/**
 * tpl resource - comments get child-posts
 *
 * @author [z]biko
 * @version 2.2
 * @since 1.0 function added
 * @since 2.0 `26.10.2018` `IneX` function code cleanup & optimized
 * @since 2.1 `22.01.2020` `IneX` Fix sizeof() to only be called when variable is an array, and therefore guarantee it's Countable (eliminating parsing warnings)
 * @since 2.2 `04.12.2020` `IneX` Fix error in compiled template "Warning: count(): Parameter must be an array or an object that implements Countable"
 *
 * @var $color
 * @uses Comment::getNumChildposts()
 * @uses smarty_comment_colorfade()
 * @param integer $parent_id
 * @param string $board
 * @global object $db Globales Class-Object mit allen MySQL-Methoden
 * @global object $user Globales Class-Object mit den User-Methoden & Variablen
 * @global object $smarty Globales Class-Object mit allen Smarty-Methoden
 * @return string
 */
function smartyresource_comments_get_childposts ($parent_id, $board) {
	global $db, $user, $smarty;

	/** Validate passed parameters */
	if(empty($parent_id) || !is_numeric($parent_id) || is_array($parent_id) || $parent_id < 0) {
		user_error(sprintf('<%s:%d> $parent_id is not numeric: %s', __FILE__, __LINE__, $parent_id), E_USER_WARNING);
		exit;
	}
	if (empty($board) || is_numeric($board) || is_array($board)) {
		user_error(sprintf('<%s:%d> $board is not valid: %s', __FILE__, __LINE__, $board), E_USER_WARNING);
		exit;
	}

	$html = '{if not $hdepth}{assign_array var=counthdepth value=0}{else}{assign var=counthdepth value=$hdepth|@count}{/if}'; // TODO Smarty 3.x can assign default value (and drop @): $var|default:array()|count
	$html .= '{if ($user->id != 0 && $counthdepth <= $user->maxdepth) || ($user->id == 0 && $counthdepth < $comments_default_maxdepth) || '.Comment::getNumChildposts($board, $parent_id).' == 0}';

		$html .= '<div id="layer'.$parent_id.'">';

		$sql = 'SELECT comments.* FROM comments WHERE comments.parent_id='.$parent_id.' AND comments.board="'.$board.'" ORDER BY comments.id';
		$result = $db->query($sql, __FILE__, __LINE__, __FUNCTION__);
		$rcount = 0;
		while($child = $db->fetch($result)) {
			$depth2 = $depth;
			$rcount++;
			$html .= '{comment_extend_depth depth=$hdepth childposts='.Comment::getNumChildposts($board, $parent_id).' rcount='.$rcount.'}';
			$html .= '{include file="comments:'.$child['id'].'" comments_top_additional=0}';
			$html .= '{comment_remove_depth depth=$hdepth}';
		}
		$html .= '</div>';

	$html .= '{else}';

			$html .= '{comment_extend_depth depth=$hdepth childposts='.Comment::getNumChildposts($board, $parent_id).' rcount='.$rcount.'}';

			$html .=
			 '<table class="forum">'
			 .'<tr>';

			$html .=
				'{foreach from=$hdepth item=it}'.
					'<td class="threading {$it}"></td>'.
				'{/foreach}';

			// restlicher output
			$html .=
				 '<td class="threading {$it}"></td>' // Manually added 1 space to fix alignment of "Additional posts"
				.'<td class="threading space">'
					.'<a class="threading switch expand" href="{get_changed_url change="parent_id='.$parent_id.'"}"></a>'
				.'</td>'
				.'<td align="left" class="border forum">'

			 .'<table bgcolor="{comment_colorfade depth=$sizeof_hdepth color=$color.newcomment}" class="forum">'
			 .'<tr>'
				 .'<td bgcolor="{$color.newcomment}" valign="top">'
					 .'<a href="{get_changed_url change="parent_id='.$parent_id.'"}">'
						 .' <font size="4">Additional posts</font></a>'
						 .' {if $user->id!=0}<a href="/profil.php?do=view">(du hast Forumanzeigeschwelle <b>{$user->maxdepth}</b> eingestellt)</a>{/if}'
			 .'</td></tr></table>'

				.'</td></tr></table>'
			;

			$html .= '{comment_remove_depth depth=$hdepth}';

	$html .= '{/if}';

	return $html;
}
