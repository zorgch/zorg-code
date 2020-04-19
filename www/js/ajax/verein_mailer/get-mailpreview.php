<?php
/**
 * AJAX Request validation
 */
if(!isset($_GET['action']) || empty($_GET['action']) || $_GET['action'] != 'preview')
{
	http_response_code(400); // Set response code 400 (bad request) and exit.
	die('Invalid or missing GET-Parameter');
}

/**
 * FILE INCLUDES
 */
require_once dirname(__FILE__).'/../../../includes/config.inc.php';
require_once INCLUDES_DIR.'main.inc.php';

/**
 * Get records from database
 */
header('text/html;charset=utf-8');
try {
	$smarty->display('db:' . $_GET['mailtpl_id']);
	http_response_code(200); // Set response code 200 (OK)
}
catch(Exception $e) {
	http_response_code(500); // Set response code 500 (internal server error)
	echo $e->getMessage();
}
