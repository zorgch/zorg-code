<?PHP

//require_once($_SERVER['DOCUMENT_ROOT'].'/dnd/dnd.inc.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/includes/forum.inc.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/includes/messagesystem.inc.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/includes/mysql.inc.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/includes/quotes.inc.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/includes/setiathome.inc.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/includes/addle.inc.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/includes/apod.inc.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/includes/hz_game.inc.php');


if($_GET['pw'] == 'schmelzigel') { // "altes" PW: schmelzigel

	setiathome::tagesabschluss();

	// Neuer Quote of the Day machen.
	Quotes::newDailyQuote();

	// addle-games älter als 15 wochen löschen. spieler, der nicht gezogen hat, verliehrt
	addle_remove_old_games();

	// Hunting z: auslassen, wenn jemand länger nicht zieht.
	//hz_turn_passing();  --> deaktiviert, da HZ ja momentan am Ar*ch ist. IneX, 8.6.08

	//spaceweather
	//get_spaceweather(); --> wird doch im apod Cron erledigt?? IneX, 8.6.08

	// alte kompilierte comments löschen (um speicherplatz zu sparen)
	Forum::deleteOldTemplates();

	// D&D Creeps generieren
	//generateOrcs(10);
	
	
	$mail_date = date('d.m.Y H:i');
	$body = "Der Daily Cron wurde am ".$mail_date." erfolgreich ausgef&uuml;hrt\n";
	@mail('oliver@raduner.ch', 'Daily Cron ausgef&uuml;hrt', $body, 'From: info@zooomclan.org\n');
}
?>