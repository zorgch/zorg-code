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
 * @version		1.0
 * @package		Zorg
 * @subpackage	Events
 */
/**
 * File includes
 * @include smarty.inc.php Includes the Smarty Class and Methods
 * @include usersystem.inc.php Includes the Usersystem Class and Methods
 * @include util.inc.php Includes the Helper Utilities Class and Methods
 * @include googleapis.inc.php Include the Google API Class and Methods
 */
require_once( __DIR__ .'/smarty.inc.php');
require_once( __DIR__ .'/usersystem.inc.php');
require_once( __DIR__ .'/util.inc.php');
include_once( __DIR__ .'/googleapis.inc.php');

/**
 * Events Class
 * 
 * In dieser Klasse befinden sich alle Funktionen für die Events
 *
 * @author		[z]milamber, IneX
 * @version		1.0
 * @package		Zorg
 * @subpackage	Events
 */
class Events {
	
	static function getEvent($event_id) {
		global $db;
		
		$sql =	
			"
			SELECT
				*
				, UNIX_TIMESTAMP(startdate) AS startdate
				, UNIX_TIMESTAMP(enddate) AS enddate
				, UNIX_TIMESTAMP(reportedon_date) AS reportedon_date
			FROM 
			events
			WHERE
			id = ".$event_id."
			"
		; 
		 
		$result = $db->query($sql, __FILE__, __LINE__);
		
		return $db->fetch($result);
	}
	
	static function getEventNewest() {
		global $db;
		
		$sql =	
			"
			SELECT
				*
				, UNIX_TIMESTAMP(startdate) AS startdate
				, UNIX_TIMESTAMP(enddate) AS enddate
				, UNIX_TIMESTAMP(reportedon_date) AS reportedon_date
			FROM 
			events
			ORDER BY reportedon_date DESC
			LIMIT 0,1
			"
		; 
		 
		$result = $db->query($sql, __FILE__, __LINE__);
		
		return $db->fetch($result);
	}
	
	static function getEvents($year) {
		global $db;
		
		$events = array();
		
		$sql =	
			"
			SELECT
				*
				, UNIX_TIMESTAMP(reportedon_date) AS reportedon_date
			FROM 
			events
			WHERE
			DATE_FORMAT(startdate, '%Y') = ".$year."
			ORDER BY startdate ASC, enddate ASC
			"
		; 
		 
		$result = $db->query($sql, __FILE__, __LINE__);
		
		while($rs = $db->fetch($result)) {
			array_push($events, $rs);
		}
		
		return $events;
	}
	
	static function getNext() {
		global $db, $user;
		
		$events = array();
		
		$sql = 
			"
			SELECT 
			  e.*
			  , COUNT(cu.comment_id) AS numunread
			FROM `events` e
			LEFT JOIN comments c ON (c.board = 'e' AND c.thread_id = e.id)
			LEFT JOIN comments_unread cu ON (cu.user_id = '".$user->id."' AND cu.comment_id = c.id)
			WHERE 
					UNIX_TIMESTAMP(e.enddate) > ".time()."
				AND
					UNIX_TIMESTAMP(e.startdate) < (".time()."+60*60*24*7)
			GROUP by e.id
			ORDER BY startdate ASC
			"
		;
		$result = $db->query($sql, __FILE__, __LINE__);
		
		while($rs = $db->fetch($result)) {
			$events[] = $rs;
		}
		
		return $events;
	}
	
	static function getNumNewEvents() {
		global $db, $user;
		
		if($user->lastlogin > 0) {
			$sql =	
				"
				SELECT
				*
				FROM 
				events
				WHERE
				UNIX_TIMESTAMP(reportedon_date) > ".$user->lastlogin."
				"
			;
			
			return $db->num($db->query($sql, __FILE__, __LINE__));
		} else {
			return 0;
		}
	}
	
	static function getVisitors($event_id) {
		global $db;
		$visitors = array();
		
		$sql = 
			"
			SELECT
			*
			from events_to_user e
			where e.event_id = ".$event_id." 
			"
		;
		$result = $db->query($sql, __FILE__, __LINE__);
		while ($rs = $db->fetch($result)) {
			array_push($visitors, $rs);
		}
		
		return $visitors;
	}
	
