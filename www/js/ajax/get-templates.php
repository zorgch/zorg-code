<?php
/**
 * AJAX Request validation
 */
//if(!isset($_POST['action']) || empty($_POST['action']) || $_POST['action'] != 'userlist')
if(!isset($_GET['action']) || empty($_GET['action']) || $_GET['action'] != 'templates')
{
	die('Invalid or missing POST-Parameter');
	return false;
}

/**
 * FILE INCLUDES
 */
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/main.inc.php');

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
	echo json_encode($templates);
}
catch(Exception $e) {
	echo json_encode($e);
}
