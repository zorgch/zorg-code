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
 * @TODO Acitivities-String nicht mehr parsed in die DB-speichern, sondern nur die String-Referenz
 * @TODO Change saved Acitivies-Strings in DB to be sprintf() compatible, e.g.: "sprintf('%s<br/><br/><a href="%s%s">%s</a>", 'hat ein neues Hunting z Spiel auf der Karte cruiser city 2 er&ouml;ffnet.', 'https://zorg.local', '/smarty.php?tpl=103&amp;game=728')" - but save only placeholder-reference to strings.array.php & activity_area = strings.array.php[type]. Add fallback for existing Activities-Strings - or do a DB-Cleanup of the old hardcoded Strings.
 *
 * @package zorg\Activities
 */
/**
 * File includes
 * @include config.inc.php required
 * @include mysql.inc.php required
 * @include usersystem.inc.php required
 * @include util.inc.php required
 * @include strings.inc.php required
 */
require_once dirname(__FILE__).'/config.inc.php';
require_once INCLUDES_DIR.'mysql.inc.php';
require_once INCLUDES_DIR.'usersystem.inc.php';
require_once INCLUDES_DIR.'util.inc.php';

/**
 * Activities Class
 *
 * In dieser Klasse befinden sich alle Funktionen zur Steuerung der Activities
 *
 * @author		IneX
 * @date		13.09.2009
 * @version		3.0
 * @since		1.0 13.09.2009 initial release
 * @since		2.0 18.08.2012 added RSS-Feed for Activities
 * @since		3.0 16.05.2018 added Twitter-Notifications for new Activities
 * @package		zorg\Activities
 */
class Activities
{
	/**
	 * Activities Log
	 *
	 * @author	IneX
	 * @date	13.09.2009
	 * @version	2.1
	 * @since	1.0 13.09.2009 initial release
	 * @since	2.0 04.09.2018 Added exception handling & boolean return, added support for Activity-Placeholders from strings.array.php
	 * @since	2.1 05.12.2018 fixed wrong usage of t() causing a lot of log errors and broken activity-stream
	 *
	 * @TODO Activity-Area wurde entfernt... ev. doch nötig?
	 *
	 * @param	integer	$owner			User ID von welchem die Activities ausgegeben werden sollen (Default = alle)
	 * @param	integer	$start			Von welchem Datensatz aus die Activites ausgegeben werden sollen
	 * @param	integer	$limit			Anzahl Activities, welche ausgegeben werden sollen
	 * @param	date	$date			Datum von welchem die Activities angezeigt werden sollen
	 * @global	object	$db				Globales Class-Object mit allen MySQL-Methoden
	 * @return	array|boolean			Returns all fetched $activities - or false, if execution failed
	 */
	static public function getActivities ($owner=0, $start=0, $limit=23, $date='')
	{
		global $db;

		try {
			$sql = 'SELECT
						*,
						TIME_TO_SEC(TIMEDIFF(NOW(),date)) AS date_secs,
						UNIX_TIMESTAMP(date) AS datum
					FROM
						activities
					ORDER BY
						datum DESC';
			//if ($activity_area <> '') $sql_WHERE = "activity_area = '".$activity_area."'";
			if ($date <> '') {
				$sql_WHERE = ($sql_WHERE <> '' ? ' AND datum = "'.$date.'"' : 'datum = "'.$date.'"');
			}
			$sql .= $sql_WHERE . ' LIMIT '.$start.','.$limit;

			$result = $db->query($sql, __FILE__, __LINE__, __METHOD__);

			while($rs = $db->fetch($result))
			{
				/** New way to do it - coming soon... */
				if (!empty($rs['values']) || $rs['values'] !== false) $rs['values'] = json_decode($rs['values']);
				{
					/** sprintf() each Activity */
					$rs['activity'] = sprintf($rs['activity'], $rs['values']);
				}

				/** Puash activity to $activities Array */
				$activities[] = $rs;
			}
		} catch (Exception $e) {
			error_log($e->getMessage());
			return false;
		}

		return $activities;
	}


