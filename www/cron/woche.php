<?PHP
include_once($_SERVER['DOCUMENT_ROOT'].'/includes/mysql.inc.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/includes/forum.inc.php');

if($_GET['pw'] == 'schmelzigel') {
	// Unread_comments älter als 30 Tage löschen 	
	$sql = "
		DELETE FROM comments_unread
		USING comments, comments_unread 
		WHERE 
		comments.id = comments_unread.comment_id 
		AND 
		UNIX_TIMESTAMP(date) < (UNIX_TIMESTAMP(now())-60*60*24*30*3) 
	";
	$db->query($sql, __FILE__, __LINE__);
}
?>