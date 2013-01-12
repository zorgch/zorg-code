<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/mysql.inc.php');

$sql = 
"
	SELECT 
		cu.comment_id AS id
	FROM comments_unread cu
	LEFT JOIN comments c ON cu.comment_id = c.id
	WHERE c.id IS NULL
	GROUP BY id
	ORDER BY id ASC
";
$result = $db->query($sql, __FILE__, __LINE__);

while($rs = $db->fetch($result)) {
	$db->query("DELETE FROM comments_unread where comment_id=".$rs['id']);
	echo 'deleted all unread_comments for post id '.$rs['id'].' <br />';
	flush();
}

?>