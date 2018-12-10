<?php
/**
 * Migration Script to change every user's
 * - forum_boards
 * - forum_boards_unread
 * from PHP-Array to JSON format. Here's an example:
 * - old value: f,i,e,t,o,r,
 * - new value: ["f","i","e","t","o","r"]
 * - old value: f,
 * - new value: ["f"]
 *
 * @author IneX
 * @package zorg
 * @subpackage Scripts
 * @date 27.11.2018
 * @version 1.0
 * @since 1.0 27.11.2018 Script added
 *
 * @see /includes/usersystem.inc.php, /templates/layout/pages/profile_page.tpl
 * @see Usersystem::$default_notifications, Usersystem::$default_forum_boards_unread
 */
if ($_GET['migration'] === 'start')
{
	error_log(sprintf('[INFO] <%s:%d> Starting...', 'users_convert_forum_boards.php', __LINE__));

	/**
	 * File includes
	 * @include usersystem.inc.php required
	 */
	require_once( __DIR__ .'/../../www/includes/usersystem.inc.php');
	error_log(sprintf('[INFO] <%s:%d> Included usersystem.inc.php', 'users_convert_forum_boards.php', __LINE__));

	/**
	 * Change 'Default value' on MySQL-DB columns
	 * - forum_boards
	 * - forum_boards_unread
	 */
	$forum_boards_alter_query = 'ALTER TABLE user ALTER forum_boards SET DEFAULT "'.sanitize_userinput($user->default_forum_boards).'"';
	$forumBoards_changeDefaultValue = $db->query($forum_boards_alter_query, __FILE__, __LINE__, 'ALTER TABLE user SET DEFAULT forum_boards');
	if($forumBoards_changeDefaultValue !== FALSE)
	{
	   error_log(sprintf('[INFO] <%s:%d> SET DEFAULT on table user for "forum_boards": SUCCESS', 'users_convert_forum_boards.php', __LINE__));
	} else {
	   error_log(sprintf('[INFO] <%s:%d> SET DEFAULT on table user for "forum_boards": ERROR', 'users_convert_forum_boards.php', __LINE__));
	}
	$forum_boards_unread_alter_query = 'ALTER TABLE user ALTER forum_boards_unread SET DEFAULT "'.sanitize_userinput($user->default_forum_boards_unread).'"';
	$forumBoards_changeDefaultValue = $db->query($forum_boards_unread_alter_query, __FILE__, __LINE__, 'ALTER TABLE user SET DEFAULT forum_boards_unread');
	if($forumBoards_changeDefaultValue !== FALSE)
	{
	   error_log(sprintf('[INFO] <%s:%d> SET DEFAULT on table user for "forum_boards": SUCCESS', 'users_convert_forum_boards.php', __LINE__));
	} else {
	   error_log(sprintf('[INFO] <%s:%d> SET DEFAULT on table user for "forum_boards": ERROR', 'users_convert_forum_boards.php', __LINE__));
	}

	/**
	 * Query all users (not only actives)
	 * If only active user shall be queried, do:
	 * 	WHERE 
	 *	 active = "1" 
	 *	 AND (lastlogin > "0000-00-00 00:00:00"
	 *	 OR activity > "0000-00-00 00:00:00")
	 */
	$startAll = microtime(true); // Start execution time measurement (total)
	try {
		error_log(sprintf('[INFO] <%s:%d> Query all active Users from database', 'users_convert_forum_boards.php', __LINE__));
		$sql = 'SELECT 
					 id, username, forum_boards, forum_boards_unread
				 FROM 
				 	 user 
				 ORDER BY id ASC';
				 //LIMIT 0,10';
		$query = $db->query($sql, __FILE__, __LINE__, 'SELECT FROM user');
	} catch (Exception $e) {
		user_error(sprintf('[ERROR] <%s:%d> %s', 'users_convert_forum_boards.php', __LINE__, $e->getMessage()));
		exit;
	}

	error_log(sprintf('[INFO] <%s:%d> User-Iteration starting...', 'users_convert_forum_boards.php', __LINE__));
	while ($userRecords = $db->fetch($query))
	{
		$startRecord = microtime(true); // Start execution time measurement (record)
		$id = $userRecords['id'];
		error_log(sprintf('[INFO] <%s:%d> (%d) Fetched User "%s" for processing', 'users_convert_forum_boards.php', __LINE__, $id, $userRecords['username']));

		/**
		 * 1) Processing 'forum_boards'
		 */
		error_log(sprintf('[INFO] <%s:%d> (%d) checking forum_boards: %s', 'users_convert_forum_boards.php', __LINE__, $id, $userRecords['forum_boards']));
		/** Make sure we don't have a JSON-String yet */
		if (!empty($userRecords['forum_boards']) && !is_array(json_decode($userRecords['forum_boards'], true)))
		{
			/** Make sure we can resolve old String to an Array (so it's not empty) */
			error_log(sprintf('[INFO] <%s:%d> (%d) forum_boards is no JSON-string yet', 'users_convert_forum_boards.php', __LINE__, $id));
			if (is_array(explode(',', $userRecords['forum_boards'])))
			{
				error_log(sprintf('[INFO] <%s:%d> (%d) Converting forum_boards String to JSON...', 'users_convert_forum_boards.php', __LINE__, $id));
				$forum_boards_phparray = array_filter(explode(',', $userRecords['forum_boards']), 'strlen'); // array_filter removes empty Array-elements
				$forum_boards_json = json_encode(array_values($forum_boards_phparray));  // array_values removes index-keys being falesly encoded to the JSON
				error_log(sprintf('[INFO] <%s:%d> (%d) DONE - converted forum_boards string to JSON: %s', 'users_convert_forum_boards.php', __LINE__, $id, $forum_boards_json));
				$forum_boards_json = sanitize_userinput($forum_boards_json); // Prepare for writing String to Database
			}
		}
		/** We already have a JSON-string, so reuse it */
		elseif (is_array(json_decode($userRecords['forum_boards'], true)))
		{
			error_log(sprintf('[INFO] <%s:%d> (%d) forum_boards is already JSON-string: %s', 'users_convert_forum_boards.php', __LINE__, $id, $userRecords['forum_boards']));
			$forum_boards_json = sanitize_userinput($userRecords['forum_boards']);
		}
		/** forum_boards are missing / empty */
		elseif (empty($userRecords['forum_boards']))
		{
			error_log(sprintf('[INFO] <%s:%d> (%d) forum_boards are missing / empty', 'users_convert_forum_boards.php', __LINE__, $id));
			$forum_boards_json = sanitize_userinput($user->default_forum_boards);
		}

		/**
		 * 2) Processing 'forum_boards_unread'
		 */
		error_log(sprintf('[INFO] <%s:%d> (%d) checking forum_boards_unread: %s', 'users_convert_forum_boards.php', __LINE__, $id, $userRecords['forum_boards_unread']));
		/** Make sure we don't have a JSON-String yet */
		if (!empty($userRecords['forum_boards_unread']) && !is_array(json_decode($userRecords['forum_boards_unread'], true)))
		{
			/** Make sure we can resolve old String to an Array (so it's not empty) */
			error_log(sprintf('[INFO] <%s:%d> (%d) forum_boards_unread is no JSON-string yet', 'users_convert_forum_boards.php', __LINE__, $id));
			if (is_array(explode(',', $userRecords['forum_boards_unread'])))
			{
				error_log(sprintf('[INFO] <%s:%d> (%d) Converting forum_boards_unread String to JSON...', 'users_convert_forum_boards.php', __LINE__, $id));
				$forum_boards_unread_phparray = array_filter(explode(',', $userRecords['forum_boards_unread']), 'strlen'); // array_filter removes empty Array-elements
				$forum_boards_unread_json = json_encode(array_values($forum_boards_unread_phparray)); // array_values removes index-keys being falesly encoded to the JSON
				error_log(sprintf('[INFO] <%s:%d> (%d) DONE - converted forum_boards_unread string to JSON: %s', 'users_convert_forum_boards.php', __LINE__, $id, $forum_boards_unread_json));
				$forum_boards_unread_json = sanitize_userinput($forum_boards_unread_json); // Prepare for writing String to Database
			}
		}
		/** We already have a JSON-string, so reuse it */
		elseif (is_array(json_decode($userRecords['forum_boards_unread'], true)))
		{
			error_log(sprintf('[INFO] <%s:%d> (%d) forum_boards_unread is already JSON-string: %s', 'users_convert_forum_boards.php', __LINE__, $id, $userRecords['forum_boards_unread']));
			$forum_boards_unread_json = sanitize_userinput($userRecords['forum_boards_unread']);
		}
		/** forum_boards_unread are missing / empty */
		elseif (empty($userRecords['forum_boards_unread']))
		{
			error_log(sprintf('[INFO] <%s:%d> (%d) forum_boards_unread are missing / empty', 'users_convert_forum_boards.php', __LINE__, $id));
			$forum_boards_json = sanitize_userinput($user->default_forum_boards_unread);
		}

		/**
		 * 3) Update Database-Record for User
		 */
		if (!empty($forum_boards_json) && !empty($forum_boards_unread_json))
		{
			error_log(sprintf('[INFO] <%s:%d> (%d) Writing new JSON-strings to database...', 'users_convert_forum_boards.php', __LINE__, $id));
			try {
				$updateUserDbRecord = $db->update('user', ['id', $id], ['forum_boards' => $forum_boards_json, 'forum_boards_unread' => $forum_boards_unread_json], 'users_convert_forum_boards.php', __LINE__, 'UPDATE user SET forum_boards, forum_boards_unread');
				if ($updateUserDbRecord === 0 || !$updateUserDbRecord)
				{
					error_log(sprintf('[INFO] <%s:%d> (%d) ERROR - update of database failed, or no change required.', 'users_convert_forum_boards.php', __LINE__, $id));
				}
			} catch (Exception $e) {
				error_log(sprintf('[INFO] <%s:%d> (%d) EXCEPTION - update of database failed: %s.', 'users_convert_forum_boards.php', __LINE__, $id, $e->getMessage()));
			}
			error_log(sprintf('[INFO] <%s:%d> (%d) DONE - new JSON-strings updated in database in %g s', 'users_convert_forum_boards.php', __LINE__, $id, microtime(true) - $startRecord)); // Stop execution time measurement (record)
		}
	}

	/** Execution time (total) */
	sprintf('[INFO] <%s:%d> Execution completed within %g s', 'users_convert_forum_boards.php', __LINE__, microtime(true) - $startAll);
}

/** Password mismatch */
else {
	user_error('Zauberwörtli bitte', E_USER_NOTICE);
}
