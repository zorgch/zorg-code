<?php
/**
 * Forum
 * 
 * Das Forum Modul enthält 3 Klassen für alle Features:
 * - Forum
 * - Thread
 * - Comment
 * Mit diesen drei Bestandteilen wird das ganze Forum,
 * dessen Threads und das Commenting dazu - oder auch
 * eigenständige Commenting-Instanzen für Templates
 * erzeugt und abgehandelt.
 *
 * Diese Klassen benutzen folgende Tabellen aus der DB:
 * - comments_boards
 * - comments_threads
 * - comments
 * - comments_subscriptions
 * - comments_threads_favorites
 * - comments_threads_ignore
 * - comments_threads_rights
 * - comments_unread
 *
 * @version		1.0
 * @package		Zorg
 * @subpackage	Forum
 */

/**
 * File Includes
 * @include	Smarty
 * @include	Usersystem
 * @include	Utilities
 * @include	Sunrise
 * @include	Messagesystem
 * @include strings.inc.php Strings die im Zorg Code benutzt werden
 */
require_once( __DIR__ .'/smarty.inc.php');
require_once( __DIR__ .'/usersystem.inc.php');
require_once( __DIR__ .'/util.inc.php');
require_once( __DIR__ .'/sunrise.inc.php');
require_once( __DIR__ .'/messagesystem.inc.php');
require_once( __DIR__ .'/strings.inc.php');

/**
 * GLOBALS
 * @const THREAD_TPL_TIMEOUT wenn ein thread x tage nicht mehr angeschaut wurde, werden seine tpl's gelöscht. (speicherplatz sparen)
 */
define('THREAD_TPL_TIMEOUT', 30);  // in tagen

/**
 * Aktivitäten-Array bauen (mit Aktitivätsmeldungen die abgesetzt werden können)
 * @deprecated
$activities_f =
	array(
		 1	=>	"hat einen Comment im <a href=\"forum.php\">Forum</a> geschrieben."
	);
*/

/**
 * Comment Class
 * 
 * In dieser Klasse befinden sich alle Funktionen zum Commenting-System
 *
 * @author		[z]milamber, IneX
 * @version		1.0
 * @package		Zorg
 * @subpackage	Forum
 */
class Comment {

	/**
	* @return unknown
	* @param unknown $thread_id
	* @param unknown $comment_id
	* @param unknown $board
	* @desc Kompiliert das Template
	*/
	static function compile_template($thread_id, $comment_id, $board='') {
		global $db, $smarty;

		if (!$board) $board = 'f';

		if ($board == 'f') {
			if ($comment_id == 1) {
				$resource = "comments:$thread_id";
			}else{
				$resource = 'comments:'.$comment_id;
			}
		}else{
			if ($thread_id == $comment_id) {
				$resource = "comments:$board-$comment_id";
			}else{
				$resource = "comments:$comment_id";
			}
		}

		$db->query("UPDATE comments SET error = '' WHERE id='$comment_id'", __FILE__, __LINE__);

		$error = '';
		if(!$smarty->compile($resource, $error)) {
			$errortext = '';
			foreach ($error as $value) $errortext .= $value.'<br />';
			$db->query("UPDATE comments SET error = '".addslashes($errortext)."' WHERE id ='$comment_id'", __FILE__, __LINE__);
			$smarty->compile($resource, $error);
			return false;
		} else {
			$db->query("UPDATE comments SET error = '' WHERE id = '$comment_id'", __FILE__, __LINE__);
			return true;
		}
	}

	/**
	* @return String
	* @param $text String
	* @desc macht Textformatierungen fürs Forum
 	*/
	static function formatPost($text) {

		global $user;

		// in eigene funktion packen


	  // Falls Post HTML beinhaltet, schauen ob was böses[tm] drin ist.
	  $illegalhtml = false;


	  // Illegale Attribute suchen
	  /*
	  $illegalattrib = array('style', 'bgcolor');
	  while (!$illegalhtml && list($key, $value) = each ($illegalattrib)) {
		  if(strstr($text, $value.'=')) {
		  	$text = htmlentities($text).' <font color="red"><b>[Illegales Attribut: '.$value.']</b></font>';
		  	$illegalhtml = true;
		  }
	  }*/

	  // Illegale Tags suchen
	  $illegaltags = array('link', 'select', 'script', 'style');
	  while (!$illegalhtml && list($key, $value) = each ($illegaltags)) {
		  if(strstr($text, '<'.$value)) {
		  	$text = htmlentities($text).' <font color="red"><b>[Illegaler Tag: '.$value.']</b></font>';
		  	$illegalhtml = true;
		  }
	  }
	  // in eigene Funktion packen

	  // Newlines zu BRs machen
	  if(!strstr($text, '<br />')) {
	  	$text = str_replace("\n", "<br />", $text); // Newline
	  }

	  return $text;
	}

	/**
	* @return int
	* @param $comment_id int
	* @desc Anzahl Kinder-Objekte zu beliebigem Post ermitteln
	*/
	static function getNumChildposts($board, $comment_id) {
	  global $db, $user;
	  static $_cache = array();

	  if(is_numeric($comment_id)) {
		  if (!isset($_cache["$board $comment_id"])) {
		     $sql = "SELECT * FROM comments where parent_id = '".$comment_id."' AND board='".$board."'";
		     $_cache["$board $comment_id"] = $db->num($db->query($sql, __FILE__, __LINE__));
		  }
		  return $_cache["$board $comment_id"];
	  } else {
	  	echo '$comment_id is not numeric '.__FILE__.' Zeile: '.__LINE__;
	  	exit;
	  }
	}



	static function getParentid($comment_id, $height) {
		$i = 0;

		do {
			$rs = Comment::getRecordset($comment_id);
		}	while($i <= $height && $rs['parent_id'] > 0);

		return $rs['parent_id'];
	}


	/**
	* @return Array
	* @param $id int
	* @desc Fetches a Post and returns its Recordset
	*/
	static function getRecordset($id) {
	  global $db;

	  if(!is_numeric($id)) {
	  	echo '$id is not numeric '.__FILE__." Line: ".__LINE__;
	  	exit;
	  }

	  $sql =
	  	"
	  	SELECT *, UNIX_TIMESTAMP(date) as date
	  	FROM comments where id = '".$id."'
	  	"
	  ;
	  return $db->fetch($db->query($sql, __FILE__, __LINE__));
	}

	/**
	* @return String
	* @param $rs
	* @param $himages
	* @param $viewkeyword
	* @desc HTML der "Additional Posts" Teile
 	*/
	static function getHTMLadditional($rs, $himages) {

		global $db, $user, $layouttype;

	  // Farbe setzen
	  $color = NEWCOMMENTCOLOR; // TABLEBACKGROUNDCOLOR;
	  $hdepth = count($himages);

	  // table
	  $html =
	    '<table class="forum" style="table-layout:fixed;" width="100%">'
	    .'<tr>'
	  ;

	  for ($i=1; $i < ($hdepth-1); $i++) {
			$html .= '<td class="'.$himages[$i].'"></td>';
	  }

	  // restlicher output
	  $html .=
  		'<td class="space">'
  		.'<a href="'.$_SERVER['PHP_SELF'].'?parent_id='.$rs['id'].'">'
  		.'<img border="0" class="forum" src="/images/forum/'.$layouttype.'/plus.gif" />'
  		.'</a>'
	  	.'</td>'
	  	.'<td align="left" class="border forum">'
	  	.'<table bgcolor="'.Forum::colorfade($hdepth, $color).'" class="forum">'
	    .'<tr>'
	  	.'<td bgcolor="'.$color.'" valign="top">'
	    .'<a href="'.$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'].'&parent_id='.$rs['parent_id'].'">'
	    .'<font size="4"> Additional posts</font></a>'
	    .' <a href="/profil.php?do=view">(du hast Forumanzeigeschwelle <b>'.$user->maxdepth.'</b> eingestellt)</a>'
	    .'</td></tr></table>'
	  	.'</td></tr></table>'
	  ;
	  return $html;
	}

	static function getLinkComment ($comment_id) {
		global $db;

		$e = $db->query("SELECT * FROM comments WHERE id='$comment_id'", __FILE__, __LINE__);
		$d = $db->fetch($e);
		if ($d) return Comment::getLink($d['board'], $d['parent_id'], $d['id'], $d['thread_id']);
		else return '';
	}


	static function getBoardlink($board) {
		global $db;
		$sql = "SELECT * FROM comments_boards WHERE board = '".$board."'";
		$result = $db->query($sql, __FILE__, __LINE__);
		$rs = $db->fetch($result);
		return $rs;
	}

	static function getLink($board, $parent_id, $id, $thread_id) {
		global $db, $boardlinks;

		if(!isset($boardlinks)) {
			$boardlinks = array();
		}

		if(!key_exists($board, $boardlinks)) {
			$rs = Comment::getBoardlink($board);
			$boardlinks[$board] = $rs;
		}

		return $boardlinks[$board]['link'].$thread_id.'&parent_id='.$parent_id.'#'.$id;
	}

	static function getLinkThread($board, $thread_id) {
		global $db, $boardlinks;

		if(!isset($boardlinks)) {
			$boardlinks = array();
		}

		if(!key_exists($board, $boardlinks)) {
			$rs = Comment::getBoardlink($board);
			$boardlinks[$board] = $rs;
		}

		if ($board == 'f') { // Forum
			$rs = Comment::getRecordset($thread_id);
			return '<a href="'.$boardlinks[$board]['link'].$thread_id.'">'.Comment::getTitle($rs['text']).'</a>';
		} else if($board == 'i') { // Pictures
			$sql = "select name from gallery_pics where id = '$thread_id'";
			$rs = $db->fetch($db->query($sql, __FILE__, __LINE__));
			if($rs['name'] != '') {
				return '<a href="'.$boardlinks[$board]['link'].$thread_id.'">[Pic] '.substr($rs['name'], 0, 20).'</a>';
			} else {
				return '<a href="'.$boardlinks[$board]['link'].$thread_id.'">'.$boardlinks[$board]['field'].' '.$thread_id.'</a>';
			}
		} else if ($board == 'e') { // Events
			$sql = "select name from events where id = '$thread_id'";
			$rs = $db->fetch($db->query($sql, __FILE__, __LINE__));
			return '<a href="'.$boardlinks[$board]['link'].$thread_id.'">[Event] '.($rs['name'] != '' ? substr($rs['name'], 0, 20) : $thread_id).'</a>';
		} else if ($board == 'g') { // GO Game
			return '<a href="'.$boardlinks[$board]['link'].$thread_id.'">[GO] '.$boardlinks[$board]['field'].' '.$thread_id.'</a>';
		} else {
			return '<a href="'.$boardlinks[$board]['link'].$thread_id.'">'.$boardlinks[$board]['field'].' '.$thread_id.'</a>';
		}

	}

