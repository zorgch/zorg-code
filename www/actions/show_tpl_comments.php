<?php
require_once( __DIR__ .'/../includes/main.inc.php');
	
// comments ein/ausblenden
if (isset($_GET[usershowcomments]) && $_GET[usershowcomments] != $user->show_comments) {
  $db->query("UPDATE user SET show_comments='".$_GET[usershowcomments]."' WHERE id='".$user->id."'", __FILE__, __LINE__) ;
  $user->show_comments = $_GET[usershowcomments];
  
  unset($_GET['usershowcomments']);
  header("Location: /?".url_params());
  die();
}
