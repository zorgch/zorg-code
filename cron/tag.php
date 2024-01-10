<?php
/**
 * zorg cron jobs to run once per DAY
 *
 * Every day at 07:15:
 *		$ crontab -e
 *		  15 7 * * * php -f ./tag.php wwwroot=/path/to/public/www/ >/dev/null 2>>../logs/cron/cron_tag.log
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
	(!require_once( SITE_ROOT.'/includes/config.inc.php')) ?? error_log(sprintf('[%s] [ERROR] <%s> Including %s failed', date('d.m.Y H:i:s',time()), __FILE__, SITE_ROOT.'/includes/config.inc.php'));
	include_once( INCLUDES_DIR.'addle.inc.php');
	include_once( INCLUDES_DIR.'hz_game.inc.php');
	include_once( INCLUDES_DIR.'peter.inc.php');
	include_once( INCLUDES_DIR.'apod.inc.php');
	include_once( INCLUDES_DIR.'forum.inc.php');
	include_once( INCLUDES_DIR.'quotes.inc.php');
	include_once( INCLUDES_DIR.'gallery.inc.php');
	/*
	include_once( INCLUDES_DIR.'setiathome.inc.php'); --> lässt Script aufhängen
	include_once( INCLUDES_DIR.'spaceweather.inc.php');	--> tut irgendwie nicht
	include_once( SITE_ROOT.'/dnd/dnd.inc.php');
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
	//get_spaceweather(); // Fails with 'Undefined index: solarflares_percent_48hr_[]_percent', 'file' => '/www/includes/spaceweather.inc.php', 'line' => 231

	/** Forum: alte kompilierte comments löschen (um speicherplatz zu sparen) */
	error_log(sprintf('[%s] [NOTICE] <%s> Forum::deleteOldTemplates() finished: %s', date('d.m.Y H:i:s',time()), __FILE__, ( Forum::deleteOldTemplates() ? 'OK' : 'ERROR' )));
}
/** No www-Root path given */
else {
	error_log(sprintf('[%s] [WARNING] <%s> Missing Parameter 1 www-Root!', date('d.m.Y H:i:s',time()), __FILE__, $wwwroot));
	exit();
}

error_log(sprintf('[%s] [NOTICE] <%s> DONE - cron executed.', date('d.m.Y H:i:s',time()), __FILE__));
