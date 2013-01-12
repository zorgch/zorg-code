<?php
/**
* Forum Comments
* 
* Listet die Comments eines Threads auf
* 
* @author IneX
* @version 0.1
* @package mobilezorg
* @subpackage forum
*
* @global array $user Globales Array mit allen Uservariablen
* @global array $db Globales Array mit allen MySQL-Datenbankvariablen
*/

include_once($_SERVER['DOCUMENT_ROOT'].'/includes/main.inc.php');

global $user, $db;

if ($user->typ == USER_NICHTEINGELOGGT || !isset($user->typ)) header('Location: login.php');


setlocale(LC_TIME,"de_CH");

$html = '';

$thread_id = $_GET['thread_id'];
$parent_id = $_GET['parent_id'];
$comment_id = $_GET['comment_id'];
$is_favorite = $_GET['is_favorite'];


/**
 * Remove HTML tags, including invisible text such as style and
 * script code, and embedded objects.  Add line breaks around
 * block-level tags to prevent word joining after tag removal.
 */
function strip_html_tags($text)
{
		$text = preg_replace(
		array(
		// Remove invisible content
			'@<head[^>]*?>.*?</head>@siu',
			'@<style[^>]*?>.*?</style>@siu',
			'@<script[^>]*?.*?</script>@siu',
			'@<object[^>]*?.*?</object>@siu',
			'@<embed[^>]*?.*?</embed>@siu',
			'@<applet[^>]*?.*?</applet>@siu',
			'@<noframes[^>]*?.*?</noframes>@siu',
			'@<noscript[^>]*?.*?</noscript>@siu',
			'@<noembed[^>]*?.*?</noembed>@siu',
		// Add line breaks before and after blocks
			'@</?((address)|(blockquote)|(center)|(del))@iu',
			'@</?((div)|(h[1-9])|(ins)|(isindex)|(p)|(pre))@iu',
			'@</?((dir)|(dl)|(dt)|(dd)|(li)|(menu)|(ol)|(ul))@iu',
			'@</?((table)|(th)|(td)|(caption))@iu',
			'@</?((form)|(button)|(fieldset)|(legend)|(input))@iu',
			'@</?((label)|(select)|(optgroup)|(option)|(textarea))@iu',
			'@</?((frameset)|(frame)|(iframe))@iu',
		),
		array(
				' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ',
				"\n\$0", "\n\$0", "\n\$0", "\n\$0", "\n\$0", "\n\$0",
				"\n\$0", "\n\$0",
		),
		$text );
	
	return strip_tags($text);
}


/**
 * Kommentar Titel
 * 
 * Den Titel eines Kommentars holen.
 * 
 * @return String
 * @param $text String
 * @param $lengthoffset int
 */
function getTitle($text, $length=25)
{

  global $db;

  $text = strip_tags($text);
  
  $pattern = "(((\w|\d|[äöüèéàîê])(\w|\d|\s|[äöüèéàîê]|[\.,-_\"'?!^`~])[^\\n]+)(\\n|))";
  preg_match($pattern, $text, $out);
  if(strlen($out[1]) > $length) {
	$out[1] = substr($out[1], 0, $length);
  }
  if(strlen($out[1]) == 0) return '(kein Titel)';

  return $out[1];
}


