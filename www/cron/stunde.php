<?php

if($_GET['pw'] == 'schmelzigel')
{
/** DISABLED
	require_once($_SERVER['DOCUMENT_ROOT'].'/dnd/dnd.inc.php');
	healrestingplayers();	
*/

	require_once( __DIR__ . '/../includes/main.inc.php');
	error_log(sprintf('[%s] <cron> Starting...', __FILE__));

	/** Check for new UpcomingEvent() */
	$event = new UpcomingEvent();
	$upcomingEventNotification = $event->notify( (isset($_GET['hours']) && is_numeric($_GET['hours']) ? $_GET['hours'] : NULL) );
	error_log(sprintf('[%s] <cron> UpcomingEvent() finished: %s', __FILE__, ( $upcomingEventNotification ? 'OK' : 'ERROR' )));

	/** Donwload Gravatar-Userpics to local cache using usersystem::cacheGravatarImages() */
	$gravatarImagesCached = $user->cacheGravatarImages('all');
	error_log(sprintf('[%s] <cron> cacheGravatarImages() finished: %s', __FILE__, ( $gravatarImagesCached ? 'OK' : 'ERROR' )));
	
} else {
	error_log(sprintf('[%s] <cron> Access denied!', __FILE__));
}
