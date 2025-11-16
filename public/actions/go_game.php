<?php
/**
 * Go Game Actions
 *
 * @package zorg\Games\Go
 */

/**
 * File includes
 */
require_once __DIR__.'/../includes/config.inc.php';
require_once INCLUDES_DIR.'go_game.inc.php';

/** Input validation */
unset($_GET['tplupd']);
$doAction = filter_input(INPUT_POST, 'formid', FILTER_SANITIZE_SPECIAL_CHARS) ?? null;
$game = (isset($_POST['game']) ? filter_input(INPUT_POST, 'game', FILTER_SANITIZE_NUMBER_INT) : filter_input(INPUT_GET, 'game', FILTER_SANITIZE_NUMBER_INT));
$_GET['game'] = $game; // Return redirect back to game
$gameAction = filter_input(INPUT_GET, 'action', FILTER_SANITIZE_SPECIAL_CHARS) ?? null;
unset($_GET['action']);
$move = filter_input(INPUT_GET, 'move', FILTER_SANITIZE_NUMBER_INT) ?? null;
unset($_GET['move']);

switch ($doAction)
{
	case "go_skip":
		go_skip($game);
		break;

	case "go_luck":
		go_luck($game);
		break;

	case "go_thank":
		go_thank($game);
		break;

	case "go_count_propose":
		go_count_propose($game);
		break;

	case "go_count_accept":
		go_count_accept($game);
		break;

}

switch ($gameAction)
{
	case 'move':
		go_move($move, $game);
		break;

	case 'count':
		go_count($move, $game);
		break;
}

/** Redirect */
header("Location: /?".url_params());
exit;
