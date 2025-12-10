<?php
/**
 * zorg Forum Thread fixer
 * @package zorg\Forum\Utils
 */
/**
 * File includes
 */
require_once __DIR__.'/../../public/includes/config.inc.php';
require_once INCLUDES_DIR.'usersystem.inc.php';
require_once INCLUDES_DIR.'mysql.inc.php';

if ($user->is_loggedin() && !empty(USER_SPECIAL) && $user->typ >= USER_SPECIAL)
{
	$sql = 'SELECT comments.*
			FROM comments
			LEFT JOIN comments_threads ON comments.thread_id = comments_threads.thread_id AND comments_threads.board = comments.board
			WHERE comments_threads.id IS NULL';
	$result = $db->query($sql, __FILE__, __LINE__, 'SELECT comments');

	while($rs = $db->fetch($result))
	{
		$sql =
			"REPLACE INTO comments_threads (board, thread_id, comment_id)"
			." VALUES ('".$rs['board']."', ".$rs['thread_id'].", ".$rs['id'].")"
		;
		$db->query($sql, __FILE__, __LINE__, 'Fix Threads');
		echo 'Fixed Thread: '.$rs['board']."', ".$rs['thread_id'].'<br />';
		flush();
	}
} else {
	http_response_code(403); // Set response code 403 (not allowed) and exit.
	echo 'Peremission denied!';
}
