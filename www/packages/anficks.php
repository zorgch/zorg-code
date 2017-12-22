<?
/**
 * Anficker Package
 * 
 * Holt die ganzen Anficks und übergibt sie als Array an Smarty
 * 
 * @author ?
 * @version 1.0
 * @package Zorg
 * @subpackage Anficker
 *
 * @global array $db Array mit allen MySQL-Datenbankvariablen
 * @global array $user Array mit allen Uservariablen
 * @global array $smarty Array mit allen Smarty-Variablen
 */
/**
 * File Includes
 */
require_once($_SERVER['DOCUMENT_ROOT']."/includes/anficker.inc.php");
require_once($_SERVER['DOCUMENT_ROOT']."/includes/main.inc.php");
require_once($_SERVER['DOCUMENT_ROOT']."/includes/smarty.inc.php");

global $db, $smarty, $user;

if($_GET['del'] != 'no') {
	Anficker::deleteLog($user->id);
}

if ($user->typ != USER_NICHTEINGELOGGT)
{
	$smarty->assign("anficks", Anficker::getLog($user->id));
	$smarty->assign("anfickstats", Anficker::getNumAnficks());
} else {
	echo '<h2 style="font-size:large; font-weight: bold">Wenn du <a href="'.SITE_URL.'/profil.php?do=anmeldung" title="Account für Zorg.ch erstellen">eingeloggt</a> wärst könntest du gegen Spresim batteln.</h2><img border="0" src="/files/396/aficks.jpg">';
}
?>