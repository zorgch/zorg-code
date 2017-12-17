<?php
/**
 * AJAX Request validation
 */
if(!isset($_GET['action']) || empty($_GET['action']) || $_GET['action'] != 'userfiles')
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
	$sql = 'SELECT name, mime FROM files WHERE user='.$_GET['userid'].' AND name LIKE "%'.$_GET['mention'].'%" ORDER BY upload_date DESC LIMIT 0,6';
	$result = $db->query($sql, __FILE__, __LINE__);
	while ($rs = mysql_fetch_array($result))
	{
	   $images[] = [
	   	'fileName' => $rs['name'],
	   	'fileType' => $rs['mime']
	   ];
	}
	echo json_encode($images);
}
catch(Exception $e) {
	echo json_encode($e);
}
