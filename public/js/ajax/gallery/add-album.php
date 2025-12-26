<?php
/**
 * AJAX request handling for adding a new Gallery Album
 *
 * @package zorg\Gallery\Gallery Maker
 */

/**
  * FILE INCLUDES
  * @include config.inc.php Required at top in order to validate 'nonce' in $_SESSION!
  */
require_once __DIR__.'/../../../includes/config.inc.php';

/**
 * AJAX Request validation
 */
if(!isset($_GET['action']) || empty($_GET['action']) || $_GET['action'] !== 'add') // Action
{
	http_response_code(400); // Set response code 400 (bad request) and exit.
	exit('Invalid or missing GET-Parameter');
}
if (isset($_POST['nonce']) && !empty($_POST['nonce'])) // Nonce
{
	/** ! IMPORTANT: needs a SESSION to be (reused) - hence config.inc.php in the top... */
	if ($_SESSION['nonce']['gallery_maker']['add'] !== $_POST['nonce'])
	{
		http_response_code(403); // Set response code 403 (forbidden) and exit.
		exit('Invalid request validation');
	}
} else {
	http_response_code(401); // Set response code 401 (unauthorized) and exit.
	exit('Invalid or missing request validation');
}
if (!isset($_POST['album-name']) || empty($_POST['album-name'])) // Data
{
	http_response_code(406); // Set response code 406 (Not Acceptable) and exit.
	exit('Invalid or missing Data');
} else {
	$new_album_name = filter_var($_POST['album-name'], FILTER_SANITIZE_SPECIAL_CHARS);
}

/** Simple Error Handler for mkdir() E_WARNING */
function mkdirErrorHandler($errno, $errmsg, $errfile, $errline) {
	if ($errno >= E_WARNING)
	{
		error_log(sprintf('[ERROR] <%s:%d> Gallery Upload-Folder could NOT be created: %s', $errfile, $errline, $errmsg));
		http_response_code(507); // Set response code 507 (Insufficient Storage) and exit.
		exit($errmsg.' (line '.$errline.')');
	}
}

if ($user->typ >= USER_MEMBER)
{
	/* Add new empty Gallery Album to Database */
	$new_gallery_id = $db->insert('gallery_albums', ['name' => $new_album_name], __FILE__, __LINE__, __FUNCTION__);
	if (!$new_gallery_id || is_string($new_gallery_id))
	{
		/* Database Insert Error */
		http_response_code(500); // Set response code 500 (Internal Server Error) and exit.
		exit('New Gallery could not be added (database)');
	}
	else {
		/**
		 * Create Gallery-specific Upload-Folder on Server - if not yet exists
		 */
		$upload_dirpath = GALLERY_UPLOAD_DIR.(string)$new_gallery_id;
		if (!fileExists($upload_dirpath))
		{
			/* Gallery-specific Upload-Folder must be created */
			set_error_handler('mkdirErrorHandler');
			error_log(sprintf('[INFO] <%s:%d> User-ID %d attempts to create new Gallery Upload-Folder: %s', __FILE__, __LINE__, $user->id, $upload_dirpath));
			mkdir($upload_dirpath, 0775, TRUE); // If not exists, create the nested structure
			error_log(sprintf('[INFO] <%s:%d> User-ID %d created new Gallery Upload-Folder: %s', __FILE__, __LINE__, $user->id, $upload_dirpath));
			restore_error_handler();

			http_response_code(200); // Set response code 200 (OK)
			exit((string)$new_gallery_id);
		} else {
			/* Gallery-specific Upload-Folder exists */
			\zorgDebugger::log()->debug('Gallery Upload-Folder already created: %s', [$upload_dirpath]);
			http_response_code(200); // Set response code 200 (OK)
			exit((string)$new_gallery_id);
		}
	}
}
/** Insufficient Usertype level */
else {
	http_response_code(403); // Set response code 403 (forbidden) and exit.
	exit('forbidden');
}
