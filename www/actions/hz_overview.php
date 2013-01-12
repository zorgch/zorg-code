<?
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/main.inc.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/hz_game.inc.php');
require_once($_SERVER['DOCUMENT_ROOT']."/includes/util.inc.php");
	
	unset($_GET['tplupd']);
	
	if ($_POST['formid'] == "hz_new_game" && is_numeric($_POST['map'])) {
		start_new_game($_POST['map']);
	}
	
	if (is_numeric($_GET['join'])) {
		join_game($_GET['join']);
		unset($_GET['join']);
		
	}
	
	if (is_numeric($_GET['unjoin'])) {
		unjoin_game ($_GET['unjoin']);
		unset($_GET['unjoin']);
	}
	
	if (is_numeric($_GET['close'])) {
		hz_close_game ($_GET['close']);
		unset($_GET['close']);
	}
	
	header("Location: /smarty.php?".url_params());
?>
