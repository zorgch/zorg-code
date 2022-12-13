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

/**
 * FILE INCLUDES (additional)
 */
require_once __DIR__.'/../../../includes/mysql.inc.php';

/** Simple Error Handler for mkdir() E_WARNING */
function mkdirErrorHandler($errno, $errmsg, $errfile, $errline) {
	if ($errno >= E_WARNING)
	{
		error_log(sprintf('[ERROR] <%s:%d> Gallery Upload-Folder could NOT be created: %s', __FILE__, __LINE__, $errmsg));
		http_response_code(500); // Set response code 500 (Internal Server Error) and exit.
		exit($errmsg.' (line '.$errline.')');
	}
}

if ($user->typ >= USER_MEMBER)
{
	/* Get temporary next Gallery ID */
	$sql = 'SELECT id FROM gallery_albums ORDER BY id DESC LIMIT 1';
	$next_gallery_id = $db->fetch($db->query($sql, __FILE__, __LINE__, 'SELECT id FROM gallery_albums'))['id']+1;

	/**
	 * Create Gallery-specific Upload-Folder on Server - if not yet exists
	 */
	$upload_dirpath = GALLERY_UPLOAD_DIR.$next_gallery_id;
	if (!fileExists($upload_dirpath))
	{
		/* Gallery-specific Upload-Folder must be created */
		set_error_handler('mkdirErrorHandler');
		error_log(sprintf('[INFO] <%s:%d> User-ID %d attempts to create new Gallery Upload-Folder: %s', __FILE__, __LINE__, $user->id, $upload_dirpath));
		mkdir($upload_dirpath, 0775, TRUE); // If not exists, create the nested structure
		error_log(sprintf('[INFO] <%s:%d> User-ID %d created new Gallery Upload-Folder: %s', __FILE__, __LINE__, $user->id, $upload_dirpath));
		restore_error_handler();
	} else {
		/* Gallery-specific Upload-Folder exists */
		if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> Gallery Upload-Folder already created: %s', __FILE__, __LINE__, $upload_dirpath));
	}

	/* Add new empty Gallery Album to Database */
	$new_gallery_id = $db->insert('gallery_albums', ['name' => $new_album_name], __FILE__, __LINE__, __FUNCTION__);
	if ($new_gallery_id > 0 && $new_gallery_id >= $next_gallery_id)
	{
		http_response_code(200); // Set response code 200 (OK)
		exit((string)$new_gallery_id);
	} else {
		http_response_code(500); // Set response code 500 (Internal Server Error) and exit.
		exit('New Gallery could not be added');
	}
}
/** Insufficient Usertype level */
else {
	http_response_code(403); // Set response code 403 (forbidden) and exit.
	exit('forbidden');
}
