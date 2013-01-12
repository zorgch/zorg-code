<?php
/**
* Event Comments
* 
* Gibt alle Comments eines Events aus
* 
* @author IneX
* @version 0.1
* @package mobilezorg
* @subpackage events
*
* @global array $user Globales Array mit allen Uservariablen
* @global array $db Globales Array mit allen MySQL-Datenbankvariablen
*/

include_once($_SERVER['DOCUMENT_ROOT'].'/includes/main.inc.php');

global $user, $db;

if ($user->typ == USER_NICHTEINGELOGGT || !isset($user->typ)) header('Location: login.php');


function fetchChildComments($id, $board='e')
{
	global $db;
	
	if (!is_numeric($parent_id)) { header("Location: events.php?error=Comment%20ID%20ung&uuml;ltig"); exit(); }
	
	$sql =
		"
		SELECT
			*
		FROM
			comments
		WHERE
			parent_id='$id' AND board='$board'
		ORDER BY
			id ASC
		";
	$result = $db->query($sql, __FILE__, __LINE__);
}
?>

	<ul id="eventcomments" title="Kommentare">
		<li class="error"><h1>in Arbeit</h1></li>
	</ul>