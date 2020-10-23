<?php
/**
 * zorg cron jobs to run once per HOUR
 *
 * Every hour at minute 23:
 *		$ crontab -e
 *		  23 * * * * php -f ./stunde.php wwwroot=/path/to/public/www/ >/dev/null 2>>../logs/cron/cron_stunde.log
 */
error_reporting(E_ERROR);

/** Assign passed PHP CLI arguments to $_GET */
if (!empty($argv[1])) {
	parse_str($argv[1], $_GET);
}

error_log(sprintf('[%s] [NOTICE] <%s> Starting...', date('d.m.Y H:i:s',time()), __FILE__));

/** Check passed Parameters */
if (isset($_GET['wwwroot']) && is_string($_GET['wwwroot'])) $wwwroot = rtrim((string)$_GET['wwwroot'], '/\\'); // NO trailing Slash / !

/** www-Root available */
if (isset($wwwroot) && file_exists($wwwroot.'/includes/config.inc.php'))
{
	error_log(sprintf('[%s] [NOTICE] <%s> www-Root given: %s', date('d.m.Y H:i:s',time()), __FILE__, $wwwroot));
		
	error_log(sprintf('[%s] [NOTICE] <%s> Try including files...', date('d.m.Y H:i:s',time()), __FILE__));
	define('SITE_ROOT', $wwwroot); // Define own SITE_ROOT before loading general zConfigs
	require_once( SITE_ROOT.'/includes/config.inc.php');
	require_once( INCLUDES_DIR.'events.inc.php');
	require_once( INCLUDES_DIR.'gallery.inc.php');
	require_once( INCLUDES_DIR.'messagesystem.inc.php');
	//require_once(SITE_ROOT.'/dnd/dnd.inc.php');
	error_log(sprintf('[%s] [NOTICE] <%s> Files included', date('d.m.Y H:i:s',time()), __FILE__));

	/** D&D */
	//healrestingplayers();

	/** Event: check for new UpcomingEvent() */
	$event = new UpcomingEvent();
	$upcomingEventNotification = $event->notify( (isset($_GET['hours']) && is_numeric($_GET['hours']) ? $_GET['hours'] : NULL) );
	error_log(sprintf('[%s] [NOTICE] <%s> UpcomingEvent() finished: %s', date('d.m.Y H:i:s',time()), __FILE__, ( $upcomingEventNotification ? 'OK' : 'ERROR' )));

	/** Userpics: update new Gravatar-Userpics to local cache using usersystem::cacheGravatarImages() */
	$gravatarImagesCached = $user->cacheGravatarImages('all');
	error_log(sprintf('[%s] [NOTICE] <%s> cacheGravatarImages() finished: %s', date('d.m.Y H:i:s',time()), __FILE__, ( $gravatarImagesCached ? 'OK' : 'ERROR' )));
}
/** No www-Root path given */
else {
	error_log(sprintf('[%s] [WARNING] <%s> Missing Parameter 1 www-Root!', date('d.m.Y H:i:s',time()), __FILE__, $wwwroot));
	exit();
}

error_log(sprintf('[%s] [NOTICE] <%s> DONE - cron executed.', date('d.m.Y H:i:s',time()), __FILE__));
