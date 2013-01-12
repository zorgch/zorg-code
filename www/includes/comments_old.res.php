<?

require_once($_SERVER['DOCUMENT_ROOT']."/includes/smarty.inc.php");
require_once($_SERVER['DOCUMENT_ROOT']."/includes/forum.inc.php");
require_once($_SERVER['DOCUMENT_ROOT']."/includes/usersystem.inc.php");
require_once($_SERVER['DOCUMENT_ROOT']."/includes/colors.inc.php");



/**
 * Aufbau comments-resource:
 ***********************************
 * 
 * ID  z.B. comments:12345
 * holt comment 12345
 *
 * BOARD - ID  z.B. comments:b-123 oder comments:bug-123
 * holt thread 123 aus dem board b 
 * boards können mit dem einzelnen character (aus table) oder den folgenden worten angegeben werden:
 * forum, bug, foto, tpl
 */


function smartyresource_comments_get_template ($tpl_name, &$tpl_source, &$smarty) {
  // Datenbankabfrage um unser Template zu laden,
  // und '$tpl_source' zuzuweisen
	
	// boards
	$boards = array('f', 'b', 'i', 't');	
	
	$name = explode("-", $tpl_name);
	if (sizeof($name) == 1) {
  
		// forum - commenttree holen
		$tpl_source = smartyresource_comments_get_commenttree($name[0]);
	}else{
		if (in_array($name[0], $boards)) {
			// thread - thread holen
			$tpl_source = smartyresource_comments_get_thread($name[1], $name[0]);
		}else{
			// thread mit unbekanntem board - error
			$tpl_source = "<font color='red'><b>Invalid board '$name[0]' on resource 'comments:$tpl_name'</b></font><br />";
		}		
	}
	
	return true;
}


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
	if ($board == 'f') {
		return smartyresource_comments_get_commenttree($id);
	}else{
		return smartyresource_comments_get_childposts($id, $board);
	}
}


function smartyresource_comments_get_navigation ($id, $thread_id, $board) {
	$html = "";
	
	$html .= '<table class="border" width="100%"><tr><td class="small">';
			
	$count = 1;
	$parent_id = $id;
	while ($parent_id > $thread_id) {
		$up_e = $db->query("SELECT * FROM comments WHERE id=$parent_id", __FILE__, __LINE__);
		$up = $db->fetch($up_e);
		
		$html .= '<a href="{get_changed_url var="parent_id" value="'.$up['id'].'"}">'.$count.' up</a> | ';
		
		$parent_id = $up['parent_id'];
		$count++;
	}
	
	$html .= Comment::getLinkThread($board, $thread_id);
	
	$html .= '</td></tr></table>';
	
	$html .= 
		'<table bgcolor="#'.TABLEBACKGROUNDCOLOR.'" class="border forum"  style="table-layout:fixed;" width="100%">'
			.'<tr>'
				.'<td align="left" bgcolor="#'.TABLEBACKGROUNDCOLOR.'" valign="top"><nobr>'
					.'<a href="{get_changed_url var="parent_id" value="'.$id.'"}">'
					.'<font size="4">^^^ Additional posts ^^^</font></a>'
				.'</td>'
			.'</tr>'
		.'</table>'
	;
	
	return $html;
}




