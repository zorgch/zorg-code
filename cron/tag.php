<?php
/**
 * zorg cron jobs to run once per DAY
 *    $ sudo crontab -e
 *      15 7 * * * php -f /var/cron/tag.php >/dev/null 2>/var/log/cron_tag.log
 */
error_reporting(E_ERROR);

/** Assign passed PHP CLI arguments to $_GET */
if (!empty($argv[1])) {
  parse_str($argv[1], $_GET);
}

error_log(sprintf('[NOTICE] <%s> Starting...', __FILE__));

require_once( __DIR__ .'/../www/includes/config.inc.php');
//include_once( __DIR__ .'/../www/includes/main.inc.php');
include_once( __DIR__ .'/../www/includes/addle.inc.php');
include_once( __DIR__ .'/../www/includes/hz_game.inc.php');
include_once( __DIR__ .'/../www/includes/apod.inc.php');
include_once( __DIR__ .'/../www/includes/forum.inc.php');
include_once( __DIR__ .'/../www/includes/quotes.inc.php');
include_once( __DIR__ .'/../www/includes/gallery.inc.php');
/*
include_once( __DIR__ .'/../www/includes/setiathome.inc.php'); --> lässt Script aufhängen
include_once( __DIR__ .'/../www/includes/spaceweather.inc.php');  --> tut irgendwie nicht
include_once( __DIR__ .'/../www/dnd/dnd.inc.php');
*/
error_log(sprintf('[NOTICE] <%s> Files included', __FILE__));

/** Addle: Games älter als 15 wochen löschen. spieler, der nicht gezogen hat, verliehrt */
error_log(sprintf('[NOTICE] <%s> addle_remove_old_games() finished: %s', __FILE__, ( addle_remove_old_games() ? 'OK' : 'ERROR' )));

/** Hunting z: Zug auslassen, wenn jemand länger nicht zieht */
error_log(sprintf('[NOTICE] <%s> hz_turn_passing() finished: %s', __FILE__, ( hz_turn_passing() !== false ? 'OK' : 'ERROR' )));

/** D&D Creeps generieren */
//generateOrcs(10);

/** Quotes: neuer Quote of the Day machen */
error_log(sprintf('[NOTICE] <%s> Quotes::newDailyQuote() finished: %s', __FILE__, ( Quotes::newDailyQuote() ? 'OK' : 'ERROR' )));

/** Gallery: neues Daily Pic generieren */
error_log(sprintf('[NOTICE] <%s> setNewDailyPic() finished: %s', __FILE__, ( setNewDailyPic() ? 'OK' : 'ERROR' )));

/** APOD: neustes APOD holen und in die Gallery posten */
error_log(sprintf('[NOTICE] <%s> get_apod() finished: %s', __FILE__, ( get_apod() ? 'OK' : 'ERROR' )));

/** SETI/BOINC Stats */
//setiathome::tagesabschluss();

/** Spaceweather Stats */
//get_spaceweather();

/** Forum: alte kompilierte comments löschen (um speicherplatz zu sparen) */
error_log(sprintf('[NOTICE] <%s> Forum::deleteOldTemplates() finished: %s', __FILE__, ( Forum::deleteOldTemplates() ? 'OK' : 'ERROR' )));
