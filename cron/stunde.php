<?php
/** Assign passed PHP CLI arguments to $_GET */
if (!empty($argv[1])) {
  parse_str($argv[1], $_GET);
}

error_log(sprintf('[NOTICE] <%s> Starting...', __FILE__));

require_once( __DIR__ .'/../www/includes/config.inc.php');
require_once( __DIR__ .'/../www/includes/events.inc.php');
require_once( __DIR__ .'/../www/includes/gallery.inc.php');
require_once( __DIR__ .'/../www/includes/messagesystem.inc.php');
//require_once($_SERVER['DOCUMENT_ROOT'].'/dnd/dnd.inc.php');
error_log(sprintf('[NOTICE] <%s> Files included', __FILE__));

/** D&D */
//healrestingplayers();

/** Event: check for new UpcomingEvent() */
$event = new UpcomingEvent();
$upcomingEventNotification = $event->notify( (isset($_GET['hours']) && is_numeric($_GET['hours']) ? $_GET['hours'] : NULL) );
error_log(sprintf('[NOTICE] <%s> UpcomingEvent() finished: %s', __FILE__, ( $upcomingEventNotification ? 'OK' : 'ERROR' )));

/** Userpics: update new Gravatar-Userpics to local cache using usersystem::cacheGravatarImages() */
$gravatarImagesCached = $user->cacheGravatarImages('all');
error_log(sprintf('[NOTICE] <%s> cacheGravatarImages() finished: %s', __FILE__, ( $gravatarImagesCached ? 'OK' : 'ERROR' )));
