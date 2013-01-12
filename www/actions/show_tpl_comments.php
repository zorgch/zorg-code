<?
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/main.inc.php');
require_once($_SERVER['DOCUMENT_ROOT']."/includes/usersystem.inc.php");
	require_once($_SERVER['DOCUMENT_ROOT']."/includes/util.inc.php");
	
	// comments ein/ausblenden
   if (isset($_GET[usershowcomments]) && $_GET[usershowcomments] != $user->show_comments) {
      $db->query("UPDATE user SET show_comments='".$_GET[usershowcomments]."' WHERE id='".$user->id."'", __FILE__, __LINE__) ;
      $user->show_comments = $_GET[usershowcomments];
      
      unset($_GET['usershowcomments']);
      header("Location: /smarty.php?".url_params());
   }
?>