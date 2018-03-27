<?php
//require_once($_SERVER['DOCUMENT_ROOT'].'/includes/main.inc.php');
require_once( __DIR__ .'/../includes/hz_game.inc.php');
require_once( __DIR__ .'/../includes/usersystem.inc.php');

// actions
if (!empty($_GET['game']) && is_numeric($_GET['game']))
{
	try {
		$e = $db->query(
		"SELECT g.*, me.station mystation 
		FROM hz_games g
		JOIN hz_players me
		  ON me.game = g.id
		WHERE g.id='".$_GET['game']."'
		  AND me.user='".$user->id."'",
		__FILE__, __LINE__
		);
		$game = $db->fetch($e);

		if ($game) {	
			// move
			if ($_GET['ticket'] && is_numeric($_GET['move'])) {
				turn_move($_GET['game'], $_GET['ticket'], $_GET['move']);
			}
			
			// sentinel
			elseif ($_GET['do'] == "sentinel") {
				turn_sentinel($_GET['game']);
			}
			
			// stay
			elseif ($_GET['do'] == "stay") {
				turn_stay($_GET['game']);
			}
		}
		header("Location: /?tpl=103&game=".$_GET['game']);
		die();

	} catch (Exception $e) {
		error_log($e->getMessage());
	}
}
	else user_error("Nice try :-)");
