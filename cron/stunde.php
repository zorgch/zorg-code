<?php
/**
 * zorg cron jobs to run once per HOUR
 *    $ sudo crontab -e
 *      0 * * * * php -f /var/cron/stunde.php >/dev/null 2>>/var/log/cron_stunde.log
 */
error_reporting(E_ERROR);

/** Assign passed PHP CLI arguments to $_GET */
if (!empty($argv[1])) {
  parse_str($argv[1], $_GET);
}

error_log(sprintf('[%s] [NOTICE] <%s> Starting...', date('d.m.Y H:i:s',time()), __FILE__));

require_once( __DIR__ .'/../www/includes/config.inc.php');
require_once( __DIR__ .'/../www/includes/events.inc.php');
require_once( __DIR__ .'/../www/includes/gallery.inc.php');
require_once( __DIR__ .'/../www/includes/messagesystem.inc.php');
//require_once($_SERVER['DOCUMENT_ROOT'].'/dnd/dnd.inc.php');
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
