<?php
/**
 * @TODO add User-Profilepic to Array
 */

/**
 * AJAX Request validation
 */
if(!isset($_GET['action']) || empty($_GET['action']) || $_GET['action'] !== 'userlist')
{
	http_response_code(400); // Set response code 400 (bad request) and exit.
	die('Invalid or missing POST-Parameter');
}
$usernameMention = filter_var(trim($_GET['mention']), FILTER_SANITIZE_STRING);

/**
 * FILE INCLUDES
 */
require_once __DIR__.'/../../includes/config.inc.php';
require_once INCLUDES_DIR.'mysql.inc.php';

/**
 * Get records from database
 */
header('Content-type:application/json;charset=utf-8');
if (false !== $usernameMention)
{
	$sql = 'SELECT id, username FROM user WHERE username LIKE "'.$usernameMention.'%" ORDER BY CHAR_LENGTH(username) ASC, username ASC LIMIT 0,6';
	$result = $db->query($sql, __FILE__, __LINE__);
	while ($rs = $db->fetch($result))
	{
	   $users[] = [
	   	'userid' => $rs['id'],
	   	'username' => $rs['username']//,
	   	//'userpic' => USER_IMGPATH_PUBLIC.$rs['id'].'.jpg' // too slow :(
	   ];
	}
	http_response_code(200); // Set response code 200 (OK)
	echo json_encode($users);
}
/** Invalid Input */
else {
	http_response_code(400); // Set response code 500 (internal server error)
	die('Invalid GET-Parameter');
}
