<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/main.inc.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/forum.inc.php');

if($user->id > 0) {
	
	$sql =	"DELETE from comments_unread WHERE user_id = ".$user->id;
	$db->query($sql, __FILE__, __LINE__);
	$num = mysql_affected_rows();
	
	$sql = "UPDATE user set button_use = button_use + 1, posts_lost = posts_lost + $num WHERE id = '$user->id'";
	$db->query($sql,__FILE__,__LINE__);
	
	header("Location: http://www.zooomclan.org/forum.php?".session_name()."=".session_id());
	exit;
	
} else {
	echo 'Du bist nicht eingeloggt.';
}
?>