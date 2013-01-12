<?php
/**
 * Forum Newest
 * 
 * Listet die neusten Comments aus dem Forum auf mobilezorg aus
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

<!-- NEWEST THREADS -->
	<ul id="neustethreads" title="Neuste Threads">
		<?php	
			
			// Query for latest Threads
			$sql = "SELECT *, UNIX_TIMESTAMP(date) as date FROM comments WHERE parent_id='1' ORDER BY date DESC LIMIT 0,10";
			$result = $db->query($sql, __FILE__, __LINE__);
			
			while($comment = $db->fetch($result))
			{
				$html .= '<li><a href="forum_thread.php?board='.$comment['board'].'&amp;thread_id='.$comment['thread_id'].'"><small>'.usersystem::id2user($comment['user_id'],true).' @ '.strftime('%e. %B %Y', $comment['date']).'</small><br/>'.getTitle($comment['text']).'</a></li>';
			}
		
			echo $html;
		?>
	</ul>