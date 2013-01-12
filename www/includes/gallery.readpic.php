<?
/**
 * Gallery-Pic holen
 * 
 * This script reads a gallery-pic (they aren't in a public directory).
 * It uses the standard session of the User.
 *
 * @author [z]biko
 * @version 1.0
 * @package Zorg
 * @subpackage Gallery
 *
 * @param integer $pic_id
 */
/**
 * File Includes
 */
include_once($_SERVER['DOCUMENT_ROOT']."/includes/usersystem.inc.php");

$e = $db->query("SELECT * FROM gallery_pics WHERE id=$_GET[id]", __FILE__, __LINE__);
$d = mysql_fetch_array($e);

if (!$d[zensur] || $d[zensur] && $user->typ == USER_MEMBER) {
  
  if ($_GET[type] == "tn") {
     $type = "tn_";
  }else{
     $type = "pic_";
  }
  
  
  switch ($d[extension]) {
     case ".jpg": 
     case ".jpeg": $mime = "image/jpeg"; break;
     case ".gif": $mime = "image/gif"; break;
     case ".png": $mime = "image/png"; break;
     default: exit;
  }
  $file = $_SERVER['DOCUMENT_ROOT']."/../data/gallery/$d[album]/$type$d[id]$d[extension]"; 
  $lastmod = filemtime($file); 
 
  // Falls das Last_Modified Feld vom Client mit dem vom Server uebereinstimmt
  // Bild nicht senden sondern HTTP 304 Not Modified (wird fuer caching benoetigt)
  $if_modified_since = preg_replace('/;.*$/', '', $_SERVER[HTTP_IF_MODIFIED_SINCE]);
  $gmdate_mod = gmdate('D, d M Y H:i:s', $lastmod) . ' GMT';

  if ($if_modified_since == $gmdate_mod) {
     header("HTTP/1.0 304 Not Modified");
	 header("Pragma: cache"); 
	 header("Cache-Control: cache"); 
	 header("Expires: never");
     exit;
  }
  
  header("Content-Type: $mime");
  header('Content-Length: ' . filesize($file)); ;
  header("Last-Modified: " . gmdate("D, d M Y H:i:s", $lastmod) ." GMT"); 
  
  /*<H2><H2></H2></H2>
  header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); 
 
  header("Pragma: no-cache"); 
  header("Cache-Control: no-store, no-cache, max-age=0, must-revalidate"); 
*/
	  header("Expires: never");
  header("Pragma: cache"); 
  header("Cache-Control: cache"); 

  
  readfile($file);
}
//echo "access denied";
?>
