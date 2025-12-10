<?php
/**
 * Hunting z Game Actions
 *
 * @package zorg\Games\Hz
 */
require_once __DIR__.'/../includes/hz_game.inc.php';

unset($_GET['tplupd']);
$doAction = filter_input(INPUT_POST, 'formid', FILTER_SANITIZE_SPECIAL_CHARS) ?? null;
$hzMap = filter_input(INPUT_GET, 'map', FILTER_SANITIZE_NUMBER_INT) ?? null;
unset($_GET['map']);
$join = filter_input(INPUT_GET, 'join', FILTER_SANITIZE_NUMBER_INT) ?? null;
unset($_GET['join']);
$unjoin = filter_input(INPUT_GET, 'unjoin', FILTER_SANITIZE_NUMBER_INT) ?? null;
unset($_GET['unjoin']);
$close = filter_input(INPUT_GET, 'close', FILTER_SANITIZE_NUMBER_INT) ?? null;
unset($_GET['close']);

/** New Game */
if ($doAction === "hz_new_game" && $hzMap > 0) start_new_game($hzMap);

/** Join Game */
if ($join > 0) join_game($join);

/** Unjoin Game */
if ($unjoin > 0) unjoin_game($unjoin);

/** Close Game */
if ($close > 0) hz_close_game($close);

/** Redirect */
header('Location: /?'.url_params());
exit;
