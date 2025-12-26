<?php
/**
 * AJAX request handling for adding a new Pic to a Gallery Album
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
$action = filter_input(INPUT_GET, 'action', FILTER_SANITIZE_SPECIAL_CHARS) ?? null; // $_GET['action']
$gallery_id = filter_input(INPUT_POST, 'album_id', FILTER_VALIDATE_INT) ?? null; // $_POST['album_id']
$nonce = filter_input(INPUT_POST, 'nonce', FILTER_SANITIZE_SPECIAL_CHARS) ?? null; // $_POST['nonce']
if(empty($action) || $action !== 'add' || empty($gallery_id) || $gallery_id<=0)
{
	http_response_code(400); // Set response code 400 (bad request) and exit.
	exit('Invalid or missing GET-Parameter');
}
if (!empty($nonce)) // Nonce
{
	/** ! IMPORTANT: needs a SESSION to be (reused) - hence config.inc.php in the top... */
	if ($_SESSION['nonce']['gallery_maker']['add'] !== $nonce)
	{
		http_response_code(403); // Set response code 403 (forbidden) and exit.
		exit('Invalid request validation');
	}
} else {
	http_response_code(401); // Set response code 401 (unauthorized) and exit.
	exit('Invalid or missing request validation');
}
if (!isset($_FILES) || !isset($_FILES['dropzone-pic']['name']) || $_FILES['dropzone-pic']['error'] !== UPLOAD_ERR_OK) // Data
{
	http_response_code(406); // Set response code 406 (Not Acceptable) and exit.
	exit('Invalid or missing Data (Error: '.htmlspecialchars($_FILES['dropzone-pic']['error']).')');
} else {
	/**
	 * Sanitize Source File Properties
	 *
	 * Array
	 * (
	 *	 [dropzone-pic] => Array
	 *		(
	 *			[name] => Sample Image.png
	 *			[tmp_name] => /tmp/php/phpKUqTZQ
	 *			[type] => image/png
	 *	 		[size] => 194537
	 *			[error] => 0
	 *				0 = UPLOAD_ERR_OK
	 *				1 = UPLOAD_ERR_INI_SIZE
	 *				2 = UPLOAD_ERR_FORM_SIZE
	 *				3 = UPLOAD_ERR_PARTIAL
	 *				6 = UPLOAD_ERR_NO_TMP_DIR
	 *				7 = UPLOAD_ERR_CANT_WRITE
	 *				8 = UPLOAD_ERR_EXTENSION
	 *		)
	 */
	$sourcefile = null;
	$sourcefile['name'] = filter_var($_FILES['dropzone-pic']['name'], FILTER_SANITIZE_SPECIAL_CHARS);
	$sourcefile['tmp_name'] = $_FILES['dropzone-pic']['tmp_name']; // The temporary uploaded filename as stored on the server.
	$sourcefile['type'] = $_FILES['dropzone-pic']['type']; // example would be "image/gif"
	$sourcefile['size'] = $_FILES['dropzone-pic']['size']; // The size, in bytes
	$sourcefile['errorcode'] = $_FILES['dropzone-pic']['error']; // The error code associated with this file upload.
	//$_FILES['userfile']['full_path'] // (PHP 8.1+ only) The full path as submitted by the browser, not trustworthy
	$filename_parts = pathinfo($sourcefile['name']); // Allows splitting Filename & Extension
	$sourcefile['filename'] = $filename_parts['filename']; // Custom
	$sourcefile['fileextension'] = $filename_parts['extension']; // Custom
	if (isset($_POST['lastModified'])) $sourcefile['lastmodified'] = $_POST['lastModified']; // Custom

	/* DEBUG EXIT */
	//$print_arr = print_r($sourcefile, true);
	//exit((string)$print_arr);
}

/**
 * FILE INCLUDES (additional)
 */
require_once INCLUDES_DIR.'gallery.inc.php';

/** Simple Error Handler for mkdir() E_WARNING */
function mkdirErrorHandler($errno, $errmsg, $errfile, $errline) {
	if ($errno >= E_WARNING)
	{
		error_log(sprintf('[ERROR] <%s:%d> Gallery Upload-Folder could NOT be created: %s', $errfile, $errline, $errmsg));
		http_response_code(507); // Set response code 507 (Insufficient Storage) and exit.
		exit($errmsg.' (line '.$errline.')');
	}
}

