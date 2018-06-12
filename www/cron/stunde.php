<?php

if($_GET['pw'] == 'schmelzigel')
{
/** DISABLED
	require_once($_SERVER['DOCUMENT_ROOT'].'/dnd/dnd.inc.php');
	healrestingplayers();	
*/

require_once( __DIR__ . '/../includes/main.inc.php');

/**
 * Telegram Notifcation for upcoming Event
 *
 * @author IneX
 * @date 12.06.2018
 * @version 1.0
 * @since 1.0
 * @package Zorg
 * @subpackage Cron
 */
Class UpcomingEvent
{
	//public $check;

	//public function __construct()
	//{
	//	$this->check = function()
	public function check()
	{
			global $telegram;
			$nextEvent = $this->getUpcomingEvent();
			if (!empty($nextEvent))
			{
				if (DEVELOP) error_log("[DEBUG] getUpcomingEvent()\n\r" . print_r($nextEvent, true));
				$geolocation = $this->getLatLang($nextEvent['location']);
				if(!empty($geolocation))
				{
					if (DEVELOP) error_log("[DEBUG] getLatLang()\n\r" . print_r($geolocation, true));
					if (DEVELOP) error_log('[DEBUG] Sending Telegram Notification $telegram->send-event()');
					$eventTitle = t('telegram-event-notification', 'event', [ $nextEvent['time'], $nextEvent['name'] ]);
					$telegram->send->event('group', $geolocation['lat'], $geolocation['lng'], $eventTitle, $nextEvent['location']);
					return true;
				} else {
					error_log(sprintf('[%s] <%s:%d> ERROR', __FILE__, __METHOD__, __LINE__));
					return false;
				}
			} else {
				error_log(sprintf('[%s] <%s:%d> ERROR', __FILE__, __METHOD__, __LINE__));
				return false;
			}
	//	};
	}

	private function getUpcomingEvent()
	{
		global $db;
		try {
			$sql = 'SELECT name, location, DATE_FORMAT(startdate, "%H:%i") time
					FROM events
					WHERE startdate >= DATE_ADD(NOW(), INTERVAL 3 HOUR)
						AND startdate < DATE_ADD(NOW(), INTERVAL 4 HOUR)
					ORDER BY startdate ASC
					LIMIT 1';
			$result = $db->query($sql, __FILE__, __LINE__, __METHOD__);
	
			$event = $db->fetch($result);
	
			error_log("[DEBUG] Event Query Result:\n\r".print_r($events,true));
			
			if (!empty($event['name']))
			{
				return $event;
			} else {
				error_log(sprintf('[%s] <%s:%d> ERROR', __FILE__, __METHOD__, __LINE__));
				return NULL;
			}
		} catch (Exception $e) {
			error_log($e->getMessage());
			return $e->getMessage();
		}
	}

	private function getLatLang($address)
	{
		$googleCloudAPIkey = '***REMOVED***';
		$googleGeocodingAPIrequest = sprintf('https://maps.googleapis.com/maps/api/geocode/json?key=%s&address=%s', $googleCloudAPIkey, urlencode($address));
		if (DEVELOP) error_log('[DEBUG] $googleGeocodingAPIrequest: '.$googleGeocodingAPIrequest);
		$request = file_get_contents($googleGeocodingAPIrequest);
		$response = get_object_vars(json_decode($request));
		if (DEVELOP) error_log("[DEBUG] Google Geocoding API Response JSON:\n\r".print_r($response,true));
		if ($response['status']=='OK')
		{
			return [
						 'lat' => $response['results'][0]->geometry->location->lat
						,'lng' => $response['results'][0]->geometry->location->lng
					];
		} else {
			error_log(sprintf('[%s] <%s:%d> Google Geocoding API Response Status: %s', __FILE__, __METHOD__, __LINE__, $response['status']));
			return NULL;
		}
	}
}
error_log(sprintf('[%s] <cron> Starting...', __FILE__));
$event = new UpcomingEvent();
error_log(sprintf('[%s] <cron> Finished: %s', __FILE__, ( $event->check() ? 'OK' : 'ERROR' )));

} else {
	error_log(sprintf('[%s] <cron> Access denied!', __FILE__));
}
