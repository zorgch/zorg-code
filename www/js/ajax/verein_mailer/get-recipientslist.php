<?php
/**
 * AJAX Request validation
 */
if(!isset($_GET['action']) || empty($_GET['action']) || $_GET['action'] != 'list')
{
	http_response_code(400); // Set response code 400 (bad request) and exit.
	die('Invalid or missing GET-Parameter');
}

/**
 * FILE INCLUDES
 */
require_once( __DIR__ .'/../../../includes/config.inc.php');
require_once( __DIR__ .'/../../../includes/mysql.inc.php');

/**
 * Get records from database
 */
header('Content-type:application/json;charset=utf-8');
$_POST = json_decode(file_get_contents('php://input'), true);
try {
	//error_log('[DEBUG] ' . $_POST['member_type']);
	$sql = 'SELECT id, username, vereinsmitglied FROM user WHERE vereinsmitglied IS NOT NULL AND vereinsmitglied = "'.$_POST['member_type'].'" ORDER BY username ASC';
	$result = $db->query($sql, __FILE__, __LINE__, 'AJAX.POST(get-recipientlist)');
	while ($rs = $db->fetch($result))
	{
		//error_log('[DEBUG] ' . $rs['id'] . ', ' . $rs['username'] . ', ' . $rs['vereinsmitglied']);
		$memberlist[] = [
			'userid' => $rs['id'],
			'username' => $rs['username'],
			'membertype' => $rs['vereinsmitglied']
		];
	}
	http_response_code(200); // Set response code 200 (OK)
	echo json_encode($memberlist);
}
catch(Exception $e) {
	http_response_code(500); // Set response code 500 (internal server error)
	echo json_encode($e->getMessage());
}
