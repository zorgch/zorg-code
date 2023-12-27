<?php
/**
 * AJAX Request validation
 */
if(!isset($_GET['action']) || empty($_GET['action']) || $_GET['action'] != 'check')
{
	http_response_code(400); // Set response code 400 (bad request) and exit.
	die('Invalid or missing GET-Parameter');
}

/**
 * FILE INCLUDES
 */
require_once dirname(__FILE__).'/../../../includes/config.inc.php';
require_once INCLUDES_DIR.'mysql.inc.php';

/**
 * Get records from database
 */
header('Content-type:application/json;charset=utf-8');
$_POST = json_decode(file_get_contents('php://input'), true);
try {
	$sql = 'SELECT recipient_id mail_status, recipient_confirmation read_status, recipient_confirmationdate read_datetime
			FROM verein_correspondence
			WHERE template_id=? AND recipient_id=?
			ORDER BY recipient_confirmationdate DESC
			LIMIT 1';
	$recipientStatus = $db->fetch($db->query($sql, __FILE__, __LINE__, 'AJAX.POST(get-recipientstatus)', [$_POST['template_id'], $_POST['recipient_id']]));

	http_response_code(200); // Set response code 200 (OK)
	if ($recipientStatus) {
		echo json_encode($recipientStatus);
	} else {
		echo 'false';
	}
}
catch(Exception $e) {
	http_response_code(500); // Set response code 500 (internal server error)
	echo $e->getMessage();
}
