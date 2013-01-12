<?php  ini_set( 'display_errors', true ); error_reporting(E_ALL);
/**
* PHP Functions
* 
* Enthält für Mobielzorg benötigte PHP Funktionen
* 
* @author IneX
* @version 1.0
* @package mobilezorg
*
* @global array $user Globales Array mit allen Uservariablen
* @global array $db Globales Array mit allen MySQL-Datenbankvariablen
*/
/**
 * File Includes
 */
include_once($_SERVER['DOCUMENT_ROOT'].'/includes/messagesystem.inc.php');


/**
 * mUsers
 * 
 * Klasse für die mobile User Funktionen
 * 
 * @author IneX
 * @version 1.0
 * @package mobilezorg
 * @subpackage users
 */
Class mUsers
{
	
	/**
	 * Anzahl Benutzer gerade online
	 * 
	 * Zählt wieviele Benutzer gerade online sind
	 * 
	 * @author IneX
	 * @version 1.0
	 *
	 * @global array $user Globales Array mit allen Uservariablen
	 * @global array $db Globales Array mit allen MySQL-Datenbankvariablen
	 * @global array $onlineUsers Array mit Anzahl der gerade aktiven Benutzer
	 * @return array
	 */
	function onlineUsers()
	{
	
		global $user, $db;
		
		$sql = "
			SELECT
				id, username, clan_tag
			FROM
				user 
			WHERE
				UNIX_TIMESTAMP(activity) > (UNIX_TIMESTAMP(NOW()) - ".USER_TIMEOUT.")
			ORDER BY
				activity DESC
			";
		
		$result = $db->query($sql, __FILE__, __LINE__);
		
		return $onlineUsers = mysql_num_rows($result);
		
	}
}


/**
 * mChat
 * 
 * Klasse für den mobile Chat
 * 
 * @author IneX
 * @version 1.0
 * @package mobilezorg
 * @subpackage chat
 */
Class mChat
{
	
	/**
	 * Ungelesene Chat-Nachrichten
	 * 
	 * Zählt wieviele neue Chat-Nachrichten vorhanden sind
	 * 
	 * @author IneX
	 * @version 1.0
	 *
	 * @global array $user Globales Array mit allen Uservariablen
	 * @global array $db Globales Array mit allen MySQL-Datenbankvariablen
	 * @global array $unreadChats Array mit Anzahl der neuen Chat-Nachrichten
	 * @return array
	 */
	function unreadChats()
	{
		global $user, $db;
		
		if ($user->typ != USER_NICHTEINGELOGGT) {
			if (isset($user->lastlogin)) {
				//$lastlogin_unixdate = date("Y-m-d H:i:s", $user->lastlogin);
				$lastlogin_unixdate = date("Ymd", $user->lastlogin);
				
				$sql =
					"
					SELECT
						date
					FROM
						chat
					WHERE
						DATE_FORMAT(date, '%Y%m%e') > '".$lastlogin_unixdate."'
					";
				
				$result = $db->query($sql, __FILE__, __LINE__);
				
				return $unreadChats = mysql_num_rows($result);	
			}
		}
	}
	
}


/**
 * mMessaging
 * 
 * Klasse für das mobile Messaging
 * 
 * @author IneX
 * @version 1.0
 * @package mobilezorg
 * @subpackage messagesystem
 */
Class mMessaging
{
	
	/**
	 * Ungelesene Nachrichten
	 * 
	 * Zählt wieviele ungelesene persönliche Nachrichten vorhanden sind
	 * 
	 * @author IneX
	 * @version 1.0
	 *
	 * @param integer $user_id ID des Benutzers, für welchen die ungelesenen Nachrichten gezählt werden sollen
	 * @global array $user Globales Array mit allen Uservariablen
	 * @global array $db Globales Array mit allen MySQL-Datenbankvariablen
	 * @global array $unreadMessages Array mit Anzahl der ungelesenen Nachrichten
	 * @return array
	 *
	 * @DEPRECATED
	 */
	function unreadMessages($user_id)
	{
	
		global $user, $db;
	
		if (isset($user_id)) {
			$sql = "SELECT count(id) as num FROM messages WHERE (owner = $user_id) AND (isread = '0')";
			$result = $db->query($sql, __FILE__, __LINE__);
		  	$rs = $db->fetch($result);
			
			return $rs['num'];
			//return $unreadMessages = $rs['num'];
			//return $unreadMessages = $db->num($result);
		}
	}
}


