<?php
/**
 * File Includes
 */
//require_once( __DIR__ .'/../includes/main.inc.php');
require_once( __DIR__ .'/../includes/forum.inc.php');
require_once( __DIR__ .'/../includes/usersystem.inc.php');
require_once( __DIR__ .'/../includes/util.inc.php');

if(!($user->id > 0) || !is_numeric($user->id))
{
	http_response_code(403); // Set response code 403 (access denied) and exit.
	user_error('Du bist nicht eingeloggt.', E_USER_WARNING);
	die();
}

if($_POST['text'] == '' || empty($_POST['text']) || !isset($_POST['text']))
{
	http_response_code(400); // Set response code 400 (bad request) and exit.
	user_error('keine leeren Posts erlaubt.', E_USER_WARNING);
	die();
} else {
	$commentText = escape_text($_POST['text']);
}

if($_POST['parent_id'] == '' || empty($_POST['parent_id']) || $_POST['parent_id'] == '0' || !is_numeric($_POST['parent_id']))
{
	http_response_code(400); // Set response code 400 (bad request) and exit.
	user_error('Parent id leer oder ungültig: ' . $_POST['parent_id'], E_USER_WARNING);
	die();
}

if(Forum::hasPostedRecently($user->id, $_POST['parent_id']))
{
	http_response_code(409); // Set response code 400 (conflict) and exit.
	user_error(usersystem::id2user($user->id) . ', Du hast vor wenigen Sekunden bereits gepostet - bitte warte noch kurz!', E_USER_NOTICE);
	die();
}

// Validate msg_users is REALLY set
if(isset($_POST['msg_users']) && $_POST['msg_users'] != ' ' && !empty(array_filter($_POST['msg_users'])))
{
	$msg_users = $_POST['msg_users'];
	
	// Let's check if it's just a comma-separated String, or an Array
	if (strpos($msg_users, ',') !== false && !is_array($msg_users))
	{
		// make an Array, if necessary
		$msg_users = explode(',', $_POST['msg_users']);
	}
	
	// Remove any duplicate User-IDs
	$msg_users = array_unique($msg_users);
}

if(
	$commentlink =
		Comment::post(
			$_POST['parent_id'],
			$_POST['board'],
			$user->id,
			$commentText,
			$msg_users
		)
) {
	header("Location: ".$commentlink);
	die();

} else {
	http_response_code(500); // Set response code 500 (internal error) and exit.
	user_error('Post konnte nicht erstellt werden.', E_USER_ERROR);
	die();
}
