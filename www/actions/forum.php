<?PHP
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/main.inc.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/mysql.inc.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/usersystem.inc.php');

if($_GET['action'] == 'sticky') {
	$sql =	"UPDATE comments_threads SET sticky = '1' where thread_id = ".$_GET['thread_id'];
	$db->query($sql, __FILE__, __LINE__);
	header("Location: http://www.zorg.ch/forum.php");
	exit;
}

else if($_GET['action'] == 'unsticky') {
	$sql =	"UPDATE comments_threads SET sticky = '0' where thread_id = ".$_GET['thread_id'];
	$db->query($sql, __FILE__, __LINE__);
	header("Location: http://www.zorg.ch/forum.php");
	exit;
}

else if($_GET['action'] == 'favorite') {
	$sql =	
		"
		INSERT INTO comments_threads_favorites (board, thread_id, user_id)
		VALUES ('".$_GET['board']."', ".$_GET['thread_id'].", '".$user->id."')"
	;
	$db->query($sql, __FILE__, __LINE__);
	header("Location: http://www.zorg.ch/forum.php");
	exit;
}

else if($_GET['action'] == 'unfavorite') {
	$sql =	
		"
		DELETE FROM comments_threads_favorites
		WHERE board ='".$_GET['board']."' 
			AND thread_id = ".$_GET['thread_id']." 
			AND user_id = '".$user->id."'"
	;
	$db->query($sql, __FILE__, __LINE__);
	header("Location: http://www.zorg.ch/forum.php");
	exit;
}

else if($_GET['action'] == 'ignore') {
	$sql =	
		"
		INSERT INTO comments_threads_ignore (board, thread_id, user_id)
		VALUES ('".$_GET['board']."', ".$_GET['thread_id'].", '".$user->id."')"
	;
	$db->query($sql, __FILE__, __LINE__);
	header("Location: http://www.zorg.ch/forum.php");
	exit;
}

else if($_GET['action'] == 'unignore') {
	$sql =	
		"
		DELETE FROM comments_threads_ignore
		WHERE board ='".$_GET['board']."' 
			AND thread_id = ".$_GET['thread_id']." 
			AND user_id = '".$user->id."'"
	;
	$db->query($sql, __FILE__, __LINE__);
	header("Location: http://www.zorg.ch/forum.php");
	exit;
}
?>