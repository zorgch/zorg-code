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
 * @package zorg\Forum
 */

/**
 * File Includes
 * @include config.inc.php required
 * @include	smarty.inc.php Smarty, required
 * @include	usersystem.inc.php Usersystem, required
 * @include	util.inc.php Utilities, required
 * @include	sunrise.inc.php Sunrise, required
 * @include	Messagesystem DEPRECATED
 */
require_once __DIR__.'/config.inc.php';
require_once INCLUDES_DIR.'smarty.inc.php';
require_once INCLUDES_DIR.'usersystem.inc.php';
//require_once INCLUDES_DIR.'util.inc.php';
//require_once INCLUDES_DIR.'sunrise.inc.php';
//require_once INCLUDES_DIR.'messagesystem.inc.php';

/**
 * GLOBALS
 * @const THREAD_TPL_TIMEOUT wenn ein thread x tage nicht mehr angeschaut wurde, werden seine tpl's gelöscht. (speicherplatz sparen)
 */
define('THREAD_TPL_TIMEOUT', 30);  // in tagen

/**
 * Comment Class
 *
 * In dieser Klasse befinden sich alle Funktionen zum Commenting-System
 *
 * @version 1.1
 * @since 1.0 `[z]biko` Class added
 * @since 1.1 `IneX` Various code refactorings
 *
 * @package zorg\Forum
 */
class Comment
{
	/**
	 * Kompiliert ein Comment als Smarty-Template Resource
	 *
	 * @author [z]biko
	 * @version 2.0
	 * @since 1.0 method added
	 * @since 2.0 `05.11.2018` method ehnaced with code & query optimizations
	 *
	 * @link https://github.com/zorgch/zorg-code/blob/master/www/includes/comments.res.php Wird verwendet in comments.res.php
	 * @uses smartyresource_comments_get_template()
	 * @param integer $thread_id
	 * @param integer $comment_id
	 * @param string $board (Optional) Board-shortname, z.b. "f"=forum, als String - Default: "f"
	 * @global object $db Globales Class-Object mit allen MySQL-Methoden
	 * @global object $smarty Globales Class-Object mit allen Smarty-Methoden
	 * @return boolean Returns true or false depending on successful execution
	 */
	static function compile_template($thread_id, $comment_id, $board='f')
	{
		global $db, $smarty;

		/** Validate passed parameters */
		if (!is_numeric($thread_id) || $thread_id <= 0 || is_array($thread_id)) return false;
		if (!is_numeric($comment_id) || $comment_id <= 0 || is_array($comment_id)) return false;
		if (!$board || empty($board)) $board = 'f'; // fallback to default 'f'
		if (is_numeric($board) || $board === 0 || is_array($board)) return false;
		if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> Validation OK for: $thread_id: %d | $comment_id: %d | $board passed: %s', __METHOD__, __LINE__, $thread_id, $comment_id, $board));

		$error = '';

		/** For Forum $board... */
		if ($board === 'f')
		{
			/** Compile $thread_id (wenn $comment_id = 1 dann ist $thread_id = eigentliche $comment_id...) */
			if ($comment_id === 1 || $comment_id === '1')
			{
				if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> $board=="f" && $comment_id==1', __METHOD__, __LINE__));
				$resource = sprintf('comments:%d', $thread_id);

			/** Compile $board-$thread_id */
			} elseif ($thread_id === $comment_id) {
				if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> $thread_id==$comment_id: %d', __METHOD__, __LINE__, $thread_id));
				$resource = sprintf('comments:%s-%d', $board, $thread_id);

			/** Compile $comment_id */
			} else {
				if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> $board!="f" || $comment_id!=1', __METHOD__, __LINE__));
				$resource = sprintf('comments:%d', $comment_id);
			}

		/** For all other $boards... */
		} elseif ($thread_id === $comment_id) {
			/** Compile $board-$comment_id */
			if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> $thread_id==$comment_id: %d', __METHOD__, __LINE__, $thread_id));
			$resource = sprintf('comments:%s-%d', $board, $comment_id);

		/** Fallback / regular compile for $comment_id */
		} else {
			if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> $thread_id!=$comment_id: %d != %d', __METHOD__, __LINE__, $thread_id, $comment_id));
			$resource = sprintf('comments:%d', $comment_id);
		}
		if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> compile_template() $resource: %s', __METHOD__, __LINE__, $resource));

		/** Update record in database */
		$result = $db->update('comments', ['id', $comment_id], ['error' => null], __FILE__, __LINE__, __METHOD__);
		if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> $db->update(comments) $result: %d %s', __METHOD__, __LINE__, $result, ($result > 0 ? 'updates' : 'no change')));

		if (($result || $result >= 0) && !empty($resource))
		{
			/** Compile Smarty-Template for Comment */
			$smarty_compile_result = $smarty->compile($resource, $error);
			if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> $db->update(comments) $result OK', __METHOD__, __LINE__));

			/** $smarty->compile SUCCESS */
			if ($smarty_compile_result === true)
			{
				if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> $smarty->compile SUCCESS', __METHOD__, __LINE__));
				$comments_update_query = $db->update('comments', ['id', $comment_id], ['error' => null], __FILE__, __LINE__, __METHOD__);
				return true;

			/** $smarty->comile ERROR */
			} else {
				$errortext = '';
				foreach ($error as $value) $errortext .= $value.'<br>';
				$comments_update_query = $db->update('comments', ['id', $comment_id], ['error' => $errortext], __FILE__, __LINE__, __METHOD__);
				$smarty->compile($resource, $error);
				if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> $smarty->compile ERROR', __METHOD__, __LINE__));
				return false;
			}
			if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> $comments_update_query: %s', __METHOD__, __LINE__, ($comments_update_query ? 'true' : 'false')));

		/** Missing $resource or Comment Update-Query ERROR */
		} else {
			if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> $db->update(comments) ERROR', __METHOD__, __LINE__));
			return false;
		}
	}

	/**
	 * Macht Textformatierungen fürs Forum
	 *
	 * @author [z]milamber
	 * @version 1.0
	 * @since 1.0 method added
	 *
	 * @param string $text
	 * @global object $user Globales Class-Object mit den User-Methoden & Variablen
	 * @return string
 	 */
	static function formatPost($text)
	{

		/** Falls Post HTML beinhaltet, schauen ob was böses[tm] drin ist. */
		$illegalhtml = false;

		/** Illegale Tags suchen */
		$illegaltags = array('link', 'select', 'script', 'style');
		foreach($illegaltags as $illegaltag) {
			if($illegalhtml) {
				continue;
			}

			if(strstr($text, '<'.$illegaltag)) {
				$text = htmlentities($text).'<b style="color:red;">[Illegaler Tag: '.$illegaltag.']</b>';
				$illegalhtml = true;
			}
		}

		/** Newlines zu BRs machen */
		if(!strstr($text, '<br>')) {
			$text = str_replace("\n", "<br>", $text); // Newline
		}

		/* REMOVED BY [z]Duke
			// Milambers b?ser Post
			if($user->typ != 2) {
				$text = str_replace("###user###", usersystem::id2user($_SESSION['user_id']), $text);
			}
		*/
		return $text;
	}

	/**
	 * Anzahl Kinder-Objekte zu beliebigem Post ermitteln
	 *
	 * @author [z]biko
	 * @version 1.0
	 * @since 1.0 method added
	 *
	 * @FIXME switch Reihenfolge der params (so wie sonst auch überall): $board <--> $comment_id
	 *
	 * @param string $board Board-shortname, z.b. "f"=forum, als String
	 * @param int $comment_id
	 * @global object $db Globales Class-Object mit allen MySQL-Methoden
	 * @global object $user Globales Class-Object mit den User-Methoden & Variablen
	 * @return int Num Child-Comments
	 */
	static function getNumChildposts($board, $comment_id) {
		global $db, $user;
		static $_cache = array();

		/** Validate passed parameters */
		if (empty($board) || is_numeric($board) || $board === 0 || is_array($board)) return false;

		if(is_numeric($comment_id) && $comment_id > 0)
		{
			/** Parent zu $comment_id is NOT cached yet... */
			if (!isset($_cache["$board $comment_id"]))
			{
			   $sql = 'SELECT * FROM comments where parent_id=? AND board=?';
			   $_cache["$board $comment_id"] = $db->num($db->query($sql, __FILE__, __LINE__, __METHOD__, [$comment_id, $board]));
			}
			return $_cache["$board $comment_id"];
		} else {
			user_error(sprintf('<%s:%d> $comment_id is not numeric: %d', __FILE__, __LINE__, $comment_id), E_USER_WARNING);
			//exit;
		}
	}

	/**
	 * Get a Comment's Parent-Comment-ID
	 *
	 * @author [z]biko
	 * @version 2.0
	 * @since 1.0 method added
	 * @since 2.0 `27.11.2018` Fixed passing any $height integer to this method caused an eternal loop
	 *
	 * @param int $comment_id
	 * @param int $height (Optional)
	 * @return integer
	 */
	static function getParentid($comment_id, $height=null) {
		$i = 0;

		do {
			$rs = self::getRecordset($comment_id);
			$i++;
		} while($i <= $height && $rs['parent_id'] > 0);

		return $rs['parent_id'];
	}

	/**
	 * Fetches a Post and returns its Recordset
	 *
	 * @author [z]biko
	 * @version 2.0
	 * @since 1.0 method added
	 * @since 2.0 `06.11.2018` added parameter validation
	 *
	 * @param int $id Comment-ID
	 * @global object $db Globales Class-Object mit allen MySQL-Methoden
	 * @return arr|bool Containing the queried DB-Result record-set - or false on error
	 */
	static function getRecordset($id) {
		global $db;

		/** Validate passed parameters */
		if (empty($id) || !is_numeric($id) || $id <= 0 || is_array($id)) {
			error_log(sprintf('[WARN] <%s:%d> Passed Parameter $id is not numeric or otherwise invalid: %s', __METHOD__, __LINE__, $id));
			return false;
		}

		$sql = 'SELECT *, UNIX_TIMESTAMP(date) as date FROM comments WHERE id=?';
		return $db->fetch($db->query($sql, __FILE__, __LINE__, __METHOD__, [$id]));
	}

	/**
	 * HTML der "Additional Posts" Teile
	 *
	 * @deprecated see smartyresource_comments_get_childposts()??? (26.10.2018)
	 *
	 * @author [z]biko
	 * @version 1.0
	 * @since 1.0 method added
	 *
	 * @param object $rs SQL-Query Result
	 * @param array $himages ?
	 * @param void $viewkeyword DEPRECATED
	 * @return String
 	 */
	static function getHTMLadditional($rs, $himages)
	{
		global $db, $user;

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
  		'<td class="threading space">'
	  		.'<a class="threading switch expand" href="'.$_SERVER['PHP_SELF'].'?parent_id='.$rs['id'].'"></a>'
	  	.'</td>'
	  	.'<td align="left" class="border forum">'
		  	.'<table bgcolor="'.Forum::colorfade($hdepth, $color).'" class="forum">'
			    .'<tr>'
				  	.'<td bgcolor="'.$color.'" valign="top">'
					    .'<a href="'.$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'].'&parent_id='.$rs['parent_id'].'">'
					    .' <font size="4">Additional posts</font></a>'
					    .' <a href="/profil.php?do=view">(du hast Forumanzeigeschwelle '.$user->maxdepth.' eingestellt)</a>'
		    .'</td></tr></table>'
	  	.'</td></tr></table>'
	  ;
	  return $html;
	}

	/**
	 * Get link for a Comment
	 *
	 * @author [z]biko
	 * @version 1.0
	 * @since 1.0 method added
	 *
	 * @param integer $comment_id Comment-Id
	 * @return string HTML-Link string
	 */
	static function getLinkComment ($comment_id) {
		global $db;

		$sql = 'SELECT * FROM comments WHERE id=?';
		$e = $db->query($sql, __FILE__, __LINE__, __METHOD__, [$comment_id]);
		$d = $db->fetch($e);
		if ($d) return self::getLink($d['board'], $d['parent_id'], $d['id'], $d['thread_id']);
		else return '';
	}

	/**
	 * Get link for a Board
	 *
	 * @author [z]biko
	 * @version 1.0
	 * @since 1.0 method added
	 *
	 * @param string $board Board-Identifier
	 * @return object SQL-Query Result resource
	 */
	static function getBoardlink($board) {
		global $db;
		$sql = 'SELECT * FROM comments_boards WHERE board=?';
		$result = $db->query($sql, __FILE__, __LINE__, __METHOD__, [$board]);
		$rs = $db->fetch($result);
		return $rs;
	}

