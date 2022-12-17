<?php
/**
 * AJAX request handling for getting a list of existing Gallery Albums
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
if(!isset($_GET['action']) || empty($_GET['action']) || $_GET['action'] !== 'list')
{
	http_response_code(400); // Set response code 400 (bad request) and exit.
	exit('Invalid or missing GET-Parameter');
} else {
	$action = $_GET['action'];
	$showall = (isset($_GET['showall']) && !empty($_GET['showall']) && $_GET['showall'] === 'true' ? TRUE : FALSE);
}

/**
 * Get records from database
 */
header('Content-type:application/json;charset=utf-8');
if ($action === 'list')
{
	/**
	 * FILE INCLUDES (additional)
	 */
	require_once __DIR__.'/../../../includes/mysql.inc.php';

	if (!$showall) $sql = 'SELECT id, name, created FROM gallery_albums WHERE id NOT IN (SELECT album FROM gallery_pics)';
	else $sql = 'SELECT g.id id, g.name name, g.created created, COUNT(p.id) num_pics FROM gallery_albums g LEFT OUTER JOIN gallery_pics p ON p.album=g.id
				 WHERE g.id != '.APOD_GALLERY_ID.' GROUP BY g.id ORDER BY COUNT(p.id)=0 DESC, g.name ASC';
	$result = $db->query($sql, __FILE__, __LINE__, 'SELECT FROM gallery_albums');
	$numresult = $db->num($result);
	if ($numresult > 0)
	{
		while ($rs = $db->fetch($result))
		{
			$albums[] = [
			 	'id' => $rs['id']
				,'name' => $rs['name']
				,'created' => ($rs['created'] === '0000-00-00 00:00:00' ? NULL : $rs['created'])
			];
		}
	} else {
		$albums[] = null;
	}
	http_response_code(200); // Set response code 200 (OK)
	exit(json_encode($albums));
}
/** Invalid Input */
else {
	http_response_code(400); // Set response code 500 (internal server error)
	exit('Invalid GET-Parameter');
}
