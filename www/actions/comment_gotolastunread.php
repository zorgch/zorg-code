<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/main.inc.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/forum.inc.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/usersystem.inc.php');

if(Forum::getNumunreadposts($user->id) > 0) { 
	header("Location: ".Forum::getUnreadLink());
} else {
	header("Location: ../index.php?".session_name()."=".session_id());
}

?>