<?
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/usersystem.inc.php');

	global $smarty, $user;
	
	$types = array();
	$types_n = array();
	$types[] = "standard";
	$types_n[] = "Standard";
	
	if ($user->typ == USER_MEMBER) {
		$types[] = "member";
		$types_n[] = "Member";
	}
	
	$smarty->assign("poll_types_v", $types);
	$smarty->assign("poll_types_n", $types_n);
?>