<?PHP
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


// Neuer Quote of the Day machen.
Quotes::newDailyQuote();

?>