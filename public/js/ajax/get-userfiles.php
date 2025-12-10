<?php
header('Content-type:application/json;charset=utf-8');

/**
 * AJAX Request validation
 */
if(!isset($_GET['action']) || empty($_GET['action']) || $_GET['action'] !== 'userfiles')
{
	http_response_code(400); // Set response code 400 (bad request) and exit.
	echo json_encode(['error' => 'Invalid or missing POST-Parameter']);
	exit();
}
$user_id = filter_input(INPUT_GET, 'userid', FILTER_VALIDATE_INT);
$search_for = filter_input(INPUT_GET, 'mention', FILTER_SANITIZE_SPECIAL_CHARS);

if (!empty($user_id) && !empty($search_for))
{
	/**
 	* FILE INCLUDES
 	*/
	require_once __DIR__.'/../../includes/config.inc.php';

	/**
 	* Get records from database
 	*/
	$sql = 'SELECT name, mime FROM files WHERE user=? AND name LIKE CONCAT("%", ?, "%") ORDER BY upload_date DESC LIMIT 0,6';
	$result = $db->query($sql, __FILE__, __LINE__, 'AJAX.GET(get-userfiles)', [$user_id, $search_for]);
	if ($result !== false)
	{
		while ($rs = $db->fetch($result))
		{
			$images[] = [
				'fileName' => $rs['name'],
				'fileType' => $rs['mime']
			];
		}
		http_response_code(200); // Set response code 200 (OK)
		echo json_encode($images);
		exit();
	} else {
		http_response_code(204); // Set response code 204 (No Content) and exit.
		exit();
	}
} elseif (!empty($user_id) && empty($search_for)) {
	http_response_code(204); // Set response code 204 (No Content) and exit.
	exit();
} else {
	http_response_code(400); // Set response code 400 (bad request) and exit.
	echo json_encode(['error' => 'Invalid or missing GET-Parameter']);
	exit();
}
