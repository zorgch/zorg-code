<?
/**
 * Addle DWZ
 * 
 * Wenn vom User ID 7 ausgeführt, wird Addle DWZ neu berechnet
 * 
 * @author [z]biko
 * @version 1.0
 * @package Zorg
 * @subpackage Addle
 *  
 * @param integer $user->id
 */
/**
 * File Includes
 */
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/main.inc.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/usersystem.inc.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/addle.inc.php');
   
	
   if ($user->id == 7) {
   	echo "processing<br/>";
   	
   	$db->query("TRUNCATE TABLE addle_dwz");
   	
      $e = $db->query("select * from addle where finish='1' order by date asc", __FILE__, __LINE__);
      while ($d = $db->fetch($e)) {
      	_update_dwz($d[id]);
      	echo "=";
      	flush();
      }
   	
   	echo "<br />done";
   }else{
      echo "access denied";
   }
   
?>