	static function getChildPostsFormFields($id, $parent_id, $comment_id=0, $depth=0) {
		global $db;

		if($depth < 7) {

			if($comment_id == 0) $comment_id = $parent_id;

			$sql = "select * from comments where parent_id =".$comment_id;
			$result = $db->query($sql, __FILE__, __LINE__);

			while ($rs = mysql_fetch_array($result)) {
				if($rs['id'] != $id) {
					$html .=
						'<option value="'.$rs['id'].'"'.($parent_id == $rs['id'] ? ' selected="selected"' : '').'>'
						.str_repeat('--', $depth)
						.'#'.$rs['id'].' '
						.Comment::getTitle($rs['text'])
						.'</option>'
					;
				}

				$html .= Comment::getChildPostsFormFields($id, $parent_id, $rs['id'], ($depth+1));
			}

			return $html;
		}
	}

	/**
	* @return int
	* @param $id int
	* @param $hiers int
	* @desc Holt den Thread-Id eines Posts oder Threads.
	*
	* WICHTIG! UNBEDINGT SO LASSEN!
	*
	*/
	static function getThreadid($board, $id) {
		global $db;
	  $sql = "SELECT thread_id FROM comments WHERE board = '".$board."' AND id = ".$id;
	  $rs = $db->fetch($db->query($sql, __FILE__, __LINE__));
	  return $rs['thread_id'];
	}

	/**
	* @return String
	* @param $text String
	* @param $lengthoffset int
	* @desc Den Titel eines Kommentars holen.
	*/
	static function getTitle($text, $length=20) {

	  $text = strip_tags($text);

	  // was macht das?
	  $pattern = "(((\w|\d|[äöü√®√©√†√Æ√™])(\w|\d|\s|[äöü√®√©√†√Æ√™]|[\.,-_\"'?!^`~])[^\\n]+)(\\n|))";
	  preg_match($pattern, $text, $out);
	  if(strlen($out[1]) > $length) {
	  	$out[1] = substr($out[1], 0, $length);
	  }
	  if(strlen($out[1]) == 0) return '---';

	  return $out[1];
	}

	static function markasread($comment_id, $user_id) {
		global $db, $user;
		if($user->typ != USER_NICHTEINGELOGGT) {
		  $sql =
		  	"DELETE from comments_unread"
		  	." WHERE"
		  	." user_id = ".$user_id
		  	." AND comment_id=".$comment_id
		  ;
		  $db->query($sql, __FILE__, __LINE__);
	  }
	}

	/**
	 * Prüft, ob der Comment im therads-table eingetragen ist (= thread start)
	 * @author IneX
	 * @date 16.03.2008
	 * @desc Prüft ob der Comment ein Thread ist
	 * @param $board
	 * @param $id int
	 * @return boolean
	 */
	static function isThread($board, $id) {
		global $db;
		$sql = "SELECT thread_id FROM comments_threads WHERE board = '".$board."' AND comment_id = ".$id;
		$rs = $db->fetch($db->query($sql, __FILE__, __LINE__));

		return $rs;
	}

	// Mark as unread for all users.
	static function markasunread($comment_id) {
		global $db;

		$sql =
			"
			SELECT
				c.thread_id,
				c.board,
				ct.rights
			FROM comments c
			LEFT JOIN comments_threads ct
				ON (ct.board = c.board AND ct.thread_id = c.thread_id)
			WHERE c.id = ".$comment_id."
			LIMIT 0, 1
			"
		;
		$rs = $db->fetch($db->query($sql, __FILE__, __LINE__));

		if($rs['rights'] == '') $rs['rights'] = 0;


		if($rs['rights'] < USER_SPECIAL) {
			$sql =
				"
				REPLACE INTO comments_unread (user_id, comment_id)
					SELECT
						id,
						".$comment_id."


					FROM user

					WHERE user.usertype >= ".$rs['rights']."
					AND (UNIX_TIMESTAMP(lastlogin)+".USER_OLD_AFTER.") > UNIX_TIMESTAMP(NOW())
					AND forum_boards_unread LIKE '%".$rs['board']."%'
					"
					/*AND ISNULL(
						SELECT tignore.thread_id, tignore.user_id
						FROM comments_threads_ignore tignore
						WHERE tignore.thread_id = ".$rs['thread_id']."
						AND tignore.user_id = user.id
						)*/

			;
			$data = $db->fetch($db->query($sql, __FILE__, __LINE__));
		} else {
			$sql =
				"
				REPLACE INTO comments_unread (user_id, comment_id)
					SELECT
						user_id
						, ".$comment_id."
					FROM comments_threads_rights
					WHERE board = '".$rs['board']."'
					AND thread_id = ".$rs['thread_id']."
				"
			;
			$data = $db->fetch($db->query($sql, __FILE__, __LINE__));
		}
	}

	static function highliteKeyword($keyword,$text) {
	  global $tborderc;
	  //$keyword = htmlentities($keyword);
	  $searcher = "/$keyword/i";
	  $replace = "<b style=\"color: #".FONTC."; background: #".HIGHLITECOLOR."\">".$keyword."</b>";
	  return preg_replace("$searcher", $replace, $text);
	}


	static function post($parent_id, $board, $user_id, $text, $msg_users="") {

		//global $db, $activities_f;
		global $db;

		// (Parent-Id = 1 wenn man ein ForumThread postet
		$parent_id = ($parent_id <= 0 ? 1 : $parent_id);

		if($parent_id <= 0) {
			echo t('invalid-parent_id', 'commenting');
			exit;
		}

		// Falls Thread-Id noch nicht vorhanden, parent-id nehmen
		// (1 bei forum, anderes bei den anderen boards)
		$thread_id = Comment::getThreadid($board, $parent_id);
		if(!($thread_id > 0)) $thread_id = $parent_id;



		if($thread_id <= 0) {
			echo t('invalid-thread_id', 'commenting');
			exit;
		}


		// Rechte checken
		if (Thread::hasRights($board, $thread_id, $user_id)) {
			
			  // Text escapen
			  $text = escape_text($text);
			  
			  // Comment in die DB abspeichern
			  $sql =
			  	"INSERT INTO comments (user_id, parent_id, thread_id, text, date, board, error)"
			  	."VALUES ( $user_id, $parent_id, $thread_id, '$text', now(), '$board', '$comment_error' )";
			  $db->query($sql, __FILE__, __LINE__);
			  $comment_id = mysql_insert_id();

			  // Falls parent_id = 1, thread_id = id. Für Forum->neue Threads.
			  $sql = "
			  	UPDATE comments
			  	SET thread_id = id
			  	WHERE parent_id = 1
			  		AND board = 'f'
			  ";
	  		$db->query($sql, __FILE__, __LINE__);

	  		$rs = Comment::getRecordset($comment_id);
	  		$commentlink = Comment::getLink($rs['board'], $rs['parent_id'], $rs['id'], $rs['thread_id']);

			  // Falls neuer Thread, Record in Thread-Tabelle generieren
				$sql =
					"INSERT IGNORE INTO comments_threads (board, thread_id, comment_id)"
					." VALUES ('".$rs['board']."', ".$rs['thread_id'].", ".$rs['id'].")";
			  $db->query($sql, __FILE__, __LINE__);

			  // last post setzen
			  $sql =
			  	"
			  	UPDATE comments_threads
					SET
						last_comment_id = ".$rs['id']."
						, comment_id = IF(ISNULL(comment_id), ".$rs['id'].", comment_id)

					WHERE thread_id = ".$rs['thread_id']." AND board = '".$board."'
					"
			  ;
			  $db->query($sql, __FILE__, __LINE__);

			  // Template Kompilieren
			  Comment::compile_template($rs['thread_id'], $rs['id'], $rs['board']);

			  if ($rs['parent_id'] != 1 || $rs['board'] != 'f') {
			  		// Templates neu Kompilieren, zuerst Parent
			  		/*
				  if($parent_id != $rs['thread_id']) {
				  		Comment::compile_template($rs['thread_id'], $rs['parent_id']);
				  } else {
				  		Comment::compile_template($rs['thread_id'], $rs['parent_id'], $rs['board']);
				  }*/

				  Comment::compile_template($rs['thread_id'], $rs['parent_id'], $rs['board']);
			  }


			  // Mark comment as unread for all users.
				Comment::markasunread($rs['id']);


				// Mark comment as read for this user.
				Comment::markasread($rs['id'], $user_id);


				// Activity Eintrag auslösen (ausser bei der Bärbel, die trollt zuviel)
				if ($user_id != 59)
				{
					Activities::addActivity($user_id, 0, t('activity-newcomment', 'commenting', [ SITE_URL, Comment::getLink($board, $rs['parent_id'], $rs['id'], $rs['thread_id']), Forum::getBoardTitle($rs['board']), Comment::getTitle($text, 100) ]), 'c');
				}


				// Message an alle gewünschten senden
				if(count($msg_users) > 0) {
					for ($i=0; $i < count($msg_users); $i++) {
						Messagesystem::sendMessage(
							 $user_id
							,$msg_users[$i]
							,t('message-newcomment-subject', 'commenting', usersystem::id2user($user_id))
							,t('message-newcomment', 'commenting', [ usersystem::id2user($user_id), addslashes(stripslashes($text)), Comment::getLink($board, $parent_id, $rs['id'], $thread_id) ])
							,(is_array($msg_users) ? implode(',', $msg_users) : $msg_users)
						);
					}
				}

				// Message an alle Subscriber senden
				$sql =
					"SELECT * FROM comments_subscriptions"
					." WHERE comment_id = ".$parent_id
					." AND board='".$board."'"
				;
			  $result = $db->query($sql, __FILE__, __LINE__);
			  if($db->num($result) > 0) {
				  while($rs2 = $db->fetch($result)) {
				  	Messagesystem::sendMessage(
							 59
							,$rs2['user_id']
							,t('message-newcomment-subscribed-subject', 'commenting', [ usersystem::id2user($user_id), $parent_id ])
							,t('message-newcomment-subscribed', 'commenting', [ usersystem::id2user($user_id), Comment::getLink($rs['board'], $rs['parent_id'], $rs['id'], $rs['thread_id']), addslashes(stripslashes(Comment::getTitle($rs['text']))) ])
						);
				   }
			}

			return $commentlink;

		} else {
			user_error( t('invalid-permissions', 'commenting', [ $board, $thread_id ]), E_USER_WARNING);
			exit;
		}
	}

}