/**
 * mAddle
 * 
 * Klasse für das mobile Addle
 * 
 * @author IneX
 * @version 1.0
 * @package mobilezorg
 * @subpackage addle
 */
Class mAddle
{
	
	/**
	* Anzahl offener Addle Spiele
	* 
	* Zählt wieviele offene Addle Spiele vorhanden sind
	* 
	* @author IneX
	* @version 1.0
	*
	* @param integer $user_id ID des Benutzers, für welchen die offenen Addle Spiele gezählt werden sollen
	* @global array $user Globales Array mit allen Uservariablen
	* @global array $db Globales Array mit allen MySQL-Datenbankvariablen
	* @global array $openAddle Array mit Anzahl der offenen Addle Spiele
	* @return array
	*/
	function openAddleCount($user_id)
	{
		global $user, $db;
		
		if(isset($user_id)) {
			// Spieler am zug (nexttur) ist aktueller User und spiel ist nicht fertig
			$sql = "select id from addle where ( (player1 = $user_id and nextturn = 1) or ( player2 = $user_id and nextturn = 2) ) and finish = 0";
			$result = $db->query($sql);
			return $openAddle = $db->num($result);
		}
	}
	
	/**
	* Addle Spiele Übersicht
	* 
	* Zeigt die Übersicht an offenen Addle Spielen des Benutzers
	* 
	* @author IneX
	* @version 1.0
	*
	* @param integer $user_id ID des Benutzers, dessen Spiele angezeigt werden sollen
	* @global array $db Globales Array mit allen MySQL-Datenbankvariablen
	* @global array $addleGames Array mit den offenen Addle Games des Benutzers
	* @return array
	*/
	function openAddle($user_id)
	{
		global $db;
		
		$sql =
			"
			SELECT
				*
			FROM
				addle
			WHERE
				(player1 = $user_id OR player2 = $user_id)
				AND finish = '0'
			ORDER BY
				id DESC
			"
		;
		$result = $db->query($sql, __FILE__, __LINE__);
		
		while ($rs = mysql_fetch_array($result))
		{
			$addleGames[] = $rs;
		}
		
		return $addleGames;
	}
	
	
	/**
	* Addle Stats
	* 
	* Zeigt die Addle Stats des Users
	* 
	* @author IneX
	* @version 1.0
	*
	* @param integer $user_id ID des Benutzers, dessen Stats angezeigt werden soll
	* @global array $db Globales Array mit allen MySQL-Datenbankvariablen
	* @global array $addleMyStats Array mit den Addle Stats des Benutzers
	* @return array
	*/
	function myAddleStats($user_id)
	{
		global $db;
		
		$sql =
			"
			SELECT
				rank
				, user
				, score
			FROM addle_dwz
			WHERE user = $user_id
			ORDER BY rank DESC
			"
		;
		$result = $db->query($sql, __FILE__, __LINE__);
		
		return $addleMyStats = $rs = mysql_fetch_array($result);
	}
	
	
	/**
	* Addle Top10
	* 
	* Zeigt die Top10 Addle Spieler
	* 
	* @author IneX
	* @version 1.0
	*
	* @global array $db Globales Array mit allen MySQL-Datenbankvariablen
	* @global array $addleTop10 Array mit den Top10 Addle Spiele
	* @return array
	*/
	function addleTop10()
	{
		global $db;
		
		$sql =
			"
			SELECT
				adwz.rank
				, adwz.user
				, adwz.score
				, user.username AS username
				, user.clan_tag AS clantag
			FROM addle_dwz adwz
			LEFT JOIN user ON (adwz.user = user.id)
			ORDER BY rank ASC
			LIMIT 0,10
			"
		;
		$result = $db->query($sql, __FILE__, __LINE__);
		
		while ($rs = mysql_fetch_array($result))
		{
			$addleTop10[] = $rs;
		}
		
		return $addleTop10;
	}
	
}


