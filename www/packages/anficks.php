<?
/**
 * Anficker Package
 * 
 * Holt die ganzen Anficks und übergibt sie als Array an Smarty
 * 
 * @author ?
 * @version 1.0
 * @package zorg
 * @subpackage Anficker
 */
/**
 * File Includes
 * @include main.inc.php
 * @include anficker.inc.php
 * @include smarty.inc.php
 */
require_once( __DIR__ . '/../includes/main.inc.php');
require_once( __DIR__ . '/../includes/smarty.inc.php');
require_once( __DIR__ . '/../includes/anficker.inc.php');

global $user;

if($_GET['del'] != 'no' && $user->is_loggedin()) {
	Anficker::deleteLog($user->id);
}

if (usersystem::islogged_in())
{
	$smarty->assign('anficks', Anficker::getLog($user->id));
	$smarty->assign('anfickstats', Anficker::getNumAnficks());
} else {
	echo menu('zorg');
	echo menu('games');
	echo '<h2 style="font-size:large; font-weight: bold">Wenn du <a href="'.SITE_URL.'/profil.php?do=anmeldung" title="Account für Zorg.ch erstellen">eingeloggt</a> wärst könntest du gegen Spresim batteln.</h2><img border="0" src="/files/396/aficks.jpg">';
}
