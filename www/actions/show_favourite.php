<?php
require_once( __DIR__ .'/../includes/main.inc.php');	

if (isset($_GET['usershowfavourite'])) {
	$db->query("UPDATE user SET tpl_favourite_show='$_GET[usershowfavourite]' WHERE id=".$user->id, __FILE__, __LINE__);
	$user->tpl_favourite_show = $_GET[usershowfavourite];
	
	unset($_GET['usershowfavourite']);
	header("Location: /?".url_params());
	die();
}
