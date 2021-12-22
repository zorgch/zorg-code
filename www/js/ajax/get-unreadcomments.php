<?php
/**
 * Get unread Comments asynchronously
 *
 * @package zorg\Forum
 */
/**
 * AJAX Request validation
 */
if(!isset($_GET['user']) || empty($_GET['user']) || false === filter_var(trim($_GET['user']), FILTER_SANITIZE_NUMBER_INT))
{
	http_response_code(400); // Set response code 400 (bad request) and exit.
	die('Invalid or missing GET-Parameter');
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
if (!empty($user_id))
{
	/** Requires mysql.inc.php */
	require_once dirname(__FILE__).'/../../includes/mysql.inc.php';
	/** Unread Comments are only valid while a User is online... for minimum external exposure. */
	$sql = 'SELECT COUNT(*) AS numunread FROM comments_unread WHERE user_id IN (SELECT id FROM user WHERE activity > (NOW()-200) AND id='.$user_id.')';
	$rs = $db->fetch($db->query($sql, __FILE__, __LINE__, 'SELECT FROM comments_unread'));
	/** Check if at least 1 unread comment */
	$numUnreadComments = (false !== $rs['numunread'] && !empty($rs['numunread']) ? (int)$rs['numunread'] : 0);
	if ($numUnreadComments > 0)
	{
		http_response_code(200); // Set response code 200 (OK)
		header('Content-type: text/html;charset=utf-8');
		printf('%d Comment%s', $numUnreadComments, ($numUnreadComments > 1 ? 's' : ''));
	} else {
		http_response_code(204); // Set response code 204 (OK but no Content)
	}
} else {
	http_response_code(403); // Set response code 403 (Forbidden)
	//die('Invalid User');
}
