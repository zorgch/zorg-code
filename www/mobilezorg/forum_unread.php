<?php
/**
 * Forum Unreads
 * 
 * Gibt die ungelesenen Comments auf mobilezorg aus
 * 
 * @author IneX
 * @version 0.9
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

$numUnreads = $_GET['numUnreads'];


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
?>

<!-- UNREAD COMMENTS -->
	<ul id="unreadcomments" title="Ungelesen">
		<?php	
			
			// Query for latest Threads
			$sql =
				"SELECT
					comments.*
					, IF(ISNULL(comments_unread.comment_id), 0, 1) AS isunread
					, UNIX_TIMESTAMP(comments.date) as date
				FROM comments
					LEFT JOIN user on comments.user_id = user.id
					LEFT JOIN comments_unread ON (comments.id=comments_unread.comment_id AND comments_unread.user_id = '$user->id')
				WHERE comments_unread.comment_id IS NOT NULL
				ORDER by date ASC
				LIMIT 0,10";
			
			$result = $db->query($sql, __FILE__, __LINE__);
			
			while($comment = $db->fetch($result))
			{
				$html .= '<li><a href="forum_comment.php?board='.$comment['board'].'&amp;thread_id='.$comment['thread_id'].'&amp;comment_id='.$comment['id'].'"><small>'.usersystem::id2user($comment['user_id'],true).' @ '.strftime('%e. %B %Y', $comment['date']).'</small><br/>'.getTitle($comment['text']).'</a></li>';
			}
			
			if ($numUnreads > 10) $html .= '<li><a href="forum_unread_more.php?numUnreads='.$numUnreads.'&amp;prev=0" target="_replace">Mehr...</a></li>';
		
			echo $html;
		?>
	</ul>