	static function getYears() {
		global $db;
		
		$years = array();
		
		$sql =	
			"
			SELECT
				DATE_FORMAT(startdate, '%Y') as year
			FROM 
			events
			GROUP BY year
			ORDER BY year ASC
			"
		; 
		 
		$result = $db->query($sql, __FILE__, __LINE__);
		
		while($rs = $db->fetch($result)) {
			array_push($years, $rs['year']);
		}
		
		return $years;
	}
	
	static function hasJoined($user_id, $event_id) {
		global $db;
		
		$sql = 
			"
			SELECT 
			* 
			FROM events_to_user 
			WHERE user_id = ".$user_id."
			AND event_id = ".$event_id
		;
		$result = $db->query($sql, __FILE__, __LINE__);
		
		return $db->fetch($result);
	}
	
	
	/**
	 * Returns the Title of an Event based on a given ID
	 * @author IneX
	 * @date 18.08.2012
	 *
	 * @param $event_id int
	 * @return String mit Eventname
	 */
	static function getEventName($event_id) {
		global $db;
		
		$sql =	"SELECT id, name FROM events WHERE id = ".$event_id; 
		$rs = $db->fetch($db->query($sql, __FILE__, __LINE__));
		
		return $rs['name'];
	}
	
	
	/**
	 * Returns a specific amount of the Description of an Event
	 * @author IneX
	 * @date 18.08.2012
	 *
	 * @param $text string
	 * @param $length int
	 * @return String mit Event Kurzbeschreibung
	 */
	static function getEventExcerpt($text, $length=50) {
		
		$text = strip_tags($text);
		
		// was macht das?
		$pattern = "(((\w|\d|[√§√∂√º√®√©√†√Æ√™])(\w|\d|\s|[√§√∂√º√®√©√†√Æ√™]|[\.,-_\"'?!^`~])[^\\n]+)(\\n|))";
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
 * @date 12.06.2018
 * @version 1.0
 * @since 1.0
 * @package Zorg
 * @subpackage Events
 */
Class UpcomingEvent
{
	/**
	 * Telegram Notification for an upcoming Event
	 *
	 * @see Telegram::send::event()
	 * @see UpcomingEvent::getUpcomingEvent()
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
				if (DEVELOPMENT) error_log('[DEBUG] Sending Telegram Notification $telegram->send-event()');
				$eventTitle = t('telegram-event-notification', 'event', [ $nextEvent['time'], $nextEvent['name'] ]);
				$telegram->send->event('group', $nextEvent['lat'], $nextEvent['lng'], $eventTitle, $nextEvent['location']);
				return true;
			} else {
				error_log( t('error-upcoming-event', 'event', [__METHOD__, __LINE__]) );
				return false;
			}
		} else {
			error_log( sprintf('[WARN] <%s:%d> Starts in X hours is no valid integer value!', __METHOD__, __LINE__) );
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
	private function getUpcomingEvent(int $hours_until_start)
	{
		global $db, $googleMapsApi;
		try {
			$sql = 'SELECT name, location, DATE_FORMAT(startdate, "%H:%i") time
					FROM events
					WHERE startdate >= DATE_ADD(NOW(), INTERVAL '.($hours_until_start-1).' HOUR)
						AND startdate < DATE_ADD(NOW(), INTERVAL '.($hours_until_start).' HOUR)
					ORDER BY startdate ASC
					LIMIT 1';
			$result = $db->query($sql, __FILE__, __LINE__, __METHOD__);
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
					return $event;
				} else {
					error_log( t('error-googlemapsapi-geocode', 'event', [__METHOD__, __LINE__]) );
					return NULL;
				}
			} else {
				error_log( t('error-upcoming-event', 'event', [__METHOD__, __LINE__]) );
				return NULL;
			}
		} catch (Exception $e) {
			error_log($e->getMessage());
			return $e->getMessage();
		}
	}
}
