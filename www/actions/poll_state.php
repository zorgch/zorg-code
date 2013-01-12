<?
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/main.inc.php');
include_once($_SERVER['DOCUMENT_ROOT']."/includes/usersystem.inc.php");
	include_once($_SERVER['DOCUMENT_ROOT']."/includes/smarty.inc.php");
	include_once($_SERVER['DOCUMENT_ROOT']."/includes/poll.inc.php");
	
	if (!$user->id) user_error("Access denied", E_USER_ERROR);
	if (!in_array($_GET['state'], array("open", "closed"))) user_error("Invalid state '$_GET[state]'", E_USER_ERROR);
	
	
	$e = $db->query("SELECT * FROM polls WHERE user=$user->id AND id=$_GET[poll]", __FILE__, __LINE__);
	$d = $db->fetch($e);
	
	if ($d && user_has_vote_permission($d['type'])) {
		$db->query("UPDATE polls SET state='$_GET[state]' WHERE id=$_GET[poll]", __FILE__, __LINE__);
	}else{
		user_error("Invalid poll_change_state (poll=$_GET[poll] & state=$_GET[state])", E_USER_ERROR);
	}
	
	unset($_GET['poll']);
	unset($_GET['state']);
	
	header("Location: /smarty.php?".url_params());
?>