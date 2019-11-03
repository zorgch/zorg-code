<?php
/**
 * Anficker Package
 * 
 * Holt die ganzen Anficks und übergibt sie als Array an Smarty
 * 
 * @author ?
 * @version 1.0
 * @package zorg\Games\Anficker
 */
/**
 * File Includes
 * @include main.inc.php
 * @include smarty.inc.php
 * @include anficker.inc.php
 */
require_once( __DIR__ . '/../includes/config.inc.php');
require_once( __DIR__ . '/../includes/smarty.inc.php');
require_once( __DIR__ . '/../includes/anficker.inc.php');

global $user, $smarty;

if($_GET['del'] != 'no' && $user->is_loggedin())
{
	Anficker::deleteLog($user->id);
}

if ($user->is_loggedin())
{
	$smarty->assign('anficks', Anficker::getLog($user->id));
	$smarty->assign('anfickstats', Anficker::getNumAnficks());
} else {;
	$smarty->assign('error', ['type' => 'info', 'dismissable' => 'true', 'title' => 'Spresim seit: nope!', 'message' => 'Wenn du <a href="/profil.php?do=anmeldung">eingeloggt</a> wärst könntest du gegen Spresim batteln.<br><img border="0" src="/files/396/aficks.jpg">']);
}
