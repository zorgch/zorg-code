<?php
/**
 * zorg Stats
 * Verschiedene Klassen um Statistiken zu generieren. Zum Beispiel:
 * - User Stats
 * - zorg Weekly/Monthly/Yearly Stats
 *
 * Diese Klasee benutzt folgende Tabellen aus der DB:
 *		user
 *		activities
 *		chat
 *		comments
 *		comments_threads
 *		events
 *		events_to_user
 *		gallery_albums
 *		bugtracker_bugs
 *		messages
 *		go_games
 *		addle
 *		hz_games
 *		peter
 *		peter_players
 *		stl
 *		stl_players
 *		polls
 *		wetten
 *		wetten_teilnehmer
 *
 * @author		IneX
 * @package		zorg\Usersystem
 */
/**
 * File includes
 * @include config.inc.php
 * @include mysql.inc.php
 */
require_once dirname(__FILE__).'/config.inc.php';
require_once INCLUDES_DIR.'mysql.inc.php';
require_once INCLUDES_DIR.'addle.inc.php';
require_once INCLUDES_DIR.'bugtracker.inc.php';

/**
 * zorg User Stats Class
 * In dieser Klasse befinden sich alle Methoden zum Erstellen und Anzeigen von User-basierten Stats
 *
 * @author		IneX
 * @date		19.08.2018
 * @package		zorg
 * @subpackage	Usersystem
 * @version		1.0
 * @since		1.0 Class added with first version of methods
 */
class UserStatistics
{
	/**
	* Define global default Stats settings
	* @const TELEGRAM_BOT_PARSE_MODE Specifies the Message Format to use - either empty, Markdown or HTML
	* @const TELEGRAM_BOT_DISABLE_WEB_PAGE_PREVIEW Specifies whether link previews for links in the message should be enabled or disabled
	* @const TELEGRAM_BOT_DISABLE_NOTIFICATION Specifies whether the Bot's messages should be silent or regular notifications
	*/
	/*const PARSE_MODE = 'html';
	const DISABLE_WEB_PAGE_PREVIEW = 'false';
	const DISABLE_NOTIFICATION = 'false';*/

	/**
	 * Last login statistic
	 *
	 * @author	IneX
	 * @date	21.08.2018
	 * @version	1.0
	 * @since	1.0 method added
	 *
	 * @see usersystem::
	 * @param	integer	$userid	User-ID integer to show Stats for
	 * @global	object	$db		Globales Class-Object mit allen MySQL-Methoden
	 * @return	string|boolean	Returns timestamp of last user activity, or false
	 */
	public function last_login($userid)
	{
		global $db;

		/** Validate passed $userid parameter */
		if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> Passed $userid: %d', __METHOD__, __LINE__, $userid));
		if (empty($userid) || $userid <= 0)
		{
			error_log(sprintf('[WARN] <%s:%d> Invalid $userid: %d', __METHOD__, __LINE__, $userid));
			return false;

		/** Passed $userid seems legit */
		} else {
			$sql = 'SELECT UNIX_TIMESTAMP(activity) AS last_activity FROM user WHERE id='.$userid;
			$result = $db->fetch($db->query($sql, __FILE__, __LINE__, __METHOD__));

			/** A User with $userid exists */
			if (!empty($result))
			{
				return $result['last_activity'];
			}
		}
	}

	/**
	 * Bugtracker reported pending Bugs statistic
	 *
	 * @author	IneX
	 * @date	21.08.2018
	 * @version	1.0
	 * @since	1.0 method added
	 *
	 * @see usersystem::
	 * @param	integer	$userid	User-ID integer to show Stats for
	 * @global	object	$db		Globales Class-Object mit allen MySQL-Methoden
	 * @return	integer|boolean	Returns number of User's pending Bugs, or false
	 */
	public function reported_pending_bugs($userid)
	{
		global $db;

		/** Validate passed $userid parameter */
		if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> Passed $userid: %d', __METHOD__, __LINE__, $userid));
		if (empty($userid) || $userid <= 0)
		{
			error_log(sprintf('[WARN] <%s:%d> Invalid $userid: %d', __METHOD__, __LINE__, $userid));
			return false;

		/** Passed $userid seems legit */
		} else {
			$sql = 'SELECT count(*) AS bugs_pending FROM bugtracker_bugs WHERE resolved_date IS NULL AND denied_date IS NULL AND reporter_id='.$userid;
			$result = $db->fetch($db->query($sql, __FILE__, __LINE__, __METHOD__));

			/** User has reported Bugs which are pending */
			if (!empty($result) && $result['bugs_pending'] > 0)
			{
				return $result['bugs_pending'];
			}
		}
	}
}


