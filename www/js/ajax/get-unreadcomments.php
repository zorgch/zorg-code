<?php
/**
 * Get unread Comments asynchronously
 *
 * @package zorg\Forum
 */

/**
 * FILE INCLUDES
 * @include config.inc.php Required at top! (e.g. for ENV vars, and to validate 'nonce' in $_SESSION)
 */
require_once __DIR__.'/../../includes/config.inc.php';

/**
 * AJAX Request validation
 */
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
	/** The request is not an AJAX request */
	http_response_code(405); // Set response code 405 (Method Not Allowed)
	exit('Request not allowed');
}
if(!isset($_GET['user']) || empty($_GET['user']) || false === filter_var(trim($_GET['user']), FILTER_SANITIZE_NUMBER_INT))
{
	http_response_code(400); // Set response code 400 (bad request) and exit.
	exit('Invalid or missing GET-Parameter');
}
$user_id = (int)filter_var(trim($_GET['user']), FILTER_SANITIZE_NUMBER_INT);

/**
 * Get online user HTML
 *
 * Remarks: the following code incl. SQL-query has been extracted
 * to run standalone (without further Forum or other contexts).
 * The reason is to have a very minimal "overhead" for repeated
 * checks for unread comments (updating the corresponding frontend)
 */
if (!empty($user_id) && $user_id > 0)
{
	/** Requires mysql.inc.php */
	require_once INCLUDES_DIR.'mysql.inc.php';
	/** Unread Comments are only valid while a User is online... for minimum external exposure. */
	$sql = 'SELECT COUNT(*) AS numunread FROM comments_unread WHERE user_id=?';
	$rs = $db->fetch($db->query($sql, __FILE__, __LINE__, 'AJAX.GET(get-unreadcomments)', [$user_id]));
	/** Check if at least 1 unread comment */
	$numUnreadComments = (false !== $rs['numunread'] && !empty($rs['numunread']) ? (int)$rs['numunread'] : 0);
	if ($numUnreadComments > 0)
	{
		http_response_code(200); // Set response code 200 (OK)
		header('Content-type: text/html;charset=utf-8');
		exit(sprintf('%d Comment%s', $numUnreadComments, ($numUnreadComments > 1 ? 's' : '')));
	} else {
		http_response_code(204); // Set response code 204 (OK but no Content)
		exit;
	}
} else {
	http_response_code(403); // Set response code 403 (Forbidden)
	exit;
}