/**
 * Forum Class
 * 
 * In dieser Klasse befinden sich die Hauptfunktionen zum Forum-System
 * inkl. Boards und Board-Management
 *
 * @author		[z]milamber, IneX
 * @version		1.0
 * @package		Zorg
 * @subpackage	Forum
 */
class Forum {

	static function deleteOldTemplates () {
		global $db, $smarty;

		$e = $db->query(
			"SELECT c.id, c.board, c.thread_id
			FROM comments c, comments_threads ct
			WHERE c.thread_id = ct.thread_id AND ct.last_seen!='0000-00-00'
				AND unix_timestamp(now())-unix_timestamp(ct.last_seen) > (60*60*24*".THREAD_TPL_TIMEOUT.")",
			__FILE__, __LINE__
		);
		$anz = 0;
		while ($d = $db->fetch($e)) {
			$anz++;
			if ($d['board']=='f' && $d['id']==1) {
				$smarty->clear_compiled_tpl("comments:$d[thread_id]");
			}elseif ($d['board'] != 'f' && $d['thread_id']==$d['id']) {
				$smarty->clear_compiled_tpl("comments:$d[board]-$d[id]");
			}else{
				$smarty->clear_compiled_tpl("comments:$d[id]");
			}
		}

		return $anz;
	}

	static function colorfade($depth, $color) {
		if (substr($color,0,1) == '#') $color = substr($color, 1);

		// Einstellungen
		$coloroffset = 17;
		$mincolorvalue = 10;
		$maxcolorvalue = 230;

		// Farben aus rgb String herauslesen
		$r = hexdec(substr($color, 0, 2)); // red
	  $g = hexdec(substr($color, 2, 2)); // green
	  $b = hexdec(substr($color, 4, 2)); // blue

	  // $depth umwandeln in -4 bis +4
	  $tempdepth = $depth % 16; // 0-15
	  $offsetswitcher = array();
	  $offsetswitcher[0] = 0;
	  $offsetswitcher[1] = -1;
	  $offsetswitcher[2] = -2;
	  $offsetswitcher[3] = -3;
	  $offsetswitcher[4] = -4;
		$offsetswitcher[5] = -3;
		$offsetswitcher[6] = -2;
		$offsetswitcher[7] = -1;
		$offsetswitcher[8] = 0;
		$offsetswitcher[9] = 1;
		$offsetswitcher[10] = 2;
		$offsetswitcher[11] = 3;
		$offsetswitcher[12] = 4;
		$offsetswitcher[13] = 3;
		$offsetswitcher[14] = 2;
		$offsetswitcher[15] = 1;
		$depthoffset = $offsetswitcher[$tempdepth];

		// Farben heller/dünkler machen
		$r = $r + $depthoffset * $coloroffset;
		$g = $g + $depthoffset * $coloroffset;
		$b = $b + $depthoffset * $coloroffset;

		// Farben werden max. $maxcolorvalue
		$r = min($r, $maxcolorvalue);
		$g = min($g, $maxcolorvalue);
		$b = min($b, $maxcolorvalue);

		// Farben werden min. $mincolorvalue
		$r = max($r, $mincolorvalue);
		$g = max($g, $mincolorvalue);
		$b = max($b, $mincolorvalue);

		return sprintf("%02X%02X%02X", $r, $g, $b);
	}

	static function getForumBoards($check) {
		global $db;
		$sql = "SELECT * FROM comments_boards";
		$result = $db->query($sql, __FILE__, __LINE__);
		$html .= '<table><tr>';
		while($rs = $db->fetch($result)) {
			$html .=
				'<td>'
				.'<input name="boards[]" type="checkbox" value="'.$rs['board'].'" id="'.$rs['board'].'" '.
				(in_array($rs['board'], $check) ? 'checked' : '').'>'
				.'</td><td valign="middle">'
				.'<label for="'.$rs['board'].'">'.$rs['title'].'</label>&nbsp;'
				.'</td>'
			;
		}

		$html .= '</tr></table>';

		return $html;
	}

	static function getFormBoardsShown($show) {
		global $db;

		$html .=
			'<table cellpadding="0" cellspacing="0" style="font-size: x-small">'
			.'<form action="/actions/forum_setboards.php" method="POST" name="showboards">'
			.'<input name="do" type="hidden" value="set_show_boards">'
			.'<tr><td>'
		;

		$html .= Forum::getForumBoards($show);

		$html .=
			'</td><td>'
			.'<input class="button" type="submit" value="refresh">'
			.'</td></tr>'
			.'</form>'
			.'</table>'
		;

		return $html;
	}

	static function getBoards($user_id) {
		global $db;
		$sql = "SELECT forum_boards FROM user WHERE id = ".$user_id;
		$rs = $db->fetch($db->query($sql, __FILE__, __LINE__));

		return explode(',', $rs['forum_boards']);
	}

	/**
	 * Board Titel ausgeben
	 * @author IneX
	 * @date 16.03.2008
	 * @desc Query für den Board Titel
	 * @param $board int
	 * @return string
	 */
	static function getBoardTitle($board) {
		global $db;

		$sql = "SELECT title FROM comments_boards WHERE board = '".$board."'";
		$rs = $db->fetch($db->query($sql, __FILE__, __LINE__));

		return $rs['title'];
	}


	/**
	* @return String
	* @param $comment_id
	* @desc Form for editing posts
	*
	* @TODO merge Forum::getFormEdit() into tpl/commentform.tpl
	*/
	static function getFormEdit($comment_id) {
	  global $db, $user;

	  if(!is_numeric($comment_id)) echo '$comment_id is not numeric.'.__LINE__;

	  $rs = Comment::getRecordset($comment_id);

	  $html .= '
	  	<br />
	    <a name="edit"></a>
	    <form name="commentform" action="/actions/comment_edit.php" method="post">
	    <input type="hidden" name="action" value="update">
	  	<input type="hidden" name="url" value="'.$_GET['url'].'">
	  	<input name="thread_id" type="hidden" value="'.$rs['thread_id'].'">
	    <input type="hidden" name="id" value="'.$comment_id.'">
	  	<input class="text" name="board" type="hidden" value="'.$rs['board'].'">
	    <table width="'.FORUMWIDTH.'" class="border" align="center">
	    <tr><td align="left" colspan="6">
	    <textarea name="text" cols="80" rows="20" class="text">'
	  	.htmlentities($rs['text'])
	  	.'</textarea>'
	    .'</td>'
	    .'<td align="left" valign="top">Benachrichtigen:<br />'
	    .usersystem::getFormFieldUserlist('msg_users[]', 20).'</td>'
	  	.'</tr>
	  	<tr><td align="left" valign="top">
	    <input type="submit" name="submit" value="Update" class="button">

	  	<td align="left">
	    Parent
	    </td><td>
	  	<input class="text" name="parent_id" type="text" value="'.$rs['parent_id'].'">
	  	</td>
	  	</form>
	    </td><td align="right">
		';
		if(Comment::getNumchildposts($rs['board'], $comment_id) < 1) {
			$html .= '
				<table cellpadding="0" cellspacing="0">
				<form action="/actions/comment_delete.php" method="post">
				<input type="hidden" name="url" value="'.$_GET['url'].'">
		    <input type="hidden" name="id" value="'.$comment_id.'">
				<tr><td>
				<input type="submit" value="Delete" class="button">
				</td></tr>
				</form>
				</table>
			';
		}
		$html .= '
	    </td></tr></table>
	    <br />
	  ';
	  return $html;
	}
	
	/**
	 * Start Ausgabe Commentform Form HTML-Tag
	 * Neu als Smarty-Template "/templates/commentform.tpl" verfügbar!
	 * Usage im Smarty Template: {include file='file:commentform.tpl'}
	 * 
	 * @DEPRECATED
	 * @author unknown
	 * @see printCommentingSystem()
	 */
	static function getFormNewPart1of2() {
		return '<form action="/actions/comment_new.php" method="post" name="commentform">';
	}
	
	/**
	 * Ausgabe Commentforms HTML
	 * 
	 * @DEPRECATED
	 * @author unknown
	 * @see printCommentingSystem()
	 */
	/*
	static function getFormNewPart2of2($board, $thread_id, $parent_id) {
	  return
	  	"\n"
	  	.'<br />'
	    .$start_form
	  	.'<a name="reply"></a>'
	    .'<input type="hidden" name="action" value="new">'
	  	.'<input type="hidden" name="url" value="'.base64_encode($_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING']).'">'
	  	.'<input type="hidden" name="board" value="'.$board.'">'
	  	.'<input type="hidden" name="thread_id" value="'.$thread_id.'">'
	    .'<table width="400" class="border" align="center">'
	    .'<tr>'.'<td align="left" colspan="3" valign="middle">'
	    .'Neuen Kommentar hinzufügen:'
	  	.'</td>'
	  	.'<td align="right">'
	  	//.($board != 'f' ? '<input name="parent_id" style="visibility: hidden;" type="radio" value="'.$parent_id.'" checked="checked" />' : '')
	  	.'<input name="parent_id" style="visibility: hidden;" type="radio" value="'.$parent_id.'" checked="checked" />'
	  	.'</td></tr>'
	  	.'<tr>'
	  	.'<td align="left" colspan="1">'
	    .'<textarea class="text" cols="80" name="text" rows="20" tabindex="1"></textarea>'
	    .'</td>'
	    .'<td valign="top" width="100"><small><nobr>'
	    .'<br />ä = <a href="javascript:addsymbol(\'&amp;auml;\');">&amp;auml;</a>'
	    .'<br />ö = <a href="javascript:addsymbol(\'&amp;ouml;\');">&amp;ouml;</a>'
	    .'<br />ü = <a href="javascript:addsymbol(\'&amp;uuml;\');">&amp;uuml;</a>'
	    .'<br />& = <a href="javascript:addsymbol(\'&amp;amp;\');">&amp;amp;</a>'
	    .'<br />&lt; = <a href="javascript:addsymbol(\'&amp;lt;\');">&amp;lt;</a>'
	    .'<br />&gt; = <a href="javascript:addsymbol(\'&amp;gt;\');">&amp;gt;</a>'
	    .'</nobr></small></td>'
	    .'<td align="left" valign="top">Benachrichtigen:<br />'
	    .usersystem::getFormFieldUserlist('msg_users[]', 20).'</td>'
	    .'</tr><tr><td align="left" colspan="2">'
	    .'<input class="button" name="submit" tabindex="2" type="submit" value="Erstellen">'
	    .'</td></tr></table>'
	    .'</form>'
	    .'<br />'
	  ;
	}
	*/

