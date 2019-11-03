<?php
/**
 * FILE INCLUDES
 */
require_once 'config.php';
require_once PHP_INCLUDES_DIR.'mobilez/chat.inc.php';

if(isset($_POST['title']) && isset($_POST['description']) && $user->id > 0)
{
	$from_mobile = (!isset($_POST['from_mobile']) ? 0 : $_POST['from_mobile']);
	
	$mobilezChat->saveBug($user->id, $_POST['title'], $_POST['description']);
	
	echo 'Bug reported, thanks!';
} else {
	header("Location: ".SITE_URL."/mobilezorg-v2/?error_msg=Bug%20fields%20are%20empty%21");
}

// In case this Script was called directly...
header("Location: ".SITE_URL."/mobilezorg-v2/");
