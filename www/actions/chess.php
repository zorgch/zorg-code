<?
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/main.inc.php');
include_once($_SERVER['DOCUMENT_ROOT'].'includes/usersystem.inc.php');
	include_once($_SERVER['DOCUMENT_ROOT'].'includes/chess.inc.php');
	include_once($_SERVER['DOCUMENT_ROOT'].'includes/util.inc.php');

	// move
	if ($_GET['game'] && $_GET['from'] && $_GET['to']) {
		$e = $db->query(
			"SELECT *, IF(white=next_turn, 'w', 'b') player
			FROM chess_games 
			WHERE id='$_GET[game]' AND next_turn='$user->id'", 
			__FILE__, __LINE__
		);
		$d = $db->fetch($e);

		
		if ($d && Chess::is_valid_position($_GET['from']) && Chess::is_valid_position($_GET['to'])
			&& Chess::do_move($d['id'], $d['player'], $_GET['from'], $_GET['to'])
		) {
			unset($_GET['from']);
			unset($_GET['to']);
			header('Location: /smarty.php?'.url_params());
		}else{
			echo "Invalid chess move: <br /> game = $_GET[game] <br /> from = $_GET[from] <br /> to = $_GET[to]";
			exit;
		}
	}
	
	// offer remis
	if ($_GET['game'] && $_GET['do'] == 'offer_remis') {
		$e = $db->query("SELECT * FROM chess_games WHERE id='$_GET[game]' AND next_turn='$user->id'", __FILE__, __LINE__);
		$d = $db->fetch($e);
		if ($d) {
			Chess::do_offer_remis($_GET['game']);
			unset($_GET['do']);
			header("Location: /smarty.php?".url_params());
		}else{
			echo "'offer remis' is not allowed.";
			exit;
		}
	}
	
	// accept remis
	if ($_GET['game'] && $_GET['do'] == 'accept_remis') {
		$e = $db->query(
			"SELECT * 
			FROM chess_games 
			WHERE id='$_GET[game]' AND (white='$user->id' OR black='$user->id') AND next_turn!='$user->id' AND offering_remis='1'",
			__FILE__, __LINE__
		);
		$d = $db->fetch($e);
		if ($d) {
			Chess::do_remis($_GET['game']);
			unset($_GET['do']);
			header("Location: /smarty.php?".url_params());
		}else{
			echo "'accept remis' is not allowed.";
			exit;
		}
	}

	// deny remis
	if ($_GET['game'] && $_GET['do'] == 'deny_remis') {
		$e = $db->query(
			"SELECT *
			FROM chess_games
			WHERE id='$_GET[game]' AND (white='$user->id' OR black='$user->id') AND next_turn!='$user->id' AND offering_remis='1'",
			__FILE__, __LINE__
		);
		$d = $db->fetch($e);
		if ($d) {
			Chess::deny_remis($_GET['game']);
			header("Location: /smarty.php?".url_params());
		}else{
			echo "'deny remis' is not allowed";
			exit;
		}
	}
	
	// start new game
	if ($_POST['formid'] == 'chess_start') {
		if (Chess::new_game($_POST['user'])) {
			header("Location: /smarty.php?tpl=139");
		}else{
			echo "invalid chess_start: <br /> user = $_POST[user]";
			exit;
		}
	}
	
	// aufgeben
	if ($_GET['game'] && $_GET['do'] == 'aufgeben') {
		Chess::aufgabe($_GET['game']);
		
		unset($_GET['do']);
		header("Location: /smarty.php?tpl=141".url_params());
	}
?>