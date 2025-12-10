<?php
/**
 * Events Funktionen
 *
 * Beinhaltet die Events-Klasse und deren Methoden, welche für die Events benötigt werden
 *
 * Diese Klassen benutzen folgende Tabellen aus der DB:
 * - events
 * - events_to_user
 *
 * @version 1.0
 * @package zorg\Events
 */
/**
 * File includes
 * @include config.inc.php
 * @include DEPRECATED smarty.inc.php Includes the Smarty Class and Methods
 * @include usersystem.inc.php Includes the Usersystem Class and Methods
 * @include util.inc.php Includes the Helper Utilities Class and Methods
 * @include googleapis.inc.php Include the Google API Class and Methods
 */
require_once __DIR__.'/config.inc.php';
require_once INCLUDES_DIR.'usersystem.inc.php';
include_once INCLUDES_DIR.'googleapis.inc.php';

/**
 * Events Class
 *
 * In dieser Klasse befinden sich alle Funktionen für die Events
 *
 * @author [z]milamber
 * @author IneX
 * @version 1.0
 * @package	zorg\Events
 */
class Events
{
	static function getEvent($event_id)
	{
		global $db;

		$sql = 'SELECT *, UNIX_TIMESTAMP(startdate) AS startdate, UNIX_TIMESTAMP(enddate) AS enddate, UNIX_TIMESTAMP(reportedon_date) AS reportedon_date
				FROM events WHERE id=?';
		$result = $db->query($sql, __FILE__, __LINE__, __METHOD__, [$event_id]);
		return $db->fetch($result);
	}

	static function getEventNewest()
	{
		global $db;

		$sql = 'SELECT *, UNIX_TIMESTAMP(startdate) AS startdate, UNIX_TIMESTAMP(enddate) AS enddate, UNIX_TIMESTAMP(reportedon_date) AS reportedon_date
				FROM events ORDER BY reportedon_date DESC LIMIT 1';
		$result = $db->query($sql, __FILE__, __LINE__, __METHOD__);
		return $db->fetch($result);
	}

	static function getEvents($year)
	{
		global $db;

		$events = array();

		$sql = 'SELECT *, UNIX_TIMESTAMP(reportedon_date) AS reportedon_date FROM events WHERE DATE_FORMAT(startdate, "%Y")=? ORDER BY startdate ASC, enddate ASC';

		$result = $db->query($sql, __FILE__, __LINE__, __METHOD__, [$year]);

		while($rs = $db->fetch($result)) {
			array_push($events, $rs);
		}

		return $events;
	}

	/**
	 * Bevorstehende Events suchen.
	 * Findet alle Events der nächsten 7 Tagen und gibt diese als PHP-Array zurück
	 *
	 * @version 1.1
	 * @since 1.0 Method added
	 * @since 1.1 `17.04.2020` `IneX` SQL Slow-Query optimization
	 * @since 1.2 `24.10.2020` `IneX` Enhanced SQL-Query to not miss ongoing nor upcoming events
	 *
	 * @see /includes/smarty.fnc.php
	 * @global object $user Globales Class-Object mit den User-Methoden & Variablen
	 * @global object $db Globales Class-Object mit allen MySQL-Methoden
	 * @return array List holding all upcoming events
	 */
	static function getNext()
	{
		global $db, $user;

		$events = array();
		$user_id = isset($user->id) ? $user->id : 0;
		$sql = 'SELECT e.id, e.name, e.startdate, e.enddate, COUNT(cu.comment_id) AS numunread FROM events e
					LEFT JOIN comments c ON (c.board = "e" AND c.thread_id = e.id)
					LEFT JOIN comments_unread cu ON (cu.user_id = ? AND cu.comment_id = c.id)
				WHERE e.enddate>=? AND (e.startdate BETWEEN (?-INTERVAL 7 DAY) AND ?) OR e.startdate BETWEEN ? AND (?+INTERVAL 5 DAY)
				GROUP by e.id ORDER BY e.startdate ASC';
		$result = $db->query($sql, __FILE__, __LINE__, __METHOD__, [$user_id, timestamp(true), timestamp(true), timestamp(true), timestamp(true), timestamp(true)]);

		while($rs = $db->fetch($result)) {
			$events[] = $rs;
		}
		return $events;
	}

