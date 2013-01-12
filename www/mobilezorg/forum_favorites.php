<?php
/**
* Forum Favorites
* 
* Listet die markierten Threads aus dem Forum auf
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

<!-- FAVORITED COMMENTS -->
	<ul id="favoritecomments" title="Favoriten">
		<?php	
			
			// Query for favorite Comments
			$sql =
				"SELECT
					comments.*
					, comments_threads_favorites.*
					, UNIX_TIMESTAMP(comments.date) AS date
					, comments.user_id AS user_id
				FROM comments
					LEFT JOIN user on comments.user_id = user.id
					LEFT JOIN comments_threads_favorites ON (comments.id=comments_threads_favorites.comment_id AND comments_threads_favorites.user_id = '$user->id')
				WHERE comments_threads_favorites.comment_id IS NOT NULL
					OR comments_threads_favorites.thread_id IS NOT NULL
				ORDER by date ASC
				LIMIT 0,23";
			
			$result = $db->query($sql, __FILE__, __LINE__);
			
			while($comment = $db->fetch($result))
			{
				$html .= '<li><a href="forum_comment.php?board='.$comment['board'].'&amp;thread_id='.$comment['thread_id'].'&amp;comment_id='.$comment['id'].'&amp;is_favorite=true"><small>'.usersystem::id2user($comment['user_id'],true).' @ '.strftime('%e. %B %Y', $comment['date']).'</small><br/>'.getTitle($comment['text']).'</a></li>';
			}
			
			if ($numUnreads > 23) $html .= '<li><a href="forum_unread_more.php?numUnreads='.$numUnreads.'&amp;prev=0" target="_replace">Mehr...</a></li>';
			
			echo $html;
		?>
	</ul>