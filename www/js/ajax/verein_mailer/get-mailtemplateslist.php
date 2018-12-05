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
require_once( __DIR__ .'/../../../includes/usersystem.inc.php');
require_once( __DIR__ .'/../../../includes/util.inc.php');

/**
 * Get records from database
 */
header('Content-type:application/json;charset=utf-8');
try {
	$sql = 'SELECT tpls.id, tpls.owner, UNIX_TIMESTAMP(tpls.last_update) as updated, corr.subject_text
			FROM verein_correspondence corr
				INNER JOIN templates tpls
				ON corr.template_id = tpls.id
			WHERE corr.recipient_id = 451
			ORDER BY updated DESC';
	$result = $db->query($sql, __FILE__, __LINE__, 'AJAX.GET(get-mailtemplate)');
	while ($rs = mysql_fetch_array($result))
	{
		$templates[] = [
			 'tplid' => $rs['id']
			,'owner' => $user->id2user($rs['owner'], TRUE)
			,'subject' => $rs['subject_text']
			,'updated' => datename($rs['updated'])
		];
	}
	http_response_code(200); // Set response code 200 (OK)
	echo json_encode($templates);
}
catch(Exception $e) {
	http_response_code(500); // Set response code 500 (internal server error)
	echo json_encode($e->getMessage());
}
