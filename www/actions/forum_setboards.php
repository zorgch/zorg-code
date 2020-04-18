<?php
/**
 * Forum Actions
 * @package zorg\Forum
 */
/**
 * File includes
 */
//require_once(__DIR__.'/../includes/main.inc.php');
require_once( __DIR__ .'/../includes/config.inc.php');
require_once( __DIR__ .'/../includes/usersystem.inc.php');

global $db;

if ($user->is_loggedin())
{
	if (isset($_POST['forum_boards']) && is_array($_POST['forum_boards']))
	{
		/* DEPRECATED (brauchen neu nur noch ein JSON...)
		for($i = 0; $i < count($_POST['boards']); $i++)
		{
			$boards .= $_POST['boards'][$i].',';
		}*/
		$boards = escape_text(json_encode($_POST['forum_boards']));
		$sql = 'UPDATE user SET forum_boards = "'.$boards.'" WHERE id = '.$user->id;
		$db->query($sql, __FILE__, __LINE__, 'SET forum_boards');
	}

	header("Location: /forum.php");
	die();
} else {
	http_response_code(403); // Set response code 403 (not allowed) and exit.
	die('Permission denied!');
}
