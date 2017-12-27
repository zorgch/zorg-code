<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/main.inc.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/forum.inc.php');

if(!($user->id > 0)) {
	echo 'Du bist nicht eingeloggt.';
	exit;
}

if($_POST['text'] == '') {
	echo 'keine leeren Posts erlaubt.';
	exit;
} else {
	$commentText = escape_text($_POST['text']);
}

if($_POST['parent_id'] == '') {
	echo 'Parent id leer.';
	exit;
}

// Validate msg_users is REALLY set
if(isset($_POST['msg_users']) && $_POST['msg_users'] != ' ' && !empty(array_filter($_POST['msg_users'])))
{
	$msg_users = $_POST['msg_users'];
	
	// Let's check if it's just a comma-separated String, or an Array
	if (strpos($msg_users, ',') !== false && !is_array($msg_users))
	{
		// make an Array, if necessary
		$msg_users = explode(',', $_POST['msg_users']);
	}
	
	// Remove any duplicate User-IDs
	$msg_users = array_unique($msg_users);
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
			$commentText,
			$msg_users
		)
) {
	header("Location: ".$commentlink);
} else {
	echo 'Post konnte nicht erstellt werden.';
}

?>