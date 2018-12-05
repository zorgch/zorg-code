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

/**
 * FILE INCLUDES
 */
require_once( __DIR__ .'/../../../includes/activities.inc.php');

/**
 * Delete Activity
 */
try {
	if ( $_GET['action'] === 'delete' && !empty($_POST['activity']) && is_numeric($_POST['activity']) && $_POST['activity'] > 0 )
	{
		error_log('[INFO] Deleting existing Activity #' . $_POST['activity']);

		/** Instantiate new Activities() class & remove Activity */
		$activities = new Activities();
		$removeActivity = $activities->remove($_POST['activity']);
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

}
catch(Exception $e) {
	http_response_code(500); // Set response code 500 (internal server error)
	echo $e->getMessage();
}