	/**
	 * Activity hinzufügen
	 *
	 * @author	IneX
	 * @date	13.09.2009
	 * @version	4.0
	 * @since	1.0 13.09.2009 initial release
	 * @since	2.0 16.05.2018 added Telegram Notification for new Activities
	 * @since	2.1 16.05.2018 Changed to new Telegram Notification-Method
	 * @since	3.0 02.09.2018 Added exception handling & boolean return, changed Activities to support Placeholders from strings.array.php
	 * @since	4.0 30.10.2018 Enabled self::checkAllowActivities() for User-ID, if "activities_allowed" is set to "ON"
	 *
	 * @see Activities::checkAllowActivities(), Telegram::send::message()
	 * @param	integer	$fromUser		Benutzer ID der die Activity ausgelöst hat
	 * @param	integer	$forUser		Benutzer ID dem die Nachricht zugeordner werden soll (Owner)
	 * @param	string	$activity		Activity-Nachricht, welche ausgelöst wurde
	 * @param	string	$activityArea	Activity-Area, Bereich zu dessen die Activity ausgelöst wurde
	 * @global	object	$db				Globales Class-Object mit allen MySQL-Methoden
	 * @global	object	$user			Globales Class-Object mit den User-Methoden & Variablen
	 * @global	object	$telegram		Globales Class-Object mit den Telegram-Methoden
	 * @return	boolean					Returns true/false depending on a the successful execution or not
	 */
	static public function addActivity ($fromUser, $forUser, $activity, $activityArea=NULL, $values=NULL)
	{
		global $db, $user, $telegram;
		//$activities = $_ENV['$activities_HZ']; // Globale Activity-Arrays mergen

		if (self::checkAllowActivities($fromUser))
		{
			/** Array to JSON conversion */
			if (is_array($values) && !empty($values)) $activityValues = json_encode($values);
	
			try {
				$sql = sprintf('INSERT INTO activities
									(`date`, `activity_area`, `from_user_id`, `owner`, `activity`, `values`)
								VALUES
									(NOW(), "%s", %d, %d, "%s", "%s")',
									$activityArea, $fromUser, $forUser, (strpos($activity,' ')!==false ? escape_text($activity) : $activity), $values
								);
				if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> INSERT INTO activities: %s', __METHOD__, __LINE__, $sql));
				$db->query($sql, __FILE__, __LINE__, __METHOD__);
			} catch (Exception $e) {
				error_log($e->getMessage());
				return false;
			}

			/** Telegram Notification auslösen */
			$telegram->send->message('group', t('telegram-notification', 'activity', [ $user->id2user($fromUser, TRUE), $activity ]), ['disable_notification' => 'true']);

			return true;
		} else {
			return false;
		}
	}


	/**
	 * Activity aktualisieren
	 *
	 * @author	IneX
	 * @date	16.05.2018
	 * @version	2.0
	 * @since	1.0 16.05.2018 initial release
	 * @since	2.0 04.09.2018 enhanced method to work with updating new values
	 *
	 * @param	integer	$activity_id	ID der Activity, welche aktualisiert werden soll
	 * @param	array	$newValues	Array containing new Values to be written to the defined Activity
	 * @global	object	$user		Globales Class-Object mit den User-Methoden & Variablen
	 * @global	object	$db 		Globales Class-Object mit allen MySQL-Methoden
	 * @return	boolean				Gibt TRUE oder FALSE zurück
	 */
	static public function update ($activity_id, $newValues)
	{
		global $db, $user;

		if($user->id === self::getActivityOwner($activity_id))
		{
			/** Array to JSON conversion */
			if (is_array($newValues) && !empty($newValues)) $activityValues = json_encode($newValues);

			try {
				$sql = sprintf('UPDATE activities SET
									values = "%s"
								WHERE
									id = %d',
								$activityValues, $activity_id);
				return ( $db->query($sql, __FILE__, __LINE__, __METHOD__) ? true : false );

			} catch (Exception $e) {
				error_log($e->getMessage());
				return false;
			}

		/** When User is not allowed to edit the specified $activity_id, then exit */
		} else {
			return false;
		}
	}


