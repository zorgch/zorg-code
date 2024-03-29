<?php
/**
 * AJAX Request validation
 */
if(!isset($_GET['action']) || empty($_GET['action']) || ( $_GET['action'] != 'save' && $_GET['action'] != 'update' ))
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
 * Delete a template
 */
if ( $_GET['action'] == 'delete' && !empty($_GET['template_id']) && is_numeric($_GET['template_id']) )
{
	error_log('[INFO] Deleting existing Mail Template ' . $_GET['template_id']);


	http_response_code(200); // Set response code 200 (OK)
	echo $tplid;

} else {
	http_response_code(403); // Set response code 403 (forbidden) and exit.
	die('Method not allowed');
}
