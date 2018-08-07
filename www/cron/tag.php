<?php

if($_GET['pw'] == 'schmelzigel')
{
	error_log(sprintf('[%s] <cron> Starting...', __FILE__));

	include_once( __DIR__ .'/../includes/main.inc.php');
	//include_once($_SERVER['DOCUMENT_ROOT'].'/includes/addle.inc.php'); --> lässt Script aufhängen
	include_once( __DIR__ .'/../includes/apod.inc.php');
	//include_once($_SERVER['DOCUMENT_ROOT'].'/includes/forum.inc.php'); --> lässt Script aufhängen
	//include_once($_SERVER['DOCUMENT_ROOT'].'/includes/quotes.inc.php');
	//include_once($_SERVER['DOCUMENT_ROOT'].'/includes/util.inc.php');
	//include_once($_SERVER['DOCUMENT_ROOT'].'/includes/setiathome.inc.php'); --> lässt Script aufhängen
	//include_once($_SERVER['DOCUMENT_ROOT'].'/includes/spaceweather.inc.php');  --> tut irgendwie nicht

	error_log('[INFO] ' . __FILE__ . ': files included');

	/*
	include_once($_SERVER['DOCUMENT_ROOT'].'/includes/main.inc.php');
	include_once($_SERVER['DOCUMENT_ROOT'].'/includes/util.inc.php');
	include_once($_SERVER['DOCUMENT_ROOT'].'/includes/gallery.inc.php');
	include_once($_SERVER['DOCUMENT_ROOT'].'/includes/forum.inc.php');
	include_once($_SERVER['DOCUMENT_ROOT'].'/includes/messagesystem.inc.php');
	include_once($_SERVER['DOCUMENT_ROOT'].'/includes/mysql.inc.php');
	include_once($_SERVER['DOCUMENT_ROOT'].'/includes/quotes.inc.php');
	include_once($_SERVER['DOCUMENT_ROOT'].'/includes/setiathome.inc.php');
	include_once($_SERVER['DOCUMENT_ROOT'].'/includes/addle.inc.php');
	include_once($_SERVER['DOCUMENT_ROOT'].'/includes/apod.inc.php');
	include_once($_SERVER['DOCUMENT_ROOT'].'/includes/hz_game.inc.php');
	*/

	//setiathome::tagesabschluss();

	/** alte kompilierte comments löschen (um speicherplatz zu sparen) */
	error_log(sprintf('[%s] <cron> Forum::deleteOldTemplates() finished: %s', __FILE__, ( Forum::deleteOldTemplates() ? 'OK' : 'ERROR' )));

	/** addle-games älter als 15 wochen löschen. spieler, der nicht gezogen hat, verliehrt */
	error_log(sprintf('[%s] <cron> addle_remove_old_games() finished: %s', __FILE__, ( addle_remove_old_games() ? 'OK' : 'ERROR' )));

	/** Neuer Quote of the Day machen. */
	//Quotes::newDailyQuote();
	error_log(sprintf('[%s] <cron> Quotes::newDailyQuote() finished: %s', __FILE__, ( Quotes::newDailyQuote() ? 'OK' : 'ERROR' )));

	/** Neustes APOD holen und in die Gallery posten */
	error_log(sprintf('[%s] <cron> get_apod() finished: %s', __FILE__, ( get_apod() ? 'OK' : 'ERROR' )));

	//spaceweather --> wird doch eigentlich schon im apod Cron erledigt?? IneX, 8.6.08
	//$status_html .= get_spaceweather() ? "get_spaceweather: done\n" : "get_spaceweather: ERROR\n" ;

} else {
	error_log(sprintf('[%s] <cron> Access denied!', __FILE__));
}