	static function getNumNewEvents()
	{
		global $db, $user;

		if(isset($user->lastlogin) && $user->lastlogin > 0)
		{
			$sql = 'SELECT * FROM events WHERE UNIX_TIMESTAMP(reportedon_date)>?';
			return $db->num($db->query($sql, __FILE__, __LINE__, __METHOD__, [$user->lastlogin]));
		} else {
			return 0;
		}
	}

	static function getVisitors($event_id)
	{
		global $db;
		$visitors = array();

		$sql = 'SELECT * FROM events_to_user WHERE event_id=?';
		$result = $db->query($sql, __FILE__, __LINE__, __METHOD__, [$event_id]);
		while ($rs = $db->fetch($result)) {
			array_push($visitors, $rs);
		}
		return $visitors;
	}

	static function getYears()
	{
		global $db;
		$years = array();

		$sql = 'SELECT DATE_FORMAT(startdate, "%Y") as year FROM events GROUP BY year ORDER BY year ASC';
		$result = $db->query($sql, __FILE__, __LINE__, __METHOD__);
		while($rs = $db->fetch($result)) {
			array_push($years, $rs['year']);
		}

		return $years;
	}

	static function hasJoined($user_id, $event_id)
	{
		global $db;

		$sql = 'SELECT * FROM events_to_user WHERE user_id=? AND event_id=?';
		$result = $db->query($sql, __FILE__, __LINE__, __METHOD__, [$user_id, $event_id]);

		return $db->fetch($result);
	}

	/**
	 * Returns the Title of an Event based on a given ID.
	 *
	 * @author IneX
	 * @version 1.1
	 * @since 1.0 `18.08.2012` `IneX` method added
	 * @since 1.1 `04.12.2020` `IneX` Fixed PHP Notice trying to access array offset of type null
	 *
	 * @param int $event_id
	 * @global object $db Globales Class-Object mit allen MySQL-Methoden
	 * @return string Eventname
	 */
	static function getEventName($event_id)
	{
		global $db;

		$sql = 'SELECT id, name FROM events WHERE id=?';
		$rs = $db->fetch($db->query($sql, __FILE__, __LINE__, __METHOD__, [$event_id]));

		return (!empty($rs) && false !== $rs ? remove_html($rs['name']) : '');
	}

	/**
	 * Returns the Link to an Event based on a given ID.
	 * URL Format: /event/[year]/[month]/[day]/[event-id|eventname]
	 *
	 * @author IneX
	 * @version 1.0
	 * @since 1.0 `20.09.2018` `IneX` Method added
	 *
	 * @see index.php
	 * @param int $event_id
	 * @return string|bool Die relative Event-URL - oder false bei Fehler
	 */
	static function getEventLink($event_id)
	{
		global $db;

		$sql = 'SELECT DATE_FORMAT(startdate,"%Y/%m/%d") as date_path, id FROM events WHERE id=?';
		$rs = $db->fetch($db->query($sql, __FILE__, __LINE__, __METHOD__, [$event_id]));
		if ($rs) {
			$eventLink = sprintf('/event/%s/%d', $rs['date_path'], $rs['id']);
			return $eventLink;
		} else {
			return false;
		}
	}

	/**
	 * Returns a specific amount of the Description of an Event
	 *
	 * @TODO use text_width() from util.inc.php
	 *
	 * @author IneX
	 * @version 1.0
	 * @since 1.0 `18.08.2012` `IneX` Method added
	 *
	 * @param string $text
	 * @param int $length
	 * @return String mit Event Kurzbeschreibung
	 */
	static function getEventExcerpt($text, $length=50)
	{
		$text = strip_tags($text);

		// was macht das?
		$pattern = "(((\w|\d|[√§√∂√º√®√©√†√Æ√™])(\w|\d|\s|[√§√∂√º√®√©√†√Æ√™]|[\.,-_\"'?!^`~])[^\\n]+)(\\n|))"; // FIXME Umlauts are b0rken
		preg_match($pattern, $text, $out);
		if(strlen($out[1]) > $length)
		{
			$out[1] = substr($out[1], 0, $length);
		}
		if(strlen($out[1]) == 0) return '---';

		return $out[1];
	}
}

/**
 * Upcoming Events
 *
 * @author IneX
 * @version 1.0
 * @since 1.0 `12.06.2018` `IneX` Class added
 * @package zorg\Events
 */
