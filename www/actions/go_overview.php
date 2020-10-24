<?php
/**
 * Go Game actions
 * @package zorg\Games\Go
 */
/**
 * File includes
 */
require_once dirname(__FILE__).'/../includes/main.inc.php';
require_once INCLUDES_DIR.'go_game.inc.php';

unset($_GET['tplupd']); // FIXME Was ist das & wozu? / IneX, 18.04.2020

/** New Go Game */
if (isset($_POST['formid']) && $_POST['formid'] == "go_new_game" &&
	is_numeric($_POST['opponent']) && is_numeric($_POST['size']) && is_numeric($_POST['handicap'])) {
		go_new_game($_POST['opponent'], $_POST['size'], $_POST['handicap']);
}

/** Accept Game */
if (isset($_GET['accept']) && is_numeric($_GET['accept']))
{
	go_accept_game($_GET['accept']);
        $_GET['tpl'] = 699;
        $_GET['game'] = $_GET['accept'];
	        unset($_GET['accept']);
}

/** Decline Game */
if (isset($_GET['decline']) && is_numeric($_GET['decline']))
{
	go_decline_game($_GET['decline']);
	unset($_GET['unjoin']);
}

/** Close Game */
if (isset($_GET['close']) && is_numeric($_GET['close']))
{
	go_close_game($_GET['close']);
	unset($_GET['close']);
}

header("Location: /?".url_params());
die();
