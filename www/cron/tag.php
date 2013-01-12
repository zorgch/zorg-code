<?php

if($_GET['pw'] == 'schmelzigel') { // "altes" PW: schmelzigel

	//include_once($_SERVER['DOCUMENT_ROOT'].'/includes/main.inc.php');;
	//include_once($_SERVER['DOCUMENT_ROOT'].'/includes/addle.inc.php'); --> lässt Script aufhängen
	include_once($_SERVER['DOCUMENT_ROOT'].'/includes/apod.inc.php');
	//include_once($_SERVER['DOCUMENT_ROOT'].'/includes/forum.inc.php'); --> lässt Script aufhängen
	include_once($_SERVER['DOCUMENT_ROOT'].'/includes/quotes.inc.php');
	//include_once($_SERVER['DOCUMENT_ROOT'].'/includes/util.inc.php');
	include_once($_SERVER['DOCUMENT_ROOT'].'/includes/mysql.inc.php');
	//include_once($_SERVER['DOCUMENT_ROOT'].'/includes/setiathome.inc.php'); --> lässt Script aufhängen
	//include_once($_SERVER['DOCUMENT_ROOT'].'/includes/spaceweather.inc.php');  --> tut irgendwie nicht
	
	$status_html = "";
	
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
	
	// alte kompilierte comments löschen (um speicherplatz zu sparen)
	//Forum::deleteOldTemplates();
	
	// addle-games älter als 15 wochen löschen. spieler, der nicht gezogen hat, verliehrt
	//$status_html .= addle_remove_old_games() ? "addle_remove_old_games: done\n" : "addle_remove_old_games: ERROR\n" ;
	
	// Neuer Quote of the Day machen.
	//$status_html .= Quotes::newDailyQuote() ? "Quotes::newDailyQuote: done\n" : "Quotes::newDailyQuote: ERROR\n" ;
	Quotes::newDailyQuote();
	$status_html .=  "Quotes::newDailyQuote: done\n";
	
	//spaceweather --> wird doch eigentlich schon im apod Cron erledigt?? IneX, 8.6.08
	//$status_html .= get_spaceweather() ? "get_spaceweather: done\n" : "get_spaceweather: ERROR\n" ;
	
	// E-Mailbenachrichtigung
	/*
	$mail_date = date('d.m.Y');
	$mail_time = date('H:i');
	$recipient = "zorg@raduner.ch";
	$sender = "From: info@zooomclan.org";
	$subject = "Daily Cron ausgeführt";
	$body = "Der Daily Cron wurde am $mail_date um $mail_time ausgeführt\n\nStatus:\n $status_html";
	@mail($recipient, $subject, $body, $sender);
	*/

}
	
?>