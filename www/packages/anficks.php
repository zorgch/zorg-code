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
require_once($_SERVER['DOCUMENT_ROOT']."/includes/mysql.inc.php");
require_once($_SERVER['DOCUMENT_ROOT']."/includes/smarty.inc.php");
require_once($_SERVER['DOCUMENT_ROOT']."/includes/usersystem.inc.php");

global $db, $smarty, $user;

if($_GET['del'] != 'no') {
	Anficker::deleteLog($user->id);
}

$smarty->assign("anficks", Anficker::getLog($user->id));
$smarty->assign("anfickstats", Anficker::getNumAnficks());
?>