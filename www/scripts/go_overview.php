<?php
/**
 * GO Overview
 *
 * ...
 * ...
 * ...
 *
 * @author [z]bert
 * @author [z]domi
 * @date nn.nn.nnnn
 * @version 1.0
 * @package zorg\Games\Go
 */

 /**
 * File Includes
 */
require_once __DIR__.'/../includes/config.inc.php';
require_once INCLUDES_DIR.'go_game.inc.php';

/**
 * Globals
 */
global $db, $user, $smarty;

/** running games */
$running_games = [];
$e = $db->query('SELECT g.* FROM go_games g WHERE state="counting" ORDER BY round DESC', __FILE__, __LINE__, 'SELECT counted Go Games');
while ($d = $db->fetch($e)) {
    $d['gamelink'] = 'game='.intval($d['id']);
    $running_games[] = $d;
}

$e = $db->query('SELECT g.* FROM go_games g WHERE state="running" ORDER BY round DESC', __FILE__, __LINE__, 'SELECT running Go Games');
while ($d = $db->fetch($e)) {
    $d['gamelink'] = 'game='.intval($d['id']);
    $running_games[] = $d;
}
$smarty->assign("running_games", $running_games);

// finished games
$finished_games = [];
$e = $db->query('SELECT g.* FROM go_games g WHERE state="finished"', __FILE__, __LINE__, 'SELECT finished Go Games');
while ($d = $db->fetch($e)) {
    $d['gamelink'] = 'game='.$d['id'];
    $finished_games[] = $d;
}
$smarty->assign("finished_games", $finished_games);

if ($user->is_loggedin())
{
	//waiting for acception:
	$e = $db->query('SELECT g.* FROM go_games g WHERE pl1=? AND state="open"',
					__FILE__, __LINE__, 'SELECT pending User Go Games', [$user->id]);
	while ($d = $db->fetch($e)) {
		$d['closelink'] = "close=$d[id]";
		$waiting_games[] = $d;
	}
	$smarty->assign("waiting_games", $waiting_games);

	//challenges:
	$e = $db->query('SELECT g.* FROM go_games g WHERE pl2=? AND state="open"',
					__FILE__, __LINE__, 'SELECT challenged User Go Games', [$user->id]);
	while ($d = $db->fetch($e)) {
		$d['acceptlink'] = 'accept='.$d['id'];
		$d['declinelink'] = 'decline='.$d['id'];
		$challenges[] = $d;
	}
	$smarty->assign("challenges", $challenges);

	$users = [];
	$ids = [];
	$e = $db->query('SELECT id, username  FROM user WHERE active=1 AND DATEDIFF(NOW(), lastlogin)<365 AND id !=?
					ORDER BY username ASC', __FILE__, __LINE__, 'SELECT active User Go Games', [$user->id]);
	while ($d = $db->fetch($e)) {
		$ids[] = intval($d['id']);
		$names[] = strval($d['username']);
	}
	$smarty->assign("ids", $ids);
	$smarty->assign("names", $names);
}

$sizes = array(9, 13, 19);
$smarty->assign("sizes", $sizes);
$handicap = array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9);
$smarty->assign("handicap", $handicap);
