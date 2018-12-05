<?php
require_once( __DIR__ .'/../includes/hz_game.inc.php');

unset($_GET['tplupd']);

/** New Game */
if ($_POST['formid'] == "hz_new_game" && is_numeric($_POST['map'])) {
	start_new_game($_POST['map']);
}

/** Join Game */
if (is_numeric($_GET['join'])) {
	join_game($_GET['join']);
	unset($_GET['join']);
	
}

/** Unjoin Game */
if (is_numeric($_GET['unjoin'])) {
	unjoin_game ($_GET['unjoin']);
	unset($_GET['unjoin']);
}

/** Close Game */
if (is_numeric($_GET['close'])) {
	hz_close_game ($_GET['close']);
	unset($_GET['close']);
}

header('Location: /?'.url_params());
exit;
