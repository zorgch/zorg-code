<?php
/**
 * Post new Comment Action
 *
 * @package zorg\Forum
 */

/**
 * File Includes
 */
//require_once __DIR__ .'/../includes/main.inc.php';
require_once dirname(__FILE__).'/../includes/forum.inc.php';
require_once INCLUDES_DIR.'usersystem.inc.php';
require_once INCLUDES_DIR.'util.inc.php';

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

if($_POST['parent_id'] == '' || !is_numeric($_POST['parent_id']))
{
	http_response_code(400); // Set response code 400 (bad request) and exit.
	user_error('Parent id leer oder ungültig: ' . $_POST['parent_id'], E_USER_WARNING);
	die();
}

if(Forum::hasPostedRecently($user->id, $_POST['parent_id']))
{
	http_response_code(409); // Set response code 400 (conflict) and exit.
	user_error($user->id2user($user->id) . ', Du hast vor wenigen Sekunden bereits gepostet - bitte warte noch kurz!', E_USER_NOTICE);
	die();
}

/** Validate msg_users is REALLY set */
if(isset($_POST['msg_users']) && $_POST['msg_users'] != ' ' && !empty(array_filter($_POST['msg_users'])))
{
	$msg_users = $_POST['msg_users'];

	/** Let's check if it's just a comma-separated String, or an Array */
	if (!is_array($msg_users) && strpos($msg_users, ',') !== false)
	{
		/** make an Array, if necessary */
		$msg_users = explode(',', $_POST['msg_users']);
	}

	/** Remove any duplicate User-IDs */
	$msg_users = array_unique($msg_users);
}

/** Post new Comment & get Link */
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
	/** Redirect browser to new Comment */
	if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> Redirect to $commentlink: %s', __FILE__, __LINE__, $commentlink));
	header('Location: '.$commentlink);
	exit;

/** Error posting new Comment */
} else {
	http_response_code(500); // Set response code 500 (internal error) and exit.
	user_error('Post konnte nicht erstellt werden.', E_USER_ERROR);
	exit;
}
