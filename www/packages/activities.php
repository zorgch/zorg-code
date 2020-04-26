<?php
/**
 * Activities Packages
 *
 * Holt und übergibt Activities an Smarty
 *
 * @author		IneX
 * @date		13.09.2009
 * @version		1.0
 * @package		zorg\Activities
 *
 * @global	object	$db		Globales Class-Object mit allen MySQL-Methoden
 * @global	object	$user	Globales Class-Object mit den User-Methoden & Variablen
 * @global	array	$smarty	Globales Class-Object mit allen Smarty-Methoden
 */
/**
 * File Includes
 * @include activities.inc.php
 * @include smarty.inc.php
 */
require_once INCLUDES_DIR.'activities.inc.php';
require_once INCLUDES_DIR.'smarty.inc.php';

global $db, $smarty, $user;

$smarty->assign("activities", Activities::getActivities($params));
$smarty->assign("num_activities", Activities::countActivities($params['user']));
