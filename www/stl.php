<?php
/**
 * Shoot The Lamber (Game)
 * 
 * Shoot The Lamber ist ein Schiffchen-Versenken-Klon auf Zorg
 * 
 * @author Milamber
 * @version 1.0
 * @package Zorg
 * @subpackage STL
 */
/**
 * File includes
 * @include main.inc.php
 * @include stl.inc.php Alle Shoot the Lamber Klasse & Methoden
 */
require_once( __DIR__ .'/includes/main.inc.php');
require_once( __DIR__ .'/includes/stl.inc.php');

$stl = new stl();

/** Zugriff nur wenn User eingeloggt ist */
if($user->islogged_in())
{	
	if($_GET['do'] == 'game') {
		if($_GET['game_id']) {
			if($_GET['shoot']) {
				$stl->shoot();
			}
			//echo head(46, "Shoot the Lamber");
			printStlPageHeader();
			echo $stl->data['game'];
			echo $stl->data['legende'];

		} else {
			$sql = 'SELECT game_id 
					FROM stl_players 
					WHERE user_id = '.$user->id.' 
					ORDER by last_shoot DESC';
			$result = $db->query($sql,__FILE__,__LINE__,__FUNCTION__);
			if($db->num($result)) {
				$rs = $db->fetch($result);
				//header("Location: http://".$_SERVER['SERVER_NAME'].$_SERVER['PHP_SELF']."?do=game&game_id=".$rs['game_id']."&".session_name()."=".session_id());
				header('Location: '.base64_decode(getURL(false)).'?do=game&game_id='.$rs['game_id']);
				exit;
			} else {
				//header("Location: http://".$_SERVER['SERVER_NAME'].$_SERVER['PHP_SELF']."?do=overview&".session_name()."=".session_id());
				header('Location: '.base64_decode(getURL(false)).'?do=overview');
				exit;
			}
		}
	}
	if($_GET['do'] == 'overview' || !isset($_GET['do'])) {
		printStlPageHeader();
		//echo head(45, "Shoot the Lamber");
		echo $stl->data['overview'];
		echo $stl->data['legende'];
	}
	if($_GET['do'] == 'reshuffle' && $_GET['game_id']) {
		$sql = 'DELETE FROM stl_positions WHERE game_id = '.$_GET['game_id'];
		$db->query($sql,__FILE__,__LINE__,__FUNCTION__);
		$sql = 'UPDATE stl SET status = 0 WHERE game_id = '.$_GET['game_id'];
		$db->query($sql,__FILE__,__LINE__,__FUNCTION__);
		//header("Location: http://".$_SERVER['SERVER_NAME'].$_SERVER['PHP_SELF']."?do=game&game_id=$_GET[game_id]&".session_name()."=".session_id());
		header('Location: '.base64_decode(getURL(false)).'?do=game&game_id='.$_GET['game_id']);
		exit;
	}
	//echo foot(1);

/** ...sonst "Access denied" (für nicht-eingeloggte) */
} else {
	//echo head(45, "Shoot the Lamber");
	printStlPageHeader();
	echo '<h4 style="color:#FF0000;">Wenn Du eingeloggt wärst, könntest Du hier Shoot the Lamber spielen... aber bis dahin: access denied!</h4>';
	//echo foot(1);
}

/** Page Footer */
$smarty->display('file:layout/footer.tpl');

/**
 * Function to print STL Page Header & Menüs
 */
function printStlPageHeader()
{
	global $smarty;
	$page_title = 'Shoot the Lamber';
	$smarty->assign('tplroot', array('page_title' => $page_title));
	$smarty->display('file:layout/head.tpl');
	echo menu('zorg');
	echo menu('games');
	echo '<h1>'.$page_title.'</h1>';
}
