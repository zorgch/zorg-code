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
require_once dirname(__FILE__).'/../../../includes/config.inc.php';
require_once INCLUDES_DIR.'mysql.inc.php';

/**
 * Get records from database
 */
header('Content-type:application/json;charset=utf-8');
$_POST = json_decode(file_get_contents('php://input'), true);
$sql = 'SELECT id, username, vereinsmitglied FROM user WHERE vereinsmitglied IS NOT NULL AND vereinsmitglied = "'.$_POST['member_type'].'" ORDER BY username ASC';
$result = $db->query($sql, __FILE__, __LINE__, 'AJAX.POST(get-recipientlist)');
if (empty($result) || false === $result)
{
	http_response_code(500); // Set response code 500 (internal server error)
	die('Error... No recipients found!');
} else {
	while ($rs = $db->fetch($result))
	{
		if ((int)$rs['id'] !== VORSTAND_USER) // Vorstand-User ausschliessen (dem darf man nix mailen)
		{
			$memberlist[] = [
				'userid' => $rs['id'],
				'username' => $rs['username'],
				'membertype' => $rs['vereinsmitglied']
			];
		}
	}
	http_response_code(200); // Set response code 200 (OK)
	echo json_encode($memberlist);
}
