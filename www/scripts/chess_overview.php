<?
	include_once($_SERVER['DOCUMENT_ROOT'].'includes/chess.inc.php');

	global $db, $user, $smarty;
	
	
	// my games
	$smarty->assign("my_games", Chess::my_games());
	
	
	// users for new game
	$e = $db->query("SELECT id, username FROM user WHERE chess='1' AND id!='$user->id' ORDER BY username ASC", __FILE__, __LINE__);
	$user_ids = array();
	$user_names = array();
	while ($d = $db->fetch($e)) {
		$user_ids[] = $d['id'];
		$user_names[] = $d['username'];
	}
	$smarty->assign("user_ids", $user_ids);
	$smarty->assign("user_names", $user_names);
?>