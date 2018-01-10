<?php ob_start(); // Startet das Output-Buffering - damit die header() funktion nicht an oberster Stelle des Codes stehen muss

require_once($_SERVER['DOCUMENT_ROOT'].'/includes/main.inc.php');

$peter = new peter($_GET['game_id']);

if($_GET['img'] == "karten") {
	
	if($_GET['game_id']) {
		
		header("Content-Type: Image/PNG");
		header("Cache-Control: no-cache, no-store, must-revalidate");
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache");
	
		imagepng($peter->kartenberg());
	}
	
} elseif(!$_GET['img']) {
	//echo head(117, 'Peter');
	$smarty->assign('tplroot', array('page_title' => 'Peter'));
	$smarty->display('file:layout/head.tpl');
	echo menu("zorg");
	echo menu("games");
	echo menu("peter");
	
	$peter->exec_peter();

	if($_GET['game_id']) {
		
		//Infos über das game
		$sql = "
		SELECT 
			*
		FROM peter_games pg
		LEFT JOIN user u
		ON pg.next_player = u.id
		WHERE pg.game_id = '$_GET[game_id]'";
		$result = $db->query($sql,__FILE__,__LINE__,__FUNCTION__);
		$rsg = $db->fetch($result);
		
		//Wenn dem Spiel noch beigetreten werden kann
		if($rsg['status'] == "offen") {
			$peter->peter_join($rsg['players']);
			
		//Wenn das Spiel läuft	
		} elseif($rsg['status'] == "lauft" || $rsg['status'] == "geschlossen") {
			echo $peter->game($rsg,$_GET['card_id'],$_GET['make']);
		}
			
			
	
	} elseif(!$_GET['view']) {
		
		if ($user->typ != USER_NICHTEINGELOGGT)
		{
			echo $peter->offene_spiele();
			echo "<br />";
			echo $peter->meine_laufende_spiele($user->id);
			echo "<br />";
		}
		echo $peter->laufende_spiele();
		
	} else {
		
		echo $peter->peterscore();
	}
	//echo foot(1);
	$smarty->display('file:layout/footer.tpl');
	
}

ob_end_flush(); // Beendet das Output-Buffering
?>