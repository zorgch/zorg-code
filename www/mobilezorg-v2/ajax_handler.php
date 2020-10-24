<?php
/**
 * Generic AJAX Handler to reload messages.
 * currently not used in mobilez (see commented lines in templates/layout/mobilez/main.tpl
 */
/**
 * FILE INCLUDES
 */
require_once dirname(__FILE__).'/config.php';
require_once MOBILEZ_INCLUDES_DIR.'chat.inc.php';

if(isset($_POST['last_message']))
{
	$id_from = (int)$_POST['last_message'];
	$mobilezChat->getChatMessages(null, 1, $id_from);
	
	// When the user logs out from another page of the browser window, reload the current page
	if(!isset($_SESSION['user']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH'])=='xmlhttprequest'){
		echo "<script>window.location.reload()</script>";
	}
}

// In case this Script was called directly...
header("Location: ".SITE_URL."/mobilezorg-v2/");
