<?php
/**
 * Mark Template as Favourite
 * @package zorg\Smarty
 *
 * @TODO finish implementation
 */
global $db, $smarty, $user;

$e = $db->query("SELECT id, tpl_favourite FROM user WHERE tpl_favourite!=0 AND id!=".$user->id, __FILE__, __LINE__);
$userfavourites = array();
while ($d = mysql_fetch_array($e)) {
  array_push($userfavourites, $d);
}

$smarty->assign("userfavourites", $userfavourites);
