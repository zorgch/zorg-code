<?php
/**
 * Chess games overview
 * @package zorg\Games\Chess
 */

/** File includes */
include_once dirname(__FILE__).'/../includes/chess.inc.php';

global $db, $user, $smarty;

/** Instantiate Chess Game Class */
$chess = new Chess();

/** my games */
$smarty->assign('my_games', $chess->my_games());

/** users for new game */
$e = $db->query('SELECT id, username FROM user WHERE chess="1" AND id<>'.$user->id.' ORDER BY username ASC', __FILE__, __LINE__, 'SELECT Chess Players'); // chess = ENUM(string)
$user_ids = array();
$user_names = array();
while ($d = $db->fetch($e))
{
	$user_ids[] = $d['id'];
	$user_names[] = $d['username'];
}
$smarty->assign('user_ids', $user_ids);
$smarty->assign('user_names', $user_names);
