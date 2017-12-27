<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/main.inc.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/forum.inc.php');


// Error-Checking -------------------------------------------------------------

if($_POST['board'] == '') {
	echo '$_POST[board] ist leer!';
	exit;
}
	
// Parent id checken
if(!is_numeric($_POST['parent_id'])) {
	echo 'Deine parent_id ('.$_POST['parent_id'].') ist keine Nummer!';
	exit;
}

// Thread id checken
if(!is_numeric($_POST['thread_id'])) {
	echo 'Deine thread_id ('.$_POST['thread_id'].') ist keine Nummer!';
	exit;
}

// Parent id checken
if(strlen($_POST['board']) != 1) {
	echo 'Das angegebene board ('.$_POST['board'].') existiert nicht!';
	exit;
}

// Text escapen
if($_POST['text'] == '') {
	echo 'keine leeren Posts erlaubt.';
	exit;
} else {
	$commentText = escape_text($_POST['text']);
}

// Existiert der Parent-Post?
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
			echo 'Du darfst per Edit keine neuen Threads erstellen';
			exit;
		}
	}
	
	if($_POST['board'] != 'f' && $_POST['parent_id'] != $_POST['thread_id']) { // top level, nicht im forum!
		echo 'Die Parent ID existiert nicht.';
		exit;
	}
}



$rs = Comment::getRecordset($_POST['id']);
// Besitzer checken
if($_SESSION['user_id'] != $rs['user_id']) {
	echo 'Das ist nicht dein Kommentar, den darfst du nicht bearbeiten!';
	exit;
}
	

// Los ------------------------------------------------------------------------

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


// last post setzen
$sql = 
	"UPDATE comments_threads"
	." SET last_comment_id = (SELECT MAX(id) from comments WHERE thread_id = ".$_POST['thread_id']." AND board = '".$_POST['board']."')"
	." WHERE thread_id = ".$_POST['thread_id'];
$db->query($sql, __FILE__, __LINE__);


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
					'[Forumpost] von '.usersystem::id2user($user->id)
					)
				)
			, addslashes(
					stripslashes(
						usersystem::id2user($user->id).' hat geschrieben: <br /><i>'
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

?>