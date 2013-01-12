<?
require_once($_SERVER['DOCUMENT_ROOT']."/includes/stockbroker.inc.php");
require_once($_SERVER['DOCUMENT_ROOT']."/includes/usersystem.inc.php");

global $smarty, $user;	



//$smarty->assign("kurse_aktuell", Stockbroker::getKurseNeuste(10));
$smarty->assign("kurse_tagesgewinner", Stockbroker::getTodaysWinners());
$smarty->assign("kurse_tagesverlierer", Stockbroker::getTodaysLosers());

$smarty->assign("mosttraded", Stockbroker::getYesterdaysMosttraded());
$smarty->assign("bargeld", Stockbroker::getBargeld($user->id));
$smarty->assign("currentproperty", Stockbroker::getStocksOwned($user->id));
$smarty->assign("highscore", Stockbroker::getHighscore());
$smarty->assign("mytrades", Stockbroker::getTrades($user->id));
$smarty->assign("stock_warnings", Stockbroker::getWarnings($user->id));

?>