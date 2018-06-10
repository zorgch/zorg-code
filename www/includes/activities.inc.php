<?php
/**
 * Activities
 * 
 * Activities sind die Inhalte, welche auf der Zorg Start-
 * seite angezeigt werden. Man kann sich das ähnlich der
 * Facebook Startseite vorstellen. Damit soll ersichtlich
 * sein, wer was wo kürzlich gemacht hat.
 * Der User hat aber die Möglichkeit, dies zu unterdrücken
 * wenn der die entsprechende Option in seinem Profil de-
 * aktiviert.
 * Dennoch wird aber die Zorg Startseite mit solchen Act-
 * ivities gefüllt werden - wem das nicht gefällt, muss
 * in seinem Profil die Zooomclan Startseite aktivieren.
 * Diese Klasee benutzt folgende Tabellen aus der DB:
 *		activities, activities_areas, activities_votes
 *
 * @TODO make Activities a Global Class-Object!
 *
 * @package		Zorg
 * @subpackage	Activities
 */
/**
 * File includes
 * @include mysql.inc.php
 * @include usersystem.inc.php
 */
require_once( __DIR__ . '/mysql.inc.php');
require_once( __DIR__ . '/usersystem.inc.php');

/**
 * Activities Class
 * 
 * In dieser Klasse befinden sich alle Funktionen zur Steuerung der Activities
 *
 * @author		IneX
 * @date		13.09.2009
 * @date		18.08.2012
 * @date		16.05.2018
 * @version		3.0
 * @package		Zorg
 * @subpackage	Activities
 */
class Activities
{
	/**
	 * Activities Log
	 *
	 * @author	IneX
	 * @date	13.09.2009
	 * @version	1.0
	 * @since	1.0 initial release
	 *
	 * @param	integer	$owner			User ID von welchem die Activities ausgegeben werden sollen (Default = alle)
	 * @param	integer	$start			Von welchem Datensatz aus die Activites ausgegeben werden sollen
	 * @param	integer	$limit			Anzahl Activities, welche ausgegeben werden sollen
	 * @param	date	$date			Datum von welchem die Activities angezeigt werden sollen
	 * @global	object	$db				Globales Class-Object mit allen MySQL-Methoden
	 *
	 * @todo	Activity-Area wurde entfernt... ev. doch nötig?
	 */
	static public function getActivities ($owner=0, $start=0, $limit=23, $date='')
	{
		global $db;
		
		$sql = "SELECT
					*,
					TIME_TO_SEC(TIMEDIFF(NOW(),date)) AS date_secs,
					UNIX_TIMESTAMP(date) AS datum
				FROM
					activities
				ORDER BY
					datum DESC
				";
		//if ($activity_area <> '') $sql_WHERE = "activity_area = '".$activity_area."'";
		if ($date <> '') {
			if ($sql_WHERE <> '') {
				$sql_WHERE .= " AND datum = '".$date."'";
			} else {
				$sql_WHERE = "date = '".$date."'";
			}
		}
		$sql .= $sql_WHERE . " LIMIT $start,$limit";
		
		$result = $db->query($sql, __FILE__, __LINE__, __METHOD__);
		
		while($rs = $db->fetch($result)) {
			$activities[] = $rs;
		}
		
		return $activities;
	}
	
	
	/**
	 * Activity hinzufügen
	 *
	 * @author	IneX
	 * @date	13.09.2009
	 * @version	2.1
	 * @since	1.0 initial release
	 * @since	2.0 added Telegram Notification for new Activities
	 * @since	2.1 Changed to new Telegram Notification-Method
	 *
	 * @param	integer	$fromUser		Benutzer ID der die Activity ausgelöst hat
	 * @param	integer	$forUserID		Benutzer ID dem die Nachricht zugeordner werden soll (Owner)
	 * @param	string	$activity		Activity-Nachricht, welche ausgelöst wurde
	 * @param	string	$activityArea	Activity-Area, Bereich zu dessen die Activity ausgelöst wurde
	 * @global	object	$db				Globales Class-Object mit allen MySQL-Methoden
	 * @global	object	$user			Globales Class-Object mit den User-Methoden & Variablen
	 * 
	 * @see		checkAllowActivities()
	 */
	static public function addActivity ($fromUser, $forUser, $activity, $activityArea=NULL)
	{
		global $db, $user, $telegram;
		//$activities = $_ENV['$activities_HZ']; // Globale Activity-Arrays mergen
		
		//if (Activities::checkAllowActivities($userID))
		//{
			$sql = "INSERT INTO activities
						(date, activity_area, from_user_id, owner, activity)
					VALUES
						(now(), '$activityArea', $fromUser, $forUser, '".addslashes(stripslashes($activity))."')
					";
			$db->query($sql, __FILE__, __LINE__, __METHOD__);

			/** Telegram Notification auslösen */
			$telegram->send->message('group', ['text' => sprintf('<b>%s</b> %s', $user->id2user($fromUser, TRUE), $activity), 'disable_notification' => 'true'] );
		//}
		
	}
	
	
	/**
	 * Activity aktualisieren
	 *
	 * @author	IneX
	 * @date	16.05.2018
	 * @version	1.0
	 * @since	1.0 initial release
	 *
	 * @param	integer	$activityID	ID der Activity, welche aktualisiert werden soll
	 * @global	object	$user		Globales Class-Object mit den User-Methoden & Variablen
	 * @global	object	$db 		Globales Class-Object mit allen MySQL-Methoden
	 * @return	boolean				Gibt TRUE oder FALSE zurück
	 */
	static public function updateActivity ($activityID)
	{
		global $db, $user;
		
		if($user->id == getActivityOwner($activityID))
		{
		  	$sql = "INSERT INTO activities
						(date, activity_area, from_user_id, owner, activity)
					VALUES
						(now(), '$activityArea', $fromUser, $forUser, '".addslashes(stripslashes($activity))."')
					";
		  	return ( $db->query($sql, __FILE__, __LINE__, __METHOD__) ? TRUE : FALSE );
		}
	}