if ($user->typ >= USER_MEMBER && $action === 'add' && $gallery_id > 0)
{
	/** Check if Upload Directory for Gallery exists */
	$upload_dirpath = rtrim(GALLERY_UPLOAD_DIR, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.strval($gallery_id);
	if (!is_dir($upload_dirpath))
	{
		set_error_handler('mkdirErrorHandler');
		error_log(sprintf('[INFO] <%s:%d> User-ID %d attempts to create new Gallery Folder: %s', __FILE__, __LINE__, $user->id, $upload_dirpath));
		mkdir($upload_dirpath, 0775, TRUE); // If not exists, create the nested structure
		error_log(sprintf('[INFO] <%s:%d> User-ID %d created new Gallery Folder: %s', __FILE__, __LINE__, $user->id, $upload_dirpath));
		restore_error_handler();
	}

	/** Check integrity of uploaded image */
	if (!is_uploaded_file($sourcefile['tmp_name']))
	{
		http_response_code(422); // Set response code 422 (Unprocessable Content) and exit.
		exit('compromised');
	} else {
		/** Iterate Filename if File with same name already exists in Upload Dir */
		$upload_filepath = $upload_dirpath.DIRECTORY_SEPARATOR.$sourcefile['name'];
		/** Check if a File with same name already exists - and iterate up */
		if (is_file($upload_filepath))
		{
			/** File with same name already exists - iterate up */
			$total_dirfilecount = count(glob($upload_dirpath."/*.".$sourcefile['fileextension']));
			if ($total_dirfilecount > 0)
			{
				$sourcefile['filename'] .= (string)($total_dirfilecount+1); // Add +1 and update Filename
				$upload_filepath = sprintf('%s/%s.%s', $upload_dirpath, $sourcefile['filename'], $sourcefile['fileextension']); // Redefine target filepath
				\zorgDebugger::log()->debug('Gallery Upload Directory filecount %d => new dest filepath: %s', [$total_dirfilecount, $upload_filepath]);
			}
			/** Directory Filecount failed */
			else {
				http_response_code(508); // Set response code 508 (Loop Detected) and exit.
				exit('filecount');
			}
		}

		/** Move Temporary File to actual Upload Directory */
		if (!move_uploaded_file($sourcefile['tmp_name'], $upload_filepath))
		{
			http_response_code(500); // Set response code 500 (internal server error) and exit.
			exit('filemove');
		}
	}

	/** Check if uploaded image is of valid type etc. */
	if (!isPic($upload_filepath))
	{
		http_response_code(406); // Set response code 406 (Not Acceptable) and exit.
		exit('unsupported');
	}

	/**
	 * Process uploaded Pic for the zorg Gallery
	 */
	/** Create final Gallery Album Directory */
	$gallery_dirpath = GALLERY_DIR.$gallery_id;
	if (!is_dir($gallery_dirpath))
	{
		set_error_handler('mkdirErrorHandler');
		error_log(sprintf('[INFO] <%s:%d> User-ID %d attempts to create new Gallery Folder: %s', __FILE__, __LINE__, $user->id, $gallery_dirpath));
		mkdir($gallery_dirpath, 0775, TRUE); // If not exists, create the nested structure
		error_log(sprintf('[INFO] <%s:%d> User-ID %d created new Gallery Folder: %s', __FILE__, __LINE__, $user->id, $gallery_dirpath));
		restore_error_handler();
	}

	/** Get the Pic-ID from a new DB-entry */
	$new_pic_id = $db->insert('gallery_pics', ['album'=>$gallery_id, 'extension'=>'.'.$sourcefile['fileextension']], __FILE__, __LINE__, 'INSERT INTO gallery_pics');
	if ($new_pic_id <= 0) {
		http_response_code(503); // Set response code 503 (Service Unavailable) and exit.
		exit('database');
	}
	else {
		$new_pic_path = picPath($gallery_id, $new_pic_id, $sourcefile['fileextension']);
		$new_thumb_path = tnPath($gallery_id, $new_pic_id, $sourcefile['fileextension']);

		/** Process image to large Pic */
		$le_pic = createPic($upload_filepath, $new_pic_path, MAX_PIC_SIZE['width'], MAX_PIC_SIZE['height']);
		$picsize = sprintf('width=%s height=%s', $le_pic['width'], $le_pic['height']);

		/** Process image to Thumbnail */
		$le_thumb = createPic($upload_filepath, $new_thumb_path, MAX_THUMBNAIL_SIZE['width'], MAX_THUMBNAIL_SIZE['height']);
		$tnsize = sprintf('width=%s height=%s', $le_thumb['width'], $le_thumb['height']);

		/** On error / failed to create... */
		if (($le_pic === false || isset($le_pic['error'])) ||
			($le_thumb === false || isset($le_thumb['error'])))
		{
			/** Reset to start over later... */
			$db->query('DELETE FROM gallery_pics WHERE id=?', __FILE__, __LINE__, 'DELETE FROM gallery_pics', [$new_pic_id]);
			@unlink($new_pic_path); // Delete Pic, suppress any errors
			@unlink($new_thumb_path); // Delete Thumbnail, suppress any errors

			$error = ($le_pic != false ? (isset($le_pic['error']) ? $le_pic['error'] : 'createpic') : 'lepic');
			$error .= ($le_thumb != false ? (isset($le_thumb['error']) ? $le_thumb['error'] : 'createthumb') : 'lethumb');
			http_response_code(500); // Set response code 500 (internal server error) and exit.
			exit($error);
		}
		/** On Success / Pics created! */
		else {
			/** Update DB-entry with File Sizes */
			$query = $db->update('gallery_pics', ['id', $new_pic_id], ['picsize' => $picsize, 'tnsize' => $tnsize], __FILE__, __LINE__, 'UPDATE gallery_pics');
		}
		http_response_code(200);
		exit((string)$new_pic_id);
	}
}
/** Insufficient Usertype level */
else {
	http_response_code(403); // Set response code 403 (forbidden) and exit.
	exit('forbidden');
}
