<?php
require_once( __DIR__ .'/../includes/usersystem.inc.php');
	
/** Comments ein/ausblenden */
if (isset($_GET['usershowcomments']) && $_GET['usershowcomments'] != $user->show_comments)
{
	$db->query('UPDATE user SET show_comments="'.$_GET['usershowcomments'].'" WHERE id='.$user->id, __FILE__, __LINE__, 'UPDATE SET show_comments');
	$user->show_comments = $_GET['usershowcomments'];
}

/** Redirect to previous page */
unset($_GET['usershowcomments']);
header('Location: /?'.url_params());
die();
