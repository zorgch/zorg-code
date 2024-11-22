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
 * @include config.inc.php
 * @include smarty.inc.php
 * @include anficker.inc.php
 */
require_once __DIR__.'/../../public/includes/config.inc.php';
require_once INCLUDES_DIR.'smarty.inc.php';
require_once INCLUDES_DIR.'anficker.inc.php';

global $user, $smarty;

if ($user->is_loggedin())
{
	if(isset($_GET['del']) && $_GET['del'] !== 'no') Anficker::deleteLog($user->id);

	$smarty->assign('anficks', Anficker::getLog($user->id));
	$smarty->assign('anfickstats', Anficker::getNumAnficks());
}
else {
	$smarty->assign('error', ['type' => 'info', 'dismissable' => 'true', 'title' => 'Spresim seit: nope!', 'message' => 'Wenn du <a href="/profil.php?do=anmeldung">eingeloggt</a> wärst könntest du gegen Spresim batteln.<br><img border="0" src="/files/396/aficks.jpg">']);
}
