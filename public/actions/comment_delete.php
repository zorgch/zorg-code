<?php

// Includes -------------------------------------------------------------------
require_once __DIR__.'/../includes/config.inc.php';
require_once INCLUDES_DIR.'mysql.inc.php';
require_once INCLUDES_DIR.'usersystem.inc.php';
require_once INCLUDES_DIR.'forum.inc.php';

/** Input validation */
$post_id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?? 0;
$board = filter_input(INPUT_POST, 'board', FILTER_SANITIZE_SPECIAL_CHARS) ?? null;
$redirect = base64url_decode(filter_input(INPUT_POST, 'url', FILTER_SANITIZE_SPECIAL_CHARS)) ?? '/forum.php';

// Error-Checking -------------------------------------------------------------
if($post_id <= 0) {
	echo 'Fehler: $_POST[id] ist leer.';
	exit;
}

/** Fetch Comment record */
$rs = Comment::getRecordset($post_id);

if(!$rs || empty($rs)) {
	echo 'Post '.$post_id.' existiert nicht';
	exit;
}

if(($user->id !== intval($rs['user_id']))) {
	echo 'Dieser Post ('.$post_id.') gehört gar nicht dir, sondern'.$user->id2user(intval($rs['user_id']));
	exit;
}

// Actions --------------------------------------------------------------------

$numchildren = Comment::getNumChildposts($board, $post_id);
if($numchildren > 0) {
	echo 'Dieser Post ('.$post_id.') hat noch '.$numchildren.' Kinder, du darfst ihn nicht löschen.';
	exit;
} else {
	// Comment löschen
	$sql = 'DELETE FROM comments WHERE id=?';
	$db->query($sql, __FILE__, __LINE__, 'DELETE comment', [$post_id]);
}

// Threads fixen
Thread::adjustThreadRecord($rs['board'], $rs['thread_id']);

// TODO falls es ein thread war, comments_threads record löschen

// TODO last post setzen: müsste nicht _immer_ passieren
$sql = 'UPDATE comments_threads ct SET last_comment_id=(SELECT MAX(id) from comments c WHERE thread_id=? AND c.board=ct.board) WHERE thread_id=?';
$db->query($sql, __FILE__, __LINE__, 'UPDATE comments_threads', [$rs['thread_id'], $rs['thread_id']]);

// parent neu kompilieren
if($rs['parent_id'] > 1) { //$rs['board'] !== 'f' ||
	Comment::compile_template($rs['thread_id'], $rs['parent_id'], $rs['board']);
}

// todo: wenns ein thread war, redirecten auf die Übersicht oder Startseite
header("Location: ".$redirect);
exit;
