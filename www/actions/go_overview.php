<?
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/main.inc.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/go_game.inc.php');
require_once($_SERVER['DOCUMENT_ROOT']."/includes/util.inc.php");
	
	unset($_GET['tplupd']);
	
	if ($_POST['formid'] == "go_new_game" &&
	    is_numeric($_POST['opponent']) &&
	    is_numeric($_POST['size']) &&
	    is_numeric($_POST['handicap'])) {
		go_new_game($_POST['opponent'], $_POST['size'], $_POST['handicap']);
	}
	
	if (is_numeric($_GET['accept'])) {
		go_accept_game($_GET['accept']);
	        $_GET['tpl'] = 699;
	        $_GET['game'] = $_GET['accept'];
    	        unset($_GET['accept']);
	}
	
	if (is_numeric($_GET['decline'])) {
		go_decline_game($_GET['decline']);
		unset($_GET['unjoin']);
	}
	
	if (is_numeric($_GET['close'])) {
		go_close_game($_GET['close']);
		unset($_GET['close']);
	}

	header("Location: /smarty.php?".url_params());
?>
