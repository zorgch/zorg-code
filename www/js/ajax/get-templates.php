<?php
/**
 * AJAX Request validation
 */
//if(!isset($_POST['action']) || empty($_POST['action']) || $_POST['action'] != 'userlist')
if(!isset($_GET['action']) || empty($_GET['action']) || $_GET['action'] != 'templates')
{
	http_response_code(400); // Set response code 400 (bad request) and exit.
	die('Invalid or missing POST-Parameter');
}

/**
 * FILE INCLUDES
 */
require_once( __DIR__ .'/../../includes/config.inc.php');
require_once( __DIR__ .'/../../includes/mysql.inc.php');

/**
 * Get records from database
 */
header('Content-type:application/json;charset=utf-8');
try {
	$sql = 'SELECT id, title, read_rights FROM templates WHERE title LIKE "'.$_GET['mention'].'%" AND read_rights <= 1 ORDER BY CHAR_LENGTH(title) ASC, title ASC LIMIT 0,6';
	$result = $db->query($sql, __FILE__, __LINE__);
	while ($rs = mysql_fetch_array($result))
	{
	   $templates[] = [
	   	'tplId' => $rs['id'],
	   	'tplTitle' => $rs['title']
	   ];
	}
	http_response_code(200); // Set response code 200 (OK)
	echo json_encode($templates);
}
catch(Exception $e) {
	http_response_code(500); // Set response code 500 (internal server error)
	echo json_encode($e);
}