	/**
	* @return String
	* @desc gibt das HTML des Readallforms zurück
 	*/
	static function getFormReadall() {
		return
			'<table>'
			.'<form action="/actions/comments_readall.php" method="post">'
			.'<input type="hidden" name="action" value="readall">'
			.'<tr><td align="left">'
			.'<input type="submit" value="read all" class="button">'
			.'</td></tr>'
			.'</form>'
			.'</table>'
		;
	}

	/**
	* @return String
	* @desc gibt das HTML des Searchformszurück
 	*/
	static function getFormSearch() {
		return
			'<table>'
			.'<form action="'.$_SERVER['PHP_SELF'].'" method="get">'
			.'<input name="layout" type="hidden" value="search">'
			.'<tr>'
		//.'<td><b>Suche:</b></td>'
		.'<td align="left">'
			.'<input type="text" name="keyword" class="text">'
			.'</td><td align="left">'
			.'<input type="submit" value="search" class="button">'
			.'</td></tr>'
			.'</form>'
			.'</table>'
		;
	}

	static function getNumunreadposts($user_id) {

		global $db, $user;

		if($user->typ != USER_NICHTEINGELOGGT) {
		  $sql = "SELECT count(*) as numunread from comments_unread where user_id='".$user_id."'";
		  $rs = $db->fetch($db->query($sql, __FILE__, __LINE__));
		  return $rs['numunread'];
		}
	}

	static function getUnreadLink() {
		global $db, $user;

		$sql =
			"
			SELECT
			comments.*
			, IF(ISNULL(comments_unread.comment_id), 0, 1) AS isunread
			, UNIX_TIMESTAMP(comments.date) as date
			, user.clan_tag
			, user.username
			FROM comments
			LEFT JOIN user on comments.user_id = user.id
			LEFT JOIN comments_unread ON (comments.id=comments_unread.comment_id AND comments_unread.user_id = '".$user->id."')
			WHERE comments_unread.comment_id IS NOT NULL
			ORDER by date ASC LIMIT 0,1
			"
		;

  	$rs2 = $db->fetch($db->query($sql, __FILE__, __LINE__));

  	return Comment::getLink($rs2['board'], $rs2['parent_id'], $rs2['id'], $rs2['thread_id']);
	}

	/**
	* @return Array
	* @param $thread_id int
	* @desc Holt den letzten Kommentar eines Threads
	*/
	static function getLastComment() {
	  global $db;
		$sql =
	  	"SELECT user.clan_tag, user.username, comments.*, UNIX_TIMESTAMP(date) as date"
	  	." FROM comments"
	  	." left join user on comments.user_id = user.id"
	  	." order by date desc Limit 0,1"
	  ;
	  $result = $db->query($sql, __FILE__, __LINE__);
	  $rs = $db->fetch($result);
	  return $rs;
	}

	static function getNavigation($page=1, $pagesize, $numpages) {
		$html .=
			'<table bgcolor="'.TABLEBACKGROUNDCOLOR.'" cellspacing="1" cellpadding="1" class="border"  style="font-size: x-small">'
			.'<tr><td>Page '.$page.' von '.$numpages.'</td>'
		;

		if($page > 10) {
			$html .= '<td><a href="'.getChangedURL('page=1').'">¬´ First</a></td>';
		}

		if($page > 1) {
			$html .= '<td><a href="'.getChangedURL('page='.($page-1)).'">&lt;</a></td>';
		}

		for($i = max(($page - 10), 1); $i <= min(($page + 10), $numpages); $i++) {
			if($page == $i) {
				$html .= '<td>'.$i.'</td>';
			} else {
				$html .= '<td><a href="'.getChangedURL('page='.$i).'">'.$i.'</a></td>';
			}

		}

		if($page < $numpages) {
			$html .= '<td><a href="'.getChangedURL('page='.($page+1)).'">&gt;</a></td>';
		}

		if($page < ($numpages-10)) {
			$html .=	'<td><a href="'.getChangedURL('page='.$numpages).'">Last ¬ª</a></td>';
		}

		$html .= '</tr></table>';

		return $html;
	}

	static function getQueryString($qstr='') {
		$qstr .= (!strstr($qstr, 'page') && $_GET[page] != '' ? '&page='.$_GET[page] : '');
		$qstr .= (!strstr($qstr, 'order') && $_GET[order] != '' ? '&order='.$_GET[order] : '');
		$qstr .= (!strstr($qstr, 'direction') && $_GET[direction] != '' ? '&direction='.$_GET[direction] : '');
		return $qstr;
	}

	static function printSearchedComments($keyword) {
	  global $db, $smarty;
	  // Volltext suche geht nicht mit InnoDB
	  //$sql =
	  //	"SELECT"
	  //	." comments.*"
	  //	.", UNIX_TIMESTAMP(date) as date"
	  //	." FROM comments"
	  //	." WHERE MATCH(text) AGAINST ('".$keyword."')"
	  //	." ORDER by date DESC"
	  //;
	  $sql =
	  	"
	  	SELECT
	  		comments.*
	  		, UNIX_TIMESTAMP(date) as date
	  	FROM comments
	  	WHERE text LIKE '%".$keyword."%'
	  	ORDER by date DESC
	  	"
	  ;
	  $result = $db->query($sql, __FILE__, __LINE__);
		$num = $db->num($result);
		$smarty->assign("comments_no_childposts", 1);
		while($rs = $db->fetch($result)) {
	    $smarty->display("comments:".$rs['id']);
	  }
	}

	/**
	* @return String
	* @desc Gibt eine Tabelle mit Links zu den letzten Comments
	*/
	static function getLatestComments($num=10, $title = '', $board = '') {

		global $db, $user;

		if (!$num) $num = 10;

		$wboard = $board ? "comments.board='".$board."'" : "1";

	    //beschränkt auf 365 tage, da sonst unglaublich lahm
		$sql ="SELECT
			 comments.*,
			 IF(ISNULL(comments_unread.comment_id), 0, 1) AS isunread,
			 UNIX_TIMESTAMP(date) as date,
			 user.clan_tag,
			 user.username
		       FROM comments
		       LEFT JOIN user
			 ON comments.user_id = user.id
		       LEFT JOIN comments_threads ct
			 ON ct.thread_id = comments.thread_id
			 AND ct.board = comments.board
		       LEFT JOIN comments_threads_rights ctr
			 ON ctr.thread_id = comments.thread_id
			 AND ctr.board = comments.board
			 AND ctr.user_id = '$user->id'
		       LEFT JOIN comments_unread
			 ON (comments.id=comments_unread.comment_id
			 AND comments_unread.user_id = '$user->id')
		       WHERE ".$wboard."
			 AND (user.usertype >= ct.rights
			   OR ct.rights=".USER_SPECIAL."
			 AND ctr.user_id IS NOT NULL)
			 AND DATEDIFF(now(), date) < 365
			ORDER BY date desc
			LIMIT 0,".$num
		;

		$result = $db->query($sql, __FILE__, __LINE__);

		$html .=
			'<table class="border" width="100%"><tr><td align="center" colspan="4"><b>'
			.($title == '' ? 'neuste Kommentare' : $title)
			.'</b></td></tr>'
		;
		while($rs = $db->fetch($result)) {
	    $i++;
			if($user->typ != USER_NICHTEINGELOGGT && $rs['isunread'] == true) {
				$color = NEWCOMMENTCOLOR;
			} else {
				$color = ($i % 2 == 0) ? BACKGROUNDCOLOR : TABLEBACKGROUNDCOLOR;
			}

	    $html .=
	      '<tr class="small"><td align="left" bgcolor="'.$color.'">'
		  	.'<a href="'.Comment::getLink($rs['board'], $rs['parent_id'], $rs['id'], $rs['thread_id']).'" name="'.$rs['id'].'">'
		  	.Comment::getTitle($rs['text'])
		  	.'</a>'
	      .'</td><td align="left" bgcolor="'.$color.'" class="small">'
	      .usersystem::userpagelink($rs['user_id'], $rs['clan_tag'], $rs['username'])
	      .'</td><td align="left" bgcolor="'.$color.'" class="small">'
	      .datename($rs[date])
	      .'</td><td align="left" bgcolor="'.$color.'" class="small">'
	      .Comment::getLinkThread($rs['board'], $rs['thread_id'])
	      .'</td></tr>'
	    ;

	  }
	  $html .= '</table>';

	  return $html;
	}


