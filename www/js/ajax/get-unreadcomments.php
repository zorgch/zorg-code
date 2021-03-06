<?php
/**
 * Get unread Comments asynchronously
 *
 * @package zorg\Forum
 */

/**
 * FILE INCLUDES
 */
require_once dirname(__FILE__).'/../../includes/config.inc.php';
require_once INCLUDES_DIR.'forum.inc.php';

/**
 * Get online user HTML
 */
if (!empty($user->id) && $user->id > 0)
{
$numUnreadComments = Forum::getNumunreadposts($user->id);
	if (!empty($numUnreadComments) && $numUnreadComments != false && $numUnreadComments > 0)
	{
		http_response_code(200); // Set response code 200 (OK)
		header('Content-type: text/html;charset=utf-8');
		echo ($numUnreadComments > 1 ? $numUnreadComments.' Comments' : $numUnreadComments.' Comment');
	} else {
		http_response_code(204); // Set response code 204 (OK but no Content)
	}
} else {
	http_response_code(403); // Set response code 403 (Forbidden)
	die('Invalid User');
}
