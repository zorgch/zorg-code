<?
//require_once($_SERVER['DOCUMENT_ROOT'].'/includes/main.inc.php');
require_once( __DIR__ .'/../includes/usersystem.inc.php');
require_once( __DIR__ .'/../includes/mysql.inc.php');
//require_once($_SERVER['DOCUMENT_ROOT']."/includes/smarty.inc.php");

if (!$user->id) user_error("Access denied", E_USER_ERROR);	

if ($_POST['poll'] && $_POST['vote']) {
	$poll = $_POST['poll'];
	$vote = $_POST['vote'];
}else{
	$poll = $_GET['poll'];
	$vote = $_GET['vote'];
}

$e = $db->query("SELECT p.* FROM polls p, poll_answers a WHERE a.poll=p.id AND p.id='$poll' AND a.id='$vote'", __FILE__, __LINE__);
$d = $db->fetch($e);

if ($d && $d['state']=="open" && user_has_vote_permission($d['type'])) {
	$db->query("REPLACE INTO poll_votes (poll, user, answer) VALUES ($poll, $user->id, $vote)", __FILE__, __LINE__);
}else{
	user_error("Invalid Poll/Vote '$poll / $vote'", E_USER_ERROR);
}

header("Location: ".base64_decode($_GET['redirect']));
die();