	/**
	* @return String
	* @desc Gibt eine Tabelle mit Links zu den letzten  eines Users
	*/
	static function getLatestCommentsbyUser($user_id) {

		global $db, $user;

		if($user->typ == USER_NICHTEINGELOGGT) {
			$sql =
			"SELECT comments.*, UNIX_TIMESTAMP(date) as date"
			." FROM comments"
			." LEFT JOIN comments_threads ct ON ct.thread_id=comments.thread_id AND ct.board=comments.board"
			." LEFT JOIN comments_threads_rights ctr ON ctr.thread_id=comments.thread_id AND ctr.board=comments.board AND ctr.user_id='$user_id'"
			." LEFT JOIN user u ON u.id='$user_id'"
			." WHERE comments.user_id = ".$user_id
				." AND (u.usertype >= ct.rights OR ct.rights=".USER_SPECIAL." AND ctr.user_id IS NOT NULL)"
			." ORDER BY date desc"
			." LIMIT 0,7"
			;
		} else {
			$sql =
			"SELECT comments.*, comments_unread.user_id as isunread, UNIX_TIMESTAMP(date) as date"
			." FROM comments"
			." LEFT JOIN comments_unread ON (comments.id=comments_unread.comment_id AND comments_unread.user_id = '$user->id')"
			." LEFT JOIN comments_threads ct ON ct.thread_id=comments.thread_id AND ct.board=comments.board"
			." LEFT JOIN comments_threads_rights ctr ON ctr.thread_id=comments.thread_id AND ctr.board=comments.board AND ctr.user_id='$user->id'"
			." LEFT JOIN user u ON u.id='$user->id'"
			." WHERE comments.user_id = ".$user_id
				." AND (u.usertype >= ct.rights OR ct.rights=".USER_SPECIAL." AND ctr.user_id IS NOT NULL)"
			." ORDER BY date desc"
			." LIMIT 0,7"
			;
		}
		$result = $db->query($sql, __FILE__, __LINE__);

		$html = '<table class="border" width="100%"><tr><td align="center" colspan="4"><b>letzte Posts:</b></td></tr>';
		while($rs = $db->fetch($result)) {
	    $i++;
			if($user->typ != USER_NICHTEINGELOGGT && $rs['isunread'] != '') {
				$color = NEWCOMMENTCOLOR;
			} else {
				$color = ($i % 2 == 0) ? BACKGROUNDCOLOR : TABLEBACKGROUNDCOLOR;
			}

	    $html .=
	      '<tr class="small"><td align="left" bgcolor="'.$color.'" width="40%">'
	      .'<a href="'.Comment::getLink($rs['board'], $rs['parent_id'], $rs['id'], $rs['thread_id']).'" name="'.$rs['id'].'">'
		  	.Comment::getTitle($rs['text'])
		  	.'</a>'
	      .'</td><td align="center" bgcolor="'.$color.'" class="small" width="20%">'
	      .datename($rs[date])
	      .'</td><td align="center" bgcolor="'.$color.'" class="small" width="20%">'
	      .Comment::getLinkThread($rs['board'], $rs['thread_id'])
	      .'</td></tr>'
	    ;

	  }
	  $html .= '</table>';

	  return $html;
	}


	static function getLatestThreads($num=8) {
		global $db, $user;
		$sql =
			"SELECT"
			." comments.*"
			.", comments_unread.user_id as isunread"
			.", UNIX_TIMESTAMP(date) as date"
			.", user.clan_tag"
			.", user.username"
			." FROM comments"
			." LEFT JOIN user on comments.user_id = user.id"
			." LEFT JOIN comments_unread ON (comments.id=comments_unread.comment_id AND comments_unread.user_id = comments.user_id)"
			." WHERE parent_id = 1"
			." ORDER BY date desc"
			." LIMIT 0,".$num
		;
		$result = $db->query($sql, __FILE__, __LINE__);
		$html = '<table class="border" width="100%"><tr><td align="center" colspan="3"><b>neuste Threads</b></td></tr>';
		while($rs = $db->fetch($result)) {
	    $i++;
			if($user->typ != USER_NICHTEINGELOGGT && $rs['isunread'] != '') {
				$color = NEWCOMMENTCOLOR;
			} else {
				$color = ($i % 2 == 0) ? BACKGROUNDCOLOR : TABLEBACKGROUNDCOLOR;
			}

	    $html .=
	      '<tr><td align="left" bgcolor="'.$color.'" class="small" width="40%">'
		  	.'<a href="'.Comment::getLink($rs['board'], $rs['parent_id'], $rs['id'], $rs['thread_id']).'" name="'.$rs['id'].'">'
		  	.Comment::getTitle($rs['text'])
		  	.'</a>'
	      .'</td><td align="left" bgcolor="'.$color.'" class="small" width="30%">'
	      .usersystem::userpagelink($rs['user_id'], $rs['clan_tag'], $rs['username'])
	      .'</td><td align="center" bgcolor="'.$color.'" class="small" width="30%">'
	      .datename($rs[date])
	      .'</td></tr>'
	    ;

	  }
	  $html .= '</table>';

	  return $html;
	}

	/**
	* @return String
	* @desc Gibt eine Tabelle mit den letzten ungelesenen Kommentaren zurück
	*/
	static function getLatestUnreadComments($title="", $board="") {
		global $db, $user;

		if (!$title) $title = "ungelesene Kommentare";
		if ($board) $whereboard = "AND comments.board='$board'";

		if($user->typ != USER_NICHTEINGELOGGT) {
			$sql =
				"
				SELECT
				 comments.*
				, IF(ISNULL(comments_unread.comment_id), 0, 1) AS isunread
				, UNIX_TIMESTAMP(comments.date) as date
				, user.clan_tag
				, user.username
				FROM comments
				LEFT JOIN user on comments.user_id = user.id
				LEFT JOIN comments_unread ON (comments.id=comments_unread.comment_id AND comments_unread.user_id = '".$user->id."')
				WHERE comments_unread.comment_id IS NOT NULL ".$whereboard."
				ORDER by date ASC LIMIT 0,5
				"
			;
			$result = $db->query($sql, __FILE__, __LINE__);

			if($db->num($result) > 0) {
				$html = '<table class="border small" width="100%"><tr><td align="center" colspan="3"><b>'.$title.'</b></td></tr>';
				while($rs = $db->fetch($result)) {

			    $i++;

					if($user->typ != USER_NICHTEINGELOGGT && $rs['isunread'] != '') {
						$color = NEWCOMMENTCOLOR;
					} else {
						$color = ($i % 2 == 0) ? BACKGROUNDCOLOR : TABLEBACKGROUNDCOLOR;
					}

			    $html .=
			      '<tr><td align="left" bgcolor="'.$color.'" width="40%">'
						.'<a href="'.Comment::getLink($rs['board'], $rs['parent_id'], $rs['id'], $rs['thread_id']).'" name="'.$rs['id'].'">'
				  	.Comment::getTitle($rs['text'])
				  	.'</a>'
			      .'</td><td align="left" bgcolor="'.$color.'" width="30%">'
			      .usersystem::userpagelink($rs['user_id'], $rs['clan_tag'], $rs['username'])
			      .'</td><td align="center" bgcolor="'.$color.'" width="30%">'
			      .datename($rs[date])
			      .'</td></tr>'
			    ;

			  }
			  $html .= '</table>';
			}
		}

	  return $html;
	}

	/**
	* @return String
	* @desc Gibt eine Tabelle mit Threads zurück, welche genau vor 3 Jahren erstellt wurden
	* @autor Grischa Ebinger
	* @date 2004-02-08
	*/

	static function get3YearOldThreads() {
		global $db, $user;
		$sql =
			"SELECT"
			." comments.*"
			.", comments_unread.user_id as isunread"
			.", UNIX_TIMESTAMP(date) as date"
			.", user.clan_tag"
			.", user.username"
			." FROM comments"
			." LEFT JOIN user on comments.user_id = user.id"
			." LEFT JOIN comments_unread ON (comments.id=comments_unread.comment_id AND comments_unread.user_id = comments.user_id)"
			." WHERE DAY(NOW())=DAY(date) and MONTH(NOW()) = MONTH(date) AND YEAR(NOW())-3 = YEAR(date) AND parent_id = 1"
			." ORDER BY date desc"

		;
		$result = $db->query($sql, __FILE__, __LINE__);
		$html = '<table class="border" width="100%"><tr><td align="center" colspan="3"><b>Jaja, früher...</b></td></tr>';
		while($rs = $db->fetch($result)) {
	    $i++;
			if($user->typ != USER_NICHTEINGELOGGT && $rs['isunread'] != '') {
				$color = NEWCOMMENTCOLOR;
			} else {
				$color = ($i % 2 == 0) ? BACKGROUNDCOLOR : TABLEBACKGROUNDCOLOR;
			}

	    $html .=
	      '<tr><td align="left" bgcolor="'.$color.'" class="small" width="40%">'
		  	.'<a href="'.Comment::getLink($rs['board'], $rs['parent_id'], $rs['id'], $rs['thread_id']).'" name="'.$rs['id'].'">'
		  	.Comment::getTitle($rs['text'])
		  	.'</a>'
	      .'</td><td align="left" bgcolor="'.$color.'" class="small" width="30%">'
	      .usersystem::userpagelink($rs['user_id'], $rs['clan_tag'], $rs['username'])
	      .'</td><td align="center" bgcolor="'.$color.'" class="small" width="30%">'
	      .datename($rs[date])
	      .'</td></tr>'
	    ;

	  }
	  $html .= '</table>';

	  return $html;
	}

	/**
	* gibt den entspr. link zum sortieren des Forums zurück
	*/
	static function getSortlink($order) {
		if($_GET['order'] == $order) {
			$direction = ($_GET['direction'] == 'asc') ? 'desc' : 'asc';
			return '
				<a href="'.$_SERVER['PHP_SELF'].'?sortby='.$order.'&direction='.$direction.'">
				<img border="0" src="/images/forum/sort'.$direction.'.gif"></a>
			';
		} else {
			return '';
		}
	}

