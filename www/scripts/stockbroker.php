<?php
/**
 * Stockbroker Smarty-Function assignments
 * @package zorg\Games\Stockbroker
 */

/** File includes */
require_once __DIR__.'/../includes/stockbroker.inc.php';
require_once INCLUDES_DIR.'usersystem.inc.php';

global $smarty, $user;

//$smarty->assign("kurse_aktuell", Stockbroker::getKurseNeuste(10));
$smarty->assign("kurse_tagesgewinner", $stockbroker->getTodaysWinners());
$smarty->assign("kurse_tagesverlierer", $stockbroker->getTodaysLosers());

$smarty->assign("mosttraded", $stockbroker->getYesterdaysMosttraded());
if ($user->is_loggedin()) $smarty->assign("bargeld", $stockbroker->getBargeld($user->id));
if ($user->is_loggedin()) $smarty->assign("currentproperty", $stockbroker->getStocksOwned($user->id));
$smarty->assign("highscore", $stockbroker->getHighscore());
if ($user->is_loggedin()) $smarty->assign("mytrades", $stockbroker->getTrades($user->id));
if ($user->is_loggedin()) $smarty->assign("stock_warnings", $stockbroker->getWarnings($user->id));
