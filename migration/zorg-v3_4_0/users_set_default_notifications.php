<?php
/**
 * Migration Script to initially set every user's
 * - notifications
 * because new field & data which every user needs to set:
 * - old value: EMPTY / NULL
 * - new value: {"bugtracker":{"message":"true","email":"true"},"games":{"email":"true"},"mentions":{"email":"true"},"messagesystem":{"email":"true"},"subscriptions":{"message":"true"}}
 *
 * @author IneX
 * @date 27.11.2018
 * @package zorg
 * @subpackage Scripts
 * @version 1.0
 * @since 1.0 27.11.2018 Script added
 *
 * @see /includes/usersystem.inc.php, /templates/layout/pages/profile_page.tpl
 * @see Usersystem::$notifications
 */
if ($_GET['migration'] === 'start')
{
	error_log(sprintf('[INFO] <%s:%d> Starting...', 'user_set_default_notifications.php', __LINE__));

	/**
	 * File includes
	 * @include usersystem.inc.php required
	 */
	require_once( __DIR__ .'/../../www/includes/usersystem.inc.php');
	error_log(sprintf('[INFO] <%s:%d> Included usersystem.inc.php', 'user_set_default_notifications.php', __LINE__));

	/**
	 * Query all users
	 */
	$startAll = microtime(true); // Start execution time measurement (total)
	try {
		error_log(sprintf('[INFO] <%s:%d> Query all active Users from database', 'user_set_default_notifications.php', __LINE__));
		$sql = 'SELECT 
					 id, username, notifications
				 FROM 
				 	 user 
				 ORDER BY id ASC';
				 //LIMIT 0,5';
		$query = $db->query($sql, __FILE__, __LINE__, 'SELECT FROM user');
	} catch (Exception $e) {
		user_error(sprintf('[ERROR] <%s:%d> %s', 'user_set_default_notifications.php', __LINE__, $e->getMessage()));
		exit;
	}

	error_log(sprintf('[INFO] <%s:%d> User-Iteration starting...', 'user_set_default_notifications.php', __LINE__));
	while ($userRecords = $db->fetch($query))
	{
		$startRecord = microtime(true); // Start execution time measurement (record)
		$id = $userRecords['id'];
		error_log(sprintf('[INFO] <%s:%d> (%d) Fetched User "%s" for processing', 'user_set_default_notifications.php', __LINE__, $id, $userRecords['username']));

		/**
		 * Processing 'notifications'
		 */
		error_log(sprintf('[INFO] <%s:%d> (%d) checking notifications: %s', 'user_set_default_notifications.php', __LINE__, $id, $userRecords['notifications']));
		/** Make sure we don't have a JSON-String yet */
		if (empty($userRecords['notifications']) && !is_array(json_decode($userRecords['notifications'], true)))
		{
			error_log(sprintf('[INFO] <%s:%d> (%d) notifications are missing / empty', 'user_set_default_notifications.php', __LINE__, $id));
			$user_notifications = sanitize_userinput($user->default_notifications);
		}
		/** We already have a JSON-string, so reuse it */
		elseif (is_array(json_decode($userRecords['notifications'], true)))
		{
			error_log(sprintf('[INFO] <%s:%d> (%d) "notifications" is already JSON-string: %s', 'user_set_default_notifications.php', __LINE__, $id, $userRecords['notifications']));
			$user_notifications = sanitize_userinput($userRecords['notifications']);
		}

		/**
		 * Update Database-Record for User
		 */
		if (!empty($user_notifications) && !empty($user_notifications))
		{
			error_log(sprintf('[INFO] <%s:%d> (%d) Writing new JSON-strings to database...', 'user_set_default_notifications.php', __LINE__, $id));
			try {
				$updateUserDbRecord = $db->update('user', ['id', $id], ['notifications' => $user_notifications], 'user_set_default_notifications.php', __LINE__, 'UPDATE user SET notifications');
				if ($updateUserDbRecord === 0 || !$updateUserDbRecord)
				{
					error_log(sprintf('[INFO] <%s:%d> (%d) ERROR - update of database failed, or no change required.', 'user_set_default_notifications.php', __LINE__, $id));
				}
			} catch (Exception $e) {
				error_log(sprintf('[INFO] <%s:%d> (%d) EXCEPTION - update of database failed: %s.', 'user_set_default_notifications.php', __LINE__, $id, $e->getMessage()));
			}
			error_log(sprintf('[INFO] <%s:%d> (%d) DONE - new JSON-strings updated in database in %g s', 'user_set_default_notifications.php', __LINE__, $id, microtime(true) - $startRecord)); // Stop execution time measurement (record)
		}
	}

	/** Execution time (total) */
	sprintf('[INFO] <%s:%d> Execution completed within %g s', 'user_set_default_notifications.php', __LINE__, microtime(true) - $startAll);
}

/** Password mismatch */
else {
	user_error('Zauberwörtli bitte', E_USER_NOTICE);
}
