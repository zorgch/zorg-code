<?php

// Includes --------------------------------------------------------------------
require_once __DIR__.'/config.inc.php';
require_once INCLUDES_DIR.'/includes/main.inc.php';
require_once INCLUDES_DIR.'/includes/stockbroker.inc.php';


// Warning ändern -------------------------------------------------------------
if($_POST['do'] == 'changewarning') {
	if(Stockbroker::changeWarning($user->id, $_POST['symbol'], $_POST['comparison'], $_POST['kurs'])) {
		header("Location: /?tpl=164");
	}
	exit;
}


// Kaufen ---------------------------------------------------------------------
if($_POST['action'] == 'buy') {
	if(Stockbroker::buyStock($user->id, $_POST['symbol'], $_POST['menge'], $_POST['max'])) {
		header("Location: /?tpl=164");
	}
	exit;
}


// Verkaufen ------------------------------------------------------------------
if($_POST['action'] == 'sell') {
	if(Stockbroker::sellStock($user->id, $_POST['symbol'], $_POST['menge'], $_POST['max'])) {
		header("Location: /?tpl=164");
	}
	exit;
}