function smartyresource_comments_get_commenttree ($id) {
	global $db, $user;
	
	
	$sql = "SELECT"
		." comments.*, UNIX_TIMESTAMP(comments.date) date, user.clan_tag, user.username"
		.", count(c2.id) as numchildposts"
		." FROM comments, user"
		." LEFT JOIN comments as c2 ON (comments.id = c2.parent_id AND comments.board = c2.board)"
		." WHERE comments.id = $id AND user.id = comments.user_id"
		." GROUP BY comments.id"
	;
	$rs = $db->fetch($db->query($sql, __FILE__, __LINE__));
		
	$root_top = $db->fetch($db->query(
		"SELECT id FROM comments WHERE thread_id='$rs[thread_id]' AND board='$rs[board]' ORDER BY id ASC LIMIT 0,1", 
		__FILE__, __LINE__
	));
	
	$html = "";
	
	
	$html .= '{if $comments_no_top_additional == 0 &&  ('.
						$rs['parent_id'].'!='.$rs['thread_id'].' && "'.$rs['board'].'"!="f" || '.
						$rs['parent_id'].'=='.$rs['thread_id'].' && "'.$rs['board'].'"!="f" && "'.$rs['id'].'"!="'.$root_top['id'].'" || '.
						$rs['parent_id'].'!=1 && "'.$rs['board'].'"=="f"'.
					')}';
	
	
			$html .= '<table class="border" width="100%"><tr><td class="small">';
			
			$count = 1;
			$parent_id = $rs['parent_id'];
			while ($parent_id > $rs['thread_id']) {
				$up_e = $db->query("SELECT * FROM comments WHERE id=$parent_id", __FILE__, __LINE__);
				$up = $db->fetch($up_e);
				
				$html .= '<a href="{get_changed_url var="parent_id" value="'.$up['id'].'"}">'.$count.' up</a> | ';
				
				$parent_id = $up['parent_id'];
				$count++;
			}
			
			$html .= Comment::getLinkThread($rs['board'], $rs['thread_id']);

			$html .= '</td></tr></table>';
	
			$html .= 
				'<table bgcolor="#'.TABLEBACKGROUNDCOLOR.'" class="border forum"  style="table-layout:fixed;" width="100%">'
		    .'<tr>'
		  	 .'<td align="left" bgcolor="#'.TABLEBACKGROUNDCOLOR.'" valign="top"><nobr>'
		    .'<a href="{get_changed_url var="parent_id" value="'.$rs['parent_id'].'"}">'
		    .'<font size="4">^^^ Additional posts ^^^</font></a>'
		    .'</td></tr></table>'
	    ;
	
	
	$html .= '{/if}';
	
	
	
		$html .=
			'<table class="forum" style="table-layout:fixed;" width="100%">'
		 .'<tr>'
		;
		
		$html .= 
			'{foreach from=$hdepth item=it key=k}'.
				'{if $k == (sizeof($hdepth) - 1)}';
					if($rs['numchildposts'] > 0) {
				  		$html .=
				  			'<td class="{$it}">'
				    		.'<a onClick="onoff(\''.$rs['id'].'\')">'
				    		.'<img class="forum" name="img'.$rs['id'].'" src="/images/forum/minus.gif" />'
					    	.'</a>'
					    	.'</td>'
				    	;
				  	} else {
				  		$html .= 
				  			'{if $it == "space"}'.
				  				'<td class="end"></td>'.
				  			'{else}'.
				  				'<td class="vertline"><img class="forum" src="/images/forum/split.gif" /></td>'.
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
		 	.'{assign var=comment_color value="'.NEWCOMMENTCOLOR.'"}'
		 	.'{comment_mark_read comment_id="'.$rs['id'].'" user_id=$user->id}'
		 .'{else}'
		 	.'{assign var=comment_color value="'.TABLEBACKGROUNDCOLOR.'"}'
		 .'{/if}'
		 .'{capture assign="sizeof_hdepth"}{sizeof array=$hdepth}{/capture}'
		 .'<table bgcolor="#{comment_colorfade depth=$sizeof_hdepth color=$comment_color}"'
		 .' class="forum"  style="table-layout:fixed;" width="100%">'
		 .'<tr>'
			.'<td class="forum" style="font-size: x-small;">'
			.'<a href="{comment_get_link board='.$rs['board'].' parent_id='.$rs['parent_id'].' id='.$rs['id'].' thread_id='.$rs['thread_id'].'}" name="'.$rs['id'].'">'
			.'#'.$rs['id']
			.'</a>'
			.' by '.usersystem::userpagelink($rs['user_id'], $rs['clan_tag'], $rs['username'])
			.' @ '.datename($rs['date'])
		 .'</td><td class="forum" style="text-align: right;">'
		 .' '
		;
		
			$html .= 
				'{if $user->id == '.$rs['user_id'].'}'.
		  		'<a href="/forum.php?layout=edit&parent_id='.$rs['parent_id'].'&id='.$rs['id'].
		  		'&url={base64_encode text=$request.url}">[EDIT]</a> '.
		  	'{/if}'.
		  	
		  	'{if $user->id != 0}'.
		  		'{if $hdepth <= 1}Reply: {/if}'.
		  		'<input name="parent_id" onClick="reply()" type="radio" value="'.$rs['id'].'" '.
		  		'{if $smarty.get.parent_id == '.$rs['id'].'} checked="checked" {/if}'.
		  		' />'.
		  	'{/if}'
			;
		
			
		
		$html .= '</td></tr><tr><td class="forum" colspan="2">'; 
		if (!$rs['error']) {
				$html .= Comment::formatPost($rs['text'], $rs['html']);
		}else{
				$html .= "<b><font color='red'>{literal}$rs[error]{/literal}</font></b>";
		}
		$html .= '</td></tr></table></td></tr></table>';
		
		$html .= smartyresource_comments_get_childposts($rs['id'], $rs['board']);
		
		return $html;		
}


function smartyresource_comments_get_childposts ($parent_id, $board) {
	global $db, $user;
	
	
	$html = "";
	
	$html .= '{if sizeof($hdepth) < $user->maxdepth}';	
		
			$sql =
		  	"SELECT"
		  	." comments.*"
		  	." FROM comments"
		  	." WHERE comments.parent_id=$parent_id AND comments.board='$board'"
		  	." ORDER BY comments.id"
		  ;
		  $result = $db->query($sql, __FILE__, __LINE__);
			$rcount = 0;
		
			
			$html .= '<div id="layer'.$rs['id'].'">';
			while($child = $db->fetch($result)) {
				$depth2 = $depth;
				$rcount++;
		
				$html .= '{comment_extend_depth depth=$hdepth childposts='.Comment::getNumChildposts($board, $parent_id).' rcount='.$rcount.'}';
				$html .= '{include file="comments:'.$child['id'].'" comments_no_top_additional=1}';
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
			
				.'<a href="{get_changed_url var="parent_id" value="'.$parent_id.'"}">'
				.'<img border="0" class="forum" src="/images/forum/plus.gif" />'
				.'</a>'
				.'</td>'
				.'<td align="left" class="border forum">'
				
				
			 .'<table bgcolor="#{comment_colorfade depth=$sizeof_hdepth color="'.NEWCOMMENTCOLOR.'"}" class="forum">'
			 .'<tr>'
				.'<td bgcolor="'.NEWCOMMENTCOLOR.'" valign="top">'
			 .'<a href="{get_changed_url var="parent_id" value="'.$parent_id.'"}">'
			 .'<font size="4"> Additional posts</font></a>'
			 .' <a href="/profil.php?do=view">(du hast Forumanzeigeschwelle <b>{$user->maxdepth}</b> eingestellt)</a>'
			 .'</td></tr></table>'
			 
				.'</td></tr></table>'
			;
			
			$html .= '{comment_remove_depth depth=$hdepth}';
	
	$html .= '{/if}';
	
	
	return $html;
}

?>