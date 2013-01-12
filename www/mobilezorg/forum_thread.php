<?php
/**
 * Forum Threads
 * 
 * Listet die Threads vom Forum auf mobilezorg aus
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


switch ($_GET['action'])
{

	case 'reply':
		
		
		
		break;
}


// Query for Thread data
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
			
			$html .= '<li><small>'.usersystem::id2user($comment['user_id'],true).' @ '.strftime('%e. %B %Y %H:%M Uhr', $comment['date']).'</small><br/>';
			$html .= $comment['text'].'</li>';
			
			$html .= '<li><a href="#reply">Antworten</a></li>';
			$html .= (!$is_favorite) ? '<li><a href="forum_comment.php?board='.$comment['board'].'&amp;thread_id='.$comment['thread_id'].'&amp;comment_id='.$comment['id'].'&amp;action=favorite">Kommentar markieren</a></li>' : '<li><a href="forum_comment.php?board='.$comment['board'].'&amp;thread_id='.$comment['thread_id'].'&amp;comment_id='.$comment['id'].'&amp;action=unfavorite">Markierung aufheben</a></li>' ;
					
			echo $html;
			
			markAsRead($comment['id'], $user->id);
		?>
	</ul>
	
<!-- Chat new Message -->
	<form id="reply" class="dialog" action="forum_comment.php?board=<?php $comment['board'] ?>&amp;thread_id=<?php $comment['thread_id'] ?>&amp;comment_id=<?php $comment['id'] ?>&amp;action=reply" method="post">
		<fieldset>
			<h1>Neuer Kommentar</h1>
			<a class="button leftButton" type="cancel">Cancel</a>
			<a class="button blueButton" type="submit">Senden</a>
			
			<!-- input type="hidden" name="url" value="aHR0cDovL3pvcmcuY2gvbW9iaWxlem9yZy9jaGF0LnBocCNfaG9tZQ=="/ -->
			<!-- Base64 Encoded URL: http://zorg.ch/mobilezorg/chat.php#_home -->
			
			<input type="text" name="message"/>
		</fieldset>
	</form>