	/**
	* @return String
	* @desc Gibt das HTML des Forums zurück
 	*/
	static function getHTML($showboards, $pagesize, $sortby='') {

	  global $db, $user;

	  // Sortieren
	  //if($sortby == '') $sortby = 'ct.sticky DESC, ct.last_comment_id';
	  //if($sortby == '') $sortby = 'ct.last_comment_id';
	  if($sortby == '') $sortby = 'last_post_date';

	  // "ASC"-Sortierung ist nur bei Nummern oder Datum erlaubt, nicht bei Text
	  // ...prüfen, ob wir eine numerische/datum Spalte sortieren wollen
	  if (strpos($sortby,'_id') > 0 || strpos($sortby,'date') > 0 || strpos($sortby,'num') > 0)
	  {
		  switch ($_GET['order']) {
			case 'ASC':
				$order = 'ASC';
				$new_order = 'DESC';
				break;
			case 'DESC':
				$order = 'DESC';
				$new_order = 'ASC';
				break;
			default:
				$order = 'DESC';
				$new_order = 'ASC';
		  }
	  } else {
		  // Wenn wir Textspalten sortieren, immer "DESC" als Sortierreihenfolge verwenden
		  $order = 'DESC';
		  $new_order = 'ASC';
	  }


	  // Blättern...
	  $page = ($_GET['page'] == '') ? 1 : $_GET['page'];
	  $limit = ($page-1) * $pagesize.",".$pagesize;
	  $sql = "
	  	SELECT

	  	ct.sticky,

			c.board, c.id, c.parent_id, c.text last_post_text,
			UNIX_TIMESTAMP(c.date) last_post_date,

			lu.id lu_id, lu.clan_tag lu_clan_tag, lu.username lu_username,

			t.thread_id,
			tu.id tu_id, tu.clan_tag tu_clan_tag, tu.username tu_username,
			UNIX_TIMESTAMP(t.date) thread_date

	  	, IF(ISNULL(tfav.thread_id ), 0, 1) AS isfavorite

	  	, IF(ISNULL(tignore.thread_id ), 0, 1) AS ignoreit

			, count(DISTINCT cnum.id) numposts

			FROM comments_threads ct

			LEFT JOIN comments c ON ( c.id = (
				SELECT MAX( id )
				FROM comments
				WHERE thread_id = ct.thread_id AND board = ct.board) )
			LEFT JOIN comments t ON (t.id = ct.comment_id)
			LEFT JOIN comments cnum ON (ct.board = cnum.board AND ct.thread_id = cnum.thread_id)
	  ".
	  /*
			LEFT JOIN templates s ON s.id = c.thread_id
			LEFT JOIN bugtracker_bugs b ON b.id=c.thread_id
			LEFT JOIN `events` e ON e.id=c.thread_id
	  */
	  "
			LEFT JOIN user lu ON lu.id=c.user_id
			LEFT JOIN user tu ON tu.id=t.user_id
	  		LEFT JOIN comments_threads_rights ctr
	  			ON (ctr.thread_id=ct.thread_id AND ctr.board=ct.board AND ctr.user_id='$user->id')
	  		LEFT JOIN comments_threads_favorites tfav
	  			ON (tfav.board = ct.board AND tfav.thread_id = ct.thread_id AND tfav.user_id = '$user->id')
	  		LEFT JOIN comments_threads_ignore tignore
	  			ON (tignore.board = ct.board AND tignore.thread_id = ct.thread_id AND tignore.user_id = '$user->id')

			WHERE
				 c.board IN ('".implode("','", $showboards)."')
				 AND ('$user->typ' >= ct.rights OR ct.rights=".USER_SPECIAL." AND ctr.user_id IS NOT NULL)
				 AND ct.comment_id IS NOT NULL

			GROUP BY ct.thread_id

			ORDER BY ".$sortby." ".$order
	   ;


	  $numpages = floor($db->num($db->query($sql, __FILE__, __LINE__)) / $pagesize); // number of pages


	  // biko: auskommentieren im query tut nicht. musst es php-mässig auskommentieren.
	  $sql =
	  	$sql."
	  	LIMIT $limit
	  	"
	  ;
	  $result = $db->query($sql, __FILE__, __LINE__);


	  // Ausgabe ----------------------------------------------------------------
	  $html .=
			'<br />'
			.'<table cellpadding="1" cellspacing="1" class="border" width="100%">'
			.'<tr class="title">'
			.'<td align="left" width="25%"><a href="'.$_SERVER['PHP_SELF'].'?sortby=t.text&amp;order='.$new_order.'">Thread</a></td>'
			.'<td align="left" class="small" width="16%"><a href="'.$_SERVER['PHP_SELF'].'?sortby=tu_username&amp;order='.$new_order.'">&nbsp;&nbsp;&nbsp;Thread starter</a></td>'
			.'<td align="center"><a href="'.$_SERVER['PHP_SELF'].'?sortby=numposts&amp;order='.$new_order.'">#</a></td>'
			.'<td align="center"><a href="'.$_SERVER['PHP_SELF'].'?sortby=ct.thread_id&amp;order='.$new_order.'">Datum</a></td>'
			.'<td align="left" width="25%"><a href="'.$_SERVER['PHP_SELF'].'?sortby=last_post_date&amp;order='.$new_order.'">Last Post</a></td>'
	  ;

	  $html .= '</tr>';

	  $i = 0;

	  while($rs = $db->fetch($result)) {
	    $i++;

			$color = ($i % 2 == 0) ? BACKGROUNDCOLOR : TABLEBACKGROUNDCOLOR;
			if($rs['tu_id'] == $user->id) $color = OWNCOMMENTCOLOR;
			if($rs['isfavorite']) $color = FAVCOMMENTCOLOR;
			if($rs['ignoreit']) $color = IGNORECOMMENTCOLOR;

	    $html .=
	      '<tr style="font-size: 9px;">'
	      /*.'<td>'.$rs['sticky'].'</td>'*/
	      .'<td align="left" bgcolor="'.$color.'"><span style="float: left">'
	      .Comment::getLinkThread($rs['board'], $rs['thread_id'])
	      .'</span>'
	    ;

	    /*
    	if($rs['sticky'] == 1) {
    		if($user->typ >= USER_MEMBER) {
    			$html .= ' <a href="/actions/forum.php?action=unsticky&thread_id='.$rs['thread_id'].'">*sticky*</a>';
    		} else {
    			$html .= ' sticky';
    		}
    	} else {
    		if($user->typ >= USER_MEMBER) {
    			$html .= ' <a href="/actions/forum.php?action=sticky&thread_id='.$rs['thread_id'].'">[x]</a>';
    		}
    	}*/

		// alles was jetzt kommt, steht im feld rechtsbündig
		$html .=	'<span style="float: right">';

    	if($user->id > 0) {

	    // links ganz rechts ausrichten
	    $html .=	'<span style="float: right">';

	    	### Favorite or unfavorite Thread
	    	if($rs['isfavorite'] == 1) {
    			$html .=
    				' <a href="/actions/forum.php?action=unfavorite&board='.$rs['board'].'&thread_id='
    				.$rs['thread_id'].'">[unfav]</a>'
    			;
	    	} else {
	    		$html .=
	    			' <a href="/actions/forum.php?action=favorite&board='.$rs['board'].'&thread_id='
	    			.$rs['thread_id'].'">[fav]</a>'
	    		;
	    	}


			### Ignore or Unignore Thread
	    	if($rs['ignoreit'] == 1) {
    			$html .=
    				' <a href="/actions/forum.php?action=unignore&board='.$rs['board'].'&thread_id='
    				.$rs['thread_id'].'">[unignore]</a>'
    			;
	    	} else {
	    		$html .=
	    			' <a href="/actions/forum.php?action=ignore&board='.$rs['board'].'&thread_id='
	    			.$rs['thread_id'].'">[ignore]</a>'
	    		;
	    	}

	    	//$html .=	'&nbsp;&nbsp;&nbsp;</span>';
	  	}

	  	### RSS Feed Thread
	  	$html .=
					' <a href="'.RSS_URL.'&amp;type=forum&amp;board='.$rs['board'].'&amp;thread_id='
    				.$rs['thread_id'].'">[rss]</a>'
    	;

    	// rechtsbündig-span-element schliessen
    	$html .=	'</span>';


	    $html .= '</td><td align="left" bgcolor="'.$color.'" class="small">&nbsp;&nbsp;&nbsp;'
	      .usersystem::userpagelink($rs['tu_id'], $rs['tu_clan_tag'], $rs['tu_username'])
	      .'</td><td align="center" bgcolor="'.$color.'" class="small">'
	      .$rs['numposts']
	      .'</td><td align="center" bgcolor="'.$color.'" class="small">'
	      .datename($rs['thread_date'])
	      .'</td><td align="left" bgcolor="'.$color.'" class="small">'
	      .'<a href="'.Comment::getLink($rs['board'], $rs['parent_id'], $rs['id'], $rs['thread_id']).'">'
	      .str_pad(Comment::getTitle($rs['last_post_text']), 25, ' . ', STR_PAD_RIGHT)
	      .'</a>'
	      .' &raquo;</a>'
	      .' by '
	      .usersystem::userpagelink($rs['lu_id'], $rs['lu_clan_tag'], $rs['lu_username'])
	      .'</td><td align="center" bgcolor="'.$color.'" class="small">'
	      .datename($rs['last_post_date'])
	      .'</td>'
	    ;
	    if($user->typ != USER_NICHTEINGELOGGT && $rs['thread_id'] != '') {
	    	$lastp = Thread::getLastUnreadComment($rs['board'], $rs['thread_id'], $user->id);
	    	if($lastp) {
	    		$html .=
			    	'<td align="left" bgcolor="'.NEWCOMMENTCOLOR.'">'
			      .'<a href="'.Comment::getLink($lastp['board'], $lastp['parent_id'], $lastp['id'], $lastp['thread_id']).'">'
			      .Comment::getTitle($lastp['text'])
			      .'</a>'
			      .'</td>'
		      ;
	    	}
	    }
	    $html .= '</tr>';

	  }

	  $html .=
	   	'<tr class="title">'
	   	.'<td colspan="6">'

	   	.'<table cellpadding="0" cellspacing="0" width="100%">'

	   	.'<tr>'
	   	.'<td align="left" class="s">'.Forum::getFormSearch().'</td>'
	   	.'<td align="left">'.($user->typ != USER_NICHTEINGELOGGT ? Forum::getFormReadall() : '').'</td>'
			.'<td align="right">'
			.Forum::getNavigation($page, $pagesize, $numpages)
			.'</td>'
			.'</tr>'

			.'<tr>'
			.'<td align="center" colspan="3">'
	   	.($user->typ != USER_NICHTEINGELOGGT ? Forum::getFormBoardsShown($showboards) : '')
	   	.'</td>'

	   	.'</tr>'
			.'</table>'

			.'</td>'
			.'</tr>'
			.'</table>'
		;
		return $html;
	}

	static function hasPostedRecently($user_id, $parent_id) {
		global $db;
		$sql =
			"select UNIX_TIMESTAMP(date) as date, parent_id from comments where user_id = ".$user_id
			." order by date desc limit 0,1";
	  $rs = $db->fetch($db->query($sql, __FILE__, __LINE__));
		return time() < ($rs['date'] + 10) && $parent_id == $rs['parent_id'];
	}

