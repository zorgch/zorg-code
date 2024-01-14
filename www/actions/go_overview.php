<?php
/**
 * Go Game Overview actions
 *
 * @package zorg\Games\Go
 */

/**
 * File includes
 */
require_once __DIR__.'/../includes/config.inc.php';
require_once INCLUDES_DIR.'go_game.inc.php';

unset($_GET['tplupd']); // FIXME Was ist das & wozu? / IneX, 18.04.2020
$doAction = filter_input(INPUT_POST, 'formid', FILTER_DEFAULT, FILTER_REQUIRE_SCALAR) ?? null;
$opponent = filter_input(INPUT_POST, 'opponent', FILTER_SANITIZE_NUMBER_INT) ?? null;
$size = filter_input(INPUT_POST, 'size', FILTER_SANITIZE_NUMBER_INT) ?? 9;
$handicap = filter_input(INPUT_POST, 'handicap', FILTER_SANITIZE_NUMBER_INT) ?? 0;
$accept = filter_input(INPUT_GET, 'accept', FILTER_SANITIZE_NUMBER_INT) ?? null;
unset($_GET['accept']);
$decline = filter_input(INPUT_GET, 'decline', FILTER_SANITIZE_NUMBER_INT) ?? null;
unset($_GET['decline']);
$close = filter_input(INPUT_GET, 'close', FILTER_SANITIZE_NUMBER_INT) ?? null;
unset($_GET['close']);

/** New Go Game */
if ($doAction === "go_new_game" && $opponent > 0) go_new_game($opponent, $size, $handicap);

/** Accept Game */
if ($accept > 0)
{
	go_accept_game($accept);
	$_GET['tpl'] = 699;
    $_GET['game'] = $_GET['accept'];
}

/** Decline Game */
if ($decline > 0) go_decline_game($decline);

/** Close Game */
if ($close > 0) go_close_game($close);

/** Redirect */
header("Location: /?".url_params());
exit;
