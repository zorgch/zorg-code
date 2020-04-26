<?php
/**
 * Mobilezorg V2 - Create new User
 *
 * @package zorg\Chat\Mobilezorg
 */

/**
 * FILE INCLUDES
 */
require_once dirname(__FILE__).'/config.php';

$new_user = htmlentities($_POST['new_username']);
$new_pass = htmlentities($_POST['new_password']);
$new_pass2 = htmlentities($_POST['new_password2']);
$new_email = htmlentities($_POST['new_email']);

if(isset($new_user) && isset($new_pass) && isset($new_pass2) && isset($new_email))
{
	// Check if the 2 Passwords match
	if($new_pass == $new_pass2)
	{
		require_once INCLUDES_DIR.'usersystem.inc.php';
		$result = $user->create_newuser($new_user, $new_pass, $new_pass2, $new_email);
		error_log("[INFO] User '$new_user': $result"); // Output an Info to PHP-Errorlog
		echo $result;
	} else {
		header("Location: ".SITE_URL."/mobilezorg-v2/?error_msg=Passwörter%20stimmen%20nicht%20überein%21");
	}
} else {
	// In case this Script was called directly or $_POST-values missing...
	header("Location: ".SITE_URL."/mobilezorg-v2/?error_msg=Username%20fehlt%21");
}
