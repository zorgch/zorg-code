<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/smarty.inc.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/usersystem.inc.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/util.inc.php');

class Events {
	
	function getEvent($event_id) {
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
	
	function getEventNewest() {
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
	
	function getEvents($year) {
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
	
	function getNext() {
		global $db, $user;
		
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
	
	function getNumNewEvents() {
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
	
	function getVisitors($event_id) {
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
	
	function getYears() {
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
	
	function hasJoined($user_id, $event_id) {
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
	function getEventName($event_id) {
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
	function getEventExcerpt($text, $length=50) {
		
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
?>
