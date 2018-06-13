<?php

if($_GET['pw'] == 'schmelzigel')
{
/** DISABLED
	require_once($_SERVER['DOCUMENT_ROOT'].'/dnd/dnd.inc.php');
	healrestingplayers();	
*/

	require_once( __DIR__ . '/../includes/main.inc.php');
	error_log(sprintf('[%s] <cron> Starting...', __FILE__));
	$event = new UpcomingEvent();
	$upcomingEventNotification = $event->notify( (isset($_GET['hours']) && is_numeric($_GET['hours']) ? $_GET['hours'] : NULL) );
	error_log(sprintf('[%s] <cron> Finished: %s', __FILE__, ( $upcomingEventNotification ? 'OK' : 'ERROR' )));
} else {
	error_log(sprintf('[%s] <cron> Access denied!', __FILE__));
}
