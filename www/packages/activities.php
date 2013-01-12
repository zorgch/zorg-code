<?
/**
 * Activities Packages
 * 
 * Holt und übergibt Activities an Smarty
 *
 * @author		IneX
 * @date		13.09.2009
 * @version		1.0
 * @package		Zorg
 * @subpackage	Activities
 *
 * @global	array	$db		Array mit allen MySQL-Datenbankvariablen
 * @global	array	$user	Array mit allen Uservariablen
 * @global	array	$smarty	Array mit allen Smarty-Variablen
 */

/**
 * File Includes
 */
require_once($_SERVER['DOCUMENT_ROOT']."/includes/activities.inc.php");
require_once($_SERVER['DOCUMENT_ROOT']."/includes/smarty.inc.php");

global $db, $smarty, $user;

$smarty->assign("activities", Activities::getActivities($params));
$smarty->assign("num_activities", Activities::countActivities($params['user']));
?>