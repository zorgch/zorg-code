<?php
/**
 * AJAX Request validation
 */
if(!isset($_GET['action']) || empty($_GET['action']))
{
	http_response_code(400); // Set response code 400 (bad request) and exit.
	die('Invalid or missing GET-Parameter');
}
if(!isset($_POST['activity']) || empty($_POST['activity']) || !is_numeric($_POST['activity']) || $_POST['activity'] <= 0)
{
	http_response_code(400); // Set response code 400 (bad request) and exit.
	die('Invalid or missing POST-Parameter');
}
$activity = (int)$_POST['activity'];

/**
 * FILE INCLUDES
 */
require_once __DIR__.'/../../../includes/activities.inc.php';

/**
 * Delete Activity
 */
if ( $_GET['action'] === 'delete' )
{
	zorgDebugger::log()->info('Deleting existing Activity #%d', [$activity]);

	/** Instantiate new Activities() class & remove Activity */
	$activities = new Activities();
	$removeActivity = $activities->remove($activity);
	if ($removeActivity != false)
	{
		http_response_code(200); // Set response code 200 (OK)
		echo $removeActivity;
	} else {
		http_response_code(500); // Set response code 500 (internal server error)
		echo 'false';
	}

} else {
	http_response_code(403); // Set response code 403 (forbidden) and exit.
	die('Method not allowed');
}
