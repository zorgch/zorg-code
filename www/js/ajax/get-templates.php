<?php
header('Content-type:application/json;charset=utf-8');

/**
 * AJAX Request validation
 */
if(!isset($_GET['action']) || empty($_GET['action']) || $_GET['action'] != 'templates')
{
	http_response_code(400); // Set response code 400 (bad request) and exit.
	echo json_encode(['error' => 'Invalid or missing POST-Parameter']);
	exit();
}
$search_for = filter_input(INPUT_GET, 'mention', FILTER_SANITIZE_SPECIAL_CHARS);

if (!empty($search_for))
{
	/**
 	* FILE INCLUDES
 	*/
	require_once __DIR__.'/../../includes/config.inc.php';

	/**
 	* Get records from database
 	*/
	$sql = 'SELECT id, title, read_rights FROM templates WHERE title LIKE "?%" AND read_rights <= 1 ORDER BY CHAR_LENGTH(title) ASC, title ASC LIMIT 0,6';
	$result = $db->query($sql, __FILE__, __LINE__, 'SELECT', [$search_for]);
	if ($result !== false)
	{
		while ($rs = $db->fetch($result))
		{
			$templates[] = [
				'tplId' => $rs['id'],
				'tplTitle' => $rs['title']
			];
		}
		http_response_code(200); // Set response code 200 (OK)
		echo json_encode($templates);
		exit();
	} else {
		http_response_code(204); // Set response code 204 (No Content)
		exit();
	}
} else {
	http_response_code(400); // Set response code 400 (bad request) and exit.
	echo json_encode(['error' => 'Invalid or missing GET-Parameter']);
	exit();
}
