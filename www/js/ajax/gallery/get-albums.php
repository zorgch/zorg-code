<?php
/**
 * AJAX request handling for getting a list of existing Gallery Albums
 *
 * @package zorg\Gallery\Gallery Maker
 */

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
	 * FILE INCLUDES
	 */
	require_once __DIR__.'/../../../includes/mysql.inc.php';

	if (!$showall) $sql = 'SELECT id, name, created FROM gallery_albums WHERE id NOT IN (SELECT album FROM gallery_pics)';
	//else $sql = 'SELECT g.id, g.name, g.created, COUNT(p.id) num_pics FROM gallery_albums g INNER JOIN gallery_pics p ON p.album = g.id WHERE g.id != 41 GROUP BY g.id ORDER BY g.name ASC';
	else $sql = 'SELECT g.id id, g.name name, g.created created, (SELECT COUNT(*) FROM gallery_pics p WHERE p.album=g.id) as num_pics FROM gallery_albums g WHERE g.id != 41 ORDER BY g.name ASC';
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
