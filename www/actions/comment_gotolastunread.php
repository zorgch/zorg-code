<?php
require_once( __DIR__ .'/../includes/main.inc.php');

if(Forum::getNumunreadposts($user->id) > 0) { 
	header("Location: ".Forum::getUnreadLink());
	die();
} else {
	header("Location: ../index.php?".session_name()."=".session_id());
	die();
}
