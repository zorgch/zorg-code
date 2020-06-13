{if ($user->id != 0 && sizeof($hdepth) <= $user->maxdepth) || ($user->id == 0 && sizeof($hdepth) < $comments_default_maxdepth) || '.Comment::getNumChildposts($board, $parent_id).' == 0}';

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

{else}

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

{/if}