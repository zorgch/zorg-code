<?
/**
 * FILE INCLUDES
 */
require_once 'config.php';
require_once PHP_INCLUDES_DIR.'mobilez/chat.inc.php';

if(isset($_POST['email']))
{
	$result = $mobilezChat->execPwReset($_POST['email']);
	
	echo $result;
} else {
	header("Location: ".SITE_URL."/mobilezorg-v2/?error_msg=EMail%20missing%21");
}

// In case this Script was called directly...
header("Location: ".SITE_URL."/mobilezorg-v2/");