	/**
	 * Activity entfernen
	 *
	 * @author	IneX
	 * @date	13.09.2009
	 * @version	1.0
	 * @since	1.0 initial release
	 *
	 * @param	integer	$activityID	ID der Activity, welche entfernt werden soll
	 * @global	object	$user		Globales Class-Object mit den User-Methoden & Variablen
	 * @global	object	$db 		Globales Class-Object mit allen MySQL-Methoden
	 * @return	boolean				Gibt TRUE oder FALSE zurück
	 */
	static public function removeActivity ($activityID)
	{
		global $db, $user;
		
		if($user->id == getActivityOwner($activityID))
		{
		  	$sql = "DELETE FROM
		  				activities
		  			WHERE
		  				id = ".$activityID." AND
		  				owner = ".$user->id
		  			;
		  	return ( $db->query($sql, __FILE__, __LINE__, __METHOD__) ? TRUE : FALSE );
		}
	}
	
	
	/**
	 * Activity bewerten
	 *
	 * @author	IneX
	 * @date	13.09.2009
	 * @version	1.0
	 * @since	1.0 initial release
	 *
	 * @param	integer	$activityID	ID der Activity, welche bewertet werden soll
	 * @param	string	$rating		Bewertungstext der Activity - MAXIMAL 20 Zeichen!
	 * @global	object	$db			Globales Class-Object mit allen MySQL-Methoden
	 * @global	object	$user		Globales Class-Object mit den User-Methoden & Variablen
	 *
	 * @todo Modifier addslahes() für $rating könnte zu Problemen führen wegen der 20 Zeichen Begrenzung!
	 * @todo Eventuell muss noch ein header("Location: URL") hinzugefügt werden, weil man sonst im Leeren landet?
	 */
	static public function rateActivity ($activityID, $rating)
	{
		global $db, $user;
		
		if ($user->typ != USER_NICHTEINGELOGGT && !hasRated($activityID, $user->id))
		{
			if($activityID > 0 && $rating != '') {
				$sql = "REPLACE INTO
							activities_votes
								(activity_id,
								 date,
								 user_id,
								 rating)
						VALUES
								($activityID,
								 now(),
								 $user->id,
								 ".addslashes(stripslashes($rating)).")
						";
				$db->query($sql, __FILE__, __LINE__, __METHOD__);
			}
		}
		
	}
	
	
	/**
	 * Activity Bewertung entfernen
	 *
	 * @author	IneX
	 * @date	13.09.2009
	 * @version	1.0
	 * @since	1.0 initial release
	 *
	 * @param	integer	$activityID	ID der Activity, welche bewertet werden soll
	 * @global	object	$db			Globales Class-Object mit allen MySQL-Methoden
	 * @global	object	$user		Globales Class-Object mit den User-Methoden & Variablen
	 *
	 * @todo Eventuell muss noch ein header("Location: URL") hinzugefügt werden, weil man sonst im Leeren landet?
	 */
	static public function unrateActivity ($activityID)
	{
		global $db, $user;
		
		if ($activityID > 0 && hasRated($activityID, $user->id))
		{
			$sql = "DELETE FROM
						activities_votes
					WHERE
						activity_id = ".$activityID." AND
		  				user_id = ".$user->id
					;
			$db->query($sql, __FILE__, __LINE__, __METHOD__);
		}
		
	}
	
	
	/**
	 * Activity durch User bereits bewertet
	 *
	 * @author	IneX
	 * @date	13.09.2009
	 * @version	1.0
	 * @since	1.0 initial release
	 *
	 * @param	integer	$activityID	ID der Activity, welche überprüft werden soll
	 * @param	integer	$userID		Benutzer ID welcher eine Bewertung abgeben möchte
	 * @global	object	$db			Globales Class-Object mit allen MySQL-Methoden
	 */
	static public function hasRated ($activityID, $userID)
	{
		global $db;

		$sql = "SELECT
					*
				FROM
					activities_votes
				WHERE
					activity_id = ".$activityID." AND user_id =".$userID
				;
		$rs = $db->num($db->query($sql, __FILE__, __LINE__, __METHOD__));
		return ( $rs > 0 ? TRUE : FALSE );
	}
	
	
	/**
	 * Activity Owner
	 * (Gibt die User ID des Activity Owners zurück)
	 *
	 * @author	IneX
	 * @date	13.09.2009
	 * @version	1.0
	 * @since	1.0 initial release
	 *
	 * @param	integer	$activityID	ID der Activity deren Owner ermittelt werden soll
	 * @global	object	$db 		Globales Class-Object mit allen MySQL-Methoden
	 * @return	integer				User ID des Activity Owners
	 */
	static public function getActivityOwner ($activityID)
	{
		global $db;
		
		$sql = "SELECT
					owner
				FROM
					activities
				WHERE
					id = ".$activityID."
				";
		$rs = $db->fetch($db->query($sql, __FILE__, __LINE__, __METHOD__));
		return $rs['owner'];
	}
	
	
	/**
	 * Activities zählen
	 *
	 * @author	IneX
	 * @date	13.09.2009
	 * @version	1.0
	 * @since	1.0 initial release
	 *
	 * @param	integer	$userID	Wenn angegeben, werden nur die Activities diesesn Benutzers gezählt
	 * @global	object	$db 	Globales Class-Object mit allen MySQL-Methoden
	 * @return	integer			Anzahl gefundener Activities aufgrund der Kriterien
	 */
	static public function countActivities ($userID=0)
	{
		global $db;
		
		$sql = "SELECT
					COUNT(id) AS num
				FROM
					activities
				";
		
		if ($userID > 0) $sql .= "WHERE owner = ".$userID;
		
		$rs = $db->fetch($db->query($sql, __FILE__, __LINE__, __METHOD__));
		return $rs['num'];
	}
	
	
	/**
	 * Activity darf geloggt werden
	 * (Prüft ob der User in seinem Profil das loggen von Activities deaktiviert hat)
	 *
	 * @author	IneX
	 * @date	13.09.2009
	 * @version	1.0
	 * @since	1.0 initial release
	 *
	 * @param	integer	$userID	Benutzer ID für welchen die Einstellung überprüft werden muss
	 * @global	object	$db 	Globales Class-Object mit allen MySQL-Methoden
	 * @return	boolean			Gibt TRUE oder FALSE zurück, je nach Setting des Users
	 */
	static public function checkAllowActivities ($userID)
	{
		global $db;
		
		$sql = "SELECT
					activities_allow
				FROM
					user
				WHERE
					id = ".$userID." AND
					activities_allow = 1
				";
		$rs = $db->fetch($db->query($sql, __FILE__, __LINE__, __METHOD__));
		return ($rs['activities_allow'] == 1) ? TRUE : FALSE;
	}
	
	
	/**
	 * Activities als RSS ausgeben
	 * (kann mit RSS Readern abonniert werden)
	 *
	 * @author	IneX
	 * @date	18.08.2012
	 * @version	1.0
	 * @since	2.0 initial release
	 *
	 * @param	integer	$num	Anzahl maximal auszugebender Activities-Einträge
	 * @global	object	$db 	Globales Class-Object mit allen MySQL-Methoden
	 * @return	string			Gibt das XML des zusammengebauten RSS-Feeds aus
	 */
	static public function getActivitiesRSS ($num)
	{
		global $db, $user;
		
		$xmlfeed = '';	// Ausgabestring für XML Feed initialisieren
		
		/**
		 * Ausgabe evaluieren und Daten holen
		 * @author IneX
		 */
		$activityFeed = Activities::getActivities(0, 0, $num, 0);
		 
		 /**
		* Feed bauen
		* @author IneX
		*/
		if (count($activityFeed) > 0) {

			// Datensätze ausgeben	
			foreach($activityFeed as $activity)
			{

				// Assign Values
				$activityFromUser = $user->id2user($activity['from_user_id']);
				$xmlitem_title = 'Neue Activity von '.$activityFromUser;
				$xmlitem_link = str_replace('&', '&amp;amp;', SITE_URL); // &amp;amp; for xml-compatibility
				$xmlitem_pubDate = date('D, d M Y H:i:s', $activity['datum']);
				$xmlitem_author = $activityFromUser;
				$xmlitem_category = 'Activity';
				$xmlitem_guid = str_replace('&', '&amp;amp;', $activity['id']); // &amp;amp; for xml-compatibility
				$xmlitem_description = '<![CDATA[';
					$desc = $activityFromUser.' '.$activity['activity'];
					$limit = 360;
					$xmlitem_description .= (strlen($desc) > $limit ? substr($desc, 0, $limit - 3) . '...' : $desc);
					$xmlitem_description .= ']]>';
				$xmlitem_content = remove_html($activityFromUser.' '.$activity['activity']);

				// XML Feed items schreiben
				$xmlfeed[] = [
						'xmlitem_title' => $xmlitem_title,
						'xmlitem_link' => $xmlitem_link,
						'xmlitem_pubDate' => $xmlitem_pubDate,
						'xmlitem_author' => $xmlitem_author,
						'xmlitem_category' => $xmlitem_category,
						'xmlitem_guid' => $xmlitem_guid,
						'xmlitem_description' => $xmlitem_description,
						'xmlitem_content' => $xmlitem_content
					];

			} // end foreach $activityFeed

			// Return XML
			return $xmlfeed;

		} // end if count()	

	}


	/**
	 * Daily Activities Summary
	 * Gibt alle Activities eines Tages zusammengefasst aus
	 *
	 * @author	IneX
	 * @date	26.05.2018
	 * @version	1.0
	 * @since	3.0 initial release
	 *
	 * @param	date	$date	Tag für welcher Activities-Einträge ausgegeben werden sollen
	 * @param	integer	$num	Anzahl maximal auszugebender Activities-Einträge
	 * @param	string	$format	Style wie die auszugebenden Activities-Einträge formatiert werden sollen: html oder plain
	 * @global	object	$db 	Globales Class-Object mit allen MySQL-Methoden
	 * @return	string			Gibt ein zusammengebautes Activities-Summary mit $num Einträgen von $date als $format aus
	 */
	static public function getActivitiesDaily ($date, $num=5, $format='html')
	{
		global $db;

		if (empty($date)) $date = date('Y-m-d');
	}

}

//$activities = new Activities();
