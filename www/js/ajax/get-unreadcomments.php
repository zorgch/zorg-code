<?php
/**
 * FILE INCLUDES
 */
require_once( __DIR__ .'/../../includes/config.inc.php');
require_once( __DIR__ .'/../../includes/forum.inc.php');

/**
 * Get online user HTML
 */
if (!empty($user->id) && $user->id > 0)
{
$numUnreadComments = Forum::getNumunreadposts($user->id);
	if (!empty($numUnreadComments) && $numUnreadComments != false && $numUnreadComments > 0)
	{
		http_response_code(200); // Set response code 200 (OK)
		header('Content-type:document;charset=utf-8');
		echo ($numUnreadComments > 1 ? $numUnreadComments.' Comments' : $numUnreadComments.' Comment');
	} else {
		http_response_code(204); // Set response code 204 (OK but no Content)
	}
} else {
	http_response_code(403); // Set response code 403 (Forbidden)
	die('Invalid User');
}
