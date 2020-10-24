<?php
require_once dirname(__FILE__).'/../includes/main.inc.php';
require_once INCLUDES_DIR.'mysql.inc.php';
require_once INCLUDES_DIR.'usersystem.inc.php';

$headerLocation = 'Location: '.SITE_URL.'/forum.php';

// The magical chain of actions...
switch ($_GET['action'])
{
    case 'sticky':
    	$sql =	"UPDATE comments_threads SET sticky = '1' where thread_id = ".$_GET['thread_id'];
    	break;
    case 'unsticky':
        $sql = "UPDATE comments_threads SET sticky = '0' where thread_id = ".$_GET['thread_id'];
        break;
    case 'favorite':
    	$sql =	
			"
			INSERT INTO comments_threads_favorites (board, thread_id, user_id)
			VALUES ('".$_GET['board']."', ".$_GET['thread_id'].", '".$user->id."')"
			;
		break;
    case 'unfavorite':
    	$sql =	
			"
			DELETE FROM comments_threads_favorites
			WHERE board ='".$_GET['board']."' 
				AND thread_id = ".$_GET['thread_id']." 
				AND user_id = '".$user->id."'"
			;
		break;
    case 'ignore':
    	$sql =	
			"
			INSERT INTO comments_threads_ignore (board, thread_id, user_id)
			VALUES ('".$_GET['board']."', ".$_GET['thread_id'].", '".$user->id."')"
			;
		break;
    case 'unignore':
    	$sql =	
			"
			DELETE FROM comments_threads_ignore
			WHERE board ='".$_GET['board']."' 
				AND thread_id = ".$_GET['thread_id']." 
				AND user_id = '".$user->id."'"
			;
		break;
}

// ...execute the query which made it into $sql
if (isset($sql))
{
	$db->query($sql, __FILE__, __LINE__);
	header("Location: ".SITE_URL."/forum.php");
	exit;
}
