<?php
/**
 * zorg cron jobs to run once per DAY
 *    $ sudo crontab -e
 *      15 7 * * * php -f /var/cron/tag.php >/dev/null 2>>/var/log/cron_tag.log
 */
error_reporting(E_ERROR);

/** Assign passed PHP CLI arguments to $_GET */
if (!empty($argv[1])) {
  parse_str($argv[1], $_GET);
}

error_log(sprintf('[%s] [NOTICE] <%s> Starting...', date('d.m.Y H:i:s',time()), __FILE__));

require_once( __DIR__ .'/../www/includes/config.inc.php');
include_once( __DIR__ .'/../www/includes/addle.inc.php');
include_once( __DIR__ .'/../www/includes/hz_game.inc.php');
include_once( __DIR__ .'/../www/includes/peter.inc.php');
include_once( __DIR__ .'/../www/includes/apod.inc.php');
include_once( __DIR__ .'/../www/includes/forum.inc.php');
include_once( __DIR__ .'/../www/includes/quotes.inc.php');
include_once( __DIR__ .'/../www/includes/gallery.inc.php');
/*
include_once( __DIR__ .'/../www/includes/setiathome.inc.php'); --> lässt Script aufhängen
include_once( __DIR__ .'/../www/includes/spaceweather.inc.php');  --> tut irgendwie nicht
include_once( __DIR__ .'/../www/dnd/dnd.inc.php');
*/
error_log(sprintf('[%s] [NOTICE] <%s> Files included', date('d.m.Y H:i:s',time()), __FILE__));

/** Addle: Games älter als 15 wochen löschen. spieler, der nicht gezogen hat, verliehrt */
error_log(sprintf('[%s] [NOTICE] <%s> addle_remove_old_games() finished: %s', date('d.m.Y H:i:s',time()), __FILE__, ( addle_remove_old_games() ? 'OK' : 'ERROR' )));

/** Hunting z: Zug auslassen, wenn jemand länger nicht zieht */
error_log(sprintf('[%s] [NOTICE] <%s> hz_turn_passing() finished: %s', date('d.m.Y H:i:s',time()), __FILE__, ( hz_turn_passing() !== false ? 'OK' : 'ERROR' )));

/** Peter: Zug an nächsten Spieler geben, wenn jemand länger nicht zieht */
$peter = new peter();
$peterRunningGames = $peter->laufende_spiele(false);
if (is_array($peterRunningGames) && count($peterRunningGames) > 0) {
	$i = 0;
	foreach ($peterRunningGames as $peterGame) {
		error_log(sprintf('[%s] [NOTICE] <%s> $peter->auto_nextplayer() finished: %s', date('d.m.Y H:i:s',time()), __FILE__, ( $peter->auto_nextplayer($peterGame) !== false ? 'OK' : 'Kein Zug' )));
		$i++;
	}
} else {
	error_log(sprintf('[%s] [NOTICE] <%s> $peter->laufende_spiele() finished: keine laufenden Spiele', date('d.m.Y H:i:s',time()), __FILE__));
}

/** D&D Creeps generieren */
//generateOrcs(10);

/** Quotes: neuer Quote of the Day machen */
error_log(sprintf('[%s] [NOTICE] <%s> Quotes::newDailyQuote() finished: %s', date('d.m.Y H:i:s',time()), __FILE__, ( Quotes::newDailyQuote() ? 'OK' : 'ERROR' )));

/** Gallery: neues Daily Pic generieren */
error_log(sprintf('[%s] [NOTICE] <%s> setNewDailyPic() finished: %s', date('d.m.Y H:i:s',time()), __FILE__, ( setNewDailyPic() ? 'OK' : 'ERROR' )));

/** APOD: neustes APOD holen und in die Gallery posten */
error_log(sprintf('[%s] [NOTICE] <%s> get_apod() finished: %s', date('d.m.Y H:i:s',time()), __FILE__, ( get_apod() ? 'OK' : 'ERROR' )));

/** SETI/BOINC Stats */
//setiathome::tagesabschluss();

/** Spaceweather Stats */
//get_spaceweather();

/** Forum: alte kompilierte comments löschen (um speicherplatz zu sparen) */
error_log(sprintf('[%s] [NOTICE] <%s> Forum::deleteOldTemplates() finished: %s', date('d.m.Y H:i:s',time()), __FILE__, ( Forum::deleteOldTemplates() ? 'OK' : 'ERROR' )));
