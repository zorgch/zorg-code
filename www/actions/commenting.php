<?PHP

// Includes -------------------------------------------------------------------
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/main.inc.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/mysql.inc.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/usersystem.inc.php');


// Actions --------------------------------------------------------------------
if($_GET['do'] == 'subscribe') {
	$sql = 
		"
		INSERT INTO 
			comments_subscriptions (board, comment_id, user_id)
		VALUES('".$_GET['board']."', ".$_GET['comment_id'].", ".$user->id.")
		"
	;
		
	$db->query($sql, __FILE__, __LINE__);	
	
	header("Location: ".base64_decode($_GET['url']));
	exit;
}


if($_GET['do'] == 'unsubscribe') {

	$sql = 
		"
		DELETE FROM comments_subscriptions
		WHERE 
			board = '".$_GET['board']."'
			AND comment_id = ".$_GET['comment_id']."
			AND user_id = ".$user->id."
		"
	;
	$db->query($sql, __FILE__, __LINE__);	
	
	header("Location: ".base64_decode($_GET['url']));
	exit;
}
?>