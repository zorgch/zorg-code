<?php
/**
 * AJAX Request validation
 */
if(!isset($_GET['style']) || empty($_GET['style']) || is_numeric($_GET['style']) || is_array($_GET['style']) || is_bool($_GET['style']))
{
	http_response_code(400); // Set response code 400 (bad request) and exit.
	die('Invalid or missing GET-Parameter');
}

/**
 * FILE INCLUDES
 */
require_once( __DIR__ .'/../../includes/config.inc.php');
require_once( __DIR__ .'/../../includes/usersystem.inc.php');

/**
 * Get online user HTML
 */
$onlineUserListstyle = sanitize_userinput($_GET['style']);
if ($onlineUserListstyle === 'image' || $onlineUserListstyle === 'list')
{
	$onlineUserHtml = $user->online_users(($onlineUserListstyle === 'image' ? true : false));
	if (!empty($onlineUserHtml) && $onlineUserHtml != false)
	{
		http_response_code(200); // Set response code 200 (OK)
		header('Content-type:document;charset=utf-8');
		echo $onlineUserHtml;
	} else {
		http_response_code(204); // Set response code 204 (OK but no Content)
	}
} else {
	http_response_code(400); // Set response code 400 (Bad Request)
	die('Invalid GET-Parameter');
}
