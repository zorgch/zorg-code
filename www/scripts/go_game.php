<?php
/**
 * GO Spiel - Smarty Page Actions
 *
 * @author [z]berg, [z]domi
 * @date nn.nn.nnnn
 * @version 1.0
 * @package Zorg
 * @subpackage GO
 */
/**
 * File Includes
 * @include go_game.inc.php Required
 */
require_once( __DIR__ .'/../includes/go_game.inc.php');

/**
 * Globals
 */
global $db, $user, $smarty;

/** load game */
$gameid = $_GET['game'];

/** no game supplied, choose one randomly */
if (!$gameid) {
	$e = $db->query('SELECT g.id
					  FROM go_games g
					  WHERE g.nextturn='.$user->id.'
						AND g.state="running"
					 OR g.nextturn='.$user->id.'
					  AND g.state="counting"
					  ORDER BY RAND()',
					 __FILE__, __LINE__);
	$gameid = $db->fetch($e);
	$gameid = $gameid['id'];
	if (!$gameid){ //kein spiel gefunden - random spiel wählen
	 $e = $db->query('SELECT g.id
					  FROM go_games g
					  WHERE g.state="running"
					  OR g.state="counting"
					  ORDER BY RAND()'
					  ,__FILE__, __LINE__);
		$gameid = $db->fetch($e);
		$gameid = $gameid['id'];
	}
}

if (is_numeric($gameid))
{
	$e = $db->query('SELECT *
					 FROM go_games g
					 WHERE g.id = '.$gameid
					 ,__FILE__, __LINE__);
	$game = $db->fetch($e);
	
	if (!$game){
	user_error('Invalid game-ID: "'.$gameid.'"');
	return;
	}
	$smarty->assign('game', $game);
	$smarty->assign('nextstone_map', nextstone_map($gameid));
}
