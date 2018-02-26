<?php

require_once( __DIR__ ."/smarty.inc.php");
require_once( __DIR__ ."/forum.inc.php");
require_once( __DIR__ ."/usersystem.inc.php");
require_once( __DIR__ ."/sunrise.inc.php");
require_once( __DIR__ ."/colors.inc.php");




// tpl resource -------------------------------------------------------------
function smartyresource_comments_get_timestamp($tpl_name, &$tpl_timestamp, &$smarty) {
  // comments werden nie automatisch kompiliert. immer nur manuell.      
  global $compile_comments;
  
  $tpl_timestamp = 0;
  return true;
}

// tpl resource
function smartyresource_comments_get_secure($tpl_name, &$smarty_obj) {
  // sicherheit des templates $tpl_name überprüfen
  return true;
}
  
// tpl resource
function smartyresource_comments_get_trusted($tpl_name, &$smarty_obj) {
  // nicht verwendet; funktion muss aber existieren
}


function smartyresource_comments_get_thread ($id, $board) {
	//if(!is_numeric($id)) echo '$id is not numeric!'; 
	if ($board == 'f') {
		return smartyresource_comments_get_commenttree($id);
	} else {
		return smartyresource_comments_get_childposts($id, $board);
	}
}




/**
 * Aufbau comments-resource:
 ***********************************
 * 
 * ID  z.B. comments:12345
 * holt comment 12345
 *
 * BOARD - ID  z.B. comments:b-123
 * holt thread 123 aus dem board b 
 * boards können mit dem einzelnen character (aus table) angegeben werden.
 */


function smartyresource_comments_get_template ($tpl_name, &$tpl_source, &$smarty) {
  // Datenbankabfrage um unser Template zu laden,
  // und '$tpl_source' zuzuweisen
	global $db;
	
	$tpl_source = "";
	
	$name = explode("-", $tpl_name);
	if (sizeof($name) == 1) {
  
		// forum - commenttree holen
		
		$tpl_source = smartyresource_comments_get_commenttree($name[0]);
	}else{
		// thread - thread holen
		//$tpl_source = smartyresource_comments_get_thread(Comment::getParentid($name[1], 3), $name[0]);
		$tpl_source = smartyresource_comments_get_thread($name[1], $name[0]);
	}
	
	return true;
}




function smartyresource_comments_get_navigation ($id, $thread_id, $board) {
	global $db;
	
	$html = "";
		$html .= '<table class="border" width="100%"><tr><td class="small">';
				
		$count = 1;
		$parent_id = $id;
		while ($parent_id > $thread_id) {
			$up_e = $db->query("SELECT * FROM comments WHERE id=$parent_id", __FILE__, __LINE__);
			$up = $db->fetch($up_e);
			
			$html .= '<a href="{get_changed_url change="parent_id='.$up['id'].'"}">'.$count.' up</a> | ';
			
			
			$parent_id = $up['parent_id'];
			$count++;
		}
		
		$html .= Comment::getLinkThread($board, $thread_id);
		
		$html .= '</td></tr></table>';
		
		$html .= 
			'<table bgcolor="{$color.background}" class="border forum"  style="table-layout:fixed;" width="100%">'
				.'<tr>'
					.'<td align="left" bgcolor="{$color.background}" valign="top"><nobr>'
						.'<a href="{get_changed_url change="parent_id='.$id.'"}">'
						.'<font size="4">^^^ Additional posts ^^^</font></a>'
					.'</td>'
				.'</tr>'
			.'</table>'
		;
		
	
	
	return $html;
}