	/**
	* @return String
	* @param $board
	* @param $thread_id
	* @param $parent_id
	* @desc Printet das "Pluggable" Commenting-System
	*/
	static function printCommentingSystem($board, $thread_id) {
		global $db, $user, $smarty;

	    if($_GET['parent_id'] == '') {
	    	$_GET['parent_id'] = $thread_id;
	    }

		if (Thread::hasRights($board, $thread_id, $user->id)) {
		   // damit man die älteren kompilierten comments löschen kann (speicherplatz sparen)
			Thread::setLastSeen($$board, $thread_id);

			// @DEPRECATED
			// @SEE $smarty->display("file:commentform.tpl");
			//if($user->typ != USER_NICHTEINGELOGGT) echo Forum::getFormNewPart1of2();

			// Subscribed_Comments Array Bauen
			$comments_subscribed = array();
			$sql = "
				SELECT comment_id
				FROM comments_subscriptions
				WHERE board='".$board."' AND user_id='".$user->id."'
			";
			$e = $db->query($sql, __FILE__, __LINE__);
			while ($d = $db->fetch($e)) $comments_subscribed[] = $d['comment_id'];
			$smarty->assign("comments_subscribed", $comments_subscribed);

			// Unread Comment Array Bauen
			$comments_unread = array();
			$sql = "
				SELECT u.*
				FROM comments_unread u, comments c
				WHERE c.id=u.comment_id
					AND c.thread_id='$thread_id'
					AND c.board='$board'
					AND u.user_id='$user->id'
			";
			$e = $db->query($sql, __FILE__, __LINE__);
			while ($d = $db->fetch($e)) $comments_unread[] = $d['comment_id'];
			$smarty->assign("comments_unread", $comments_unread);

			// Comments ausgeben
			$sql = "SELECT * FROM comments WHERE id='".$_GET['parent_id']."' AND board='$board'";
			$d = $db->fetch($db->query($sql, __FILE__, __LINE__));

			if ($_GET['parent_id'] == $thread_id || $d['parent_id'] == $thread_id) {
				$smarty->display("comments:$board-$thread_id");
			} else {
				$smarty->assign("comments_top_additional", 1);
				$smarty->display("comments:$d[parent_id]");
			}

	    	if($user->typ != USER_NICHTEINGELOGGT) {
	    		//echo Forum::getFormNewPart2of2($board, $thread_id, $_GET['parent_id']);
	    		$smarty->assign("board", $board);
				$smarty->assign("thread_id", $thread_id);
				$smarty->assign("parent_id", $_GET['parent_id']);
				//$smarty->display("tpl:194"); @DEPRECATED
				$smarty->display("file:commentform.tpl");
	    	}
		}
	}

	/**
	 * RSS functionality for Zorg Boards
	 * @return string
	 * @param $board default f (=forum)
	 * @param user_id default null (=nicht eingeloggt)
	 * @param $thread_id default null (=kein thread gewählt)
	 * @desc Gibt einen XML RSS-Feed zurück
	 */
	 static function printRSS($board='f', $user_id=null, $thread_id=null) {
	 	global $db, $user;

	 	// where-board Bedingung für SQL-Query bilden
		$wboard = $board ? "comments.board='".$board."'" : "1";

		$num = 15;		// Anzahl auszugebender Datensätze

	 	$xmlfeed = '';	// Ausgabestring für XML Feed initialisieren

		/**
		 * Ausgabe evaluieren und entsprechendes SQL holen
		 * @author IneX
		 */
		// nicht eingeloggter User...
		if (is_null($user_id)) {

			// Feed für forum board
			if ($board == 'f') {

				// keine thread_id übergeben
				if (is_null($thread_id)) {

					$sql =
						"SELECT"
						." comments.*"
						.", comments_unread.user_id as isunread"
						.", UNIX_TIMESTAMP(date) as date"
						.", user.clan_tag"
						.", user.username"
						." FROM comments"
						." LEFT JOIN user on comments.user_id = user.id"
						." LEFT JOIN comments_unread ON (comments.id=comments_unread.comment_id AND comments_unread.user_id = comments.user_id)"
						." WHERE parent_id = 1"
						." ORDER BY date desc"
						." LIMIT 0,".$num
					;

				// thread_id vorhanden
				} else {

					$sql =
						"SELECT user.*, comments.*, UNIX_TIMESTAMP(date) as date"
						." FROM comments"
						." LEFT JOIN user on comments.user_id = user.id"
						." WHERE thread_id = $thread_id AND board='".$board."'"
						." ORDER BY date DESC"
						." LIMIT 0,".$num
					;

				}

			// feed für anderes board
			} else {

				// für den Moment wird hier einfach ein Query über alle neuen Sachen gemacht.... IneX, 16.3.08
				// erm... aber so wies scheint, kommen die richtigen Sachen (weil alles über s board gesteuert wird). IneX, 16.3.08
				$sql =
					"
					SELECT
					comments.*
					, IF(ISNULL(comments_unread.comment_id), 0, 1) AS isunread
					, UNIX_TIMESTAMP(date) as date
					, user.clan_tag
					, user.username
					FROM comments
					LEFT JOIN user on comments.user_id = user.id
					LEFT JOIN comments_threads ct ON ct.thread_id = comments.thread_id AND ct.board = comments.board
					LEFT JOIN comments_threads_rights ctr ON ctr.thread_id = comments.thread_id AND ctr.board = comments.board AND ctr.user_id = '$user->id'
					LEFT JOIN comments_unread ON (comments.id=comments_unread.comment_id AND comments_unread.user_id = '$user->id')
					WHERE ".$wboard." AND (user.usertype >= ct.rights OR ct.rights=".USER_SPECIAL." AND ctr.user_id IS NOT NULL)
					ORDER BY date desc
					LIMIT 0,".$num
				;

			}

		// User ist eingeloggt
		} else {

			// Feed für forum board
			if ($board == 'f') {

				// keine thread_id übergeben
				if (is_null($thread_id)) {

					$sql =
						"SELECT"
						." comments.*"
						.", comments_unread.user_id as isunread"
						.", UNIX_TIMESTAMP(date) as date"
						.", user.clan_tag"
						.", user.username"
						." FROM comments"
						." LEFT JOIN user on comments.user_id = user.id"
						." LEFT JOIN comments_unread ON (comments.id=comments_unread.comment_id AND comments_unread.user_id = comments.user_id)"
						." WHERE parent_id = 1"
						." ORDER BY date desc"
						." LIMIT 0,".$num
					;

				// thread_id vorhanden
				} else {

					$sql =
						"SELECT user.*, comments.*, UNIX_TIMESTAMP(date) as date"
						." FROM comments"
						." LEFT JOIN user on comments.user_id = user.id"
						." WHERE thread_id = $thread_id AND board='".$board."'"
						." ORDER BY date DESC"
						." LIMIT 0,".$num
					;

				}

			// Feed für ein anderes board
			} else {

				// für den Moment wird hier einfach ein Query über alle neuen Sachen gemacht.... IneX, 16.3.08
				// erm... aber so wies scheint, kommen die richtigen Sachen (weil alles über s board gesteuert wird). IneX, 16.3.08
				$sql =
					"
					SELECT
					comments.*
					, IF(ISNULL(comments_unread.comment_id), 0, 1) AS isunread
					, UNIX_TIMESTAMP(date) as date
					, user.clan_tag
					, user.username
					FROM comments
					LEFT JOIN user on comments.user_id = user.id
					LEFT JOIN comments_threads ct ON ct.thread_id = comments.thread_id AND ct.board = comments.board
					LEFT JOIN comments_threads_rights ctr ON ctr.thread_id = comments.thread_id AND ctr.board = comments.board AND ctr.user_id = '$user->id'
					LEFT JOIN comments_unread ON (comments.id=comments_unread.comment_id AND comments_unread.user_id = '$user->id')
					WHERE ".$wboard." AND (user.usertype >= ct.rights OR ct.rights=".USER_SPECIAL." AND ctr.user_id IS NOT NULL)
					ORDER BY date desc
					LIMIT 0,".$num
				;
			}

		} // end if is_null($user_id)


			/**
			* Feed bauen
			* @author IneX
			*/
			// Query mit $sql
			if ($result = $db->query($sql, __FILE__, __LINE__)) {

				// Datensätze auslesen
				while($rs = $db->fetch($result)) {

					// Assign Values
					$xmlitem_title = ( Comment::isThread($rs['board'], $rs['id']) ? Comment::getTitle($rs['text']) : 'Comment zu '.remove_html(Comment::getLinkThread($rs['board'], Comment::getThreadid($rs['board'], $rs['id']))) );
					$xmlitem_link = str_replace('&', '&amp;amp;', SITE_URL . Comment::getLink($rs['board'], $rs['parent_id'], $rs['id'], $rs['thread_id'])); // &amp;amp; for xml-compatibility
					$xmlitem_pubDate = date('D, d M Y H:i:s', $rs[date]);//.' '.gmt_diff($rs[date]);
					$xmlitem_author = $rs['clan_tag'].$rs['username'];
					$xmlitem_category = '<![CDATA[';
						$xmlitem_category .= remove_html(Comment::getLinkThread($rs['board'], Comment::getThreadid($rs['board'], $rs['id'])));
						$xmlitem_category .= ']]>';
					$xmlitem_guid = str_replace('&', '&amp;amp;', SITE_URL . Comment::getLink($rs['board'], $rs['parent_id'], $rs['id'], $rs['thread_id'])); // &amp;amp; for xml-compatibility
					$xmlitem_description = '<![CDATA[';
						$desc = $rs['text'];
						$limit = 360;
						$xmlitem_description .= (strlen($desc) > $limit ? substr($desc, 0, $limit - 3) . '...' : $desc);
						$xmlitem_description .= ']]>';
					$xmlitem_content = remove_html($rs['text']);

					// XML Feed items schreiben
					$xmlfeed[] = [
							'xmlitem_title' => $xmlitem_title,
							'xmlitem_link' => $xmlitem_link,
							'xmlitem_pubDate' => $xmlitem_pubDate,
							'xmlitem_author' => $xmlitem_author,
							'xmlitem_category' => $xmlitem_category,
							'xmlitem_guid' => $xmlitem_guid,
							'xmlitem_description' => $xmlitem_description,
							'xmlitem_content' => $xmlitem_content
						];

				} // end while $rs

				// Return XML
				return $xmlfeed;

			} // end if $result

	} // end static function printRSS()

} // end class Forum()


/**
 * Thread Class
 * 
 * In dieser Klasse befinden sich alle Funktionen zum Thread-System
 *
 * @author		[z]milamber, IneX
 * @version		1.0
 * @package		Zorg
 * @subpackage	Forum
 */
class Thread {
	static function setLastSeen ($board, $thread_id) {
		global $db;

		$db->query("UPDATE comments_threads SET last_seen=now() WHERE board='$board' AND thread_id='$thread_id'", __FILE__, __LINE__);

	}

