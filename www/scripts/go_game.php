<?
/**
 * GO Spiel Funktionen
 * 
 * ...
 * ...
 * ...
 *
 * @author [z]berg, [z]domi
 * @date nn.nn.nnnn
 * @version 1.0
 * @package Zorg
 * @subpackage GO
 */
/**
 * File Includes
 */
//	require_once($_SERVER['DOCUMENT_ROOT'].'/includes/hz_game.inc.php');

//	hz_turn_passing();

/**
 * Globals
 */
global $db, $user, $smarty;

require_once($_SERVER['DOCUMENT_ROOT'].'/includes/go_game.inc.php');

// load game
$gameid = $_GET[game];
if (!$gameid) { //no game supplied, choose one
    $e = $db->query("SELECT g.id
		      FROM go_games g
		      WHERE g.nextturn='".$user->id."'
		        AND g.state='running'
			 OR g.nextturn='".$user->id."'
			AND g.state='counting'
		      ORDER BY RAND()",
		     __FILE__, __LINE__);
    $gameid = $db->fetch($e);
    $gameid = $gameid['id'];
    if (!$gameid){ //kein spiel gefunden - random spiel wählen
	 $e = $db->query("SELECT g.id
		          FROM go_games g
		          WHERE g.state='running'
			     OR g.state='counting'
		          ORDER BY RAND()",
			  __FILE__, __LINE__);
	    $gameid = $db->fetch($e);
	    $gameid = $gameid['id'];
    }
}

if (is_numeric($gameid)){
    $e = $db->query(
		    "SELECT *
		      FROM go_games g
		      WHERE g.id = '$gameid'", __FILE__, __LINE__);
    $game = $db->fetch($e);
    
    if (!$game){
	user_error("Invalid game-ID: '$gameid'");
	return;
    }
    $smarty->assign("game", $game);
    $smarty->assign("nextstone_map", nextstone_map($gameid));
}

?>
