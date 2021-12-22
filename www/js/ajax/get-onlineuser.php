<?php
/**
 * Get unread Comments asynchronously
 *
 * @package zorg\Usersystem
 */
/**
 * AJAX Request validation
 */
if(!isset($_GET['style']) || empty($_GET['style']) || false === filter_var(trim($_GET['style']), FILTER_SANITIZE_STRING))
{
	http_response_code(400); // Set response code 400 (bad request) and exit.
	die('Invalid or missing GET-Parameter');
}
$onlineUserListstyle = filter_var(trim($_GET['style']), FILTER_SANITIZE_STRING);

/**
 * Get online user HTML
 */
switch ($onlineUserListstyle)
{
	case 'image':
		/** Requires usersystem.inc.php */
		require_once INCLUDES_DIR.'usersystem.inc.php';
		$onlineUserHtml = $user->online_users(true);

		if (!empty($onlineUserHtml))
		{
			http_response_code(200); // Set response code 200 (OK)
			header('Content-type: text/html; charset=utf-8');
			echo $onlineUserHtml;
		} else {
			http_response_code(204); // Set response code 204 (OK but no Content)
		}
		break;

	case 'list':
		/**
		 * Remarks: the following code incl. SQL-query has been extracted
		 * to run standalone (without further Usersystem or other contexts).
		 * The reason is to have a very minimal "overhead" for repeated
		 * checks for any online users (updating the corresponding frontend)
		 */
		/** Requires mysql.inc.php */
		require_once dirname(__FILE__).'/../../includes/mysql.inc.php';
		$sql = 'SELECT id, username, clan_tag FROM user WHERE activity > (NOW()-200) ORDER by activity DESC';
		$result = $db->query($sql, __FILE__, __LINE__, 'SELECT FROM user');
		/** Check if at least 1 user is online */
		$num_online = (false !== $result && !empty($result) ? (int)$db->num($result) : 0);
		if (false !== $num_online && !empty($num_online))
		{
			while ($rs = $db->fetch($result))
			{
				$onlineUsersArr[] = sprintf('<a href="/profil.php?user_id=%s">%s</a>', (string)$rs['id'], (!empty($rs['clan_tag']) ? $rs['clan_tag'] : '').$rs['username']);
			}
			http_response_code(200); // Set response code 200 (OK)
			header('Content-type: text/html; charset=utf-8');
			echo implode(', ', $onlineUsersArr);
		}
		/** No logged-in user seems to be online... */
		else {
			http_response_code(204); // Set response code 204 (OK but no Content)
		}
		break;

	default:
		http_response_code(400); // Set response code 400 (Bad Request)
		die('Invalid GET-Parameter');
}
