<?php
global $db, $user, $smarty;

/**
 * @DEPRECATED
 * @FIXME Remove this file & move all $smarty->assign to smarty.fnc.php - see e.g. smarty_peter()
 */
if (isset($user) && isset($user->id) && $user->id > 0)
{
	/** addle */
	$smarty->assign('open_addle', getOpenAddleGames($user->id));

	/** Hunting z */
	$smarty->assign('hz_running_games', hz_running_games());
	$smarty->assign('hz_open_games', hz_open_games());

	/** GO */
	$smarty->assign('go_running_games', go_running_games());
	$smarty->assign('go_open_games', go_open_games());

	/** Chess */
	$smarty->assign('open_chess', getOpenChessGames($user->id));

	/** forum */
	$smarty->assign('new_comments', Forum::getNumunreadposts($user->id));

	/** bugtracker */
	$smarty->assign('own_bugs', Bugtracker::getNumOwnBugs());
	$smarty->assign('open_bugs', Bugtracker::getNumOpenBugs());
	$smarty->assign('new_bugs', Bugtracker::getNumNewBugs());

	/** messages */
	$smarty->assign('new_messages', Messagesystem::getNumNewMessages($user->id));

	/** forum */
	$smarty->assign('new_rezepte', Rezepte::getNumNewRezepte($user->id));
	
	/**
	 * Peter
	 * @see smarty_peter()
	*/
	
	/**
	 * Shoot the Lamber
	 * @see smarty_stl_games()
	 */
}
