<?php
require_once dirname(__FILE__).'/../includes/hz_game.inc.php';
require_once dirname(__FILE__).'/../includes/usersystem.inc.php';

/** hz actions */
if (!empty($_GET['game']) && is_numeric($_GET['game']))
{
	try {
		$e = $db->query('SELECT g.*, me.station mystation 
			FROM hz_games g
			JOIN hz_players me
			  ON me.game = g.id
			WHERE g.id='.$_GET['game'].'
			  AND me.user='.$user->id,
			__FILE__, __LINE__);
		$game = $db->fetch($e);
	} catch (Exception $e) {
		user_error($e->getMessage(), E_USER_WARNING);
		exit;
	}

	if ($game)
	{	
		/** move */
		if ($_GET['ticket'] && is_numeric($_GET['move']))
		{
			if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> turn_move(): %d, %s, %s', __FILE__, __LINE__, $_GET['game'], $_GET['ticket'], $_GET['move']));
			turn_move($_GET['game'], $_GET['ticket'], $_GET['move']);
		}
		
		/** sentinel */
		elseif ($_GET['do'] === 'sentinel')
		{
			if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> turn_sentinel(): %d', __FILE__, __LINE__, $_GET['game']));
			turn_sentinel($_GET['game']);
		}
		
		/** stay */
		elseif ($_GET['do'] === 'stay')
		{
			if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> turn_stay(): %d', __FILE__, __LINE__, $_GET['game']));
			turn_stay($_GET['game']);
		}
	}
	header('Location: /?tpl=103&game='.$_GET['game']);
	exit;
}
else user_error('Nice try :-)', E_USER_NOTICE);
