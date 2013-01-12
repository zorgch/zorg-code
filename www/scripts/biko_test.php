<?
	global $db, $smarty, $user;
	
	$e = $db->query("SELECT * FROM comments_unread WHERE user_id=$user->id", __FILE__, __LINE__);
	$u = array();
	while ($d = $db->fetch($e)) {
		$u[] = $d['comment_id'];
	}
	$smarty->assign("comments_unread", $u);
	
	
	
	$smarty->assign("comment_resource", "comments:$_GET[parent_id]");
	
?>