	static function setRights ($board, $thread_id, $rights) {
		global $db;

		$e = $db->query("SELECT * FROM comments_threads WHERE board='$board' AND thread_id='$thread_id'", __FILE__, __LINE__);
		$d = $db->fetch($e);
		if (!$d && $rights) $db->query("INSERT INTO comments_threads (board, thread_id) VALUES ('$board', $thread_id)", __FILE__, __LINE__);
		elseif (!$d && !$rights) return;

		if (!$rights) $rights = '0';

		if (is_array($rights)) $set_right = '3';
		else $set_right = $rights;

		$db->query("DELETE FROM comments_threads_rights WHERE thread_id='$thread_id'", __FILE__, __LINE__);
		$db->query("UPDATE comments_threads SET rights='$set_right' WHERE board='$board' AND thread_id='$thread_id'", __FILE__, __LINE__);
		if (is_array($rights)) {
			foreach ($rights as $it) {
				$db->query("INSERT INTO comments_threads_rights (board, thread_id, user_id) VALUES ('$board', $thread_id, $it)", __FILE__, __LINE__);
			}
		}
	}

	static function hasRights ($board, $thread_id, $user_id) {
		global $db;

		$sql =
			"
			SELECT
				user.usertype
				, ct.rights AS thread_rights
				, IF(ISNULL(ctr.user_id), 0, 1) AS special_rights
			FROM comments_threads ct
			LEFT JOIN comments_threads_rights ctr
				ON (ct.thread_id = ctr.thread_id
				AND ctr.user_id = '".$user_id."')
			LEFT JOIN user ON(user.id = '".$user_id."')
			WHERE ct.thread_id = ".$thread_id."
			AND ct.board = '".$board."'
			"
		;
		//echo $sql;
		$result = $db->query($sql, __FILE__, __LINE__);
		$rs = $db->fetch($result);
		if(
			$rs['usertype'] == NULL && $rs['thread_rights'] == 0
			|| $rs == NULL
			|| $rs['usertype'] >= $rs['thread_rights']
			|| $rs['thread_rights'] == USER_SPECIAL && $rs['special_rights'] == 1
		) {
			return true;
		}else{
			return false;
		}
	}


	static function adjustThreadRecord($board, $thread_id) {
		global $db;

		if(Thread::hasRecords($board, $thread_id)) {
  		$sql =
  			"select * from comments"
  			." where board = "
  			."'".$board."' and thread_id = ".$thread_id
  			." ORDER BY date asc"
  			." LIMIT 0,1"
  		;
  		$result = $db->query($sql, __FILE__, __LINE__);
  		$rs = $db->fetch($result);
  		$sql =
  			"update comments_threads"
  			." set comment_id = ".$rs['id']
  			." where board = '".$rs['board']."' and thread_id = ".$rs['thread_id']
  		;
  		$db->query($sql, __FILE__, __LINE__);
  	} else {
  		$sql =
  			"delete from comments_threads"
  			." where board = "."'".$board."'"
  			." and thread_id = ".$thread_id
  		;
  		$db->query($sql, __FILE__, __LINE__);
  	}
	}

	static function hasRecords($board, $thread_id) {
		global $db;
		$sql =
	  	"SELECT * from comments"
	  	." WHERE thread_id = ".$thread_id
	  	." AND board = '".$board."'"
	  ;
	  if($db->fetch($db->query($sql, __FILE__, __LINE__))) {
	  	return true;
	  } else {
	  	return false;
	  }
	}


	/**
	* @return Array
	* @param $board
	* @param $thread_id
	* @desc Holt den letzten Kommentar eines Threads
	*/
	static function getLastComment($board, $thread_id) {
	  global $db;
		$sql =
			"SELECT user.*, comments.*, UNIX_TIMESTAMP(date) as date"
	  	." FROM comments"
	  	." left join user on comments.user_id = user.id"
	  	." where thread_id = $thread_id AND board='".$board."'"
	  	." order by date desc Limit 0,1"
	  ;
	  $rs = $db->fetch($db->query($sql, __FILE__, __LINE__));
	  return $rs;
	}

	/**
	* @return Array
	* @param $thread_id int
	* @desc Holt den letzten ungelesenen Kommentar
 	*/
	static function getLastUnreadComment($board, $thread_id, $user_id) {
		global $db;
		$sql =
			"
			SELECT
			comments.*
			, UNIX_TIMESTAMP(comments.date) as date
			FROM comments
			LEFT JOIN comments_unread
				ON (comments.id = comments_unread.comment_id AND comments_unread.user_id = ".$user_id.")
			WHERE
				comments_unread.comment_id is NOT NULL
				AND comments.thread_id = ".$thread_id."
				AND comments.board='".$board."'
			ORDER by date ASC LIMIT 0,1
			"
		;
	  return $db->fetch($db->query($sql, __FILE__, __LINE__));
	}

	/**
	* @return String
	* @param $parent_id int
	* @param $thread_id int
	* @desc returns the Thread-Title and its navigation-bar
	*/
	static function getNavigation($board, $id, $thread_id) {

		$html =
			'<table class="border forum" style="font-size: x-small;table-layout:fixed;" width="100%">'
			.'<tr><td>'
		;

		if($id > $thread_id) {


			$tempid = $id;
			$i = 0;
			while($tempid > $thread_id) {
				$i++;
				$tempid = Comment::getParentid($tempid);
				if($tempid > $thread_id) {
					$html .= '<a href="'.getChangedURL('parent_id='.$tempid).'">'.$i.'up</a> | ';
				}
			}

			$rs = Thread::getRecordset($board, $thread_id);
			$html .= Comment::getLinkThread($rs['board'], $rs['thread_id']);
			$html .= '</td></tr></table>';

			// Additional Posts
			$html .=
				'<table bgcolor="'.Forum::colorfade(0, TABLEBACKGROUNDCOLOR).'" class="border forum"  style="table-layout:fixed;" width="100%">'
		    .'<tr>'
		  	.'<td bgcolor="'.$color.'" valign="top"><nobr>'
		    .'<a href="'.getChangedURL('parent_id='.Comment::getParentid($id)).'">'
		    .'<font size="4">^^^ Additional posts ^^^</font></a>'
		    .'</td></tr></table>'
	    ;

			return $html;

		}
	}

	static function getNumPosts($board, $thread_id) {
		global $db;
		$sql = "select * from comments where thread_id = ".$thread_id." AND board='".$board."'";
		return $db->num($db->query($sql, __FILE__, __LINE__));
	}


	/*
	 * tut nicht mehr
	 *
	static function getNumRead($board, $thread_id) {
		global $db;
		$sql =
			"select * from comments_unread where thread_id = ".$thread_id." AND board='".$board."'"
			." and user_id = '".$_SESSION['user_id']."'"
		;
		return $db->num($db->query($sql, __FILE__, __LINE__));
	}
	*/

	static function getNumUnread ($board, $thread_id, $user_id=0) {
		global $db, $user;

		if (!$user_id) $user_id = $user->id;

		$e = $db->query(
			"SELECT count(c.id) anz
			FROM comments c, comments_unread u
			WHERE c.board = '$board' AND c.thread_id='$thread_id' AND u.comment_id=c.id AND u.user_id='$user_id'",
			__FILE__, __LINE__
		);
		$d = $db->fetch($e);
		return $d['anz'];
	}

	/**
	* @return Array
	* @param $id int
	* @desc Fetches a Thread and returns its Recordset
	*/
	static function getRecordset($board, $thread_id) {
	  global $db;
	  $sql =
	  	"SELECT *, UNIX_TIMESTAMP(date) as date"
	  	." FROM comments where thread_id = ".$thread_id." and board = '".$board."'"
	  ;
	  return $db->fetch($db->query($sql, __FILE__, __LINE__));
	}

	/**
	* @return void
	* @param $parent_id int
	* @param $depth Array
	* @desc Post-Recursion Function.
	*/
	static function printChildPosts($board, $parent_id, $depth=array("space")) {

	  global $db, $user;

	  if(!is_numeric($parent_id)) {
				echo t('invalid-parent_id', 'commenting');
				exit;
			}

	  $hierdepth = count($depth);
	  $sql =
	  	"SELECT"
	  	." comments.*"
	  	.", user.clan_tag, user.username"
	  	.", comments_unread.user_id as isunread"
	  	.", UNIX_TIMESTAMP(comments.date) as date"
	  	.", count(c2.id) as numchildposts"
	  	." FROM comments"
	    ." LEFT JOIN comments_unread ON (comments.id=comments_unread.comment_id AND comments_unread.user_id = '".$_SESSION['user_id']."')"
	    ." LEFT JOIN user ON comments.user_id = user.id"
	    ." LEFT JOIN comments as c2 ON (comments.id = c2.parent_id AND comments.board = c2.board)"
	  	." WHERE comments.parent_id = $parent_id AND comments.board = '".$board."'"
	  	." GROUP BY comments.id"
	  	." ORDER BY comments.id"
	  ;

	  $result = $db->query($sql, __FILE__, __LINE__);
	  $rcount = 0;
	  $additional = FALSE; // already posted "Additional Posts" ?
	  while($rs = $db->fetch($result)) {
	    $depth2 = $depth;
	    $rcount++;

	    // put line-imagename into depth-array
	    //if ($rs['numchildpostsparent'] > $rcount) { geht nicht


	    if (Comment::getNumChildposts($board, $parent_id) > $rcount) {
	      array_push($depth2, "vertline");
	    } else {
	      array_push($depth2, "space");
	    }

	  	if(
	  		$user->typ != USER_NICHTEINGELOGGT && $hierdepth < $user->maxdepth
	  		|| $rs['isunread'] != ''
	  		|| $user->typ == USER_NICHTEINGELOGGT && $hierdepth < 10
	  	) {
	  		echo Comment::getHTML($rs, $depth2); // print formatted post
	  		if ($rs['numchildposts'] > 0) {
		    	echo '<div id="layer'.$rs['id'].'">';
		    	Thread::printChildPosts($board, $rs['id'], $depth2);
		    	echo '</div>';
		    }
	  	} else {
	  		if(!$additional) {
	  			echo Comment::getHTMLadditional($rs, $depth2); // prints "additional posts"...
	  			$additional = true;
	  		}
	  	}
	  	flush(); // HTML bereits ausgeben
	  }
	}

}
