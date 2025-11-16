<?php
/**
 * Commenting Actions
 *
 * @package zorg\Forum
 */

/**
 * File Includes
 */
require_once __DIR__.'/../includes/config.inc.php';
require_once INCLUDES_DIR.'mysql.inc.php';
require_once INCLUDES_DIR.'usersystem.inc.php';

/** Input validation & sanitization */
$doAction = filter_input(INPUT_GET, 'do', FILTER_SANITIZE_SPECIAL_CHARS) ?? null; // $_GET['do']
$comment_id = filter_input(INPUT_GET, 'comment_id', FILTER_VALIDATE_INT) ?? 0; // $_GET['comment_id']
$board = filter_input(INPUT_GET, 'board', FILTER_SANITIZE_SPECIAL_CHARS) ?? null; // $_GET['board']
$redirect = base64url_decode(filter_input(INPUT_GET, 'url', FILTER_SANITIZE_SPECIAL_CHARS)) ?? null; // $_GET['url']

if (!$user->is_loggedin()) {
	http_response_code(403); // Set response code 403 (Access denied)
	user_error('Access denied', E_USER_ERROR);
	exit;
}
if($comment_id <= 0) {
	http_response_code(404); // Set response code 404 (Not found)
	user_error('Invalid comment: '.$comment_id, E_USER_ERROR);
	exit;
}

/** Subscribe */
if($doAction === 'subscribe')
{
	$sql = 'INSERT INTO comments_subscriptions (board, comment_id, user_id) VALUES(?, ?, ?)';
	$db->query($sql, __FILE__, __LINE__, 'Commenting subscribe', [$board, $comment_id, $user->id]);
}

/** Unsubscribe */
elseif($doAction === 'unsubscribe')
{
	$sql = 'DELETE FROM comments_subscriptions WHERE board=? AND comment_id=? AND user_id=?';
	$db->query($sql, __FILE__, __LINE__, 'Commenting unsubscribe', [$board, $comment_id, $user->id]);
}

header("Location: ".$redirect);
exit;
