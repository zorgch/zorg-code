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
 * @package		Zorg
 * @subpackage	Activities
 */

/**
 * File Includes
 */
include_once($_SERVER['DOCUMENT_ROOT'].'/includes/mysql.inc.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/usersystem.inc.php');

/**
 * Activities Class
 * 
 * In dieser Klasse befinden sich alle Funktionen zur Steuerung der Activities
 *
 * @author		IneX
 * @date		18.08.2012
 * @version		2.0
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
	 * @since	1.0
	 *
	 * @param	integer	$owner			User ID von welchem die Activities ausgegeben werden sollen (Default = alle)
	 * @param	integer	$start			Von welchem Datensatz aus die Activites ausgegeben werden sollen
	 * @param	integer	$limit			Anzahl Activities, welche ausgegeben werden sollen
	 * @param	date	$date			Datum von welchem die Activities angezeigt werden sollen
	 * @global	array	$db				Array mit allen MySQL-Datenbankvariablen
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
		
		$result = $db->query($sql, __FILE__, __LINE__);
		
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
	 * @version	1.0
	 * @since	1.0
	 *
	 * @param	integer	$fromUser		Benutzer ID der die Activity ausgelöst hat
	 * @param	integer	$forUserID		Benutzer ID dem die Nachricht zugeordner werden soll (Owner)
	 * @param	string	$activity		Activity-Nachricht, welche ausgelöst wurde
	 * @global	array	$db				Array mit allen MySQL-Datenbankvariablen
	 * @global	array	$user			Array mit allen Uservariablen
	 * 
	 * @see		checkAllowActivities()
	 * @todo	Würde es Sinn machen, noch die Activity-Area zu speichern?
	 */
	static public function addActivity ($fromUser, $forUser, $activity, $activityArea=NULL)
	{
		global $db;
		//$activities = $_ENV['$activities_HZ']; // Globale Activity-Arrays mergen
		
		//if (Activities::checkAllowActivities($userID))
		//{
			$sql = "INSERT INTO activities
						(date, activity_area, from_user_id, owner, activity)
					VALUES
						(now(), '$activityArea', $fromUser, $forUser, '".addslashes(stripslashes($activity))."')
					";
			$db->query($sql, __FILE__, __LINE__);
		//}
		
	}
	
	
	/**
	 * Activity entfernen
	 *
	 * @author	IneX
	 * @date	13.09.2009
	 * @version	1.0
	 * @since	1.0
	 *
	 * @param	integer	$activityID	ID der Activity, welche entfernt werden soll
	 * @global	array	$user		Array mit allen Uservariablen
	 * @global	array	$db 		Array mit allen MySQL-Datenbankvariablen
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
		  	if ($db->query($sql, __FILE__, __LINE__)) return TRUE; else return FALSE;
		}
	}
	
	
	/**
	 * Activity bewerten
	 *
	 * @author	IneX
	 * @date	13.09.2009
	 * @version	1.0
	 * @since	1.0
	 *
	 * @param	integer	$activityID	ID der Activity, welche bewertet werden soll
	 * @param	string	$rating		Bewertungstext der Activity - MAXIMAL 20 Zeichen!
	 * @global	array	$db			Array mit allen MySQL-Datenbankvariablen
	 * @global	array	$user		Array mit allen Uservariablen
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
				$db->query($sql, __FILE__, __LINE__);
			}
		}
		
	}
	
	
	/**
	 * Activity Bewertung entfernen
	 *
	 * @author	IneX
	 * @date	13.09.2009
	 * @version	1.0
	 * @since	1.0
	 *
	 * @param	integer	$activityID	ID der Activity, welche bewertet werden soll
	 * @global	array	$db			Array mit allen MySQL-Datenbankvariablen
	 * @global	array	$user		Array mit allen Uservariablen
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
			$db->query($sql, __FILE__, __LINE__);
		}
		
	}
	
	
	/**
	 * Activity durch User bereits bewertet
	 *
	 * @author	IneX
	 * @date	13.09.2009
	 * @version	1.0
	 * @since	1.0
	 *
	 * @param	integer	$activityID	ID der Activity, welche überprüft werden soll
	 * @param	integer	$userID		Benutzer ID welcher eine Bewertung abgeben möchte
	 * @global	array	$db			Array mit allen MySQL-Datenbankvariablen
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
		$rs = $db->num($db->query($sql, __FILE__, __LINE__));
		return ($rs > 0) ? TRUE : FALSE;
	}
	
	
	/**
	 * Activity Owner
	 * (Gibt die User ID des Activity Owners zurück)
	 *
	 * @author	IneX
	 * @date	13.09.2009
	 * @version	1.0
	 * @since	1.0
	 *
	 * @param	integer	$activityID	ID der Activity deren Owner ermittelt werden soll
	 * @global	array	$db 		Array mit allen MySQL-Datenbankvariablen
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
		$rs = $db->fetch($db->query($sql, __FILE__, __LINE__));
		return $rs['owner'];
	}
	
	
	/**
	 * Activities zählen
	 *
	 * @author	IneX
	 * @date	13.09.2009
	 * @version	1.0
	 * @since	1.0
	 *
	 * @param	integer	$userID	Wenn angegeben, werden nur die Activities diesesn Benutzers gezählt
	 * @global	array	$db 	Array mit allen MySQL-Datenbankvariablen
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
		
		$rs = $db->fetch($db->query($sql, __FILE__, __LINE__));
		return $rs['num'];
	}
	
	
	/**
	 * Activity darf geloggt werden
	 * (Prüft ob der User in seinem Profil das loggen von Activities deaktiviert hat)
	 *
	 * @author	IneX
	 * @date	13.09.2009
	 * @version	1.0
	 * @since	1.0
	 *
	 * @param	integer	$userID	Benutzer ID für welchen die Einstellung überprüft werden muss
	 * @global	array	$db 	Array mit allen MySQL-Datenbankvariablen
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
		$rs = $db->fetch($db->query($sql, __FILE__, __LINE__));
		return ($rs['activities_allow'] == 1) ? TRUE : FALSE;
	}
	
	
	/**
	 * Activities als RSS ausgeben
	 * (kann mit RSS Readern abonniert werden)
	 *
	 * @author	IneX
	 * @date	18.08.2012
	 * @version	1.0
	 * @since	2.0
	 *
	 * @param	integer	$num	Anzahl maximal auszugebender Activities-Einträge
	 * @global	array	$db 	Array mit allen MySQL-Datenbankvariablen
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
				$activityFromUser = usersystem::id2user($activity['from_user_id']);
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

}

//$activities = new Activities();

?>