function smartyresource_comments_get_commenttree ($id) {
	global $db, $user, $layouttype;
	
	
	$sql = "SELECT"
		." comments.*"
		.", UNIX_TIMESTAMP(comments.date) date"
		.", UNIX_TIMESTAMP(comments.date_edited) date_edited"
		.", user.clan_tag, user.username"
		.", count(c2.id) as numchildposts
			, IF(ISNULL(cs.comment_id), 0, 1) AS issubscribed
		FROM comments
		LEFT JOIN user ON (comments.user_id = user.id)
		LEFT JOIN comments as c2 ON (comments.id = c2.parent_id AND comments.board = c2.board)
		LEFT JOIN comments_subscriptions cs 
			ON (comments.id = cs.id AND comments.board = cs.board AND cs.user_id = '".$user->id."')
		WHERE comments.id = '$id'"
		." GROUP BY comments.id"
	;
	$rs = $db->fetch($db->query($sql, __FILE__, __LINE__));
	
	$html = "";
	
	
	$html .= '{if $comments_top_additional == 1}';
		$html .= smartyresource_comments_get_navigation($rs['id'], $rs['thread_id'], $rs['board']);
	$html .= '{else}';	
	
			$html .=
				'<table class="forum">'
			 .'<tr>'
			;
			
			$html .= 
				'{foreach from=$hdepth item=it key=k}'.
					'{if $k == (sizeof($hdepth) - 1)}';
						if($rs['numchildposts'] > 0) {
					  		$html .=
					  			'<td class="{$it}">'
					    		.'<a onClick="onoff(\''.$rs['id'].'\')">'
					    		.'<img class="forum" name="img'.$rs['id'].'" src="/images/forum/'.$layouttype.'/minus.gif" />'
						    	.'</a>'
						    	.'</td>'
					    	;
					  	} else {
					  		$html .= 
					  			'{if $it == "space"}'.
					  				'<td class="end"></td>'.
					  			'{else}'.
					  				'<td class="vertline"><img class="forum" src="/images/forum/'.$layouttype.'/split.gif" /></td>'.
					  			'{/if}';
					  	}
			$html .= 
					'{else}'.
						'<td class="{$it}"></td>'.
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
			 .'<table bgcolor="{comment_colorfade depth=$sizeof_hdepth color=$comment_color}"'
			 .' style="table-layout:fixed;" width="100%">'
			 .'<tr style="font-size: x-small;">'
				.'<td class="forum" width="75%">'
				.'<a href="{comment_get_link board='.$rs['board'].' parent_id='.$rs['parent_id'].' id='.$rs['id'].' thread_id='.$rs['thread_id'].'}" name="'.$rs['id'].'">'
				.'#'.$rs['id']
				.'</a>'
				.' by '.$user->userpagelink($rs['user_id'], $rs['clan_tag'], $rs['username'])
				.' @ {datename date='.$rs['date'].'}'
				
			;
			
			if($rs['date_edited'] > 0) {
				$html .= ', edited @ {datename date='.$rs['date_edited'].'}';
			}
			
			$html .= 
				' <a href="#top">- nach oben -</a> '
				.'</td><td class="forum" style="text-align: right;" width="22%"><nobr>'
			;
			
			// Subscribe / Unsubscribe
			$html .=
				'{if $user->id > 0}'
				.'{if in_array('.$rs['id'].', $comments_subscribed)}
				<a href="/actions/commenting.php'
				.'?do=unsubscribe'
				.'&board='.$rs['board']
				.'&comment_id='.$rs['id']
				.'&url={base64_encode text=$request.url}'
				.'">[unsubscribe]</a>
				{else}
				<a href="/actions/commenting.php'
				.'?do=subscribe'
				.'&board='.$rs['board']
				.'&comment_id='.$rs['id']
				.'&url={base64_encode text=$request.url}'
				.'">[subscribe]</a>
				{/if}
				{/if}'
			;
					
			$html .=
					'{if $user->id == '.$rs['user_id'].'}'.
			  		'<a href="/forum.php?layout=edit&parent_id='.$rs['parent_id'].'&id='.$rs['id'].
			  		'&url={base64_encode text=$request.url}">[edit]</a> '.
			  	'{/if}'.
			  	
			  	'{if $user->id != 0}'.
			  		'{if $hdepth <= 1}<label for="replyfor-'.$rs['id'].'">Reply:</label>{/if}'.
			  		'</td><td class="forum" style="text-align: right;" width="3%">'.
			  		'<input name="parent_id" id="replyfor-'.$rs['id'].'" onClick="reply()" type="radio" value="'.$rs['id'].'" '.
			  		'{if $smarty.get.parent_id == '.$rs['id'].'} checked="checked" {/if}'.
			  		' />'.
			  	'{/if}'
				;
			
				
			
			$html .= '</nobr></td></tr><tr><td class="forum" colspan="3">'; 
			if (!$rs['error']) {
					$html .= Comment::formatPost($rs['text']);
			} else {
					$html .= "<b><font color='red'>{literal}$rs[error]{/literal}</font></b>";
			}
			$html .= '</td></tr></table></td></tr></table>';
			$html .= '{/if}';
		
		
		if(!is_numeric($rs['id'])) {
			echo '$rs[id] is not numeric: '.$sql.' '.__FILE__.' Line: '.__LINE__;
			exit;
			$html .= '$rs[id] is not numeric! '.__FILE__.' Line: '.__LINE__;
		}
		$html .= '{if !$comments_no_childposts}';
		$html .= smartyresource_comments_get_childposts($rs['id'], $rs['board']);
		$html .= '{/if}';
		
		return $html;		
}


function smartyresource_comments_get_childposts ($parent_id, $board) {
	global $db, $user, $layouttype;
	
	if(!is_numeric($parent_id)) {
		echo '$parent_id is not numeric '.__FILE__.' Line: '.__LINE__;
		exit;
	}
	
	$html = "";
	
	$html .= '{if ($user->id != 0 && sizeof($hdepth) <= $user->maxdepth) || ($user->id == 0 && sizeof($hdepth) < $comments_default_maxdepth) || '.Comment::getNumChildposts($board, $parent_id).' == 0}';	
		
			$sql =
		  	"SELECT"
		  	." comments.*"
		  	." FROM comments"
		  	." WHERE comments.parent_id=$parent_id AND comments.board='$board'"
		  	." ORDER BY comments.id"
		  ;
		  $result = $db->query($sql, __FILE__, __LINE__);
			$rcount = 0;
		
			
			$html .= '<div id="layer'.$parent_id.'">';
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
			 '<table class="forum" style="table-layout:fixed;" width="100%">'
			 .'<tr>'
			;
			
			
			
			$html .= 
				'{foreach from=$hdepth item=it}'.
					'<td class="{$it}"></td>'.
				'{/foreach}';
			
			// restlicher output
			$html .=
				'<td class="space">'
			
				.'<a href="{get_changed_url change="parent_id='.$parent_id.'"}">'
				.'<img border="0" class="forum" src="/images/forum/'.$layouttype.'/plus.gif" />'
				.'</a>'
				.'</td>'
				.'<td align="left" class="border forum">'
				
				
			 .'<table bgcolor="{comment_colorfade depth=$sizeof_hdepth color=$color.newcomment}" class="forum">'
			 .'<tr>'
				.'<td bgcolor="{$color.newcomment}" valign="top">'
			 .'<a href="{get_changed_url change="parent_id='.$parent_id.'"}">'
			 .'<font size="4"> Additional posts</font></a>'
			 .' {if $user->id!=0}<a href="/profil.php?do=view">(du hast Forumanzeigeschwelle <b>{$user->maxdepth}</b> eingestellt)</a>{/if}'
			 .'</td></tr></table>'
			 
				.'</td></tr></table>'
			;
			
			$html .= '{comment_remove_depth depth=$hdepth}';
	
	$html .= '{/if}';
	
	
	return $html;
}

