<?php
/**
 * FILE INCLUDES
 */
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/main.inc.php');

/**
 * AJAX Request validation
 */
header('Content-type:application/json;charset=utf-8');
$_POST = json_decode(file_get_contents('php://input'), true);
if(!isset($_GET['action']) || empty($_GET['action']) || $_GET['action'] != 'set')
{
	http_response_code(400); // Set response code 400 (bad request) and exit.
	die(json_encode('Invalid or missing POST-Parameter (Error 1)'));
}
if ( !isset($_POST['picid']) || empty($_POST['picid']) || $_POST['picid'] <= 0 )
{
	http_response_code(400); // Set response code 400 (bad request) and exit.
	die(json_encode('Invalid or missing POST-Parameter (Error 2)'));
}
if ( !isset($_POST['userid']) || empty($_POST['userid']) || $_POST['userid'] <= 0 )
{
	http_response_code(400); // Set response code 400 (bad request) and exit.
	die(json_encode('Invalid or missing POST-Parameter (Error 3)'));
}

/**
 * Add user_id to faceplusplus DB-table
 */
try {
	$sql = 'UPDATE gallery_pics_faceplusplus SET user_id_tagged = '.$_POST['userid'].' WHERE pic_id = '.$_POST['picid'];
	$query = $db->query($sql, __FILE__, __LINE__, 'AJAX.POST(set-userfaceid)');
	http_response_code(200); // Set response code 200 (OK)
}
catch(Exception $e) {
	http_response_code(500); // Set response code 500 (internal server error)
	die(json_encode($e));
}
