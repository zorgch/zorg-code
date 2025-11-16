<?php
/**
 * AJAX request handling for returning a list of Pictures of a Gallery Album
 *
 * @package zorg\Gallery\Gallery Maker
 */

/**
 * AJAX Request validation
 */
$action = filter_input(INPUT_GET, 'action', FILTER_SANITIZE_SPECIAL_CHARS) ?? null; // $_GET['action']
$album_id = filter_input(INPUT_GET, 'album_id', FILTER_VALIDATE_INT) ?? null; // $_GET['album_id']
if (empty($action) || $action !== 'fetch' || empty($album_id) || $album_id<=0)
{
	http_response_code(400); // Set response code 400 (bad request) and exit.
	exit('Invalid or missing GET-Parameter');
}

/**
 * Get records from database
 */
header('Content-type:application/json;charset=utf-8');
if ($action === 'fetch' && $album_id > 0)
{
	/**
	 * FILE INCLUDES
	 */
	//require_once __DIR__.'/../../../includes/mysql.inc.php';
	require_once __DIR__.'/../../../includes/gallery.inc.php';

	$sql = 'SELECT id as pic_id, album as album_id, name as pic_name, extension FROM gallery_pics WHERE album=? ORDER BY id ASC';
	$result = $db->query($sql, __FILE__, __LINE__, 'SELECT FROM gallery_pics', [$album_id]);
	$num_pics = $db->num($result);

	http_response_code(200); // Set response code 200 (OK)
	if ($num_pics > 0)
	{
		while ($rs = $db->fetch($result))
		{
			$pics[] = [
				'id' => $rs['pic_id'],
				'title' => $rs['pic_name'],
				'url' => '/gallery/thumbs/'.$rs['pic_id']
			];
		}
		exit(json_encode($pics));
	}
	exit(json_encode(0));
}
/** Invalid Input */
else {
	http_response_code(400); // Set response code 400 (bad request) and exit.
	exit('Invalid GET-Parameter');
}
