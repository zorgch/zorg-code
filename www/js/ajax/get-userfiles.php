<?php
/**
 * AJAX Request validation
 */
if(!isset($_GET['action']) || empty($_GET['action']) || $_GET['action'] != 'userfiles')
{
	http_response_code(400); // Set response code 400 (bad request) and exit.
	die('Invalid or missing POST-Parameter');
}

/**
 * FILE INCLUDES
 */
require_once __DIR__.'/../../includes/config.inc.php';
require_once INCLUDES_DIR.'mysql.inc.php';

/**
 * Get records from database
 */
header('Content-type:application/json;charset=utf-8');
try {
	$sql = 'SELECT name, mime FROM files WHERE user='.$_GET['userid'].' AND name LIKE "%'.$_GET['mention'].'%" ORDER BY upload_date DESC LIMIT 0,6';
	$result = $db->query($sql, __FILE__, __LINE__);
	while ($rs = $db->fetch($result))
	{
	   $images[] = [
	   	'fileName' => $rs['name'],
	   	'fileType' => $rs['mime']
	   ];
	}
	http_response_code(200); // Set response code 200 (OK)
	echo json_encode($images);
}
catch(Exception $e) {
	http_response_code(500); // Set response code 500 (internal server error)
	echo json_encode($e);
}