class UpcomingEvent
{
	/**
	 * Telegram Notification for an upcoming Event
	 *
	 * @uses Telegram::send::event()
	 * @uses UpcomingEvent::getUpcomingEvent()
	 * @param integer $starts_in_hours Integer value representing N hours to check for when any event might start. Default: 4 (hours)
	 * @global object $telegram Globales Class-Object mit den Telegram-Methoden
	 * @return boolean Returns true or false, depending on successful result
	 */
	public function notify($starts_in_hours=null)
	{
		global $telegram;

		/** If Function Parameter is not set, set a default */
		if (null === $starts_in_hours) {
	        $starts_in_hours = 4;
	    } elseif (DEVELOPMENT) {
		    error_log(sprintf('[DEBUG] <%s:%d> Function Parameter: %s', __METHOD__, __LINE__, $starts_in_hours));
	    }

		/** Validate $starts_in_hours - must be valid integer */
		if (is_numeric($starts_in_hours))
		{
			$nextEvent = $this->getUpcomingEvent($starts_in_hours);

			if ($nextEvent)
			{
				/** If we have lat+lng, send an event... */
				if (isset($nextEvent['lat']) && isset($nextEvent['lng']))
				{
					if (DEVELOPMENT) error_log('[DEBUG] Sending Telegram Notification $telegram->send->event()');
					$eventTitle = t('telegram-event-notification', 'event', [ $nextEvent['name'] ]); // timename($nextEvent['time'])
					$telegram->send->event('group', $nextEvent['lat'], $nextEvent['lng'], $eventTitle, $nextEvent['location']);

				/** ...otherwise just send a message */
				} else {
					if (DEVELOPMENT) error_log('[DEBUG] Sending Telegram Notification $telegram->send->message()');
					$eventName = html_tag($nextEvent['name'], 'b')."\n@ " . $nextEvent['location'];
					$eventTitle = t('telegram-event-notification', 'event', [ $eventName ]); // timename($nextEvent['time'])
					$telegram->send->message('group', $eventTitle);
				}
				return true;
			} else {
				error_log( t('error-upcoming-event', 'event', [__METHOD__, __LINE__, $starts_in_hours]) );
				return false;
			}
		} else {
			error_log( t('error-invalid-hours', 'event', [__METHOD__, __LINE__, $starts_in_hours]) );
			return false;
		}
	}

	/**
	 * Check & return Data of upcoming Event
	 *
	 * @see GoogleMapsApi::geocode()
	 * @param integer $hours_until_start Integer value representing N hours to check for when any event might start
	 * @global object $db Globales Class-Object mit allen MySQL-Methoden
	 * @global object $googleMapsApi Globales Class-Object mit den Google Maps API-Methoden
	 * @return array|null Returns either an Array representing the upcoming Event, or NULL if no upcoming Event was found
	 */
	private function getUpcomingEvent($hours_until_start)
	{
		global $db, $googleMapsApi;

		$sql = 'SELECT name, location, UNIX_TIMESTAMP(startdate) time FROM events
				WHERE startdate >= DATE_ADD(?, INTERVAL ? HOUR) AND startdate<DATE_ADD(?, INTERVAL ? HOUR)
				ORDER BY startdate ASC LIMIT 1';
		$result = $db->query($sql, __FILE__, __LINE__, __METHOD__, [timestamp(true), $hours_until_start-1, timestamp(true), ($hours_until_start), ]);
		$event = $db->fetch($result);
		if (DEVELOPMENT) error_log("[DEBUG] Event Query Result:\n\r".print_r($event,true));

		if (!empty($event['name']))
		{
			if (DEVELOPMENT) error_log("[DEBUG] getUpcomingEvent()\n\r" . print_r($event, true));
			$geolocation = $googleMapsApi->geocode($event['location']);
			if(!empty($geolocation))
			{
				if (DEVELOPMENT) error_log('[DEBUG] $googleMapsApi->geocode()'."\n\r" . print_r($geolocation, true));
				$event['lat'] = $geolocation['lat'];
				$event['lng'] = $geolocation['lng'];
				//return $event;
			}/* else {
				error_log( t('error-googlemapsapi-geocode', 'event', [__METHOD__, __LINE__]) );
				return NULL;
			}*/
			return $event;
		} else {
			error_log( t('error-upcoming-event', 'event', [__METHOD__, __LINE__, $hours_until_start]) );
			return NULL;
		}
	}
}