function markAsRead($comment_id, $user_id)
{
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


function getThreadid($board, $id)
{
	global $db;
	
	$sql = "SELECT thread_id FROM comments WHERE board = '".$board."' AND id = ".$id;
	$rs = $db->fetch($db->query($sql, __FILE__, __LINE__));
	
	return $rs['thread_id'];
}


function post($parent_id, $board, $user_id, $text)
{

	global $db, $user;

	// (Parent-Id = 1 wenn man ein ForumThread postet
	$parent_id = ($parent_id <= 0 ? 1 : $parent_id);

	if($parent_id <= 0) {
		echo 'Parent id ist kleiner gleich 0.';
		exit;
	}

	// Falls Thread-Id noch nicht vorhanden, parent-id nehmen
	// (1 bei forum, anderes bei den anderen boards)
	$thread_id = Comment::getThreadid($board, $parent_id);
	if(!($thread_id > 0)) $thread_id = $parent_id;
	
	
	if($thread_id <= 0) {
		header('Location: index.php?error=Thread%20ID%20ist%20ung&uuml;ltig!');
		exit;
	}


	// Rechte checken
	//if (Thread::hasRights($board, $thread_id, $user_id)) {

		// Comment in die DB abspeichern
		  $sql =
			"INSERT INTO comments (user_id, parent_id, thread_id, text, date, board, error)"
			." VALUES ("
			.$user_id
			.", ".$parent_id
			.", ".$thread_id
			.",'".addslashes(stripslashes($text))
			."', now()"
			.", '".$board."'"
			.", '$comment_error'"
			.")"
		  ;
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

		$sql =
	  	"SELECT *, UNIX_TIMESTAMP(date) as date"
	  	." FROM comments where thread_id = ".$comment_id." and board = '".$board."'"
		  ;
		  return $db->fetch($db->query($sql, __FILE__, __LINE__));
		
		
		//$commentlink = Comment::getLink($rs['board'], $rs['parent_id'], $rs['id'], $rs['thread_id']);
		

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
		//  Comment::compile_template($rs['thread_id'], $rs['id'], $rs['board']);

		//  if ($rs['parent_id'] != 1 || $rs['board'] != 'f') {
				// Templates neu Kompilieren, zuerst Parent
				/*
			  if($parent_id != $rs['thread_id']) {
					Comment::compile_template($rs['thread_id'], $rs['parent_id']);
			  } else {
					Comment::compile_template($rs['thread_id'], $rs['parent_id'], $rs['board']);
			  }*/

		//	  Comment::compile_template($rs['thread_id'], $rs['parent_id'], $rs['board']);
		//  }


		  // Mark comment as unread for all users.
		//	Comment::markasunread($rs['id']);


			// Mark comment as read for this user.
		//	Comment::markasread($rs['id'], $user_id);

		//	return $commentlink;

	/*} else {
		echo "Permission denied for posting on thread '$board / $thread_id'";
		exit;
	}*/
}


switch ($_GET['action'])
{

	case 'reply':
		//post($parent_id, $board, $user_id, $text);
		
		$error = "in Arbeit";
		
		break;

	case 'favorite':
		$sql =	
		"
		INSERT INTO comments_threads_favorites (board, thread_id, comment_id, user_id)
		VALUES ('".$_GET['board']."', '".$_GET['thread_id']."', '".$_GET['comment_id']."', '".$user->id."')
		"
		;
		$db->query($sql, __FILE__, __LINE__);
		
		break;
		
	case 'unfavorite':
		$sql =	
		"
		DELETE FROM comments_threads_favorites
		WHERE board ='".$_GET['board']."' 
			AND thread_id = ".$_GET['thread_id']."
			AND comment_id = ".$_GET['comment_id']."
			AND user_id = '".$user->id."'
		"
		;
		$db->query($sql, __FILE__, __LINE__);
		
		break;
}


// Query for Comment data
$sql =
	"SELECT
		comments.*
		, UNIX_TIMESTAMP(comments.date) as date
	FROM comments
		LEFT JOIN user on comments.user_id = user.id
	WHERE comments.id = '$comment_id'
	";

$result = $db->query($sql, __FILE__, __LINE__);
$comment = $db->fetch($result)

?>

<!-- SHOW COMMENT -->
	<ul id="showcomment" title="<?php echo getTitle($comment['text']) ?>">
		<?php	
			
			// Error Message
			if ($_GET['error'] <> '') echo "<li class=\"error\"><h1>$_GET[error]</h1></li>";
			if ($error <> '') echo "<li class=\"error\"><h1>$error</h1></li>";
			
						
			$html .= '<li><small>'.usersystem::id2user($comment['user_id'],true).' @ '.strftime('%e. %B %Y %H:%M Uhr', $comment['date']).'</small><br/>';
			$html .= strip_html_tags($comment['text']).'</li>';
			
			$html .= '<li><a href="#reply">Antworten</a></li>';
			$html .= (!$is_favorite) ? '<li><a href="forum_comment.php?board='.$comment['board'].'&amp;thread_id='.$comment['thread_id'].'&amp;comment_id='.$comment['id'].'&amp;action=favorite">Kommentar markieren</a></li>' : '<li><a href="forum_comment.php?board='.$comment['board'].'&amp;thread_id='.$comment['thread_id'].'&amp;comment_id='.$comment['id'].'&amp;action=unfavorite">Markierung aufheben</a></li>' ;
					
			echo $html;
			
			markAsRead($comment['id'], $user->id);
		?>
	</ul>
	
<!-- Forum new Message -->
	<form id="reply" class="dialog" action="forum_comment.php?board=<?php $comment['board'] ?>&amp;thread_id=<?php $comment['thread_id'] ?>&amp;comment_id=<?php $comment['id'] ?>&amp;action=reply" method="post">
		<fieldset>
			<h1>Neuer Kommentar</h1>
			<a class="button leftButton" type="cancel">Cancel</a>
			<a class="button blueButton" type="submit">Senden</a>
			
			<!-- input type="hidden" name="url" value="aHR0cDovL3pvcmcuY2gvbW9iaWxlem9yZy9jaGF0LnBocCNfaG9tZQ=="/ -->
			<!-- Base64 Encoded URL: http://zorg.ch/mobilezorg/chat.php#_home -->
			<input type="hidden" name="board" value="<?php echo $board['board']; ?>" />
			<input type="hidden" name="parent_id" value="<?php echo $comment['id'] ?>" />
			<input type="hidden" name="thread_id" value="<?php echo $comment['thread_id']; ?>" />
			<input type="hidden" name="user_id" value="<?php echo $comment['user_id']; ?>" />
			
			<input type="text" name="message" />
		</fieldset>
	</form>