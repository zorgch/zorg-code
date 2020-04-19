<?php
/**
 * FILE INCLUDES
 */
require_once dirname(__FILE__).'/config.php';
require_once MOBILEZ_INCLUDES_DIR.'chat.inc.php';

if(isset($_POST['email']))
{
	$result = $mobilezChat->execPwReset($_POST['email']);
	
	echo $result;
} else {
	header("Location: ".SITE_URL."/mobilezorg-v2/?error_msg=EMail%20missing%21");
}

// In case this Script was called directly...
header("Location: ".SITE_URL."/mobilezorg-v2/");
