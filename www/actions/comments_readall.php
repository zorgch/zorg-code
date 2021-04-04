<?php
/**
 * Read All Comments action
 *
 * @package zorg\Forum
 */
/**
 * File includes
 */
require_once dirname(__FILE__).'/../includes/main.inc.php';

if($user->id > 0)
{	
	/** Get current # unread comments */
	$currNumUnreads = Forum::getNumunreadposts($user->id);
	if (false !== $currNumUnreads && $currNumUnreads > 0)
	{
		if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> To delete comments_unread for user #%d : %d', __FILE__, __LINE__, $user->id, $currNumUnreads));

		/** Delete all unread posts */
		$sqlDel = 'DELETE FROM comments_unread WHERE user_id = '.$user->id;
		$rsDel = $db->query($sqlDel, __FILE__, __LINE__, 'DELETE comments_unread for user '.$user->id);

		/** Updated user's total number of button-of-shame and lost posts */
		$sqlUpd = sprintf('UPDATE user SET button_use=button_use+1, posts_lost=posts_lost+%d WHERE id=%d', $currNumUnreads, $user->id);
		$rsUpd = $db->query($sqlUpd,__FILE__,__LINE__, 'UPDATE user SET button_use + posts_lost for user '.$user->id);
	}

	header('Location: /forum.php');
	exit;

} else {
	echo 'Du bist nicht eingeloggt.';
}