/**
 * zorg Site Stats Class
 * In dieser Klasse befinden sich alle Methoden zum Erstellen und Anzeigen von zorg-übergreifenden Stats
 *
 * @author		IneX
 * @date		03.09.2018
 * @package		zorg
 * @subpackage	System
 * @version		1.0
 * @since		1.0 Class added with first version of methods
 */
class ZorgStatistics
{
	/**
	 * Threads created statistics
	 *
	 * @author	IneX
	 * @date	03.09.2018
	 * @version	1.0
	 * @since	1.0 method added
	 *
	 * @see forum.inc.php
	 * @param	date	$from	Start Date string to show Stats for
	 * @param	date	$to		End Date string to show Stats for
	 * @global	object	$db		Globales Class-Object mit allen MySQL-Methoden
	 * @return	integer|boolean	Returns total amount of Threads created during $from - $to, or false
	 */
	public function threads_created($from, $to)
	{
		global $db;

		/** Validate passed $userid parameter */
		if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> Passed $userid: %d', __METHOD__, __LINE__, $userid));
		if (empty($userid) || $userid <= 0)
		{
			error_log(sprintf('[WARN] <%s:%d> Invalid $userid: %d', __METHOD__, __LINE__, $userid));
			return false;

		/** Passed $userid seems legit */
		} else {
			$sql = 'SELECT UNIX_TIMESTAMP(activity) AS last_activity FROM user WHERE id='.$userid;
			$result = $db->fetch($db->query($sql, __FILE__, __LINE__, __METHOD__));

			/** A User with $userid exists */
			if (!empty($result))
			{
				return $result['last_activity'];
			}
		}
	}

	/**
	 * Comments created statistics
	 *
	 * @author	IneX
	 * @date	03.09.2018
	 * @version	1.0
	 * @since	1.0 method added
	 *
	 * @see forum.inc.php
	 * @param	date	$from	Start Date string to show Stats for
	 * @param	date	$to		End Date string to show Stats for
	 * @param	boolean	$include_comments_threads	(Optional) Specifiy whether Comments of type "Thread" should be included or excluded
	 * @global	object	$db		Globales Class-Object mit allen MySQL-Methoden
	 * @return	integer|boolean	Returns total amount of Comments added during $from - $to, or false
	 */
	public function comments_created($from, $to, $include_comments_threads=FALSE)
	{
		global $db;

		/** Validate passed parameters */
		if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> Passed $userid: %d', __METHOD__, __LINE__, $userid));
		if (empty($userid) || $userid <= 0)
		{
			error_log(sprintf('[WARN] <%s:%d> Invalid $userid: %d', __METHOD__, __LINE__, $userid));
			return false;

		/** Passed $userid seems legit */
		} else {
			$sql = 'SELECT UNIX_TIMESTAMP(activity) AS last_activity FROM user WHERE id='.$userid;
			$result = $db->fetch($db->query($sql, __FILE__, __LINE__, __METHOD__));

			/** A User with $userid exists */
			if (!empty($result))
			{
				return $result['last_activity'];
			}
		}
	}

	/**
	 * Bugtracker reported Bugs statistic
	 *
	 * @author	IneX
	 * @date	03.09.2018
	 * @version	1.0
	 * @since	1.0 method added
	 *
	 * @param	date	$from	Start Date string to show Stats for
	 * @param	date	$to		End Date string to show Stats for
	 * @param	boolean	$include_comments_threads	(Optional) Specifiy whether Comments of type "Thread" should be included or excluded
	 * @global	object	$db		Globales Class-Object mit allen MySQL-Methoden
	 * @return	array|boolean	Returns Array with total amount of Bugs reported and amount of their current status (open, closed, denied,...), or false
	 */
	public function bugs_created($from, $to)
	{
		global $db;

		/** Validate passed parameters */
		if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> Passed $userid: %d', __METHOD__, __LINE__, $userid));
		if (empty($userid) || $userid <= 0)
		{
			error_log(sprintf('[WARN] <%s:%d> Invalid $userid: %d', __METHOD__, __LINE__, $userid));
			return false;

		/** Passed $userid seems legit */
		} else {
			$sql = 'SELECT count(*) AS bugs_pending FROM bugtracker_bugs WHERE resolved_date IS NULL AND denied_date IS NULL AND reporter_id='.$userid;
			$result = $db->fetch($db->query($sql, __FILE__, __LINE__, __METHOD__));

			/** User has reported Bugs which are pending */
			if (!empty($result) && $result['bugs_pending'] > 0)
			{
				return $result['bugs_pending'];
			}
		}
	}
}


/**
 * Instantiating new Class-Object
 */
$userStats = new UserStatistics();
$zorgStats = new ZorgStatistics();
