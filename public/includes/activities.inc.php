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
 * @TODO Change saved Acitivies-Strings in DB to be sprintf() compatible, e.g.: "`sprintf('%s<br/><br/><a href="%s%s">%s</a>`", 'hat ein neues Hunting z Spiel auf der Karte cruiser city 2 er&ouml;ffnet.', 'https://zorg.local', '/smarty.php?tpl=103&amp;game=728')" - but save only placeholder-reference to strings.array.php & activity_area = strings.array.php[type]. Add fallback for existing Activities-Strings - or do a DB-Cleanup of the old hardcoded Strings.
 *
 * @package zorg\Activities
 */
/**
 * File includes
 * @include config.inc.php required
 * @include mysql.inc.php required --> already included via config.inc.php
 * @include usersystem.inc.php required --> already included via config.inc.php
 * @include util.inc.php required --> already included via config.inc.php
 */
require_once __DIR__.'/config.inc.php';
//require_once INCLUDES_DIR.'mysql.inc.php';
//require_once INCLUDES_DIR.'usersystem.inc.php';
//require_once INCLUDES_DIR.'util.inc.php';

/**
 * Activities Class
 *
 * In dieser Klasse befinden sich alle Funktionen zur Steuerung der Activities
 *
 * @author IneX
 * @date 13.09.2009
 * @version 3.0
 * @since 1.0 `13.09.2009` initial release
 * @since 2.0 `18.08.2012` added RSS-Feed for Activities
 * @since 3.0 `16.05.2018` added Twitter-Notifications for new Activities
 * @package zorg\Activities
 */
class Activities
{
	/**
	 * Activities Log
	 *
	 * @version	2.1
	 * @since 1.0 `13.09.2009` `IneX` Method added
	 * @since 2.0 `04.09.2018` `IneX` Added exception handling & boolean return, added support for Activity-Placeholders from strings.array.php
	 * @since 2.1 `05.12.2018` `IneX` Fixed wrong usage of t() causing a lot of log errors and broken activity-stream
	 * @since 2.1 `27.12.2023` `IneX` Formatted SQL as prepared statement
	 *
	 * @TODO Activity-Area wurde entfernt... ev. doch nötig?
	 * @FIXME SQL-WHERE Clause disabled - readd?
	 *
	 * @param	integer	$owner			User ID von welchem die Activities ausgegeben werden sollen (Default = alle)
	 * @param	integer	$start			Von welchem Datensatz aus die Activites ausgegeben werden sollen
	 * @param	integer	$limit			Anzahl Activities, welche ausgegeben werden sollen
	 * @param	string	$date			Datum von welchem die Activities angezeigt werden sollen
	 * @global	object	$db				Globales Class-Object mit allen MySQL-Methoden
	 * @return	array|boolean			Returns all fetched $activities - or false, if execution failed
	 */
	static public function getActivities ($owner=0, $start=0, $limit=23, $date='')
	{
		global $db;

		$sql = 'SELECT *, TIME_TO_SEC(TIMEDIFF(NOW(),date)) AS date_secs, UNIX_TIMESTAMP(date) AS datum
				FROM activities
				ORDER BY datum DESC
				LIMIT ?,?';
		//if ($activity_area <> '') $sql_WHERE = "activity_area = '".$activity_area."'";
		// if ($date <> '') {
		// 	$sql_WHERE = ($sql_WHERE <> '' ? ' AND datum = "'.$date.'"' : 'datum = "'.$date.'"');
		// } else { $sql_WHERE = null; }
		// $sql .= $sql_WHERE . ' LIMIT '.$start.','.$limit;
		$result = $db->query($sql, __FILE__, __LINE__, __METHOD__, [$start, $limit]);

		while($rs = $db->fetch($result))
		{
			/** New way to do it - coming soon... */
			if (!empty($rs['values']) || $rs['values'] !== false) $rs['values'] = json_decode($rs['values']);
			{
				/** sprintf() each Activity */
				$rs['activity'] = sprintf($rs['activity'], $rs['values']);
			}

			/** Push activity to $activities Array */
			$activities[] = $rs;
		}

		return $activities;
	}


