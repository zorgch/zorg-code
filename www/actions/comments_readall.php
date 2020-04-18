<?php
/**
 * Read All Comments action
 *
 * @package zorg\Forum
 */
/**
 * File includes
 */
require_once( __DIR__ .'/../includes/main.inc.php');

if($user->id > 0)
{	
	$sql = 'DELETE from comments_unread WHERE user_id = '.$user->id;
	$rs = $db->query($sql, __FILE__, __LINE__);
	$num = $db->num($rs);
	
	$sql = 'UPDATE user SET button_use = button_use+1, posts_lost = posts_lost+'.$num.' WHERE id = '.$user->id;
	$db->query($sql,__FILE__,__LINE__);

	header("Location: /forum.php?".session_name()."=".session_id());
	exit;
	
} else {
	echo 'Du bist nicht eingeloggt.';
}
