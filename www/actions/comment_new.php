<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/main.inc.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/forum.inc.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/messagesystem.inc.php');

if(!($user->id > 0)) {
	echo 'Du bist nicht eingeloggt.';
	exit;
}

if($_POST['text'] == '') {
	echo 'keine leeren Posts erlaubt.';
	exit;
}

if($_POST['parent_id'] == '') {
	echo 'Parent id leer.';
	exit;
}

if(Forum::hasPostedRecently($user->id, $_POST['parent_id'])) {
	echo 'Du hast vor wenigen Sekunden bereits gepostet, du musst noch warten.';
	exit;
}

if(
	$commentlink = 
		Comment::post(
			$_POST['parent_id'], 
			$_POST['board'], 
			$user->id, 
			$_POST['text'],
			$_POST['msg_users']
		)
) {
	header("Location: ".$commentlink);
} else {
	echo 'Post konnte nicht erstellt werden.';
}

?>