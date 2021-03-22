<?php
/**
 * GO Spiel - Smarty Page Actions
 *
 * @author [z]bert
 * @author [z]domi
 * @version 1.0
 * @package zorg\Games\Go
 */

/**
 * File Includes
 * @include go_game.inc.php Required
 */
require_once dirname(__FILE__).'/../includes/config.inc.php';
require_once INCLUDES_DIR.'go_game.inc.php';

/**
 * Globals
 */
global $db, $user, $smarty;

/** Validate and set passed Game-ID */
$gameid = (isset($_GET['game']) && is_numeric($_GET['game']) ? $_GET['game'] : null);

/** No Game-ID supplied, choose one randomly */
if (empty($gameid))
{
	/** Für eingeloggte User */
	if ($user->is_loggedin())
	{
		$notice = 'SELECT a GO-Game of the User';
		$e = $db->query('SELECT g.id FROM go_games g
						 WHERE g.nextturn='.$user->id.'
						   AND g.state="running"
						 OR g.nextturn='.$user->id.'
						   AND g.state="counting"
						 ORDER BY RAND()
						 LIMIT 1'
						 ,__FILE__, __LINE__, $notice);
		$gameid = $db->fetch($e);
		$gameid = $gameid['id'];

		/** kein Spiel gefunden - random Spiel wählen */
		if (!$gameid)
		{
			$notice = 'SELECT random GO-Game for logged-in';
			$e = $db->query('SELECT g.id FROM go_games g
							 WHERE g.state="running"
							 OR g.state="counting"
							 ORDER BY RAND()
							 LIMIT 1'
							 ,__FILE__, __LINE__, $notice);
			$gameid = $db->fetch($e);
			$gameid = $gameid['id'];
		}
	}
	
	/** Für nicht-eingeloggte */
	else {
		$notice = 'SELECT random GO-Game for Guests';
		$e = $db->query('SELECT g.id FROM go_games g
						 WHERE g.state="running"
						 OR g.state="counting"
						 ORDER BY RAND()
						 LIMIT 1'
						 ,__FILE__, __LINE__, $notice);
		$gameid = $db->fetch($e);
		$gameid = $gameid['id'];
	}
}

/** Load Game Details */
if (is_numeric($gameid))
{
	$notice = 'Load GO-Game Details';
	$e = $db->query('SELECT * FROM go_games g WHERE g.id='.$gameid.' LIMIT 1', __FILE__, __LINE__, $notice);
	$game = $db->fetch($e);
}

if (empty($game) || $game === false)
{
	$smarty->assign('error', ['type' => 'info', 'dismissable' => 'false', 'title' => 'GO Game with ID #'.$gameid.' not found', 'message' => 'Error: '.$notice]);
	$smarty->display('file:layout/elements/block_error.tpl');
} else {
	$smarty->assign('game', $game);
	$smarty->assign('nextstone_map', nextstone_map($gameid));
}
