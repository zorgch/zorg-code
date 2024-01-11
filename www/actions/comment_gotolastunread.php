<?php
require_once __DIR__.'/../includes/config.inc.php';
require_once INCLUDES_DIR.'forum.inc.php';

if(Forum::getNumunreadposts($user->id) > 0) {
	header("Location: ".Forum::getUnreadLink());
	exit();
} else {
	header("Location: /index.php");
	exit();
}
