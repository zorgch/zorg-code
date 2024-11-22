<?php
require_once __DIR__.'/../includes/config.inc.php';
require_once INCLUDES_DIR.'mysql.inc.php';
require_once INCLUDES_DIR.'usersystem.inc.php';

if ($user->is_loggedin())
{
	$doAction = filter_input(INPUT_GET, 'action', FILTER_DEFAULT, FILTER_REQUIRE_SCALAR) ?? null;
	$board = filter_input(INPUT_GET, 'board', FILTER_DEFAULT, FILTER_REQUIRE_SCALAR) ?? null;
	$thread_id = filter_input(INPUT_GET, 'thread_id', FILTER_SANITIZE_NUMBER_INT) ?? null;
	$sql = null;
	$sqlparams = [];

	// The magical chain of actions...
	switch ($doAction)
	{
		case 'sticky':
			$sql =	"UPDATE comments_threads SET sticky = '1' where thread_id=?";
			$sqlparams[] = $thread_id;
			break;

		case 'unsticky':
			$sql = "UPDATE comments_threads SET sticky = '0' where thread_id=?";
			$sqlparams[] = $thread_id;
			break;

		case 'favorite':
			$sql = "INSERT INTO comments_threads_favorites (board, thread_id, user_id) VALUES (?, ?, ?)";
			$sqlparams[] = $board;
			$sqlparams[] = $thread_id;
			$sqlparams[] = $user->id;
			break;

		case 'unfavorite':
			$sql ="DELETE FROM comments_threads_favorites WHERE board=? AND thread_id=? AND user_id=?";
			$sqlparams[] = $board;
			$sqlparams[] = $thread_id;
			$sqlparams[] = $user->id;
			break;

		case 'ignore':
			$sql =	"INSERT INTO comments_threads_ignore (board, thread_id, user_id) VALUES (?, ?, ?)";
			$sqlparams[] = $board;
			$sqlparams[] = $thread_id;
			$sqlparams[] = $user->id;
			break;

		case 'unignore':
			$sql = "DELETE FROM comments_threads_ignore WHERE board=? AND thread_id=? AND user_id=?";
			$sqlparams[] = $board;
			$sqlparams[] = $thread_id;
			$sqlparams[] = $user->id;
			break;
	}

	// ...execute the query which made it into $sql
	if (!empty($sql))
	{
		$db->query($sql, __FILE__, __LINE__, 'Forum Actions', $sqlparams);
	}
}

header("Location: ".SITE_URL."/forum.php");
exit;
