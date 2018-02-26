<?php
require_once( __DIR__ .'/../includes/main.inc.php');
require_once( __DIR__ .'/../includes/forum.inc.php');


// Error-Checking -------------------------------------------------------------

// Board checken und validieren
if($_POST['board'] == '' || empty($_POST['board']) || strlen($_POST['board']) != 1) {
	http_response_code(400); // Set response code 400 (bad request) and exit.
	user_error('Board nicht angegeben!', E_USER_WARNING);
	die();
}
	
// Parent id checken
if($_POST['parent_id'] == '' || empty($_POST['parent_id']) || $_POST['parent_id'] == '0' || !is_numeric($_POST['parent_id']))
{
	http_response_code(400); // Set response code 400 (bad request) and exit.
	user_error('Parent id leer oder ungültig: ' . $_POST['parent_id'], E_USER_WARNING);
	die();
}

// Thread id checken
if($_POST['thread_id'] == '' || empty($_POST['thread_id']) || $_POST['thread_id'] == '0' || !is_numeric($_POST['thread_id']))
{
	http_response_code(400); // Set response code 400 (bad request) and exit.
	user_error('Thread id leer oder ungültig: ' . $_POST['thread_id'], E_USER_WARNING);
	die();
}

// Text escapen
if($_POST['text'] == '' || empty($_POST['text']) || !isset($_POST['text']))
{
	http_response_code(400); // Set response code 400 (bad request) and exit.
	user_error('keine leeren Posts erlaubt.', E_USER_WARNING);
	die();
} else {
	$commentText = escape_text($_POST['text']);
}


// Existiert der Parent-Post?
try {
	$sql = 
		"
		SELECT 
		* 
		FROM comments 
		WHERE id = ".$_POST['parent_id']." 
		AND board = '".$_POST['board']."'
		AND thread_id = '".$_POST['thread_id']."'
		"
	;
	$result = $db->query($sql, __FILE__, __LINE__);
	$rs = $db->fetch($result);
	if($rs == FALSE) {
		
		if($_POST['board'] == 'f') {
			
			$rs = $db->fetch($db->query("SELECT * FROM comments WHERE id = ".$_POST['id'], __FILE__, __LINE__));
			if($rs['parent_id'] != $_POST['parent_id']) {
				http_response_code(403); // Set response code 400 (bad request) and exit.
				user_error('Du darfst per Edit keine neuen Threads erstellen', E_USER_WARNING);
				die();
			}
		}
		
		if($_POST['board'] != 'f' && $_POST['parent_id'] != $_POST['thread_id']) { // top level, nicht im forum!
			http_response_code(400); // Set response code 400 (bad request) and exit.
			user_error('Die Parent ID existiert nicht.', E_USER_WARNING);
			die();
		}
	}
	
	
	
	$rs = Comment::getRecordset($_POST['id']);
	// Besitzer checken
	if($_SESSION['user_id'] != $rs['user_id']) {
		http_response_code(403); // Set response code 400 (bad request) and exit.
		user_error('Das ist nicht dein Kommentar, den darfst du nicht bearbeiten!', E_USER_WARNING);
		die();
	}
} catch(Exception $e) {
	http_response_code(500); // Set response code 500 (internal server error)
	echo $e->getMessage();
}
	

// Los ------------------------------------------------------------------------

try {
	$sql =
		"
		UPDATE comments 
		SET
			text='".$commentText."'
			, board='".$_POST['board']."'
			, parent_id='".$_POST['parent_id']."'
			, thread_id='".$_POST['thread_id']."'
			, date_edited=now()
		WHERE id = ".$_POST['id']."	AND board='".$_POST['board']."'
		"
	;
	$db->query($sql, __FILE__, __LINE__);
	
	
	// Templates neu Kompilieren 
	Comment::compile_template($rs['thread_id'], $rs['id'], $rs['board']); // sich selbst
	Comment::compile_template($rs['thread_id'], $rs['parent_id'], $rs['board']); // alter parent
	Comment::compile_template($rs['thread_id'], $_POST['parent_id'], $rs['board']); // neuer Parent
} catch(Exception $e) {
	http_response_code(500); // Set response code 500 (internal server error)
	echo $e->getMessage();
}

// last post setzen
try {
	$sql = 
		"UPDATE comments_threads"
		." SET last_comment_id = (SELECT MAX(id) from comments WHERE thread_id = ".$_POST['thread_id']." AND board = '".$_POST['board']."')"
		." WHERE thread_id = ".$_POST['thread_id'];
	$db->query($sql, __FILE__, __LINE__);
} catch(Exception $e) {
	http_response_code(500); // Set response code 500 (internal server error)
	echo $e->getMessage();
}

// Mark comment as unread for all users.
Comment::markasunread($_POST['id']); 


// Mark comment as read for this user.
Comment::markasread($_POST['id'], $user->id); 


// Message an alle gewünschten senden
if(count($_POST['msg_users']) > 0) {
	for ($i=0; $i < count($_POST['msg_users']); $i++) {				
		Messagesystem::sendMessage(
			$user->id
			, $_POST['msg_users'][$i]
			, addslashes(
					stripslashes(
					'[Forumpost] von '.$user->id2user($user->id)
					)
				)
			, addslashes(
					stripslashes(
						$user->id2user($user->id).' hat geschrieben: <br /><i>'
						.$commentText
						.'</i><br /><br /><a href="'.Comment::getLink($_POST['board'], $_POST['parent_id'], $_POST['id'], $_POST['thread_id'])
						.'">--> zum Post</a>'
					)
				)
			, implode(',', $_POST['msg_users'])
		);
	}
}


// redirecten
header("Location: ".base64_decode($_POST['url']));
die();
