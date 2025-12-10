<?php
/**
 * Post new Comment Action
 *
 * @package zorg\Forum
 */

/**
 * File Includes
 */
require_once __DIR__.'/../includes/config.inc.php';
require_once INCLUDES_DIR.'usersystem.inc.php';
require_once INCLUDES_DIR.'forum.inc.php';

/** Only allowed for logged-in Users */
if(!$user->is_loggedin()) {
	http_response_code(403); // Set response code 403 (access denied) and exit.
	user_error('Du bist nicht eingeloggt.', E_USER_WARNING);
	exit;
}

$parent_id = filter_input(INPUT_POST, 'parent_id', FILTER_VALIDATE_INT) ?? null;
$board = filter_input(INPUT_POST, 'board', FILTER_SANITIZE_SPECIAL_CHARS) ?? null;
$commentText = htmlspecialchars_decode(filter_input(INPUT_POST, 'text', FILTER_SANITIZE_FULL_SPECIAL_CHARS), ENT_COMPAT | ENT_SUBSTITUTE) ?? null;
$msg_users = isset($_POST['msg_users']) ? explode(',', $_POST['msg_users'][0]) : null;

/** Validate User has sent a non-empty Comment */
if (empty($commentText)) {
	http_response_code(400); // Set response code 400 (bad request) and exit.
	user_error('keine leeren Posts erlaubt.', E_USER_WARNING);
	exit;
}

/** Validate parent_id */
if(!empty($parent_id) && $parent_id <= 0) {
	http_response_code(400); // Set response code 400 (bad request) and exit.
	user_error('Parent id leer oder ungültig: ' . $parent_id, E_USER_WARNING);
	exit;
}

/** Validate User has not posted recently */
if(Forum::hasPostedRecently($user->id, $parent_id))
{
	http_response_code(409); // Set response code 409 (conflict) and exit.
	user_error($user->id2user($user->id) . ', du hast vor wenigen Sekunden bereits gepostet - bitte warte noch kurz!', E_USER_NOTICE);
	exit;
}

/** Validate msg_users is REALLY set */
if(!empty($msg_users) && is_array($msg_users))
{
	/** Remove any duplicate User-IDs */
	$msg_users = array_unique($msg_users);
	zorgDebugger::log()->debug('$msg_users = %s', [print_r($msg_users,true)]);
}

/** Post new Comment & get Link */
if($commentlink = Comment::post($parent_id, $board, $user->id, $commentText, $msg_users))
{
	/** Redirect browser to new Comment */
	zorgDebugger::log()->debug('Redirect to $commentlink: %s', [$commentlink]);
	header('Location: '.$commentlink);
	exit;

/** Error posting new Comment */
} else {
	http_response_code(500); // Set response code 500 (internal error) and exit.
	user_error('Comment konnte nicht hinzugefügt werden.', E_USER_ERROR);
	exit;
}
