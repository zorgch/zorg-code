<?php

// Includes -------------------------------------------------------------------
require_once( __DIR__ .'/../includes/main.inc.php');


// Error-Checking -------------------------------------------------------------
if($_POST['id'] == '') {
	echo 'Fehler: $_POST[id] ist leer.';
	exit;
}
	
$rs = Comment::getRecordset($_POST['id']);
if($rs == false) {
	echo 'Post '.$_POST['id'].' existiert nicht';
	exit;
}

if(($_SESSION['user_id'] != $rs['user_id'])) {
	echo 'Dieser Post ('.$_POST['id'].') gehört gar nicht dir, sondern'.$user->id2user($rs['user_id']);
	exit;
}

$numchildren = Comment::getNumChildposts($_POST['board'], $_POST['id']);
if($numchildren > 0) {
	echo 'Dieser Post ('.$_POST['id'].') hat noch '.$numchildren.' Kinder, du darfst ihn nicht löschen.';
	exit;
}


// Actions --------------------------------------------------------------------
			
// Brauchts nicht mehr wegen InnoDB-Relation
// Delete read post-records
//$sql = "delete from comments_unread where comment_id = ".$_POST['id'];
//$db->query($sql, __FILE__, __LINE__);


// Comment löschen
$sql = "delete from comments where id = ".$_POST['id'];
$db->query($sql, __FILE__, __LINE__);


// Threads fixen
Thread::adjustThreadRecord($rs['board'], $rs['thread_id']);

// todo: falls es ein thread war, comments_threads record löschen


// last post setzen todo: müsste nicht _immer_ passieren
$sql = 
	"UPDATE comments_threads ct"
	." SET last_comment_id = (SELECT MAX(id) from comments c WHERE thread_id = ".$rs['thread_id']." AND c.board = ct.board)"
	." WHERE thread_id = ".$rs['thread_id'];
$db->query($sql, __FILE__, __LINE__);  	


// parent neu kompilieren
if($rs['board'] != 'f' || $rs['parent_id'] > 1) {
	Comment::compile_template($rs['thread_id'], $rs['parent_id'], $rs['board']);
}

// todo: wenns ein thread war, redirecten auf die Übersicht oder Startseite
header("Location: ".base64_decode($_POST['url']));	  
exit;
