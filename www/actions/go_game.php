<?
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/main.inc.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/go_game.inc.php');

unset($_GET['tplupd']);

if ($_POST['formid'] == "go_skip" && is_numeric($_POST['game'])){
       go_skip($_POST['game']);
       $_GET['game'] = $_POST['game']; //return to the game
}

if ($_POST['formid'] == "go_luck" && is_numeric($_POST['game'])){
       go_luck($_POST['game']);
       $_GET['game'] = $_POST['game'];
}

if ($_POST['formid'] == "go_thank" && is_numeric($_POST['game'])){
       go_thank($_POST['game']);
       $_GET['game'] = $_POST['game'];
}

if ($_POST['formid'] == "go_count_propose" && is_numeric($_POST['game'])){
    go_count_propose($_POST['game']);
    $_GET['game'] = $_POST['game'];
}

if ($_POST['formid'] == "go_count_accept" && is_numeric($_POST['game'])){
    go_count_accept($_POST['game']);
    $_GET['game'] = $_POST['game'];
}

if ($_GET['action']=='move'){
   if (is_numeric($_GET['move']) && is_numeric($_GET['game'])){
       go_move($_GET['move'], $_GET['game']);
       unset($_GET['move']);
   }
}
if ($_GET['action']=='count'){
    if (is_numeric($_GET['move']) && is_numeric($_GET['game'])){
	go_count($_GET['move'], $_GET['game']);
	unset($_GET['move']);
    }
}

unset($_GET['action']);
header("Location: /?".url_params());
die();
