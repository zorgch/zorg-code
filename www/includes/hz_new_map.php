<?
	include_once($_SERVER['DOCUMENT_ROOT'].'/includes/usersystem.inc.php');

	header("Content-Type: image/gif");
   header("Last-Modified: " . gmdate("D, d M Y H:i:s") ." GMT"); 
      
   readfile($_SERVER['DOCUMENT_ROOT']."/../data/hz_maps/$user->id.gif");
?>