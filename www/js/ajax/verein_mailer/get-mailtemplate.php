<?php
/**
 * AJAX Request validation
 */
if(!isset($_GET['action']) || empty($_GET['action']) || $_GET['action'] != 'load')
{
	http_response_code(400); // Set response code 400 (bad request) and exit.
	die('Invalid or missing GET-Parameter');
}
$_POST = json_decode(file_get_contents('php://input'), true);
if(!isset($_POST['tpl_id']) || empty($_POST['tpl_id']) || !is_numeric($_POST['tpl_id']))
{
	http_response_code(400); // Set response code 400 (bad request) and exit.
	die('Invalid or missing POST-Parameter');
}

/**
 * FILE INCLUDES
 */
require_once( __DIR__ .'/../../../includes/mysql.inc.php');

/**
 * Get records from database
 */
header('Content-type:application/json;charset=utf-8');
try {
	//error_log('[DEBUG] Loading Template: ' . $_POST['tpl_id']);
	$sql = 'SELECT template_id, sender_id, subject_text, preview_text, message_text FROM verein_correspondence WHERE template_id = ' . $_POST['tpl_id'] . ' LIMIT 1';
	$result = $db->query($sql, __FILE__, __LINE__);
	while ($rs = mysql_fetch_array($result))
	{
		//error_log(sprintf("[DEBUG] Values:\n sender_id: %d, subject: %s, preview: %s, message: %s", $rs['sender_id'], $rs['subject_text'], $rs['preview_text'], $rs['message_text']));
		$templateValues = [
							 'owner' => $rs['sender_id']
							,'subject' => $rs['subject_text']
							,'preview' => $rs['preview_text']
							,'message' => $rs['message_text']
						];
	}
	http_response_code(200); // Set response code 200 (OK)
	echo json_encode($templateValues);
}
catch(Exception $e) {
	http_response_code(500); // Set response code 500 (internal server error)
	echo json_encode($e->getMessage());
}
