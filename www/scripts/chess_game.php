<?php
/**
 * zorg Schach Scripts
 * @package zorg\Games\Schach
 */
require_once dirname(__FILE__).'/../includes/config.inc.php';
require_once INCLUDES_DIR.'chess.inc.php';

global $db, $user, $smarty;

/** Check and set passed Parameters */
$game_id = (isset($_GET['game']) && is_numeric($_GET['game']) && $_GET['game'] > 0 ? (int)$_GET['game'] : null);
if (isset($_GET['from'])) $from_pos = (string)$_GET['from'];

/** Instantiate Chess Game Class */
$chess = new Chess();

/** Assign my Games to Smarty */
$smarty->assign('my_games', $chess->my_games());

/** Load Game */
if (empty($game_id))
{
	$e = $db->query('SELECT id FROM chess_games WHERE next_turn='.$user->id.' OR offering_remis="1" LIMIT 0,1', __FILE__, __LINE__, 'SELECT Chess Game ID'); // offering_remis = ENUM(string)!
	$d = $db->fetch($e);
	if ($d) $game_id = $d['id'];
}

if (isset($game_id))
{
	$game = $db->fetch($db->query(
		'SELECT 
			g.*, 
			w.rank wrank, w.score wscore, b.score bscore, b.rank brank, 
			concat(wu.clan_tag, wu.username) wuser, concat(bu.clan_tag, bu.username) buser
		FROM chess_games g, chess_dwz w, chess_dwz b, user wu, user bu
		WHERE g.id='.$game_id.' AND w.user=g.white AND b.user=g.black AND wu.id=g.white AND bu.id=g.black', 
		__FILE__, __LINE__, 'SELECT Chess Game'
	));
	$smarty->assign('game', $game);

	if ($game['white'] == $user->id) $my_color = 'w';
	elseif ($game['black'] == $user->id) $my_color = 'b';
	else $my_color = '';

	$board = $chess->get_board($game_id);
	$smarty->assign('board', $chess->simplify_board($board));
	$smarty->assign('taken', $board['taken']);
	$smarty->assign('positions', $chess->positions());
	$smarty->assign('history', $board['history']);

	$d = $db->fetch($db->query('SELECT * FROM chess_history WHERE game='.$game_id.' ORDER BY nr DESC LIMIT 0,1', __FILE__, __LINE__, 'SELECT Chess History'));
	if (substr($d['white'], -1) == '+') {
		$smarty->assign('say_chess', 'w');
	}elseif (substr($d['black'], -1) == '+') {
		$smarty->assign('say_chess', 'b');
	}else{
		$smarty->assign('say_chess', '');
	}

	if ($game['state'] == 'running' && $game['next_turn'] == $user->id && !$game['offering_remis']) {
		$smarty->assign("my_positions", $chess->own_positions($board, $my_color));
		
		if (isset($from_pos)) {
			$e = $db->query('SELECT * FROM chess_history WHERE game='.$game_id.' ORDER BY nr DESC LIMIT 0,1', __FILE__, __LINE__, 'SELECT Chess History');
			$d = $db->fetch($e);
			$prev_move = $my_color=='w' ? $d['black'] : $d['white'];
			$smarty->assign('possible_moves', $chess->possible_moves($board, $my_color, $from_pos, $prev_move));
		}else{
			$smarty->assign('possible_moves', array());
		}
	}else{
		$smarty->assign('my_positions', array());
		$smarty->assign('possible_moves', array());
	}	
}