	/**
	 * Activity entfernen
	 *
	 * @author	IneX
	 * @date	24.07.2018
	 * @version	2.0
	 * @since	1.0 13.09.2009 initial release
	 * @since	2.0 24.07.2018 minor update to work with AJAX-Request
	 *
	 * @see delete-activity.php
	 * @see Activities::getActivityOwner()
	 * @param	integer	$activity_id	ID der Activity, welche entfernt werden soll
	 * @global	object	$user		Globales Class-Object mit den User-Methoden & Variablen
	 * @global	object	$db 		Globales Class-Object mit allen MySQL-Methoden
	 * @return	boolean				Gibt TRUE oder FALSE zurück
	 */
	static public function remove ($activity_id)
	{
		global $db, $user;
		
		if($user->id === self::getActivityOwner($activity_id))
		{
			try {
				$sql = 'DELETE FROM
							activities
						WHERE
							id = '.$activity_id.' AND
							owner = '.$user->id
						;
				return ( $db->query($sql, __FILE__, __LINE__, __METHOD__) ? true : false );
			}
			catch(Exception $e) {
				error_log($e->getMessage());
				return false;
			}
		} else {
			return false;
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
	 * @FIXME Modifier addslahes() für $rating könnte zu Problemen führen wegen der 20 Zeichen Begrenzung!
	 * @FIXME Eventuell muss noch ein header("Location: URL") hinzugefügt werden, weil man sonst im Leeren landet?
	 * @TODO Kein Rating von 1-5, sondern nur ein Like / Disklike machen
	 *
	 * @param	integer	$activity_id	ID der Activity, welche bewertet werden soll
	 * @param	string	$rating		Bewertungstext der Activity - MAXIMAL 20 Zeichen!
	 * @global	object	$db			Globales Class-Object mit allen MySQL-Methoden
	 * @global	object	$user		Globales Class-Object mit den User-Methoden & Variablen
	 */
	static public function rate ($activity_id, $rating)
	{
		global $db, $user;
		
		if ($user->typ != USER_NICHTEINGELOGGT && !hasRated($activity_id, $user->id))
		{
			if($activity_id > 0 && $rating != '')
			{
				try {
					$sql = 'REPLACE INTO
								activities_votes
									(activity_id,
									 date,
									 user_id,
									 rating)
							VALUES
									('.$activity_id.',
									 now(),
									 '.$user->id.',
									 "'.addslashes(stripslashes($rating)).'")
							';
					$db->query($sql, __FILE__, __LINE__, __METHOD__);
				}
				catch(Exception $e) {
					error_log($e->getMessage());
					return false;
				}
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
	 * @TODO Eventuell muss noch ein header("Location: URL") hinzugefügt werden, weil man sonst im Leeren landet?
	 *
	 * @param	integer	$activity_id	ID der Activity, welche bewertet werden soll
	 * @global	object	$db			Globales Class-Object mit allen MySQL-Methoden
	 * @global	object	$user		Globales Class-Object mit den User-Methoden & Variablen
	 */
	static public function unrate ($activity_id)
	{
		global $db, $user;
		
		if ($activity_id > 0 && hasRated($activity_id, $user->id))
		{
			try {
				$sql = 'DELETE FROM activities_votes WHERE
							activity_id = '.$activity_id.'
							AND user_id = '.$user->id
						;
				$db->query($sql, __FILE__, __LINE__, __METHOD__);
			}
			catch(Exception $e) {
				error_log($e->getMessage());
				return false;
			}
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
	 * @param	integer	$activity_id	ID der Activity, welche überprüft werden soll
	 * @param	integer	$user_id		Benutzer ID welcher eine Bewertung abgeben möchte
	 * @global	object	$db			Globales Class-Object mit allen MySQL-Methoden
	 */
	static public function hasRated ($activity_id, $user_id)
	{
		global $db;

		try {
			$sql = 'SELECT * FROM activities_votes WHERE activity_id='.$activity_id.' AND user_id='.$user_id;
			$rs = $db->num($db->query($sql, __FILE__, __LINE__, __METHOD__));
			return ( $rs > 0 ? TRUE : FALSE );
		}
		catch(Exception $e) {
			error_log($e->getMessage());
			return false;
		}
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
	 * @param	integer	$activity_id	ID der Activity deren Owner ermittelt werden soll
	 * @global	object	$db 		Globales Class-Object mit allen MySQL-Methoden
	 * @return	integer				User ID des Activity Owners
	 */
	static public function getActivityOwner ($activity_id)
	{
		global $db;

		try {
			$sql = 'SELECT owner FROM activities WHERE id = '.$activity_id;
			$rs = $db->fetch($db->query($sql, __FILE__, __LINE__, __METHOD__));
			return $rs['owner'];
		}
		catch(Exception $e) {
			error_log($e->getMessage());
			return false;
		}
	}
	
	
	/**
	 * Activities zählen
	 *
	 * @author	IneX
	 * @date	13.09.2009
	 * @version	1.0
	 * @since	1.0 initial release
	 *
	 * @param	integer	$user_id	Wenn angegeben, werden nur die Activities diesesn Benutzers gezählt
	 * @global	object	$db 	Globales Class-Object mit allen MySQL-Methoden
	 * @return	integer			Anzahl gefundener Activities aufgrund der Kriterien
	 */
	static public function countActivities ($user_id=0)
	{
		global $db;
		
		try {
			$sql = 'SELECT COUNT(id) AS num FROM activities'.($user_id > 0 ? ' WHERE owner = '.$user_id : '');
			$rs = $db->fetch($db->query($sql, __FILE__, __LINE__, __METHOD__));
			return $rs['num'];
		}
		catch(Exception $e) {
			error_log($e->getMessage());
			return false;
		}
	}
	
	
	/**
	 * Activity darf geloggt werden
	 *
	 * Prüft ob der User in seinem Profil das loggen von Activities deaktiviert hat
	 * Wichtig: prüft auf passable $user_id, weil Activities nicht immer per se für
	 * den aktiven / auslösenden User sind! Deshalb nicht $user->id verwendet.
	 *
	 * @author	IneX
	 * @date	13.09.2009
	 * @version	2.0
	 * @since	1.0 13.09.2009 initial release
	 * @since	2.0 30.10.2018 method updated
	 *
	 * @param	integer	$user_id	Benutzer ID für welchen die Einstellung überprüft werden muss
	 * @global	object	$db 	Globales Class-Object mit allen MySQL-Methoden
	 * @return	boolean			Gibt TRUE oder FALSE zurück, je nach Setting des Users
	 */
	static public function checkAllowActivities ($user_id)
	{
		global $db;

		/** Validte $user_id - valid integer & not empty/null */
		if (empty($user_id) || $user_id === NULL || $user_id <= 0) return false;

		try {
			$sql = 'SELECT activities_allow FROM user WHERE id = '.$user_id.' LIMIT 0,1';
			$result = $db->fetch($db->query($sql, __FILE__, __LINE__, __METHOD__));
			if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> $user_id %d => activities_allow: %s (%s)', __METHOD__, __LINE__, $user_id, $result['activities_allow'], ($result['activities_allow'] === '1' ? 'true' : 'false')));
			return ( $result ? ($result['activities_allow'] === '1' ? true : false) : false );
		}
		catch(Exception $e) {
			error_log($e->getMessage());
			return false;
		}
	}


	/**
	 * Activities als RSS ausgeben
	 * (kann mit RSS Readern abonniert werden)
	 *
	 * @author	IneX
	 * @date	18.08.2012
	 * @version	1.0
	 * @since	1.0 18.08.2012 initial release
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
	 * @since	1.0 initial release
	 *
	 * @FIXME Not yet implemented, finish method
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


	/**
	 * Activity notifications
	 * Triggers Telegram-Messenger updates for an Activity
	 *
	 * @author	IneX
	 * @date	18.09.2018
	 * @version	1.0
	 * @since	1.0 13.09.2009 method added
	 *
	 * @see checkAllowActivities()
	 * @param	integer	$fromUser		Benutzer ID der die Activity ausgelöst hat
	 * @param	integer	$forUser		Benutzer ID dem die Nachricht zugeordner werden soll (Owner)
	 * @param	string	$activity		Activity-Nachricht, welche ausgelöst wurde
	 * @param	string	$activityArea	Activity-Area, Bereich zu dessen die Activity ausgelöst wurde
	 * @global	object	$user			Globales Class-Object mit den User-Methoden & Variablen
	 * @global	object	$telegram		Globales Class-Object mit den Telegram-Methoden
	 * @return	boolean					Returns true/false depending on a the successful execution or not
	 */
	static public function notify ($fromUser, $forUser, $activity, $activityArea=NULL, $values=NULL)
	{
		global $user, $telegram;

		/** Telegram Notification auslösen */
		$telegram->send->message('group', t('telegram-notification', 'activity', [ $user->id2user($fromUser, TRUE), $activity ]), ['disable_notification' => 'true']);

		return true;
	}
}

//$activities = new Activities();