	/**
	 * Activity hinzufügen
	 *
	 * @version	4.1
	 * @since 1.0 `13.09.2009` `IneX` Method added
	 * @since 2.0 `16.05.2018` `IneX` Added Telegram Notification for new Activities
	 * @since 2.1 `16.05.2018` `IneX` Changed to new Telegram Notification-Method
	 * @since 3.0 `02.09.2018` `IneX` Added exception handling & boolean return, changed Activities to support Placeholders from strings.array.php
	 * @since 4.0 `30.10.2018` `IneX` Enabled self::checkAllowActivities() for User-ID, if "activities_allowed" is set to "ON"
	 * @since 4.1 `27.12.2023` `IneX` Formatted SQL as prepared statement, extracted Telegram Notification to notify() method
	 *
	 * @uses Activities::checkAllowActivities()
	 * @uses Telegram::send::message()
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
		global $db;
		//$activities = $_ENV['$activities_HZ']; // Globale Activity-Arrays mergen

		if (is_numeric($fromUser) && $fromUser > 0 && self::checkAllowActivities($fromUser))
		{
			$fromUser = intval($fromUser);
			$forUser = intval($forUser);
			$activity = (!empty($values) ? vsprintf($activity, $values) : $activity);

			/** Array to JSON conversion */
			if (is_array($values) && !empty($values)) $activityValues = json_encode($values);
			$sql = 'INSERT INTO activities
						(`date`, `activity_area`, `from_user_id`, `owner`, `activity`, `values`)
					VALUES
						(?, ?, ?, ?, ?, ?)';
			$result = $db->query($sql, __FILE__, __LINE__, __METHOD__, [
				timestamp(true), $activityArea, $fromUser, $forUser, $activity, $values
			]);
			if ($result !== false)
			{
				/** Telegram Notification auslösen */
				return self::notify($fromUser, $activity, $activityArea);
			} else {
				zorgDebugger::log()->debug('SQL INSERT result: %s', [strval($result)]);
				return false;
			}
		} else {
			return false;
		}
	}


	/**
	 * Activity aktualisieren
	 *
	 * @version	2.1
	 * @since 1.0 `16.05.2018` `IneX` initial release
	 * @since 2.0 `04.09.2018` `IneX` Enhanced method to work with updating new values
	 * @since 2.1 `27.12.2023` `IneX` Formatted SQL as prepared statement
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

			$sql = 'UPDATE activities SET values=? WHERE id=?';
			$result = $db->query($sql, __FILE__, __LINE__, __METHOD__, [$activityValues, $activity_id]);
			return (false !== $result ? true : false );

		/** When User is not allowed to edit the specified $activity_id, then exit */
		} else {
			return false;
		}
	}


	/**
	 * Activity entfernen
	 *
	 * @version	2.1
	 * @since 1.0 `13.09.2009` `IneX` Method added
	 * @since 2.0 `24.07.2018` `IneX` minor update to work with AJAX-Request
	 * @since 2.1 `27.12.2023` `IneX` Formatted SQL as prepared statement
	 *
	 * @see Activities::getActivityOwner()
	 * @link https://github.com/zorgch/zorg-code/blob/master/www/js/ajax/activities/delete-activity.php AJAX-Action in delete-activity
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
			$sql = 'DELETE FROM activities WHERE id=? AND owner=?';
			$result = $db->query($sql, __FILE__, __LINE__, __METHOD__, [$activity_id, $user->id]);
			return ( false !== $result ? true : false );
		} else {
			return false;
		}
	}


	/**
	 * Activity bewerten
	 *
	 * @version	1.0
	 * @since	1.0 `13.09.2009` `IneX` Method added
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

		if ($user->is_loggedin() && !self::hasRated($activity_id, $user->id))
		{
			if($activity_id > 0 && $rating != '')
			{
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
		}

	}


	/**
	 * Activity Bewertung entfernen
	 *
	 * @version	1.0
	 * @since	1.0 `13.09.2009` `IneX` Method added
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

		if ($activity_id > 0 && self::hasRated($activity_id, $user->id))
		{
			$sql = 'DELETE FROM activities_votes WHERE
						activity_id = '.$activity_id.'
						AND user_id = '.$user->id
					;
			$db->query($sql, __FILE__, __LINE__, __METHOD__);
		}

	}


	/**
	 * Activity durch User bereits bewertet
	 *
	 * @version	1.0
	 * @since	1.0 `13.09.2009` `IneX` Method added
	 *
	 * @param	integer	$activity_id	ID der Activity, welche überprüft werden soll
	 * @param	integer	$user_id		Benutzer ID welcher eine Bewertung abgeben möchte
	 * @global	object	$db			Globales Class-Object mit allen MySQL-Methoden
	 */
	static public function hasRated ($activity_id, $user_id)
	{
		global $db;

		$sql = 'SELECT * FROM activities_votes WHERE activity_id='.$activity_id.' AND user_id='.$user_id;
		$rs = $db->num($db->query($sql, __FILE__, __LINE__, __METHOD__));
		return ( $rs > 0 ? TRUE : FALSE );
	}


	/**
	 * Activity Owner
	 * (Gibt die User ID des Activity Owners zurück)
	 *
	 * @version	1.1
	 * @since 1.0 `13.09.2009` `IneX` Method added
	 * @since 1.1 `27.12.2023` `IneX` Formatted SQL as prepared statement
	 *
	 * @param	integer	$activity_id	ID der Activity deren Owner ermittelt werden soll
	 * @global	object	$db 		Globales Class-Object mit allen MySQL-Methoden
	 * @return	integer				User ID des Activity Owners
	 */
	static public function getActivityOwner ($activity_id)
	{
		global $db;

		$sql = 'SELECT owner FROM activities WHERE id=?';
		$rs = $db->fetch($db->query($sql, __FILE__, __LINE__, __METHOD__, [$activity_id]));
		return $rs['owner'];
	}


	/**
	 * Activities zählen
	 *
	 * @version	1.0
	 * @since	1.0 `13.09.2009` `IneX` Method added
	 *
	 * @param	integer	$user_id	Wenn angegeben, werden nur die Activities diesesn Benutzers gezählt
	 * @global	object	$db 	Globales Class-Object mit allen MySQL-Methoden
	 * @return	integer			Anzahl gefundener Activities aufgrund der Kriterien
	 */
	static public function countActivities ($user_id=0)
	{
		global $db;

		$sql = 'SELECT COUNT(id) AS num FROM activities'.($user_id > 0 ? ' WHERE owner = '.$user_id : '');
		$rs = $db->fetch($db->query($sql, __FILE__, __LINE__, __METHOD__));
		return (!empty($rs['num']) ? $rs['num'] : false);
	}


	/**
	 * Activity darf geloggt werden
	 *
	 * Prüft ob der User in seinem Profil das loggen von Activities deaktiviert hat
	 * Wichtig: prüft auf passable $user_id, weil Activities nicht immer per se für
	 * den aktiven / auslösenden User sind! Deshalb nicht $user->id verwendet.
	 *
	 * @version	2.1
	 * @since 1.0 `13.09.2009` `IneX` Method added
	 * @since 2.0 `30.10.2018` method updated
	 * @since 2.1 `27.12.2023` `IneX` Formatted SQL as prepared statement
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

		$sql = 'SELECT activities_allow FROM user WHERE id=? LIMIT 1';
		$result = $db->fetch($db->query($sql, __FILE__, __LINE__, __METHOD__, [$user_id]));
		if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> $user_id %d => activities_allow: %s (%s)', __METHOD__, __LINE__, $user_id, $result['activities_allow'], ($result['activities_allow'] === '1' ? 'true' : 'false')));
		return ( $result ? ($result['activities_allow'] === '1' ? true : false) : false );
	}


	/**
	 * Activities als RSS ausgeben
	 *
	 * Kann mit RSS Readern abonniert werden
	 *
	 * @version	1.1
	 * @since 1.0 `18.08.2012` `IneX` initial release
	 * @since 1.1 `01.12.2020` `IneX` fixed PHP 7 Uncaught Error: [] operator not supported for strings
	 *
	 * @param	integer	$num	Anzahl maximal auszugebender Activities-Einträge. Default: 5
	 * @global	object	$db 	Globales Class-Object mit allen MySQL-Methoden
	 * @return	string			Gibt das XML des zusammengebauten RSS-Feeds aus
	 */
	static public function getActivitiesRSS ($num=5)
	{
		global $db, $user;

		$xmlfeed = array();	// Ausgabestring für XML Feed als Array initialisieren

		/**
		 * Ausgabe evaluieren und Daten holen
		 */
		$activityFeed = Activities::getActivities(0, 0, $num, 0);

		 /**
		 * Feed bauen
		 */
		if (count($activityFeed) > 0)
		{
			/** Datensätze ausgeben	*/
			foreach($activityFeed as $activity)
			{
				/** Assign Values */
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

				/** XML Feed items schreiben */
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

			/** Return XML */
			return $xmlfeed;

		} // end if count()
	}


	/**
	 * Daily Activities Summary
	 * Gibt alle Activities eines Tages zusammengefasst aus
	 *
	 * @version	1.0
	 * @since	1.0 `26.05.2018` `IneX` Method added
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
	 * Activity notifications.
	 * Triggers Telegram-Messenger updates for an Activity.
	 *
	 * @version	1.0
	 * @since	1.0 `13.09.2009` `IneX` Method added
	 * @since	2.0 `27.12.2023` `IneX` Refactored method to be used Class-wide to Notify
	 *
	 * TODO add support for $forUser (currently always notifies 'group'!)
	 *
	 * @uses usersystem::id2user(), Telegram()
	 * @param	integer	$fromUser		Benutzer ID der die Activity ausgelöst hat
	 * @param	string	$activityText	Activity-Nachricht, welche ausgelöst wurde
	 * @param	string	$activityArea	(Optional) Activity-Area, Bereich zu dessen die Activity ausgelöst wurde
	 * @global	object	$user			Globales Class-Object mit den User-Methoden & Variablen
	 * @global	object	$telegram		Globales Class-Object mit den Telegram-Methoden
	 * @return	boolean					Returns true/false depending on a the successful execution or not
	 */
	static public function notify ($fromUser, $activityText, $activityArea=NULL)
	{
		global $user, $telegram;

		// TODO $sendTo = (is_numeric($forUser) && $forUser > 0 ? : 'group');

		/** For Polls */
		if ($activityArea === 'p')
		{
			/** Do nothing because already done in poll_edit.php */
			return true;
		}
		/** For all other Activites */
		else {
			zorgDebugger::log()->debug('Attempting to send Telegram Notification');
			$success = $telegram->send->message('group', t('telegram-notification', 'activity', [ $user->id2user($fromUser, TRUE), $activityText ]), ['disable_notification' => 'true']);
			zorgDebugger::log()->debug('Telegram Notification %s', [($success !== false ? 'SENT!' : 'NOT SENT')], ($success !== false ? 'DEBUG' : 'ERROR'));
			return $success;
		}
		return false;
	}
}

//$activities = new Activities();