	/**
	 * Link to a Comment
	 *
	 * @version 2.0
	 * @since 1.0 `[z]biko` method added
	 * @since 2.0 `26.10.2018` `IneX` added $addUrlParameters to make Link work with routes (e.g. /thread/23/?parent_id=5 vs. /gallery.php?show=pic&picID=23)
	 *
	 * @link https://github.com/zorgch/zorg-code/blob/master/www/.htaccess Routes defined for Comment links defined in .htaccess
	 * @param string $board Board-Identifier for Comment to Link
	 * @param integer $parent_id Parent-ID for Comment to Link
	 * @param integer $comment_id Comment-ID to Link
	 * @param integer $thread_id Thread-ID for Comment to Link
	 * @return string Link-URL to Comment
	 */
	static function getLink($board, $parent_id, $comment_id, $thread_id) {
		global $boardlinks;

		if(!isset($boardlinks)) {
			$boardlinks = array();
		}

		if(!key_exists($board, $boardlinks)) {
			$rs = self::getBoardlink($board);
			$boardlinks[$board] = $rs;
		}

		$addUrlParameters = (strpos($boardlinks[$board]['link'].$thread_id, '?')>0 ? '&' : '?').'parent_id='.$parent_id.'#'.$comment_id;
		$threadCommentLink = $boardlinks[$board]['link'].$thread_id.$addUrlParameters;

		return $threadCommentLink;
	}

	/**
	 * Link to a Thread
	 *
	 * @author [z]biko
	 * @author IneX
	 * @version 2.0
	 * @since 1.0 method added
	 * @since 2.0 `26.10.2018` added $output_html-Parameter & return only for Thread-URL
	 *
	 * @param string $output_html (Optional) Wenn TRUE dann wird HTML-Linktag ausgegeben, ansonsten wird nur die Link-URL returned
	 * @return string Gibt die Thread-URL als HTML-Link (String) zurück wenn $output_html=true - oder nur die Thread-URL wenn $output_html=false
	 */
	static function getLinkThread($board, $thread_id, $output_html=true) {
		global $db, $boardlinks;

		if(!isset($boardlinks)) {
			$boardlinks = array();
		}

		if(!key_exists($board, $boardlinks)) {
			$rs = self::getBoardlink($board);
			$boardlinks[$board] = $rs;
		}

		if ($board == 'f') { // Forum
			$rs = self::getRecordset($thread_id);
			$output = ($output_html === true ? '<a href="'.$boardlinks[$board]['link'].$thread_id.'" itemprop="url">'.self::getTitle($rs['text'], 40).'</a>' : $boardlinks[$board]['link'].$thread_id);
			return $output;
		} else if($board == 'i') { // Pictures
			$sql = 'SELECT name FROM gallery_pics WHERE id='.$thread_id;
			$rs = $db->fetch($db->query($sql, __FILE__, __LINE__, __METHOD__));
			if($rs['name'] != '') {
				$output = ($output_html === true ? '<a href="'.$boardlinks[$board]['link'].$thread_id.'" itemprop="url">[Pic] '.substr($rs['name'], 0, 20).'</a>' : $boardlinks[$board]['link'].$thread_id);
				return $output;
			} else {
				$output = ($output_html === true ? '<a href="'.$boardlinks[$board]['link'].$thread_id.'" itemprop="url">'.$boardlinks[$board]['field'].' '.$thread_id.'</a>' : $boardlinks[$board]['link'].$thread_id);
				return $output;
			}
		} else if ($board == 'e') { // Events
			$sql = 'SELECT name FROM events WHERE id='.$thread_id;
			$rs = $db->fetch($db->query($sql, __FILE__, __LINE__, __METHOD__));
			$output = ($output_html === true ? '<a href="'.$boardlinks[$board]['link'].$thread_id.'" itemprop="url">[Event] '.($rs['name'] != '' ? substr($rs['name'], 0, 20) : $thread_id).'</a>' : $boardlinks[$board]['link'].$thread_id);
			return $output;
		} else if ($board == 'g') { // GO Game
			$output = ($output_html === true ? '<a href="'.$boardlinks[$board]['link'].$thread_id.'" itemprop="url">[GO] '.$boardlinks[$board]['field'].' '.$thread_id.'</a>' : $boardlinks[$board]['link'].$thread_id);
			return $output;
		} else {
			$output = ($output_html === true ? '<a href="'.$boardlinks[$board]['link'].$thread_id.'" itemprop="url">'.$boardlinks[$board]['field'].' '.$thread_id.'</a>' : $boardlinks[$board]['link'].$thread_id);
			return $output;
		}

	}

	static function getChildPostsFormFields($id, $parent_id, $comment_id=0, $depth=0) {
		global $db;

		$html = '';

		if($depth < 7)
		{
			if($comment_id == 0) $comment_id = $parent_id;
			$sql = 'SELECT * FROM comments WHERE parent_id = ?';
			$result = $db->query($sql, __FILE__, __LINE__, __METHOD__, [$comment_id]);

			while ($rs = $db->fetch($result)) {
				if($rs['id'] != $id) {
					$html .=
						'<option value="'.$rs['id'].'"'.($parent_id == $rs['id'] ? ' selected="selected"' : '').'>'
						.str_repeat('--', $depth)
						.'#'.$rs['id'].' '
						.self::getTitle($rs['text'])
						.'</option>'
					;
				}

				$html .= self::getChildPostsFormFields($id, $parent_id, $rs['id'], ($depth+1));
			}
		}
		return $html;
	}

	/**
	 * Holt den Thread-Id eines Posts oder Threads.
	 *
	 * WICHTIG! UNBEDINGT SO LASSEN!
	 *
	 * @param string $board
	 * @param int $id
	 * @return int
	 */
	static function getThreadid($board, $id) {
		global $db;
		$sql = 'SELECT thread_id FROM comments WHERE board=? AND id=?';
		$rs = $db->fetch($db->query($sql, __FILE__, __LINE__, __METHOD__, [$board, $id]));
		return $rs['thread_id'];
	}

	/**
	 * Den Titel eines Kommentars holen.
	 *
	 * @author [z]biko
	 * @author IneX
	 * @version 2.1
	 * @since 1.0 `[z]biko` method added
	 * @since 2.0 `24.09.2018` `IneX` method uses now text_width() util-function
	 * @since 2.1 `15.01.2019` `IneX` enhanced to also remove Smarty curly brackets { and }
	 *
	 * @uses remove_html()
	 * @uses text_width()
	 * @param string $text
	 * @param int $length offset
	 * @param string $if_empty_use_this (Optional) Wenn Titel leer ist, dann ein besserer Fallback als nur '---' der verwendet werden soll
	 * @return string
	 */
	static function getTitle($text, $length=20, $if_empty_use_this=null) {
		$text = text_width(remove_html($text), $length, '', true, true);
		$text = str_replace('{', '', str_replace('}', '', $text)); // Remove Smarty-brackets { and }
		if (empty($text)) $text = (empty($if_empty_use_this) ? '---' : remove_html($if_empty_use_this));
		return $text;
	}

	/**
	 * Schnipsel/Auszug eines Kommentars holen.
	 *
	 * @version 1.1
	 * @since 1.0 `19.08.2019` `IneX` method added
	 * @since 1.1 `15.01.2019` `IneX` enhanced to also remove Smarty curly brackets { and }
	 *
	 * @uses remove_html()
	 * @uses text_width()
	 * @uses t()
	 * @param string $text
	 * @param int $length offset
	 * @return string
	 */
	static function getSummary($text, $length=20) {
		$text = text_width(remove_html($text), $length);
		$text = str_replace('{', '', str_replace('}', '', $text)); // Remove Smarty-brackets { and }
		return (empty($text) ? t('text-abbreviation') : $text);
	}

	/**
	 * Prüft ob der Comment ein Thread ist.
	 * Prüft, ob der Comment im therads-table eingetragen ist (= thread start)
	 *
	 * @author IneX
	 * @version 1.0
	 * @since 1.0 `16.03.2008` Method added
	 * @param string $board
	 * @param int $id int
	 * @return boolean
	 */
	static function isThread($board, $id) {
		global $db;
		$sql = 'SELECT thread_id FROM comments_threads WHERE board=? AND comment_id=?';
		$rs = $db->fetch($db->query($sql, __FILE__, __LINE__, __METHOD__, [$board, $id]));

		return $rs;
	}

