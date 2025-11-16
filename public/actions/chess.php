<?php
/**
 * Chess game actions
 *
 * @package zorg\Games\Chess
 */

/**
 * File includes
 */
require_once __DIR__.'/../includes/config.inc.php';
include_once INCLUDES_DIR.'chess.inc.php';

/** Input validation and sanitization */
$doAction = filter_input(INPUT_GET, 'do', FILTER_SANITIZE_SPECIAL_CHARS) ?? null; // $_GET['do']
$gameId = filter_input(INPUT_GET, 'game', FILTER_VALIDATE_INT) ?? 0; // $_GET['game']
$fromField = filter_input(INPUT_GET, 'from', FILTER_SANITIZE_SPECIAL_CHARS) ?? null; // $_GET['from']
$toField = filter_input(INPUT_GET, 'to', FILTER_SANITIZE_SPECIAL_CHARS) ?? null; // $_GET['to']
$viewForm = filter_input(INPUT_POST, 'formid', FILTER_SANITIZE_SPECIAL_CHARS) ?? null; // $_POST['formid']
$userId = filter_input(INPUT_POST, 'user', FILTER_VALIDATE_INT) ?? null; // $_POST['user']

if (isset($gameId) && $gameId > 0)
{
	/** move */
	if (!empty($fromField) && !empty($toField))
	{
		$e = $db->query('SELECT *, IF(white=next_turn, "w", "b") player FROM chess_games WHERE id=? AND next_turn=?',
						__FILE__, __LINE__, 'move', [$gameId, $user->id]);
		$d = $db->fetch($e);


		if ($d && $chess->is_valid_position($fromField) && $chess->is_valid_position($toField)
				&& $chess->do_move($d['id'], $d['player'], $fromField, $toField)
		) {
			unset($_GET['from']);
			unset($_GET['to']);
			header('Location: /?'.url_params());
		}else{
			echo "Invalid chess move: <br /> game = ".$gameId." <br /> from = ".$fromField." <br /> to = ".$toField;
		}
		exit;
	}

	/** offer remis */
	if ($doAction === 'offer_remis')
	{
		$e = $db->query('SELECT * FROM chess_games WHERE id=? AND next_turn=?', __FILE__, __LINE__, 'offer remis', [$gameId, $user->id]);
		$d = $db->fetch($e);
		if ($d) {
			$chess->do_offer_remis($gameId);

			unset($_GET['do']);
			header("Location: /?".url_params());
		}else{
			echo "'offer remis' is not allowed.";
		}
		exit;
	}

	/** accept remis */
	if ($doAction === 'accept_remis')
	{
		$e = $db->query('SELECT * FROM chess_games WHERE id=? AND (white=? OR black=?) AND next_turn!=? AND offering_remis="1"',
						__FILE__, __LINE__, 'accept remis', [$gameId, $user->id, $user->id, $user->id]);
		$d = $db->fetch($e);
		if ($d) {
			$chess->do_remis($gameId);

			unset($_GET['do']);
			header("Location: /?".url_params());
		}else{
			echo "'accept remis' is not allowed.";
		}
		exit;
	}

	/** deny remis */
	if ($doAction === 'deny_remis')
	{
		$e = $db->query('SELECT * FROM chess_games WHERE id=? AND (white=? OR black=?) AND next_turn!=? AND offering_remis="1"',
						__FILE__, __LINE__, 'deny remis', [$gameId, $user->id, $user->id, $user->id]);
		$d = $db->fetch($e);
		if ($d) {
			$chess->deny_remis($gameId);

			unset($_GET['do']);
			header("Location: /?".url_params());
		}else{
			echo "'deny remis' is not allowed";
		}
		exit;
	}

	/** aufgeben */
	if ($doAction === 'aufgeben')
	{
		$chess->aufgabe($gameId);

		unset($_GET['do']);
		header("Location: /tpl/141?".url_params());
		exit;
	}
}

/** start new game */
elseif ($viewForm === 'chess_start')
{
	if ($chess->new_game($userId)) {
		header("Location: /?tpl=139");
	}else{
		echo "invalid chess_start: <br /> user = ".$userId;
	}
	exit;
}
