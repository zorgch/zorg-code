<?php
/**
 * zorg Unread Comments fixer
 * @package zorg\Forum\Utils
 */
/**
 * File includes
 */
include_once(__DIR__.'/../includes/usersystem.inc.php');
include_once(__DIR__.'/../includes/mysql.inc.php');

if ($user->is_loggedin() && !empty(USER_SPECIAL) && $user->typ >= USER_SPECIAL)
{
	$sql = 'SELECT cu.comment_id AS id 
			FROM comments_unread cu 
				LEFT JOIN comments c ON cu.comment_id = c.id 
			WHERE c.id IS NULL 
			GROUP BY id 
			ORDER BY id ASC';
	$result = $db->query($sql, __FILE__, __LINE__, 'SELECT comments_unread');
	
	while($rs = $db->fetch($result)) {
		$db->query('DELETE FROM comments_unread where comment_id='.$rs['id'], __FILE__, __LINE__, 'DELETE unread_comments');
		echo 'deleted all unread_comments for post id '.$rs['id'].' <br />';
		flush();
	}

} else {
	http_response_code(403); // Set response code 403 (not allowed) and exit.
	echo 'Peremission denied!';
}
