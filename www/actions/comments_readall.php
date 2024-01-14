<?php
/**
 * Read All Comments action
 *
 * @package zorg\Forum
 */

/**
 * File includes
 */
require_once __DIR__.'/../includes/config.inc.php';
require_once INCLUDES_DIR.'mysql.inc.php';
require_once INCLUDES_DIR.'usersystem.inc.php';
require_once INCLUDES_DIR.'forum.inc.php';

if($user->is_loggedin())
{
	/** Get current # unread comments */
	$currNumUnreads = Forum::getNumunreadposts($user->id);
	if (false !== $currNumUnreads && $currNumUnreads > 0)
	{
		if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> To delete comments_unread for user #%d : %d', __FILE__, __LINE__, $user->id, $currNumUnreads));

		/** Delete all unread posts */
		$sqlDel = 'DELETE FROM comments_unread WHERE user_id=?';
		$rsDel = $db->query($sqlDel, __FILE__, __LINE__, 'DELETE comments_unread for user', [$user->id]);

		/** Updated user's total number of button-of-shame and lost posts */
		$sqlUpd = 'UPDATE user SET button_use=button_use+1, posts_lost=posts_lost+? WHERE id=?';
		$rsUpd = $db->query($sqlUpd,__FILE__,__LINE__, 'UPDATE user SET button_use + posts_lost for user', [$currNumUnreads, $user->id]);
	}

	header('Location: /forum.php');
	exit;

} else {
	echo 'Du bist nicht eingeloggt.';
}
