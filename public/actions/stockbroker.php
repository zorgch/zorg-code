<?php

// Includes --------------------------------------------------------------------
require_once __DIR__.'/config.inc.php';
require_once INCLUDES_DIR.'/includes/main.inc.php';
require_once INCLUDES_DIR.'/includes/stockbroker.inc.php';

$doAction = filter_input(INPUT_POST, 'do', FILTER_SANITIZE_SPECIAL_CHARS) ?? null; // $_POST['do']
$symbol = filter_input(INPUT_POST, 'symbol', FILTER_SANITIZE_SPECIAL_CHARS) ?? null; // $_POST['symbol']
$compareOperator = filter_input(INPUT_POST, 'compare', FILTER_SANITIZE_SPECIAL_CHARS) ?? null; // $_POST['comparison']
$kursWert = filter_input(INPUT_POST, 'kurs', FILTER_VALIDATE_FLOAT) ?? null; // $_POST['kurs']
$anzahlMenge = filter_input(INPUT_POST, 'menge', FILTER_VALIDATE_INT) ?? 0; // $_POST['comparison']
$useMaximum = filter_input(INPUT_POST, 'max', FILTER_VALIDATE_BOOLEAN) ?? false; // $_POST['max']

// Warning ändern -------------------------------------------------------------
if($doAction === 'changewarning') {
	if($stockbroker->changeWarning($user->id, $symbol, $compareOperator, $kursWert)) {
		header("Location: /?tpl=164");
	}
	exit;
}


// Kaufen ---------------------------------------------------------------------
if($doAction === 'buy') {
	if($stockbroker->buyStock($user->id, $symbol, $anzahlMenge, $useMaximum)) {
		header("Location: /?tpl=164");
	}
	exit;
}


// Verkaufen ------------------------------------------------------------------
if($doAction === 'sell') {
	if($stockbroker->sellStock($user->id, $symbol, $anzahlMenge, $useMaximum)) {
		header("Location: /?tpl=164");
	}
	exit;
}
