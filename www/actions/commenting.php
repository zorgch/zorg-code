<?php
/**
 * Commenting Actions
 * @package zorg\Forum
 */
/**
 * File Includes
 */
require_once dirname(__FILE__).'/../includes/main.inc.php';
require_once INCLUDES_DIR.'mysql.inc.php';
require_once INCLUDES_DIR.'usersystem.inc.php';

/** Subscribe */
if(isset($_GET['do']) && $_GET['do'] == 'subscribe')
{
	$sql = 'INSERT INTO comments_subscriptions (board, comment_id, user_id)
			VALUES("'.$_GET['board'].'", '.$_GET['comment_id'].', '.$user->id.')';
	$db->query($sql, __FILE__, __LINE__, 'Commenting subscribe');

	header("Location: ".base64_urldecode($_GET['url']));
	exit;
}

/** Unsubscribe */
if(isset($_GET['do']) && $_GET['do'] == 'unsubscribe')
{
	$sql = 'DELETE FROM comments_subscriptions
			WHERE board = "'.$_GET['board'].'" AND comment_id = '.$_GET['comment_id'].' AND user_id = '.$user->id;
	$db->query($sql, __FILE__, __LINE__, 'Commenting unsubscribe');

	header("Location: ".base64_urldecode($_GET['url']));
	exit;
}
