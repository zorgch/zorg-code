<?php
/**
 * GO Overview
 * 
 * ...
 * ...
 * ...
 *
 * @author [z]bert, [z]domi
 * @date nn.nn.nnnn
 * @version 1.0
 * @package zorg
 * @subpackage GO
 */
/**
 * File Includes
 */
require_once dirname(__FILE__).'/../includes/config.inc.php';

/**
 * Globals
 */
global $db, $user, $smarty;

/** running games */
$e = $db->query(
		"SELECT g.*
		  FROM go_games g
		  WHERE state = 'counting'
		  ORDER BY round DESC",
		__FILE__, __LINE__);

$running_games = array();

while ($d = $db->fetch($e)) {
    $d['gamelink'] = "game=$d[id]";
    $running_games[] = $d;
}

$e = $db->query(
		"SELECT g.*
		  FROM go_games g
		  WHERE state = 'running'
		  ORDER BY round DESC",
		__FILE__, __LINE__);

while ($d = $db->fetch($e)) {
    $d['gamelink'] = "game=$d[id]";
    $running_games[] = $d;
}
$smarty->assign("running_games", $running_games);

// finished games
$e = $db->query(
		"SELECT g.*
		  FROM go_games g
		  WHERE state = 'finished'",
		__FILE__, __LINE__);

$finished_games = array();

while ($d = $db->fetch($e)) {
    $d['gamelink'] = "game=$d[id]";
    $finished_games[] = $d;
}
$smarty->assign("finished_games", $finished_games);

//waiting for acception:
$e = $db->query(
		"SELECT g.*
		  FROM go_games g
		  WHERE pl1 = '".$user->id."'
					     AND state = 'open'",
		__FILE__, __LINE__);

while ($d = $db->fetch($e)) {    
    $d['closelink'] = "close=$d[id]";
    $waiting_games[] = $d;
}

$smarty->assign("waiting_games", $waiting_games);

//challenges:
$e = $db->query(
		"SELECT g.*
		  FROM go_games g
		  WHERE pl2 = '".$user->id."'
		    AND state = 'open'",
		__FILE__, __LINE__);

while ($d = $db->fetch($e)) {
    $d['acceptlink'] = "accept=$d[id]";
    $d['declinelink'] = "decline=$d[id]";
    $challenges[] = $d;
}
$smarty->assign("challenges", $challenges);

$e = $db->query("SELECT id, username 
		 FROM user
		 WHERE active=1
		   AND DATEDIFF(now(), lastlogin) < 365
		   AND id != '".$user->id."'
	         ORDER BY username ASC",
		                        __FILE__, __LINE__);
        $users = array();
        $ids = array();
        while ($d = $db->fetch($e)) {
	                    $ids[] = $d['id'];
	                    $names[] = $d['username'];
	}
        $smarty->assign("ids", $ids);
        $smarty->assign("names", $names);

$sizes = array(9, 13, 19);
$smarty->assign("sizes", $sizes);
$handicap = array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9);
$smarty->assign("handicap", $handicap);

