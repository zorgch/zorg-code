<?
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/main.inc.php');
require_once($_SERVER['DOCUMENT_ROOT']."/includes/usersystem.inc.php");
	require_once($_SERVER['DOCUMENT_ROOT']."/includes/util.inc.php");
	

	 if (isset($_GET['usershowfavourite'])) {
	   $db->query("UPDATE user SET tpl_favourite_show='$_GET[usershowfavourite]' WHERE id=".$user->id, __FILE__, __LINE__);
		$user->tpl_favourite_show = $_GET[usershowfavourite];
	   
      unset($_GET['usershowfavourite']);
      header("Location: /smarty.php?".url_params());
   }
   
?>