	/**
	 * Mark Comment as 'READ'.
	 * @author [z]biko
	 * @version 1.1
	 * @since 1.0 Method addded
	 * @since 1.1 `03.12.2023` `IneX` Support with MySQL prepared statements
	 *
	 * @param int $commentid
	 * @param int $user_id
	 * @return boolean
	 */
	static function markasread($comment_id, $user_id)
	{
		global $db, $user;
		if((is_numeric($comment_id) && $comment_id>0) && defined('USER_USER') && $user->typ >= USER_USER)
		{
			$sql = 'DELETE from comments_unread WHERE user_id=? AND comment_id=?';
			$db->query($sql, __FILE__, __LINE__, __METHOD__, [$user_id, $comment_id]);
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Mark Comment as 'UNREAD' for all users.
	 * @author [z]biko
	 * @version 1.1
	 * @since 1.0 Method addded
	 * @since 1.1 `03.12.2023` `IneX` Unread comments where broken: added support for MySQL prepared statements
	 *
	 * @param int $commentid
	 * @return boolean
	 */
	static function markasunread($comment_id)
	{
		global $db;

		/** Validate passed Parameters */
		if (!is_numeric($comment_id) || $comment_id <= 0) return false;

		$sql = 'SELECT c.thread_id, c.board, ct.rights
				FROM comments c LEFT JOIN comments_threads ct
					ON (ct.board=c.board AND ct.thread_id=c.thread_id)
				WHERE c.id=? LIMIT 1';
		$rs = $db->fetch($db->query($sql, __FILE__, __LINE__, __METHOD__, [$comment_id]));

		$userights = (!isset($rs['rights']) || empty($rs['rights']) ? 0 : $rs['rights']);
		$board = $rs['board'];
		$thread = $rs['thread_id'];

		/** Insert only Unread Commments to Users who are NOT USER_SPECIAL */
		if($userights < USER_SPECIAL)
		{
			$sql = 'REPLACE INTO comments_unread (user_id, comment_id)
					SELECT id, ? FROM user
						WHERE user.usertype>=?
						AND (UNIX_TIMESTAMP(lastlogin)+?) > UNIX_TIMESTAMP(?)
						AND forum_boards_unread LIKE CONCAT("%", ?, "%")'
						/*AND ISNULL(
							SELECT tignore.thread_id, tignore.user_id
							FROM comments_threads_ignore tignore
							WHERE tignore.thread_id=$thread
							AND tignore.user_id = user.id
						)*/
			;
			$affectedRows = $db->query($sql, __FILE__, __LINE__, __METHOD__, [$comment_id, $userights, USER_OLD_AFTER, timestamp(true), $board]);
		} else {
			$sql = 'REPLACE INTO comments_unread (user_id, comment_id)
					SELECT user_id, ? FROM comments_threads_rights
						WHERE board=? AND thread_id=?';
			$affectedRows = $db->query($sql, __FILE__, __LINE__, __METHOD__, [$comment_id, $board, $thread]);
		}
		return ($affectedRows > 0 ? true : false);
	}

	/**
	 * In Forum-Search, highlight the searched keyword(s) with a color
	 */
	static function highliteKeyword($keyword,$text) {
	  global $tborderc;
	  //$keyword = htmlentities($keyword);
	  $searcher = "/$keyword/i";
	  $replace = "<b style=\"color: #".FONTCOLOR."; background: #".HIGHLITECOLOR."\">".$keyword."</b>";
	  return preg_replace("$searcher", $replace, $text);
	}

	/**
	 * Post Comment to Board
	 *
	 * @version 3.2
	 * @since 1.0 `[z]biko` method added
	 * @since 2.0 `IneX` added Activities
	 * @since 3.0 `IneX` various code optimizations, new notification class used
	 * @since 3.1 `IneX` minor code optimizations, changed Forum Activity-Notification only if new Forum-Thread
	 * @since 3.2 `27.09.2019` `IneX` changed INSERT to use $db->insert()
	 *
	 * @global object $db Globales Class-Object mit allen MySQL-Methoden
	 * @global object $user Globales Class-Object mit den User-Methoden & Variablen
	 * @global object $notification Globales Class-Object mit allen Notification-Methoden
	 */
	static function post($parent_id, $board, $user_id, $text, $msg_users=NULL)
	{
		global $db, $user, $notification;

		/** Parent-Id = 1 wenn man ein ForumThread postet */
		$parent_id = ($parent_id <= 0 ? 1 : intval($parent_id));
		if(!is_numeric($user_id)) $user_id = intval($user_id);
		if(!is_numeric($parent_id) || $parent_id <= 0) user_error(t('invalid-parent_id', 'commenting'), E_USER_ERROR);

		/**
		 * Falls Thread-Id noch nicht vorhanden, parent-id nehmen
		 * (1 bei forum, anderes bei den anderen boards)
		 */
		$thread_id = self::getThreadid($board, $parent_id);
		if($thread_id <= 0) $thread_id = $parent_id;

		/** Validate comment parameters */
		if($thread_id <= 0 || !is_numeric($thread_id)) user_error(t('invalid-thread_id', 'commenting'), E_USER_ERROR);
		if (empty($text)) user_error('Comment Text darf nicht leer sein!', E_USER_ERROR);

		/** Nur weitermachen, wenn Rechte stimmen */
		if (Thread::hasRights($board, $thread_id, $user_id))
		{
			/** Böse Sachen aus dem Text entfernen */
			//$text = sanitize_userinput($text);
			$text = htmlspecialchars_decode($text, ENT_COMPAT | ENT_SUBSTITUTE);

			/** Comment in die DB abspeichern */
			$comment_error = (isset($comment_error) ? $comment_error : '');
			$comment_id = $db->insert('comments', ['user_id'=>$user_id, 'parent_id'=>$parent_id, 'thread_id'=>$thread_id, 'text'=>$text, 'date'=>timestamp(true), 'board'=>$board, 'error'=>$comment_error], __FILE__, __LINE__, __METHOD__);
			if(empty($comment_id) || !is_numeric($comment_id) || $comment_id <= 0) user_error(t('invalid-comment_id', 'commenting'), E_USER_ERROR);

			/**
			 * Falls parent_id = 1, thread_id = id.
			 * Für Forum->neue Threads.
			 */
			$sql = 'UPDATE comments SET thread_id=id
					WHERE parent_id=? AND board=? AND id=?';
			$params = [1, "f", $comment_id];
			$db->query($sql, __FILE__, __LINE__, __METHOD__, $params);

			$rs = self::getRecordset($comment_id);
			$commentlink = self::getLink($rs['board'], $rs['parent_id'], $rs['id'], $rs['thread_id']);
			if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> Comment::getRecordset(): %s', __METHOD__, __LINE__, print_r($rs,true)));
			if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> Comment::getLink(): %s', __METHOD__, __LINE__, $commentlink));
			if(empty($rs) || !$rs) user_error(t('invalid-comment_id', 'commenting'), E_USER_ERROR);
			if(empty($commentlink) || !$commentlink || is_numeric($commentlink)) user_error(t('invalid-comment_id', 'commenting'), E_USER_ERROR);

			/** Falls neuer Thread, Record in Thread-Tabelle generieren */
			$sql = 'INSERT IGNORE INTO comments_threads (board, thread_id, comment_id) VALUES (?, ?, ?)';
			$params = [$rs['board'], $rs['thread_id'], $rs['id']];
			// TODO use $db->insert('comments_threads', array(key=value)) instead of $db->query()
			$db->query($sql, __FILE__, __LINE__, __METHOD__, $params);

			/** last post setzen */
			$sql = 'UPDATE comments_threads
					SET last_comment_id=?, comment_id=IF(ISNULL(comment_id), ?, comment_id)
					WHERE thread_id=? AND board=?';
			$db->query($sql, __FILE__, __LINE__, __METHOD__, [$rs['id'], $rs['id'], $rs['thread_id'], $board]);

			/** Comment-Template kompilieren */
			$compile_template_result = self::compile_template($rs['thread_id'], $rs['id'], $rs['board']);
			if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> Comment::compile_template(%d, %d, %s): %s', __METHOD__, __LINE__, $rs['thread_id'], $rs['id'], $rs['board'], ($compile_template_result ? 'true' : 'false')));

			/** Thread-Comment Template neu kompilieren */
			if ($rs['parent_id'] != 1 || $rs['board'] != 'f') {
				if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> $rs[parent_id] != 1 || $rs[board] != "f"', __METHOD__, __LINE__));
				$compile_template2_result = self::compile_template($rs['thread_id'], $rs['parent_id'], $rs['board']);
				if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> Comment::compile_template(%d, %d, %s): %s', __METHOD__, __LINE__, $rs['thread_id'], $rs['parent_id'], $rs['board'], ($compile_template2_result ? 'true' : 'false')));
			}

			/** Mark comment as unread for all users */
			$markedAsUnread = self::markasunread(intval($rs['id']));

			/** Mark comment as read for poster (current user) */
			$markedReadForPoster = self::markasread(intval($rs['id']), $user_id);

			/**
			 * Comment Notifications
			 */
			/** 1) Activity Eintrag auslösen */
				/** Blacklist exceptions */
				$addActivity = false; // init
				if ($user_id != BARBARA_HARRIS) { // ...ausser bei der Bärbel, die trollt zuviel
					if ($rs['board'] !== 'h') { // ...ausser bei Hz-Games (weil die Comments u.A. noch geheim sein müssen)
						if ($rs['board'] !== 'f' || $parent_id === 1) { // ...und im Forum nur falls ein neuer Thread gestartet wurde (kein Comment)
							$addActivity = true;
						}
					}
				}
			if ($addActivity === true)
			{
				/** Neuer Forum-Thread */
				if ($rs['board'] === 'f' || $parent_id === 1)
				{
					Activities::addActivity($user_id, 0, t('activity-newthread', 'commenting', [ SITE_URL, self::getLink($board, $rs['parent_id'], $rs['id'], $rs['thread_id']), self::getTitle($text, 100) ]), 'c');
				}
				/** Alle anderen Comments */
				else {
					Activities::addActivity($user_id, 0, t('activity-newcomment', 'commenting', [ SITE_URL, self::getLink($board, $rs['parent_id'], $rs['id'], $rs['thread_id']), Forum::getBoardTitle($rs['board']), self::getTitle($text, 100) ]), 'c');
				}
			}

			/** 2) Message an alle markierten (@user) senden */
			if(!empty($msg_users) && count($msg_users) > 0)
			{
				foreach ($msg_users as $msg_recipient_id)
				{
					$subject = t('message-newcomment-subject', 'commenting', [$user->id2user($user_id,false)]);
					$text = t('message-newcomment', 'commenting', [ $user->id2user($user_id,true), addslashes(stripslashes($text)), self::getLink($board, $parent_id, $rs['id'], $thread_id) ]);
					$notification_status = $notification->send($msg_recipient_id, 'mentions', ['from_user_id'=>$user_id, 'subject'=>$subject, 'text'=>$text, 'message'=>$text]);
					if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> $notification_status: %s', __METHOD__, __LINE__, ($notification_status == 'true' ? 'true' : 'false')));
				}
			}

			/** 3) Message an alle Subscriber senden */
			$sql = 'SELECT * FROM comments_subscriptions WHERE comment_id=? AND board=?';
			$result = $db->query($sql, __FILE__, __LINE__, __METHOD__, [$parent_id, $board]);
			if($db->num($result) > 0)
			{
				$subject = t('message-newcomment-subscribed-subject', 'subscriptions', [ $user->id2user($user_id,false), $parent_id]);
				$text = t('message-newcomment-subscribed', 'commenting', [ $user->id2user($user_id), self::getLink($rs['board'], $rs['parent_id'], $rs['id'], $rs['thread_id']), addslashes(stripslashes(self::getTitle($rs['text']))) ]);
				while($rs2 = $db->fetch($result))
				{
					$notification_status = $notification->send($rs2['user_id'], 'subscriptions', ['from_user_id'=>BARBARA_HARRIS, 'subject'=>$subject, 'text'=>$text, 'message'=>$text]);
					if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> $notification_status: %s', __METHOD__, __LINE__, ($notification_status == 'true' ? 'true' : 'false')));
				 }
			}

			return $commentlink;

		} else {
			user_error( t('invalid-permissions', 'commenting', [ $board, $thread_id ]), E_USER_WARNING);
			exit;
		}
	}

	/**
	 * Comment update
	 *
	 * Update contents of an existing Comment (e.g. via Comment Edit Form)
	 *
	 * @version 3.0
	 * @since 1.0 `26.11.2018` `IneX` method moved to Comment-Class from /actions/comment_edit.php
	 * @since 2.0 `27.11.2018` `IneX` updated to use new $notifcation Class & some code and query optimizations
	 * @since 3.0 `14.01.2024` `IneX` refactored to no longer use $_POST but passed array()
	 *
	 * @link https://github.com/zorgch/zorg-code/blob/master/www/actions/comment_edit.php Used in Comment-Editing Action
	 * @param integer $comment_id
	 * @param array $comment_data_updated Array containing updated data values for $comment_id
	 * @global object $db Globales Class-Object mit allen MySQL-Methoden
	 * @global object $user Globales Class-Object mit den User-Methoden & Variablen
	 * @global object $notification Globales Class-Object mit allen Notification-Methoden
	 * @return bool
	 */
	static function update($comment_id, $comment_data_updated)
	{
		global $db, $user, $notification;

		$comment_id = filter_var($comment_id, FILTER_VALIDATE_INT) ?? null;
		if (isset($comment_data_updated['board'])) $board = strval($comment_data_updated['board']);
		if (isset($comment_data_updated['parent_it'])) $parent_it = intval($comment_data_updated['parent_it']);
		if (isset($comment_data_updated['thread_id'])) $thread_id = intval($comment_data_updated['thread_id']);
		if (isset($comment_data_updated['msg_users'])) $msg_users = array($comment_data_updated['msg_users']);
		if (isset($comment_data_updated['text'])) $text = strval($comment_data_updated['text']);;

		if ($comment_id > 0)
		{
			$sql = 'UPDATE comments SET text=?, board=?, parent_id=?, thread_id=?, date_edited=? WHERE id=? AND board=?';
			$numUpdatedRows = $db->query($sql, __FILE__, __LINE__, __METHOD__, [$text, $board, $parent_it, $thread_id, timestamp(true), $comment_id, $board]);

			if ($numUpdatedRows > 0)
			{
				/** Smarty Comment Templates neu Kompilieren */
				self::compile_template($thread_id, $comment_id, $board); // sich selbst
				self::compile_template($thread_id, $parent_it, $board); // alter parent
				//self::compile_template($thread_id, $parent_it, $board); // FIXME doppelt: "neuer Parent"

				/** last post setzen */
				$sql = 'UPDATE comments_threads SET last_comment_id=(SELECT MAX(id) FROM comments WHERE thread_id=? AND board=?) WHERE thread_id=?';
				$db->query($sql, __FILE__, __LINE__, __METHOD__, [$thread_id, $board, $thread_id]);

				/** Mark comment as unread for all users (again) */
				self::markasunread($comment_id);

				/** Mark comment as read for this user */
				self::markasread($comment_id, $user->id);

				/** Message an alle gewünschten senden */
				if(count($msg_users) > 0)
				{
					$subject = t('message-commentupdate-subject', 'commenting', [$user->id2user($user->id,true)]);
					$text = t('message-commentupdate', 'commenting', [ $user->id2user($user->id,true), sanitize_userinput($text), self::getLink($board, $parent_it, $comment_id, $thread_id) ]);
					foreach ($msg_users as $msg_recipient_id)
					{
						$notification_status = $notification->send($msg_recipient_id, 'mentions', ['from_user_id'=>$user->id, 'subject'=>$subject, 'text'=>$text, 'message'=>$text, 'to_users' => $msg_users]);
						if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> $notification_status to user_id %d: %s', __METHOD__, __LINE__, $msg_recipient_id, ($notification_status == 'true' ? 'true' : 'false')));
					}
				}
				return true;
			}
			/** Comment was not updated */
			else {
				zorgDebugger::log()->error('Comment could not be updated: %d', [$comment_id]);
				return false;
			}
		/** Invalid $comment_id */
		} else {
			zorgDebugger::log()->error('Invalid or empty $comment_id to update: %d', [$comment_id]);
			return false;
		}
	}
}


/**
 * Forum Class
 *
 * In dieser Klasse befinden sich die Hauptfunktionen zum Forum-System
 * inkl. Boards und Board-Management
 *
 * @version 1.0
 * @since 1.0 `[z]milamber` Class added
 * @since 1.1 `IneX` Code refactorings
 *
 * @package zorg\Forum
 */
class Forum
{
	static function deleteOldTemplates () {
		global $db, $smarty;

		$e = $db->query(
			'SELECT c.id, c.board, c.thread_id FROM comments c, comments_threads ct
			 WHERE c.thread_id=ct.thread_id AND ct.last_seen!=?
			 AND UNIX_TIMESTAMP(?)-UNIX_TIMESTAMP(ct.last_seen) > (60*60*24*?)',
			__FILE__, __LINE__, __METHOD__, ["0000-00-00", timestamp(true), THREAD_TPL_TIMEOUT]);
		$anz = 0;
		while ($d = $db->fetch($e)) {
			$anz++;
			if ($d['board']=='f' && $d['id']==1) {
				$smarty->clear_compiled_tpl('comments:'.$d['thread_id']);
			}elseif ($d['board'] != 'f' && $d['thread_id']==$d['id']) {
				$smarty->clear_compiled_tpl('comments:'.$d['board'].'-'.$d['id']);
			}else{
				$smarty->clear_compiled_tpl('comments:'.$d['id']);
			}
		}

		return $anz;
	}

	/**
	 * Forum Comments Color-faded background-effect
	 *
	 * @author [z]biko
	 * @version 1.0
	 * @since 1.0 method added
	 *
	 * @param int $depth Aktuelle Comment-Position (Tiefe im Thread) um die Color-Fade Abstufung dafür zu berechnen
	 * @param int $color Start Color von welcher aus der Color-Fade berechnet werden soll
	 * @return string "nnnnnn" Color Code (achtung: ohne leading #!)
	 */
	static function colorfade($depth, $color)
	{
		if (substr($color,0,1) == '#') $color = substr($color, 1);

		/** Color-Fade Einstellungen */
		$coloroffset = 17;
		$mincolorvalue = 10;
		$maxcolorvalue = 230;

		/** Farben aus rgb String herauslesen */
		$r = hexdec(substr($color, 0, 2)); // red
		$g = hexdec(substr($color, 2, 2)); // green
		$b = hexdec(substr($color, 4, 2)); // blue

		/** $depth umwandeln in -4 bis +4 */
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

		/** Farben heller/dünkler machen */
		$r = $r + $depthoffset * $coloroffset;
		$g = $g + $depthoffset * $coloroffset;
		$b = $b + $depthoffset * $coloroffset;

		/** Farben werden max. $maxcolorvalue */
		$r = min($r, $maxcolorvalue);
		$g = min($g, $maxcolorvalue);
		$b = min($b, $maxcolorvalue);

		/** Farben werden min. $mincolorvalue */
		$r = max($r, $mincolorvalue);
		$g = max($g, $mincolorvalue);
		$b = max($b, $mincolorvalue);

		return sprintf("%02X%02X%02X", $r, $g, $b);
	}

	/**
	 * Print Forum Boards
	 *
	 * @author [z]biko
	 * @author IneX
	 * @version 2.5
	 * @since 1.0 method added
	 * @since 2.0 `30.09.2018` `IneX` markup extracted into Smarty-Template 'forum_boards.tpl'
	 * @since 2.5 `18.04.2020` `IneX` added option to only show selected Boards (without active checkbox)
	 *
	 * @link https://github.com/zorgch/zorg-code/blob/master/www/actions/forum_setboards.php Um ausgewählte Boards via /actions/forum_setboards.php zu aktualisieren
	 * @link https://github.com/zorgch/zorg-code/blob/master/www/templates/layout/partials/forum/forum_boards.tpl forum_boards.tpl Cached output to Smarty-Template forum_boards.tpl
	 * @link https://github.com/zorgch/zorg-code/blob/master/www/profil.php Display on Profile page in profil.php
	 * @see Forum::getForumBoardsShown()
	 * @param array $selected_boards_array Array with Forum-Board IDs to set 'checked' in HTML-Markup
	 * @param string|bool $update_mode 'set_show_boards' = Board-Selektion anpassen; 'set_unread_boards' = Unread subscriptions ändern; Default: false
	 * @global object $db Globales Class-Object mit allen MySQL-Methoden
	 * @global object $smarty Globales Class-Object mit allen Smarty-Methoden
	 * @return string HTML-Markup fetched from Smarty-Template
	 */
	static function getForumBoards($selected_boards_array=null, $update_mode=false)
	{
		global $db, $smarty;

		/** Validate passed $selected_boards_array */
		if (!empty($selected_boards_array) && !is_array($selected_boards_array)) return false;

		$sql = 'SELECT * FROM comments_boards ORDER BY title';
		$result = $db->query($sql, __FILE__, __LINE__, __METHOD__);
		if (!empty($result) && $result !== false)
		{
			while ($forumBoard = $db->fetch($result)) $boards[] = $forumBoard;
			$smarty->assign('boards', $boards);
			$smarty->assign('boards_checked', $selected_boards_array);
			if ($update_mode === 'threads') $smarty->assign('do', 'set_show_boards'); // for on Forum Overview
			elseif ($update_mode === 'unreads') $smarty->assign('do', 'set_unread_boards'); // for on User's Edit Profile
			else $smarty->assign('do', 'disable'); // only show subscribed boards, no selection possible
			return $smarty->fetch('file:layout/partials/forum/forum_boards.tpl');
		} else {
			return false;
		}
	}

	/**
	 * Print Forum Boards
	 *
	 * @deprecated Merged with and replaced by Forum::getForumBoards()
	 *
	 * @author [z]biko
	 * @version 1.0
	 * @since 1.0 method added
	 * @uses Forum::getForumBoards()
	 */
	static function getFormBoardsShown($show)
	{
		global $db;

		$html = '<table cellpadding="0" cellspacing="0"><tr><td>';
		$html .= Forum::getForumBoards($show, 'threads');
		$html .= '</td></tr></table>';

		return $html;
	}

	/**
	 * Board Titel ausgeben
	 * Query für den Board Titel
	 *
	 * @author IneX
	 * @date 16.03.2008
	 * @version 2.0
	 * @since 1.0 `16.03.2008` method added
	 * @since 2.0 `30.09.2018` Code cleanup
	 *
	 * @param string $board Board ID to lookup full Title for
	 * @return string Board-Title
	 */
	static function getBoardTitle($board)
	{
		global $db;

		/** Validate passed $board */
		if (empty($board) || is_numeric($board) || is_array($board)) return false;

		$sql = 'SELECT title FROM comments_boards WHERE board=? LIMIT 1';
		$rs = $db->fetch($db->query($sql, __FILE__, __LINE__, __METHOD__, [$board]));
		return (isset($rs['title']) ? $rs['title'] : '');
	}


	/**
	 * Form for editing posts
	 *
	 * // TODO merge Forum::getFormEdit() into /templates/layout/partials/commentform.tpl
	 *
	 * @version 2.1
	 * @since 1.0 Method added
	 * @since 2.0 `IneX` Code adjustements
	 * @since 2.1 `16.01.2024` `IneX` Bug #690 : Delete & Update Buttons Seite wechseln
	 *
	 * @param int $comment_id
	 * @return string
	 *
	 */
	static function getFormEdit($comment_id) {
	  global $user;

	  if(!is_numeric($comment_id) || $comment_id<=0) user_error( t('invalid-comment_id', 'commenting'), E_USER_WARNING);
	  $passedBase64encodedUrl = filter_input(INPUT_GET, 'url', FILTER_DEFAULT, FILTER_REQUIRE_SCALAR) ?? '/forum.php'.$comment_id;

	  $rs = Comment::getRecordset($comment_id);

	  $html = '<br>
	    <a name="edit"></a><h2>Comment #'.$comment_id.' bearbeiten</h2>
	    <form name="commentform" action="/actions/comment_edit.php" method="post">
	    <input type="hidden" name="action" value="update">
	  	<input type="hidden" name="url" value="'.$passedBase64encodedUrl.'">
	  	<input name="thread_id" type="hidden" value="'.intval($rs['thread_id']).'">
	    <input type="hidden" name="id" value="'.$comment_id.'">
	  	<input class="text" name="board" type="hidden" value="'.strval($rs['board']).'">
	    <table width="'.FORUMWIDTH.'" class="border" align="center">
	    <tr><td align="left" colspan="6">
	    <textarea name="text" cols="80" rows="20" class="text">'
	  	.htmlentities($rs['text'])
	  	.'</textarea>'
	    .'</td>'
	    .'<td align="left" valign="top">Benachrichtigen:<br>'
	    .$user->getFormFieldUserlist('msg_users[]', 20).'</td>'
	  	.'</tr>';
	if(Comment::getNumchildposts($rs['board'], $comment_id) < 1)
	{
		$html .= '<table cellpadding="0" cellspacing="0">
					<form action="/actions/comment_delete.php" method="post">
						<input type="hidden" name="url" value="'.$passedBase64encodedUrl.'">
						<input type="hidden" name="id" value="'.$comment_id.'">
					<tr><td>
						<input type="submit" value="Delete" class="button">
					</td></tr>
					</form>
				</table>';
	}
	$html .= '<tr><td align="left" valign="top">
	  	<td align="left">
			Parent
			</td><td>
			<input class="text" name="parent_id" type="text" value="'.intval($rs['parent_id']).'">
	  	</td>
	  	</form>
	    </td><td align="right">
			<input type="submit" name="submit" value="Update" class="button">
	    </td></tr></table>
	    <br>';

	  return $html;
	}

	/**
	 * Start Ausgabe Commentform Form HTML-Tag
	 *
	 * Neu als Smarty-Template "/templates/layout/partials/commentform.tpl" verfügbar!
	 * Usage im Smarty Template: {include file='file:layout/partials/commentform.tpl'}
	 *
	 * @deprecated
	 * @author unknown
	 * @used-by self::printCommentingSystem()
	 */
	static function getFormNewPart1of2() {
		return '<form action="/actions/comment_new.php" method="post" name="commentform">';
	}

	/**
	 * Ausgabe Commentforms HTML
	 *
	 * @deprecated
	 * @author unknown
	 * @used-by self::printCommentingSystem()
	 */
	/*
	static function getFormNewPart2of2($board, $thread_id, $parent_id) {
	  return
	  	"\n"
	  	.'<br>'
	    .$start_form
	  	.'<a name="reply"></a>'
	    .'<input type="hidden" name="action" value="new">'
	  	.'<input type="hidden" name="url" value="'.base64url_encode($_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING']).'">'
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
	    .'<br>ä = <a href="javascript:addsymbol(\'&amp;auml;\');">&amp;auml;</a>'
	    .'<br>ö = <a href="javascript:addsymbol(\'&amp;ouml;\');">&amp;ouml;</a>'
	    .'<br>ü = <a href="javascript:addsymbol(\'&amp;uuml;\');">&amp;uuml;</a>'
	    .'<br>& = <a href="javascript:addsymbol(\'&amp;amp;\');">&amp;amp;</a>'
	    .'<br>&lt; = <a href="javascript:addsymbol(\'&amp;lt;\');">&amp;lt;</a>'
	    .'<br>&gt; = <a href="javascript:addsymbol(\'&amp;gt;\');">&amp;gt;</a>'
	    .'</nobr></small></td>'
	    .'<td align="left" valign="top">Benachrichtigen:<br>'
	    .usersystem::getFormFieldUserlist('msg_users[]', 20).'</td>'
	    .'</tr><tr><td align="left" colspan="2">'
	    .'<input class="button" name="submit" tabindex="2" type="submit" value="Erstellen">'
	    .'</td></tr></table>'
	    .'</form>'
	    .'<br>'
	  ;
	}
	 */

	/**
	 * gibt das HTML des Readallforms zurück
	 *
	 * @TODO HTML => Smarty-Template & return with $smarty->fetch()...
	 * @return String
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
	 * gibt das HTML des Searchformszurück
	 *
	 * @TODO HTML => Smarty-Template & return with $smarty->fetch()...
	 * @return String
 	 */
	static function getFormSearch($searchText = null)
	{
		return
			'<table>'
			.'<form action="'.$_SERVER['PHP_SELF'].'" method="get">'
				.'<input name="layout" type="hidden" value="search">'
				.'<tr>'
					.'<td align="left">'
					.'<input type="text" name="keyword" class="text" style="width: 120px;"'.(!empty($searchText) ? ' value="'.$searchText.'"' : null).'>'
					.'<input type="submit" value="search" class="button">'
				.'</td></tr>'
			.'</form>'
			.'</table>'
		;
	}

	/**
	 * Total Anzahl unread Comments eines Users
	 *
	 * @version 2.0
	 * @since 1.0 method added
	 * @since 2.0 `28.08.2019` `IneX` Code and SQL-query optimized
	 *
	 * @param int $user_id User-ID for whom to get unread Comments
	 * @global object $db Globales Class-Object mit allen MySQL-Methoden
	 * @global object $user Globales Class-Object mit den User-Methoden & Variablen
	 * @return integer|null|bool Anzahl laut count(*) aller unread Comments der $user_id - oder false bei error
	 */
	static function getNumunreadposts($user_id)
	{
		global $db, $user;

		if (empty($user_id) || is_array($user_id)) return false;

		if(defined('USER_USER') && $user->typ >= USER_USER)
		{
			$sql = 'SELECT count(*) as numunread from comments_unread where user_id='.$user_id;
			$rs = $db->fetch($db->query($sql, __FILE__, __LINE__, __METHOD__));
			return $rs['numunread'];
		} else {
			return false;
		}
	}

	/**
	 * Link zum letzten unread Comment ausgeben
	 *
	 * @author [z]biko
	 * @version 1.0
	 * @since 1.0 method added
	 *
	 * @used-by Comment::getNumunreadposts()
	 * @global object $db Globales Class-Object mit allen MySQL-Methoden
	 * @global object $user Globales Class-Object mit den User-Methoden & Variablen
	 * @return string HTML-Link zum ältesten ungelesenen Comment
	 */
	static function getUnreadLink() {
		global $db, $user;

		$sql = 'SELECT
					comments.*
					, IF(ISNULL(comments_unread.comment_id), 0, 1) AS isunread
					, UNIX_TIMESTAMP(comments.date) as date
					, user.clan_tag
					, user.username
				FROM comments
					LEFT JOIN user on comments.user_id = user.id
					LEFT JOIN comments_unread ON (comments.id=comments_unread.comment_id AND comments_unread.user_id=?)
				WHERE comments_unread.comment_id IS NOT NULL
				ORDER by date ASC LIMIT 0,1';
	  	$rs2 = $db->fetch($db->query($sql, __FILE__, __LINE__, __METHOD__, [$user->id]));
	  	return Comment::getLink($rs2['board'], $rs2['parent_id'], $rs2['id'], $rs2['thread_id']);
	}

	/**
	 * Holt den letzten Kommentar eines Threads
	 * @TODO HTML => Smarty-Template & return with $smarty->fetch()...
	 * @param $thread_id int
	 * @return Array
	 */
	static function getLastComment()
	{
		global $db;
		$sql = 'SELECT user.clan_tag, user.username, comments.*, UNIX_TIMESTAMP(date) as date
				FROM comments LEFT JOIN user ON comments.user_id=user.id ORDER BY date DESC LIMIT 1';
		$result = $db->query($sql, __FILE__, __LINE__, __METHOD__);
		$rs = $db->fetch($result);
		return $rs;
	}

	static function getNavigation($page=1, $pagesize, $numpages) {
		$html = '<table bgcolor="'.TABLEBACKGROUNDCOLOR.'" cellspacing="1" cellpadding="1" class="border small">'
			.'<tr><td class="hide-mobile">Page '.$page.' von '.$numpages.'</td>';

		if($page > 10) {
			$html .= '<td><a href="'.getChangedURL('page=1').'">&larrb; First</a></td>';
		}

		if($page > 1) {
			$html .= '<td><a href="'.getChangedURL('page='.($page-1)).'">&lt;</a></td>';
		}

		for($i = max(($page - 10), 1); $i <= min(($page + 10), $numpages); $i++) {
			if($page == $i) {
				$html .= '<td>'.$i.'</td>';
			} else {
				$html .= '<td class="hide-mobile"><a href="'.getChangedURL('page='.$i).'">'.$i.'</a></td>';
			}

		}

		if($page < $numpages) {
			$html .= '<td><a href="'.getChangedURL('page='.($page+1)).'">&gt;</a></td>';
		}

		if($page < ($numpages-10)) {
			$html .= '<td><a href="'.getChangedURL('page='.$numpages).'">Last &rarrb;</a></td>';
		}

		$html .= '</tr></table>';

		return $html;
	}

	static function getQueryString($qstr='') {
		$qstr .= (!strstr($qstr, 'page') && $_GET['page'] != '' ? '&page='.$_GET['page'] : '');
		$qstr .= (!strstr($qstr, 'order') && $_GET['order'] != '' ? '&order='.$_GET['order'] : '');
		$qstr .= (!strstr($qstr, 'direction') && $_GET['direction'] != '' ? '&direction='.$_GET['direction'] : '');
		return $qstr;
	}

	/**
	 * Forum Comment Search results
	 *
	 * @TODO implement $keyword highlighting in ouput via $smarty->display()
	 *
	 * @version 2.1
	 * @since 1.0 Method added
	 * @since 2.0 `07.03.2020` `IneX` Code optimizations
	 * @since 2.1 `14.06.2023` `IneX` SQL-Query optimizations
	 *
	 * @param string $keyword Search-Text for LIKE %...% search
	 * @return void
	 */
	static function printSearchedComments($keyword)
	{
		global $db, $smarty;

		$sql = 'SELECT id, text, UNIX_TIMESTAMP(date) as date
				FROM comments
				WHERE text LIKE CONCAT("%", ?, "%")
				ORDER by date DESC';
		$result = $db->query($sql, __FILE__, __LINE__, __METHOD__, [$keyword]);
		$num = $db->num($result);
		if ($num > 0)
		{
			$smarty->assign('comments_no_childposts', 1);
			while($rs = $db->fetch($result))
			{
		    	$smarty->display('comments:'.$rs['id']);
			}
		} else {
			echo t('error-search-noresult', 'commenting', [(string)$keyword]);
		}
	}

	/**
	 * Latest Comments
	 * Gibt eine Tabelle mit Links zu den letzten Comments
	 * @TODO HTML => Smarty-Template & return with $smarty->fetch()...
	 * @return String
	 */
	static function getLatestComments($num=10, $title = '', $board = '')
	{
		global $db, $user;

		$limit = $num ? $num : 10;
		$where_board = ( $board ? 'comments.board=?' : '' );

	    //beschränkt auf 365 tage, da sonst unglaublich lahm
		$sql ='SELECT
					comments.*,
					IF(ISNULL(comments_unread.comment_id), 0, 1) AS isunread,
					UNIX_TIMESTAMP(date) as date
				FROM comments
					LEFT JOIN user
						ON comments.user_id = user.id
					LEFT JOIN comments_threads ct
						ON ct.thread_id = comments.thread_id
						AND ct.board = comments.board
					LEFT JOIN comments_threads_rights ctr
						ON ctr.thread_id = comments.thread_id
						AND ctr.board = comments.board
						AND ctr.user_id = ?
					LEFT JOIN comments_unread
						ON (comments.id=comments_unread.comment_id
						AND comments_unread.user_id=?)
				WHERE '.( !empty($where_board) ? $where_board.' AND ' : '')
					.'(user.usertype >= ct.rights OR ct.rights=? AND ctr.user_id IS NOT NULL)
					AND DATEDIFF(now(), date) < 365
				ORDER BY date desc LIMIT ?';

		$params = [$user->id, $user->id];
		if ($board) array_unshift($params, $board);
		$params[] = USER_SPECIAL;
		$params[] = $limit;
		$result = $db->query($sql, __FILE__, __LINE__, __METHOD__, $params);

		$i=0;
		$html = '<table class="border" width="100%"><tr><td align="center" colspan="4"><b>'
				.($title == '' ? 'neuste Kommentare' : $title)
				.'</b></td></tr>';
		while($rs = $db->fetch($result))
		{
			$i++;
				if(defined('USER_USER') && $user->typ >= USER_USER && $rs['isunread'] == true) {
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
			//.usersystem::userpagelink($rs['user_id'], $rs['clan_tag'], $rs['username']) @deprecated
			.$user->userprofile_link($rs['user_id'], ['link' => TRUE, 'username' => TRUE, 'clantag' => TRUE])
			.'</td><td align="left" bgcolor="'.$color.'" class="small">'
			.datename($rs['date'])
			.'</td><td align="left" bgcolor="'.$color.'" class="small">'
			.Comment::getLinkThread($rs['board'], $rs['thread_id'])
			.'</td></tr>';
	  }
	  $html .= '</table>';

	  return $html;
	}


	/**
	 * Latest Comments for a specific User
	 * Gibt eine Tabelle mit Links zu den letzten Comments eines Users
	 *
	 * @TODO HTML => Smarty-Template & return with $smarty->fetch()...
	 * @TODO "LIMIT" dynamisch machen: via Method Parameter mit default value=7
	 *
	 * @version 2.1
	 * @since 1.0 method added
	 * @since 2.0 `09.09.2019` `IneX` updated code & html output
	 * @since 2.1 `04.12.2020` `IneX` Fixed PHP Notice: Undefined property: usersystem::$id
	 *
	 * @param int $user_id User-ID for whom to show latest Comments
	 * @return string HTML-Code
	 */
	static function getLatestCommentsbyUser($user_id)
	{
		global $db, $user;

		/** For guests (no unread check) */
		if (!$user->is_loggedin())
		{
			$sql = 'SELECT comments.*, UNIX_TIMESTAMP(date) as date
					FROM comments
						LEFT JOIN comments_threads ct ON ct.thread_id=comments.thread_id AND ct.board=comments.board
						LEFT JOIN comments_threads_rights ctr ON ctr.thread_id=comments.thread_id AND ctr.board=comments.board AND ctr.user_id='.$user_id.'
						LEFT JOIN user u ON u.id='.$user_id.'
					WHERE comments.user_id = '.$user_id.'
						AND (u.usertype >= ct.rights OR ct.rights='.USER_SPECIAL.' AND ctr.user_id IS NOT NULL)
					ORDER BY date DESC
					LIMIT 0,7';
		}
		/** For logged in users (check if user comment is unread) */
		else {
			$sql = 'SELECT comments.*, comments_unread.user_id as isunread, UNIX_TIMESTAMP(date) as date
					FROM comments
						LEFT JOIN comments_unread ON (comments.id=comments_unread.comment_id AND comments_unread.user_id = '.$user->id.')
						LEFT JOIN comments_threads ct ON ct.thread_id=comments.thread_id AND ct.board=comments.board
						LEFT JOIN comments_threads_rights ctr ON ctr.thread_id=comments.thread_id AND ctr.board=comments.board AND ctr.user_id='.$user->id.'
						LEFT JOIN user u ON u.id='.$user->id.'
					WHERE comments.user_id = '.$user_id.'
						 AND (u.usertype >= ct.rights OR ct.rights='.USER_SPECIAL.' AND ctr.user_id IS NOT NULL)
					ORDER BY date DESC
					LIMIT 0,7';
		}
		$result = $db->query($sql, __FILE__, __LINE__, __METHOD__);

		$html = '<h4>Letzte Posts</h4>';
		$html .= '<table class="border" width="100%">';
		$i = 0;
		while($rs = $db->fetch($result))
		{
			$i++;
			if(defined('USER_USER') && $user->typ >= USER_USER && $rs['isunread'] != '') {
				$color = NEWCOMMENTCOLOR;
			} else {
				$color = ($i % 2 == 0) ? BACKGROUNDCOLOR : TABLEBACKGROUNDCOLOR;
			}

			$html .=
				'<tr class="small"><td align="left" bgcolor="'.$color.'" width="40%">'
				.'&laquo;<a href="'.Comment::getLink($rs['board'], $rs['parent_id'], $rs['id'], $rs['thread_id']).'" name="'.$rs['id'].'">'
				.Comment::getTitle($rs['text'])
				.'</a>&raquo;<br>'
				.'<span class="tiny">in '.Comment::getLinkThread($rs['board'], $rs['thread_id']).'</span>'
				.'</td><td align="center" bgcolor="'.$color.'" class="small" width="20%">'
				.timename($rs['date'])
				.'</tr>';
		}
	  $html .= '</table>';

	  return $html;
	}


	static function getLatestThreads($num=8) {
		global $db, $user;
		$sql = 'SELECT comments.*, comments_unread.user_id as isunread, UNIX_TIMESTAMP(date) as date, user.clan_tag, user.username
				FROM comments
					LEFT JOIN user ON comments.user_id = user.id
					LEFT JOIN comments_unread ON (comments.id=comments_unread.comment_id AND comments_unread.user_id = comments.user_id)
				WHERE parent_id = 1 ORDER BY date DESC LIMIT ?';
		$result = $db->query($sql, __FILE__, __LINE__, __METHOD__, [$num]);

		$i=0;
		$html = '<table class="border" width="100%"><tr><td align="center" colspan="3"><b>neuste Threads</b></td></tr>';
		while($rs = $db->fetch($result))
		{
	    	$i++;
			if(defined('USER_USER') && $user->typ >= USER_USER && $rs['isunread'] != '') {
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
			.$user->userpagelink($rs['user_id'], $rs['clan_tag'], $rs['username'])
			.'</td><td align="center" bgcolor="'.$color.'" class="small" width="30%">'
			.datename($rs['date'])
			.'</td></tr>'
			;
	  }
	  $html .= '</table>';

	  return $html;
	}

	/**
	 * Gibt eine Tabelle mit den letzten ungelesenen Kommentaren zurück
	 *
	 * @return String
	 */
	static function getLatestUnreadComments($title="", $board="") {
		global $db, $user;

		$sqlparams = [];
		if (!$title) $title = "ungelesene Kommentare";
		if ($board) {
			$whereboard = 'AND comments.board=?';
			$sqlparams[] = $board;
		}

		if(defined('USER_USER') && $user->typ >= USER_USER)
		{
			$sqlparams[] = $user->id;
			$sql = 'SELECT comments.*, IF(ISNULL(comments_unread.comment_id), 0, 1) AS isunread, UNIX_TIMESTAMP(comments.date) AS date, user.clan_tag, user.username FROM comments LEFT JOIN user ON comments.user_id=user.id LEFT JOIN comments_unread ON (comments.id=comments_unread.comment_id AND comments_unread.user_id=?) WHERE comments_unread.comment_id IS NOT NULL '.$whereboard.' ORDER BY date ASC LIMIT 5';
			$result = $db->query($sql, __FILE__, __LINE__, __METHOD__, $sqlparams);

			if($db->num($result) > 0)
			{
				$i=0;
				$html = '<table class="border small" width="100%"><tr><td align="center" colspan="3"><b>'.$title.'</b></td></tr>';
				while($rs = $db->fetch($result))
				{
			   		$i++;

					if(defined('USER_USER') && $user->typ >= USER_USER && $rs['isunread'] != '') {
						$color = NEWCOMMENTCOLOR;
					} else {
						$color = ($i % 2 == 0) ? BACKGROUNDCOLOR : TABLEBACKGROUNDCOLOR;
					}

					$html .= '<tr><td align="left" bgcolor="'.$color.'" width="40%">'
								.'<a href="'.Comment::getLink($rs['board'], $rs['parent_id'], $rs['id'], $rs['thread_id']).'" name="'.$rs['id'].'">'
								.Comment::getTitle($rs['text'])
								.'</a>'
							.'</td><td align="left" bgcolor="'.$color.'" width="30%">'
								.$user->userpagelink($rs['user_id'], $rs['clan_tag'], $rs['username'])
							.'</td><td align="center" bgcolor="'.$color.'" width="30%">'
								.datename($rs['date'])
							.'</td></tr>';
			  }
			  $html .= '</table>';
			}
		}

	  return $html;
	}

	/**
	 * Gibt eine Tabelle mit Threads zurück, welche genau vor 3 Jahren erstellt wurden
	 *
	 * @autor Grischa Ebinger
	 * @date 2004-02-08
	 * @return String
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
		$result = $db->query($sql, __FILE__, __LINE__, __METHOD__);

		$i=0;
		$html = '<table class="border" width="100%"><tr><td align="center" colspan="3"><b>Jaja, früher...</b></td></tr>';
		while($rs = $db->fetch($result))
		{
	    	$i++;
			if(defined('USER_USER') && $user->typ >= USER_USER && $rs['isunread'] != '') {
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
			.$user->userpagelink($rs['user_id'], $rs['clan_tag'], $rs['username'])
			.'</td><td align="center" bgcolor="'.$color.'" class="small" width="30%">'
			.datename($rs['date'])
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
		if($_GET['order'] === $order) {
			$direction = ($_GET['direction'] === 'asc') ? 'desc' : 'asc';
			return '
				<a href="'.htmlentities($_SERVER['PHP_SELF']).'?sortby='.$order.'&direction='.$direction.'">
				<img border="0" src="/images/forum/sort'.$direction.'.gif"></a>
			';
		} else {
			return '';
		}
	}

	/**
	 * Gibt das HTML des Forums zurück
	 *
	 * @author [z]biko
	 * @author IneX
	 * @version 3.1
	 * @since 1.0 method added
	 * @since 2.0 `07.11.2018` `IneX` code optimizations, fixed $sql-Query for Thread list for not-loggedin Users
	 * @since 3.0 `05.12.2018` `IneX` fixed and restored Thread-Overview Pagination
	 * @since 3.1 `25.07.2019` `IneX` fixed Bug #774: In der Forumthreads-Übersicht wird ein falscher "Thread starter" angezeigt
	 *
	 * @used-by Forum::getNavigation()
	 * @param array|string $showboards Array mit den Boards für welche die Threads angezeigt werden sollen
	 * @param integer $pagesize Die Anzahl Threads welche pro Page aufgelistet werden sollen
	 * @param string $sortby Anweisung nach welcher Spalte (Thread DB-Column) die Threads sortiert werden - default: last_post_date
	 * @global object $db Globales Class-Object mit allen MySQL-Methoden
	 * @global object $user Globales Class-Object mit den User-Methoden & Variablen
	 * @return string
 	 */
	static function getHTML($showboards, $pagesize, $sortby='last_post_date')
	{
		global $db, $user;

		/** Boards als komma-separierte Liste */
		if (is_array($showboards)) $showboards_commaseparated = sprintf('"%s"', implode('","', $showboards));
		else $showboards_commaseparated = '"'.$showboards.'"';

		/** Sortieren */
		if(empty($sortby) || is_numeric($sortby) || is_array($sortby)) $sortby = 'last_post_date';

		/**
		 * "ASC"-Sortierung ist nur bei Nummern oder Datum erlaubt, nicht bei Text
		 * ...prüfen, ob wir eine numerische/datum Spalte sortieren wollen */
		$order = 'DESC';
		$new_order = 'ASC';
		if (strpos($sortby,'_id') > 0 || strpos($sortby,'date') > 0 || strpos($sortby,'num') > 0)
		{
			if(isset($_GET['order'])) {
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
			}
		}

		/** Threads analog ?page=n anzeigen... */
		$page = (!isset($_GET['page']) || empty($_GET['page']) || !is_numeric($_GET['page'])) ? 1 : $_GET['page'];
		$limit = ($page-1) * $pagesize.','.$pagesize;

		/** Query for Thread list */
		$sql = 'SELECT
					c.board board,
					max(c.id) id,
					max(c.parent_id) parent_id,
					c.text last_post_text,
					max(c.user_id) last_comment_poster,
					UNIX_TIMESTAMP(c.date) last_post_date,
					max(t.thread_id) thread_id,
					max(t.user_id) thread_starter,
					UNIX_TIMESTAMP(t.date) thread_date,
					'.($user->is_loggedin() ? 'IF(ISNULL(max(tfav.thread_id) ), 0, 1) isfavorite,
					IF(ISNULL(max(tignore.thread_id)), 0, 1) ignoreit,' : '').'
					count(DISTINCT cnum.id) numposts,
					(SELECT count(DISTINCT thread_id) FROM comments WHERE board IN ('.$showboards_commaseparated.')) numthreads
				FROM
					comments_threads ct
				LEFT JOIN comments c ON (c.id = (SELECT MAX(id) FROM comments WHERE thread_id = ct.thread_id AND board = ct.board) )
				LEFT JOIN comments t ON (t.id = ct.comment_id)
				LEFT JOIN comments cnum ON (ct.board = cnum.board AND ct.thread_id = cnum.thread_id)
				'.($user->is_loggedin() ? 'LEFT JOIN comments_threads_rights ctr
					ON (ctr.thread_id=ct.thread_id AND ctr.board=ct.board AND ctr.user_id='.$user->id.')
				LEFT JOIN comments_threads_favorites tfav
					ON (tfav.board = ct.board AND tfav.thread_id = ct.thread_id AND tfav.user_id='.$user->id.')
				LEFT JOIN comments_threads_ignore tignore
					ON (tignore.board = ct.board AND tignore.thread_id = ct.thread_id AND tignore.user_id='.$user->id.')
				' : '').'
				WHERE
					 c.board IN ('.$showboards_commaseparated.')
					 AND ('.$user->typ.' >= ct.rights OR ct.rights='.USER_SPECIAL . ($user->is_loggedin() ? ' AND ctr.user_id IS NOT NULL' : '').')
					 AND ct.comment_id IS NOT NULL
				GROUP BY
					c.board, ct.thread_id, t.date, c.date, c.text
				ORDER BY '.$sortby.' '.$order.'
				LIMIT '.$limit
		;
		$result = $db->query($sql, __FILE__, __LINE__, __METHOD__);

		/** Ausgabe ---------------------------------------------------------------- */
		/** Thread-Table mit Spaltenüberschriften */
		$html =
			'<h1>Discussions</h1>'
			.'<table cellpadding="1" cellspacing="1" class="border" width="100%">'
				.'<!--googleoff: all--><tr class="title">'
					.'<td align="left" width="30%"><a href="?sortby=t.text&amp;order='.$new_order.'">Thread</a></td>'
					.'<td align="left" class="small hide-mobile" width="11%"><a href="?sortby=tu_username&amp;order='.$new_order.'">Thread starter</a></td>'
					.'<td align="center" class="hide-mobile"><a href="?sortby=ct.thread_id&amp;order='.$new_order.'">Datum</a></td>'
					.'<td align="center" class="small hide-mobile"><a href="?sortby=numposts&amp;order='.$new_order.'">#</a></td>'
					.'<td align="left" class="small" width="25%"><a href="?sortby=last_post_date&amp;order='.$new_order.'">Last comment</a></td>'
					.'<td class="hide-mobile"></td>'
				.'</tr><!--googleon: all-->';

		$i = 0;
		while(($rs = $db->fetch($result)) && ($i < $pagesize))
		{
			$i++;

			/** Check for unread comments in Thread */
			$thread_has_unread_comments = false;
			if ($user->is_loggedin() && !empty($rs['thread_id']))
			{
				$lastp = Thread::getLastUnreadComment($rs['board'], $rs['thread_id'], $user->id);
				$thread_has_unread_comments = ($lastp ? true : false);
			}

			/** @FIXME move iterative table background colors from PHP => CSS! */
			$color = ($i % 2 == 0) ? BACKGROUNDCOLOR : TABLEBACKGROUNDCOLOR;
			if ($user->is_loggedin() && (int)$rs['thread_starter'] === $user->id) $color = OWNCOMMENTCOLOR;
			if ($user->is_loggedin() && $rs['isfavorite']) $color = FAVCOMMENTCOLOR;
			if ($user->is_loggedin() && $rs['ignoreit']) $color = IGNORECOMMENTCOLOR;
			if ($thread_has_unread_comments === true) $color = NEWCOMMENTCOLOR;

			$html .= '<tr itemscope="" itemtype="http://schema.org/Article">'
					  /*.'<td>'.$rs['sticky'].'</td>'*/
					  .'<td align="left" bgcolor="'.$color.'"><span style="float: left" itemprop="headline">'
					  .Comment::getLinkThread($rs['board'], $rs['thread_id'])
					  .'</span>';

		/** DISABLED
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

		/** alles was jetzt kommt, steht im feld rechtsbündig */
		$html .= '<!--googleoff: all--><span class="threadoptions" style="float: right;font-size: 0.8em;">';

    	if($user->is_loggedin())
    	{
			/** links ganz rechts ausrichten */
			//$html .=	'<span style="float: right">';

				/** Favorite or unfavorite Thread */
				if($rs['isfavorite'] == 1)
				{
	    			$html .=
	    				' <a href="/actions/forum.php?action=unfavorite&board='.$rs['board'].'&thread_id='
	    				.$rs['thread_id'].'">'.t('forum-unfavorite-thread-action', 'commenting').'</a>';
				} else {
					$html .=
						' <a href="/actions/forum.php?action=favorite&board='.$rs['board'].'&thread_id='
						.$rs['thread_id'].'">'.t('forum-favorite-thread-action', 'commenting').'</a>';
				}

			/** Ignore or Unignore Thread */
				if($rs['ignoreit'] == 1)
				{
	    			$html .=
	    				' <a href="/actions/forum.php?action=unignore&board='.$rs['board'].'&thread_id='
	    				.$rs['thread_id'].'">'.t('forum-unignore-thread-action', 'commenting').'</a>';
				} else {
					$html .=
						' <a href="/actions/forum.php?action=ignore&board='.$rs['board'].'&thread_id='
						.$rs['thread_id'].'">'.t('forum-ignore-thread-action', 'commenting').'</a>';
				}

				//$html .=	'&nbsp;&nbsp;&nbsp;</span>';
			}

			/** RSS Feed-Link für Thread anzeigen */
			$feedURLbase = (isset($_ENV['URLPATH_RSS']) ? $_ENV['URLPATH_RSS'] : '');
			$html .=
					' <a href="'.$feedURLbase.'&amp;type=forum&amp;board='.$rs['board'].'&amp;thread_id='
    				.$rs['thread_id'].'" class="hide-mobile">'.t('forum-rss-thread-action', 'commenting').'</a>';

			/** rechtsbündig-span & td-element schliessen */
			$html .= '</span><!--googleon: all-->
					</td>';

			$html .= '<td class="small hide-mobile" style="padding-left: 5px;" align="left" bgcolor="'.$color.'" itemprop="author">'
			  .$user->userprofile_link($rs['thread_starter'], ['link' => TRUE, 'username' => TRUE, 'clantag' => TRUE])
			  .'</td><td class="small center hide-mobile" bgcolor="'.$color.'"><meta itemprop="datePublished" content="'.date('Y-m-d', $rs['thread_date']).'">'
			  .datename($rs['thread_date'])
			  .'</td><td class="small center hide-mobile" bgcolor="'.$color.'">'
			  .$rs['numposts']
			  .'</td><td class="small hide-mobile" align="left" bgcolor="'.$color.'">'
			  .'<a href="'.Comment::getLink($rs['board'], $rs['parent_id'], $rs['id'], $rs['thread_id']).'">'
			  .str_pad(Comment::getSummary($rs['last_post_text']), 25, ' . ', STR_PAD_RIGHT)
			  .'</a>'
			  .' &raquo;</a>'
			  .' by '
			  .'<span itemscope itemtype="http://schema.org/Person">'.$user->userprofile_link($rs['last_comment_poster'], ['link' => TRUE, 'username' => TRUE, 'clantag' => TRUE])
			  .'</td><td class="small center" bgcolor="'.$color.'"><meta itemprop="dateModified" datetime="'.date('c', $rs['last_post_date']).'">'
			  .datename($rs['last_post_date'])
			  .'</td>'
			;
			$html .= '</tr>';

			$numpages = $rs['numthreads'];
		}

		/** Pagination für Thread-Liste */
		$numpages = floor($numpages / $pagesize);
		if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> $numpages (from $rs[numthreads]): %d', __FILE__, __LINE__, $numpages));

		$html .=
		 	'<tr class="title">'
		 	.'<td colspan="6">'

		 	.'<table cellpadding="0" cellspacing="0" width="100%">'

		 	.'<tr>'
		 	.($user->is_loggedin() ? '<td align="left" class="s">'.Forum::getFormSearch().'</td>' : '') // Forum Suche nur noch für eingeloggte User
		 	.'<td align="left">'.(defined('USER_USER') && $user->typ >= USER_USER ? Forum::getFormReadall() : '').'</td>'
			.'<td align="right">'
			.Forum::getNavigation($page, $pagesize, $numpages)
			.'</td>'
			.'</tr>'

			.'<tr>'
			.'<td align="center" colspan="3">'
		 	.($user->is_loggedin() ? Forum::getFormBoardsShown($showboards) : '')
		 	.'</td>'

		 	.'</tr>'
			.'</table>'

			.'</td>'
			.'</tr>'
			.'</table>'
		;
		return $html;
	}

	/**
	 * Checks if User has posted to same Thread recently.
	 *
	 * @version	2.0
	 * @since	1.0 `[z]biko` Method added
	 * @since	2.0 `13.01.2024` `IneX` Code and SQL Query refactored
	 *
	 * @param integer $user_id
	 * @param integer $parent_id
	 * @return bool TRUE=User has just commented | FALSE=User has not (yet) recently commented
	 */
	static function hasPostedRecently($user_id, $parent_id) {
		global $db;

		$pace = 10; // in Seconds
		$sql = 'SELECT date FROM comments WHERE user_id=? AND parent_id=? ORDER BY date DESC LIMIT 1';
		$rs = $db->fetch($db->query($sql, __FILE__, __LINE__, __METHOD__, [$user_id, $parent_id]));
		$next_posttime_threshold = (isset($rs['date']) ? timestamp(false, $rs['date'])+$pace : 0);
		zorgDebugger::log()->debug('Comaprison timestamps: %d %s %d', [$next_posttime_threshold, (timestamp(false)<$next_posttime_threshold ? '>' : '<'), timestamp(false)]);
		return boolval(timestamp(false)<=$next_posttime_threshold);
	}

	/**
	 * Commenting-System ausgeben
	 * Printet das "Pluggable" Commenting-System
	 *
	 * @link https://github.com/zorgch/zorg-code/blob/master/www/templates/layout/partials/commentform.tpl Template used for output is commentform.tpl
	 *
	 * @version	3.2
	 * @since	1.0 `[z]biko` added method
	 * @since	2.0 `17.12.2017` `IneX` Deprecated Forum::getFormNewPart2of2() & 'tpl:194' due to change into a Smary-Template 'file:commentform.tpl'
	 * @since	3.0 `25.07.2018` `IneX` Updated SQL-Queries, Formatting & check for logged in User regarding printing Subscriptions & Unreads
	 * @since	3.1 `22.01.2020` `IneX` Code optimizations
	 * @since	3.2 `22.01.2020` `IneX` Fixed PHP Notice undefined property: usersystem::$id
	 *
	 * @uses USER_USER
	 * @uses usersystem::is_loggedin()
	 * @uses Thread::hasRights()
	 * @uses Thread::setLastSeen()
	 * @global object $db Globales Class-Object mit allen MySQL-Methoden
	 * @global object $user Globales Class-Object mit den User-Methoden & Variablen
	 * @global object $smarty Globales Class-Object mit allen Smarty-Methoden
	 * @param string $board
	 * @param int $thread_id
	 * @var $_GET['parent_id'] directly
	 * @return void
	 */
	static function printCommentingSystem($board, $thread_id)
	{
		global $db, $user, $smarty;

		/** Get and set missing parent_id */
		// FIXME it would be better to pass $_GET[parent_id] als Function Param via {smarty_comments}-Parameter...
		$parent_id = filter_input(INPUT_GET, 'parent_id', FILTER_VALIDATE_INT) ?? intval($thread_id);

		if (true === Thread::hasRights($board, $thread_id, ($user->is_loggedin() ? $user->id : USER_ALLE)))
		{
			/** damit man die älteren kompilierten comments löschen kann (speicherplatz sparen) */
			Thread::setLastSeen($board, $thread_id);

			/** Subscribed_Comments Array Bauen (nur für eingeloggte User) */
			if($user->is_loggedin())
			{
				$comments_subscribed = array();
				$sql = 'SELECT comment_id FROM comments_subscriptions WHERE board=? AND user_id=?';
				$e = $db->query($sql, __FILE__, __LINE__, __METHOD__, [$board, $user->id]);
				while ($d = $db->fetch($e)) $comments_subscribed[] = $d['comment_id'];
				$smarty->assign('comments_subscribed', $comments_subscribed);

				/** Unread Comment Array Bauen */
				$comments_unread = array();
				$sql = 'SELECT u.* FROM comments_unread u, comments c WHERE c.id=u.comment_id AND c.thread_id=? AND c.board=? AND u.user_id=?';
				$e = $db->query($sql, __FILE__, __LINE__, __METHOD__, [$thread_id, $board, $user->id]);
				while ($d = $db->fetch($e)) $comments_unread[] = $d['comment_id'];
				$smarty->assign('comments_unread', $comments_unread);
			}

			/** Wenn $thread_id = NICHT erster (Thread) Comments, sondern Sub-Thread Comment */
			$sql = 'SELECT * FROM comments WHERE board=? AND id=? ORDER BY id ASC';
			$d = $db->fetch($db->query($sql, __FILE__, __LINE__, __METHOD__, [$board, $parent_id]));

			/** Set Thread/Sub-Thread starting Comment-ID */
			$comment_parent_id = (isset($d['parent_id']) && $d['parent_id'] > 0 ? intval($d['parent_id']) : $thread_id);

			/** Comments an Smarty übergeben */
			if ($parent_id === $thread_id || $thread_id === $comment_parent_id)
			{
				/** $thread_id ist der erste Comment (Thread) */
				$fetch_commentthread = sprintf('comments:%s-%d', $board, $thread_id);
				$smarty->display($fetch_commentthread);
			} else {
				/** $thread_id ist ein Sub-Thread (nicht Main Thread) */
				$smarty->assign('comments_top_additional', 1);
				$smarty->display('comments:'.$comment_parent_id);
			}

			/** Wenn User eingeloggt ist, Form für neue Comment(Replies) ausgeben */
	    	if($user->is_loggedin() === true)
	    	{
	    		$smarty->assign('board', $board);
				$smarty->assign('thread_id', $thread_id);
				$smarty->assign('parent_id', $comment_parent_id);
				$smarty->display('file:layout/partials/commentform.tpl');
	    	}
		}
	}

	/**
	 * RSS functionality for zorg Boards
	 *
	 * Gibt einen XML RSS-Feed zurück
	 *
	 * @author IneX
	 * @version 2.1
	 * @since 1.0 `IneX` initial method added
	 * @since 2.0 `20.07.2018` `IneX` Refactored long-running queries, optimized queries and output (e.g. unreads, unnecessary LEFT JOINs, etc.)
	 * @since 2.1 `01.12.2020` `IneX` fixed PHP 7 Uncaught Error: [] operator not supported for strings
	 *
	 * @TODO Param "user_id" can be removed with a refactoring! Doesn't have to be passed & thus Method can be simplified...
	 *
	 * @param string $board Default f (=forum)
	 * @param int $user_id Default null (=nicht eingeloggt)
	 * @param int $thread_id Default null (=kein thread gewählt)
	 * @global object $db Globales Class-Object mit allen MySQL-Methoden
	 * @global object $user Globales Class-Object mit den User-Methoden & Variablen
	 * @return array|bool Returns XML-Feed-Item-Array - or false, if building the feed failed
	 */
	 static function printRSS($board='f', $user_id=null, $thread_id=null)
	 {
	 	global $db, $user;

	 	/** where-board Bedingung für SQL-Query bilden */
		$wboard = ( $board ? 'comments.board=?' : '' );

		/** RSS-Feed items configs */
		$num = 15;		// Anzahl auszugebender Datensätze

		/**
		 * Ausgabe evaluieren und entsprechendes SQL holen
		 */
		$xmlfeed = [];	// Ausgabestring für XML Feed als Array initialisieren
		$sqlparams = [];

		/** nicht eingeloggter User... */
		if (is_null($user_id))
		{
			/** Feed für forum board */
			if ($board === 'f')
			{
				/** keine thread_id übergeben */
				if (is_null($thread_id))
				{
					$sql = 'SELECT comments.*, UNIX_TIMESTAMP(date) as date FROM comments WHERE parent_id=1 ORDER BY date desc LIMIT ?';
					$sqlparams[] = $num;
				}
				/** thread_id vorhanden */
				else {
					$sql ='SELECT comments.*, UNIX_TIMESTAMP(date) as date FROM comments WHERE thread_id=? AND board=? ORDER BY date DESC LIMIT ?';
					$sqlparams[] = $thread_id;
					$sqlparams[] = $board;
					$sqlparams[] = $num;
				}

			/**
			 * RSS Feed-Items für anderes board
			 * Long-running query, wenn LEFT JOIN & WHERE auf comments_threads_rights gemacht wird
			 * @TODO 20.07.2018 Query vereinfacht um SQL query-time von >1.5s auf <200ms zu reduzieren (!) - dafür werden Berechtigungen nicht geprüft. Wird aber eh nicht genutzt, von da her...
			 * @TODO 20.07.2018 Wieso ein LEFT JOIN auf comments_unread wenn der Query für "nicht eingeloggte" User ist? Rausgenommen...
			 */
			}
			else {
				// für den Moment wird hier einfach ein Query über alle neuen Sachen gemacht.... IneX, 16.3.08
				// FIXME erm... aber so wies scheint, kommen die richtigen Sachen (weil alles über s board gesteuert wird). IneX, 16.3.08
				$sql = 'SELECT comments.*, UNIX_TIMESTAMP(date) as date FROM comments '.(!empty($wboard) ? 'WHERE '.$wboard : '').' ORDER BY date desc LIMIT ?';
				if (!empty($wboard)) $sqlparams[] = $board;
				$sqlparams[] = $num;
			}
		}
		/** User ist eingeloggt */
		else {
			/** Feed für forum board */
			if ($board === 'f')
			{
				/** keine thread_id übergeben */
				if (is_null($thread_id))
				{
					$sql = 'SELECT comments.*, IF(ISNULL(comments_unread.comment_id), 0, 1) AS isunread, UNIX_TIMESTAMP(date) as date
							FROM comments LEFT JOIN comments_unread ON (comments.id=comments_unread.comment_id AND comments_unread.user_id = comments.user_id)
							WHERE parent_id = 1 ORDER BY date desc LIMIT ?';
					$sqlparams[] = $num;
				}
				/** thread_id vorhanden */
				else {
					$sql = 'SELECT comments.*, UNIX_TIMESTAMP(date) as date FROM comments WHERE thread_id=? AND board=? ORDER BY date DESC LIMIT ?';
					$sqlparams[] = $thread_id;
					$sqlparams[] = $board;
					$sqlparams[] = $num;
				}
			}
			/** Feed für ein anderes board */
			else {
				// für den Moment wird hier einfach ein Query über alle neuen Sachen gemacht.... IneX, 16.3.08
				// FIXME erm... aber so wies scheint, kommen die richtigen Sachen (weil alles über s board gesteuert wird). IneX, 16.3.08
				$sql ='SELECT comments.*, IF(ISNULL(comments_unread.comment_id), 0, 1) AS isunread, UNIX_TIMESTAMP(date) as date
					FROM comments LEFT JOIN comments_unread ON (comments.id=comments_unread.comment_id AND comments_unread.user_id = ?) '.( !empty($wboard) ? 'WHERE '.$wboard : '').
					' ORDER BY date desc LIMIT ?';
				$sqlparams[] = $user->id;
				if (!empty($wboard)) $sqlparams[] = $board;
				$sqlparams[] = $num;
			}
		} // end if is_null($user_id)

		/**
		 * Feed bauen - Query mit $sql
		 */
		if ($result = $db->query($sql, __FILE__, __LINE__, __METHOD__))
		{
			/** Datensätze auslesen */
			while($rs = $db->fetch($result))
			{
				/** Assign Values */
				$xmlitem_title = (isset($rs['isunread']) && $rs['isunread'] == 1 ? '*unread* ' : '') . ( Comment::isThread($rs['board'], $rs['id']) ? Comment::getTitle($rs['text'], 80) : 'Comment zu '.remove_html(Comment::getLinkThread($rs['board'], Comment::getThreadid($rs['board'], $rs['id']))) );
				$xmlitem_link = str_replace('&', '&amp;amp;', SITE_URL . Comment::getLink($rs['board'], $rs['parent_id'], $rs['id'], $rs['thread_id'])); // &amp;amp; for xml-compatibility
				$xmlitem_pubDate = date('D, d M Y H:i:s', $rs['date']);//.' '.gmt_diff($rs[date]);
				//$xmlitem_author = $rs['clan_tag'].$rs['username']; @deprecated
				$xmlitem_author = $user->id2user($rs['user_id'], true);
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

				/** XML Feed items schreiben */
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
		} // end if $result

		/** Return XML */
		return ( count($xmlfeed) > 0 ? $xmlfeed : false );

	} // end static function printRSS()

} // end class Forum()


/**
 * Thread Class
 *
 * In dieser Klasse befinden sich alle Funktionen zum Thread-System
 *
 * @author [z]milamber
 * @author IneX
 * @version 1.0
 * @package zorg\Forum
 */
class Thread {
	static function setLastSeen ($board, $thread_id) {
		global $db;

		$db->query('UPDATE comments_threads SET last_seen=? WHERE board=? AND thread_id=?',
					__FILE__, __LINE__, __METHOD__, [timestamp(true), $board, $thread_id]);

	}

	static function setRights ($board, $thread_id, $rights) {
		global $db;

		$e = $db->query("SELECT * FROM comments_threads WHERE board=? AND thread_id=?",
						__FILE__, __LINE__, __METHOD__, [$board, $thread_id]);
		$d = $db->fetch($e);
		if (!$d && $rights) {
			$db->query("INSERT INTO comments_threads (board, thread_id) VALUES (?, ?)",
						__FILE__, __LINE__, __METHOD__, [$board, $thread_id]);
		} elseif (!$d && !$rights) {
			return;
		}

		if (!$rights) $rights = '0';

		if (is_array($rights)) $set_right = '3';
		else $set_right = $rights;

		$db->query("DELETE FROM comments_threads_rights WHERE thread_id=?", __FILE__, __LINE__, __METHOD__, [$thread_id]);
		$db->query("UPDATE comments_threads SET rights=? WHERE board=? AND thread_id=?",
					__FILE__, __LINE__, __METHOD__, [$set_right, $board, $thread_id]);
		if (is_array($rights)) {
			foreach ($rights as $it) {
				$db->query("INSERT INTO comments_threads_rights (board, thread_id, user_id) VALUES (?, ?, ?)",
							__FILE__, __LINE__, __METHOD__, [$board, $thread_id, $it]);
			}
		}
	}

	/**
	 * Check a User permission to read a Comment Thread
	 *
	 * @version 1.1
	 * @since 1.1 `27.10.2020` `IneX` Fix MySQL Error 1064 because empty $user_id for guests/not logged in users, adds type casting
	 *
	 * @param string $board
	 * @param int $thread_id
	 * @param int $user_id Default: 0
	 * @return bool
	 */
	static function hasRights ($board, $thread_id, $user_id=0)
	{
		global $db;

		$check_user_id = (isset($user_id) && !empty($user_id) && (int)$user_id > 0 ? (int)$user_id : 0);

		$sql = 'SELECT user.usertype, ct.rights AS thread_rights, IF(ISNULL(ctr.user_id), 0, 1) AS special_rights
				FROM comments_threads ct LEFT JOIN comments_threads_rights ctr ON (ct.thread_id=ctr.thread_id AND ctr.user_id=?)
				LEFT JOIN user ON(user.id=?) WHERE ct.thread_id=? AND ct.board=?';
		$result = $db->query($sql, __FILE__, __LINE__, __METHOD__, [$check_user_id, $check_user_id, $thread_id, $board]);
		$rs = $db->fetch($result);
		/*if (DEVELOPMENT === true) error_log(sprintf('[DEBUG] <%s:%d> Thread::hasRights() empty($rs) => %s', __METHOD__, __LINE__, (empty($rs)?'true':'false')));
		if (DEVELOPMENT === true) error_log(sprintf('[DEBUG] <%s:%d> Thread::hasRights() empty($rs[usertype]) => %s', __METHOD__, __LINE__, (empty($rs['usertype'])?'true':'false')));
		if (DEVELOPMENT === true) error_log(sprintf('[DEBUG] <%s:%d> Thread::hasRights() $rs[thread_rights] === 0 => %s', __METHOD__, __LINE__, ((int)$rs['thread_rights']===0?'true':'false')));
		if (DEVELOPMENT === true) error_log(sprintf('[DEBUG] <%s:%d> Thread::hasRights() $rs[usertype] >= $rs[thread_rights] => %s', __METHOD__, __LINE__, ($rs['usertype'] >= $rs['thread_rights']?'true':'false')));
		if (DEVELOPMENT === true) error_log(sprintf('[DEBUG] <%s:%d> Thread::hasRights() $rs[thread_rights] === USER_SPECIAL => %s', __METHOD__, __LINE__, ($rs['thread_rights']===USER_SPECIAL?'true':'false')));
		if (DEVELOPMENT === true) error_log(sprintf('[DEBUG] <%s:%d> Thread::hasRights() $rs[special_rights] === 1 => %s', __METHOD__, __LINE__, ($rs['special_rights']===1?'true':'false')));*/
		if(
			empty($rs)
			|| (empty($rs['usertype']) && (int)$rs['thread_rights'] === 0)
			|| (int)$rs['usertype'] >= (int)$rs['thread_rights']
			|| ((int)$rs['thread_rights'] === (int)USER_SPECIAL && (int)$rs['special_rights'] === 1)
		) {
			return true;
		}else{
			return false;
		}
	}


	static function adjustThreadRecord($board, $thread_id) {
		global $db;

		if(Thread::hasRecords($board, $thread_id)) {
			$sql = 'SELECT * FROM comments WHERE board=? AND thread_id=? ORDER BY date ASC LIMIT 1';
			$result = $db->query($sql, __FILE__, __LINE__, __METHOD__, [$board, $thread_id]);
			$rs = $db->fetch($result);
			$sql = 'UPDATE comments_threads SET comment_id=? WHERE board=? AND thread_id=?';
			$db->query($sql, __FILE__, __LINE__, __METHOD__, [$rs['id'], $rs['board'], $rs['thread_id']]);
		} else {
			$sql = 'DELETE FROM comments_threads WHERE board=? AND thread_id=?';
			$db->query($sql, __FILE__, __LINE__, __METHOD__, [$board, $thread_id]);
		}
	}

	static function hasRecords($board, $thread_id) {
		global $db;
		$sql = 'SELECT * FROM comments WHERE thread_id=? AND board=?';
		$result = $db->query($sql, __FILE__, __LINE__, __METHOD__, [$thread_id, $board]);

		if($db->fetch($result)) {
			return true;
		} else {
			return false;
		}
	}


	/**
	 * Holt den letzten Kommentar eines Threads
	 *
	 * @return Array
	 * @param string $board
	 * @param int $thread_id
	 */
	static function getLastComment($board, $thread_id) {
		global $db;
		$sql = 'SELECT user.*, comments.*, UNIX_TIMESTAMP(date) as date
				FROM comments LEFT JOIN user ON comments.user_id = user.id
				WHERE thread_id=? AND board=? ORDER BY date DESC LIMIT 1';
	$rs = $db->fetch($db->query($sql, __FILE__, __LINE__, __METHOD__, [$thread_id, $board]));
		return $rs;
	}

	/**
	 * Holt den letzten ungelesenen Kommentar
	 *
	 * @return Array
	 * @param int $thread_id int
 	 */
	static function getLastUnreadComment($board, $thread_id, $user_id)
	{
		global $db;
		$sql = 'SELECT comments.*, UNIX_TIMESTAMP(comments.date) as date
				FROM comments LEFT JOIN comments_unread
					ON (comments.id = comments_unread.comment_id AND comments_unread.user_id=?)
				WHERE
					comments_unread.comment_id is NOT NULL
					AND comments.thread_id=?
					AND comments.board=?
				ORDER by date ASC LIMIT 1';
	  return $db->fetch($db->query($sql, __FILE__, __LINE__, __METHOD__, [$user_id, $thread_id, $board]));
	}

	/**
	 * returns the Thread-Title and its navigation-bar
	 *
	 * @return String
	 * @param int $parent_id int
	 * @param int $thread_id int
	 */
	static function getNavigation($board, $id, $thread_id)
	{
		$html =
			'<table class="border forum" style="table-layout:fixed;" width="100%">'
			.'<tr><td>';

		if($id > $thread_id)
		{
			$tempid = $id;
			$i = 0;
			while($tempid > $thread_id)
			{
				$i++;
				$tempid = Comment::getParentid($tempid);
				if($tempid > $thread_id) {
					$html .= '<a href="'.getChangedURL('parent_id='.$tempid).'">'.$i.'up</a> | ';
				}
			}

			$rs = Thread::getRecordset($board, $thread_id);
			$html .= Comment::getLinkThread($rs['board'], $rs['thread_id']);
			$html .= '</td></tr></table>';

			/** Additional Posts */
			$color = Forum::colorfade(0, TABLEBACKGROUNDCOLOR);
			$html .=
			'<table bgcolor="'.$color.'" class="border forum"  style="table-layout:fixed;" width="100%">'
				.'<tr>'
				.'<td bgcolor="'.$color.'" valign="top"><nobr>'
				.'<a href="'.getChangedURL('parent_id='.Comment::getParentid($id)).'">'
				.'<font size="4">^^^ Additional posts ^^^</font></a>'
			.'</td></tr></table>';

			return $html;
		}
	}

	/**
	 * Get count of total comments per Thread
	 *
	 * @version 1.1
	 * @since 1.0 method added
	 * @since 1.1 `29.08.2019` `IneX` try-catch didn't catch a failed mysql-query, changed it therefore
	 *
	 * @param string $board
	 * @param int $thread_id
	 * @global object $db Globales Class-Object mit allen MySQL-Methoden
	 * @return int|bool Containing the amount of mysql_num_rows fro the queried DB-Result as integer - or false on error
	 */
	static function getNumPosts($board, $thread_id)
	{
		global $db;

		/**
		 * Validate passed parameters
		 */
		if (empty($board) || is_numeric($board) || is_bool($board)) return false;
		if (empty($thread_id) || !is_numeric($thread_id) || $thread_id <= 0) return false;

		$sql = 'SELECT id FROM comments WHERE thread_id=? AND board=?';
		return $db->num($db->query($sql, __FILE__, __LINE__, __METHOD__, [$thread_id, $board]));
	}

	/**
	 * Get count of total read comments
	 *
	 @FIXME tut nicht mehr
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

	/**
	 * Get count of total comment unreads per Thread for a specific user
	 *
	 * @version 1.0
	 * @since 1.0 method added
	 *
	 * @param string $board
	 * @param int $thread_id
	 * @param int $user_id (Optional) User-ID to get unreads for - default: null
	 * @global object $db Globales Class-Object mit allen MySQL-Methoden
	 * @return int|bool Containing the queried count()-Result as integer - or false on error
	 */
	static function getNumUnread ($board, $thread_id, $user_id=null) {
		global $db, $user;

		/** Validate passed parameters */
		if (empty($board) || is_numeric($board) || is_array($board)) return false;
		if (empty($thread_id) || !is_numeric($thread_id) || $thread_id <= 0 || is_array($thread_id)) return false;
		if (is_array($user_id) || !is_numeric($user_id) || $user_id <= 0) return false;
		if (empty($user_id) || $user_id === null) $user_id = $user->id; // $user_id must always be $user->id (current user!)

		$sql = 'SELECT count(c.id) anz FROM comments c, comments_unread u
				WHERE c.board=? AND c.thread_id=? AND u.comment_id=c.id AND u.user_id=?';
		$d = $db->fetch($db->query($sql, __FILE__, __LINE__, __METHOD__, [$board, $thread_id, $user_id]));
		return $d['anz'];
	}

	/**
	 * Fetches a Thread and returns its Recordset
	 *
	 * @version 2.0
	 * @since 1.0 method added
	 * @since 2.0 `06.11.2018` added parameter validation
	 *
	 * @param string $board
	 * @param int $thread_id
	 * @global object $db Globales Class-Object mit allen MySQL-Methoden
	 * @return arr|bool Containing the queried DB-Result record-set - or false on error
	 */
	static function getRecordset($board, $thread_id) {
		global $db;

		/** Validate passed parameters */
		if (!is_string($board) || is_array($board)) return false;
		if (!is_numeric($thread_id) || $thread_id <= 0) return false;

		$sql = 'SELECT *, UNIX_TIMESTAMP(date) as date FROM comments WHERE board=? AND thread_id=?';
		return $db->fetch($db->query($sql, __FILE__, __LINE__, __METHOD__, [$board, $thread_id]));
	}

	/**
	 * Post-Recursion Function.
	 *
	 * @deprecated see smartyresource_comments_get_childposts??? (26.10.2018)
	 *
	 * @return void
	 * @param int $parent_id
	 * @param array $depth
	 */
	static function printChildPosts($board, $parent_id, $depth=array("space")) {

	  global $db, $user;

	  if(!is_numeric($parent_id)) {
				echo t('invalid-parent_id', 'commenting');
				exit;
			}

	  $hierdepth = count($depth);
	  $sql = 'SELECT
				comments.*
				,user.clan_tag, user.username
				,comments_unread.user_id as isunread
				,UNIX_TIMESTAMP(comments.date) as date
				,count(c2.id) as numchildposts
			FROM comments
				LEFT JOIN comments_unread ON (comments.id=comments_unread.comment_id AND comments_unread.user_id=?)
				LEFT JOIN user ON comments.user_id=user.id
				LEFT JOIN comments as c2 ON (comments.id=c2.parent_id AND comments.board=c2.board)
			WHERE comments.parent_id=? AND comments.board=?
				GROUP BY comments.id
				ORDER BY comments.id
			';
	  $result = $db->query($sql, __FILE__, __LINE__, __METHOD__, [$user->id, $parent_id, $board]);
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
	  		defined('USER_USER') && $user->typ >= USER_USER
	  		&& $hierdepth < $user->maxdepth
	  		|| $rs['isunread'] != ''
	  		|| !$user->is_loggedin() && $hierdepth < 10
	  	) {
	  		// DISABLED echo Comment::getHTML($rs, $depth2); // print formatted post => Methode gibts gar nicht (mehr)?!
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
