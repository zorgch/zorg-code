<?PHP

// Includes --------------------------------------------------------------------
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/main.inc.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/mysql.inc.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/stockbroker.inc.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/usersystem.inc.php');


// Warning ändern -------------------------------------------------------------
if($_POST['do'] == 'changewarning') {		
	if(Stockbroker::changeWarning($user->id, $_POST['symbol'], $_POST['comparison'], $_POST['kurs'])) {
		header("Location: /smarty.php?tpl=164");
	}
	exit;
}


// Kaufen ---------------------------------------------------------------------
if($_POST['action'] == 'buy') {	
	if(Stockbroker::buyStock($user->id, $_POST['symbol'], $_POST['menge'], $_POST['max'])) { 
		header("Location: /smarty.php?tpl=164");
	}
	exit;
}


// Verkaufen ------------------------------------------------------------------
if($_POST['action'] == 'sell') {
	if(Stockbroker::sellStock($user->id, $_POST['symbol'], $_POST['menge'], $_POST['max'])) { 
		header("Location: /smarty.php?tpl=164");
	}
	exit;
}


?>