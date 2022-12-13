<?php
/**
 * AJAX request handling for adding a new Activity
 *
 * @package zorg\Activities
 */

/**
  * FILE INCLUDES
  * @include config.inc.php Required at top in order to validate 'nonce' in $_SESSION!
  */
require_once __DIR__.'/../../../includes/config.inc.php';

/**
 * AJAX Request validation
 */
if(!isset($_GET['action']) || empty($_GET['action']) || $_GET['action'] !== 'post')
{
	http_response_code(400); // Set response code 400 (bad request) and exit.
	die('Invalid or missing GET-Parameter');
} else {
	$action = 'post';
}
if (isset($_POST['nonce']) && !empty($_POST['nonce'])) // Nonce
{
	/** ! IMPORTANT: needs a SESSION to be (reused) - hence config.inc.php in the top... */
	if ($_SESSION['nonce']['activities']['post'] !== $_POST['nonce'])
	{
		http_response_code(403); // Set response code 403 (forbidden) and exit.
		exit('Invalid request validation');
	}
} else {
	http_response_code(401); // Set response code 401 (unauthorized) and exit.
	exit('Invalid or missing request validation');
}
if(!isset($_POST['activity']) || empty($_POST['activity']))
{
	http_response_code(400); // Set response code 400 (bad request) and exit.
	die('Invalid or missing POST-Parameter');
} else {
	//$activity_text = filter_var(json_decode($_POST['activity']), FILTER_SANITIZE_SPECIAL_CHARS);
	$activity_text = htmlspecialchars_decode($_POST['activity'], ENT_COMPAT | ENT_SUBSTITUTE);
}

/**
 * FILE INCLUDES (additional)
 */
require_once __DIR__.'/../../../includes/activities.inc.php';

if (isset($user->id) && !empty($user->id) && $action === 'post' && !empty($activity_text))
{
	/** Add Activity */
	$successful = Activities::addActivity($user->id, 0, $activity_text);
	if ($successful !== false)
	{
		http_response_code(200); // Set response code 200 (OK)
		exit('ok');
	} else {
		http_response_code(500); // Set response code 500 (internal server error)
		exit('activity');
	}
}
/** Permissions or prerequisites insufficient */
else {
	http_response_code(403); // Set response code 403 (forbidden) and exit.
	die('forbidden');
}
