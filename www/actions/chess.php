<?php
/**
 * Chess game actions
 * @package zorg\Games\Chess
 */
/**
 * File includes
 */
require_once dirname(__FILE__).'/../includes/main.inc.php';
include_once INCLUDES_DIR.'chess.inc.php';

/** move */
if (isset($_GET['game']) && $_GET['game'] > 0 && isset($_GET['from']) && isset($_GET['to']))
{
	$e = $db->query('SELECT *, IF(white=next_turn, "w", "b") player
					FROM chess_games 
					WHERE id='.$_GET['game'].' AND next_turn='.$user->id,
					__FILE__, __LINE__, 'move');
	$d = $db->fetch($e);

	
	if ($d && Chess::is_valid_position($_GET['from']) && Chess::is_valid_position($_GET['to'])
		&& Chess::do_move($d['id'], $d['player'], $_GET['from'], $_GET['to'])
	) {
		unset($_GET['from']);
		unset($_GET['to']);
		header('Location: /?'.url_params());
	}else{
		echo "Invalid chess move: <br /> game = ".$_GET['game']." <br /> from = ".$_GET['from']." <br /> to = ".$_GET['to'];
		exit;
	}
}

/** offer remis */
if (isset($_GET['game']) && $_GET['game'] > 0 && isset($_GET['do']) && $_GET['do'] == 'offer_remis')
{
	$e = $db->query('SELECT * FROM chess_games WHERE id='.$_GET['game'].' AND next_turn='.$user->id, __FILE__, __LINE__, 'offer remis');
	$d = $db->fetch($e);
	if ($d) {
		Chess::do_offer_remis($_GET['game']);
		unset($_GET['do']);
		header("Location: /?".url_params());
	}else{
		echo "'offer remis' is not allowed.";
		exit;
	}
}

/** accept remis */
if (isset($_GET['game']) && $_GET['game'] > 0 && isset($_GET['do']) && $_GET['do'] == 'accept_remis')
{
	$e = $db->query('SELECT * 
					FROM chess_games 
					WHERE id='.$_GET['game'].' AND (white='.$user->id.' OR black='.$user->id.') AND next_turn!='.$user->id.' AND offering_remis="1"',
					__FILE__, __LINE__, 'accept remis');
	$d = $db->fetch($e);
	if ($d) {
		Chess::do_remis($_GET['game']);
		unset($_GET['do']);
		header("Location: /?".url_params());
	}else{
		echo "'accept remis' is not allowed.";
		exit;
	}
}

/** deny remis */
if (isset($_GET['game']) && $_GET['game'] > 0 && isset($_GET['do']) && $_GET['do'] == 'deny_remis')
{
	$e = $db->query('SELECT *
					FROM chess_games
					WHERE id='.$_GET['game'].' AND (white='.$user->id.' OR black='.$user->id.') AND next_turn!='.$user->id.' AND offering_remis="1"',
					__FILE__, __LINE__, 'deny remis');
	$d = $db->fetch($e);
	if ($d) {
		Chess::deny_remis($_GET['game']);
		header("Location: /?".url_params());
	}else{
		echo "'deny remis' is not allowed";
		exit;
	}
}

/** start new game */
if (isset($_POST['formid']) && $_POST['formid'] == 'chess_start')
{
	if (Chess::new_game($_POST['user'])) {
		header("Location: /?tpl=139");
	}else{
		echo "invalid chess_start: <br /> user = ".$_POST['user'];
		exit;
	}
}

/** aufgeben */
if (isset($_GET['game']) && $_GET['game'] > 0 && isset($_GET['do']) && $_GET['do'] == 'aufgeben')
{
	Chess::aufgabe($_GET['game']);
	
	unset($_GET['do']);
	header("Location: /tpl/141?".url_params());
}
