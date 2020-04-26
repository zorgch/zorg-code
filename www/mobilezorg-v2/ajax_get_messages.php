<?php
/**
 * Mobilezorg V2 - Retrieve recent Chat Messages
 *
 * @package zorg\Chat\Mobilezorg
 */

/**
 * FILE INCLUDES
 */
require_once dirname(__FILE__).'/config.php';
require_once MOBILEZ_INCLUDES_DIR.'chat.inc.php';

if(isset($_GET['lastentry_id']))
{
	if (empty($_GET['lastentry_id'])) header("Location: ".SITE_URL."/mobilezorg-v2/?error_msg=Last%20Message%20ID%20undefined%3A%20".$_GET['lastentry_id']."%20%21");
	echo json_encode($mobilezChat->getAdditionalChatMessages($_GET['lastentry_id']));
} else {
	// In case $_GET-value is empty or this Script was called directly...
	header("Location: ".SITE_URL."/mobilezorg-v2/?error_msg=No%20Message%20ID%20provided%21");
}
