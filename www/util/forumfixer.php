<?php
include_once($_SERVER['DOCUMENT_ROOT'].'/includes/mysql.inc.php');

$sql = "
	SELECT comments.*
	FROM comments
	LEFT JOIN comments_threads ON comments.thread_id = comments_threads.thread_id AND comments_threads.board = comments.board
	WHERE comments_threads.id IS NULL
";
$result = $db->query($sql, __FILE__, __LINE__);

while($rs = $db->fetch($result)) {
	
	$sql =
		"REPLACE INTO comments_threads (board, thread_id, comment_id)"
		." VALUES ('".$rs['board']."', ".$rs['thread_id'].", ".$rs['id'].")"
	;
	$db->query($sql, __FILE__, __LINE__);
	echo 'Fixed Thread: '.$rs['board']."', ".$rs['thread_id'].'<br />';
	flush();
}



?>