/**
 * mEvents
 * 
 * Klasse für die mobile Events
 * 
 * @author IneX
 * @version 1.0
 * @package mobilezorg
 * @subpackage events
 */
Class mEvents
{
	
	/**
	 * Anzahl heutiger Events
	 * 
	 * Zählt wieviele Events heute stattfinden
	 * 
	 * @author IneX
	 * @version 1.0
	 *
	 * @global array $user Globales Array mit allen Uservariablen
	 * @global array $db Globales Array mit allen MySQL-Datenbankvariablen
	 * @global array $todayEvents Array mit Anzahl der heute stattfindenden Events
	 * @return array
	 */
	function todayEvents()
	{
	
		global $user, $db;
		
		$today = date('Ymd', time());
		
		//$sql = "SELECT UNIX_TIMESTAMP(startdate) AS startdate FROM events WHERE DATE_FORMAT(startdate, '%Y%d%e') = '$today'";
		$sql = "SELECT UNIX_TIMESTAMP(startdate) AS startdate FROM events WHERE DATE_FORMAT(startdate, '%Y%m%e') = '$today'"; 
		$result = $db->query($sql, __FILE__, __LINE__);
		
		return $todayEvents = mysql_num_rows($result);
	}
	
	
	/**
	* Event-Kommentare holen
	* 
	* Holt die Kommentare zu einem Event-Thread
	* 
	* @author IneX
	* @version 1.0
	*
	* @param integer $id ID Events
	* @param string	$board Themenbereich "events"
	* @global array $db Globales Array mit allen MySQL-Datenbankvariablen
	* @return array
	*/
	function fetchChildComments($id, $board='e')
	{
		global $db;
		
		(!is_numeric($parent_id)) ? header("Location: events.php?error=Comment%20ID%20ung&uuml;ltig") : exit();
		
		$sql =
			"
			SELECT
				*
			FROM
				comments
			WHERE
				parent_id='$id' AND board='$board'
			ORDER BY
				id ASC
			";
		$result = $db->query($sql, __FILE__, __LINE__);
	}
	
}


/**
 * mForum
 * 
 * Klasse für das mobile Forum
 * 
 * @author IneX
 * @version 1.0
 * @package mobilezorg
 * @subpackage commenting
 */
Class mForum
{
	
	/**
	 * Ungelesene Comments
	 * 
	 * Zählt wieviele ungelesene Comments vorhanden sind
	 * 
	 * @author IneX
	 * @version 1.0
	 *
	 * @param integer $user_id ID des Benutzers, für welchen die ungelesenen Comments gezählt werden sollen
	 * @global array $user Globales Array mit allen Uservariablen
	 * @global array $db Globales Array mit allen MySQL-Datenbankvariablen
	 * @global array $unreadComments Array mit Anzahl der ungelesenen Comments
	 * @return array
	 */
	function unreadComments($user_id)
	{
	
		global $user, $db;
		
		if ($user->typ != USER_NICHTEINGELOGGT) {
			$sql =
				"
				SELECT
					*
				FROM
					comments_unread
				WHERE
					user_id = '".$user_id."'"
				;
			
			$result = $db->query($sql, __FILE__, __LINE__);
			
			return $unreadComments = mysql_num_rows($result);	
		}
	}
	
}


mAddle = new mAddle();
mChat = new mChat();
mEvents = new mEvents();
mForum = new mForum();
mUsers = new mUsers();
mMessaging = new mMessaging();

?>