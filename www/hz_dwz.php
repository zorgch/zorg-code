<?
	require_once($_SERVER['DOCUMENT_ROOT'].'/includes/main.inc.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/usersystem.inc.php');
	require_once($_SERVER['DOCUMENT_ROOT'].'/includes/hz_game.inc.php');
   
	
   if ($user->id == 7) {
   	echo "processing<br/>";
   	$db->query("TRUNCATE TABLE hz_dwz", __FILE__, __LINE__);
      $e = $db->query("select * from hz_games where state='finished' order by turndate asc", __FILE__, __LINE__);
      while ($d = $db->fetch($e)) {
      	echo "=";
      	flush();
      	_update_hz_dwz($d[id]);
      }
   	
   	echo "<br />done <br />";
   }else{
      echo "access denied <br />";
   }
   
?>