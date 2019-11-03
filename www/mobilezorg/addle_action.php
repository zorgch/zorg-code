<?php
/**
* Addle Actions
* 
* Verschiedene Aktionen welche für Addle benötigt werden
* 
* @author IneX
* @version 1.0
* @package mobilezorg
* @subpackage addle
*
* @global array $user Globales Array mit allen Uservariablen
* @global array $db Globales Array mit allen MySQL-Datenbankvariablen
*/

include_once($_SERVER['DOCUMENT_ROOT'].'/includes/main.inc.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/includes/addle.inc.php');

global $user, $db;

if ($user->typ == USER_NICHTEINGELOGGT || !isset($user->typ)) { header('Location: login.php'); }


define("MAX_ADDLE_GAMES", 1);


/**
* [z]Barbara Harris spielen lassen
* 
* Aktiviert die KI-Addle-Gegnerin
* 
* @author [z]bert
* @version 1.0
* @package mobilezorg
* @subpackage addle
*
* @global array $user Globales Array mit allen Uservariablen
* @global array $db Globales Array mit allen MySQL-Datenbankvariablen
*/
function use_ki($game_id)
{
   	global $user, $db;
   	
   	$ki = false;
   	$sql = "SELECT player1 FROM addle WHERE id = '$game_id'";
   	$result = $db->query($sql, __FILE__, __LINE__);
   	$rs = $db->fetch($result);
   	if ($rs['player1'] == 59) $ki = true;
}


/**
* Neues Addle Spiel
* 
* Erzeugt ein neues Addle Spiel
* 
* @author [z]bert
* @version 1.0
* @package mobilezorg
* @subpackage addle
*
* @param array $player ID des Spielers (Gegner), mit welchem ein neues Addle Spiel erstellt werden soll
* @global array $user Globales Array mit allen Uservariablen
* @global array $db Globales Array mit allen MySQL-Datenbankvariablen
*/
function newGame($player)
{

	global $user, $db;
	
	// Query for User
	$user = $db->fetch($db->query(
		"SELECT addle FROM user WHERE id='$player'",
		__FILE__, __LINE__));
		
	  if (!$user['addle']) { header("Location: addle.php?error=User%20spielt%20kein%20Addle"); exit(); }
	
	$anz = $db->fetch($db->query(
		"SELECT count(*) anz FROM addle WHERE finish=0 AND ((player1='$user->id' AND player2='$player') OR (player1='$player' AND player2='$user->id'))",
		__FILE__, __LINE__));
	  
	  if ($anz['anz'] > MAX_ADDLE_GAMES) { header("Location: addle.php?error=Zuviele%20Spiele%20offen"); exit(); }
	  
	  
	  $e = $db->query("SELECT addle FROM user WHERE id='$player'", __FILE__, __LINE__);
	  $d = $db->fetch($e);
	  
	  if (!$player || $player == $_SESSION['user_id'] || $d['addle']!=1) {
		 //user_error("Addle Spiel konnte nicht erstelle werden ", E_USER_ERROR);
		 header("Location: addle.php?error=Fehler%20beim%20Erstellen"); exit();
	  }
	
	  // create board
	  /* zahlenverteilung:
		 1:  8x
		 2:  8x
		 3:  9x
		 4:  8x
		 5:  4x
		 6:  4x
		 7:  4x
		 8:  4x
		 9:  4x
		 10: 4x
		 12: 3x
		 14: 3x
		 16: 1x
		 (total 64)
	
		 zahlen werden ascii-codiert (+96), damit sie einfacher in die db zu speichern sind.
	  */
	
	  // zahlen initialisieren
	  $zahlen = array();
	  for ($i=0; $i<8; $i++) {$zahlen[] = chr(96+1);}
	  for ($i=0; $i<8; $i++) {$zahlen[] = chr(96+2);}
	  for ($i=0; $i<9; $i++) {$zahlen[] = chr(96+3);}
	  for ($i=0; $i<8; $i++) {$zahlen[] = chr(96+4);}
	  for ($i=0; $i<4; $i++) {$zahlen[] = chr(96+5);}
	  for ($i=0; $i<4; $i++) {$zahlen[] = chr(96+6);}
	  for ($i=0; $i<4; $i++) {$zahlen[] = chr(96+7);}
	  for ($i=0; $i<4; $i++) {$zahlen[] = chr(96+8);}
	  for ($i=0; $i<4; $i++) {$zahlen[] = chr(96+9);}
	  for ($i=0; $i<4; $i++) {$zahlen[] = chr(96+10);}
	  for ($i=0; $i<3; $i++) {$zahlen[] = chr(96+12);}
	  for ($i=0; $i<3; $i++) {$zahlen[] = chr(96+14);}
	  $zahlen[] = chr(96+16);
	
	  // zahlen auf board verteilen
	  $board = "";
	  mt_srand((double)microtime()*1000000);
	  for ($i=0; $i<64; $i++) {
		 if ($i == 63) {
			$rnd = 0;
		 }else{
			$rnd = mt_rand(0, sizeof($zahlen)-1);
		 }
		 $board .= $zahlen[$rnd];
		 array_splice($zahlen, $rnd, 1);
	  }
	  $row = mt_rand(0,7);
	
	  // db-entry
	  $gameid = $db->query(
	  	"INSERT INTO addle (date, player1, player2, data, nextrow) VALUES (UNIX_TIMESTAMP(NOW()), $player, $user->id, '$board', $row)", __FILE__, __LINE__);
	  $db->query(
	  	"UPDATE user SET addle='1' WHERE id='$user->id'", __FILE__, __LINE__);
	  /*========================================
		 Addle KI - start
	  ========================================*/
	  if($player == 59) {
		//include_once($_SERVER['DOCUMENT_ROOT']."/addle_ki.php");
	  }
	  /*========================================
		 Addle KI - end
	  ========================================*/
	  
	  header("Location: addle.php?game_id=$gameid&amp;show=spiele");
}


/**
* Addle Spielzug ausführen
* 
* Führt einen Addle Spielzug aus
* 
* @author [z]bert
* @version 1.0
* @package mobilezorg
* @subpackage addle
*
* @param interger $id ID des Addle Spiels, für welches der Addle Spielzug ausgeführt wurde
* @param integer $choose Feld, welches beim Spielzug ausgewählt wurde
* @global array $user Globales Array mit allen Uservariablen
* @global array $db Globales Array mit allen MySQL-Datenbankvariablen
*/
function doPlay($id, $choose) {
      
      global $user, $db;

      if ($id) {
         $e = $db->query("SELECT * FROM addle WHERE id=$id", __FILE__, __LINE__);
         $d = $db->fetch($e);
         
         if ($d && $choose>=0 && $choose<=7) {
            
            if ($d['player'.$d[nextturn]] == $user->id) {
               
               if ($d[nextturn] == 1) {
                  $x = $choose;
                  $y = $d[nextrow];
                  $nextturn = 2;
               } else {
                  $x = $d[nextrow];
                  $y = $choose;
                  $nextturn = 1;
               }
               $num = $y*8+$x;
               $act = substr($d[data], $num, 1);
               
               if ($act != '.' && $act) {
                  // score, data change
                  $score = $d['score'.$d[nextturn]] + ord($act)-96;
                  $data = substr($d[data], 0, $num) . "." . substr($d[data], $num+1);
                  
                  // check, ob fertig
                  $finish = 1;
                  if ($nextturn == 1) {
                     for ($i=0; $i<8; $i++) {
                        if (substr($data, ($choose*8+$i), 1) != ".") {
                           $finish = 0;
                        }
                     }
                  }else{
                     for ($i=0; $i<8; $i++) {
                        if (substr($data, ($i*8+$choose), 1) != ".") {
                           $finish = 0;
                        }
                     }
                  }
                  
                  // db entry Zug
                  $db->query("
                    UPDATE
                  		addle 
                  	SET 
                  		date=UNIX_TIMESTAMP(NOW()), 
                  		score$d[nextturn]=$score, 
                  		data='$data', 
                  		nextturn=$nextturn, 
                  		nextrow=$choose, 
                  		finish='$finish',
                  		last_pick_data = '".(ord($act)-96)."', 
                  		last_pick_row = '$d[nextrow]'
                  	WHERE id=$id
                  ", __FILE__, __LINE__);
                  
                  if ($finish) {
                  	_update_dwz($id);
                  	
                  	
                  	// send message
                  	if ($nextturn == 1) {
                  		$msg_from = $d[player2];
                  		$msg_to = $d[player1];
                  	}else{
                  		$msg_from = $d[player1];
                  		$msg_to = $d[player2];
                  	}
                  	
                  	if ($d['score'.$nextturn] > $d['score'.$d[nextturn]]) {
	                  	Messagesystem::sendMessage(
	                  		$msg_from,
	                  		$msg_to,
	                  		'-- Addle -- (autom. Nachricht)',
	                  		sprintf('<a href="%s/addle.php?show=play&id=%d">Du hast unser Addle-Game gewonnen.</a>', SITE_URL, $id)
	                  	);
                  	}elseif ($d['score'.$nextturn] < $d['score'.$d[nextturn]]) {
                  		Messagesystem::sendMessage(
	                  		$msg_from,
	                  		$msg_to,
	                  		'-- Addle -- (autom. Nachricht)',
	                  		sprintf('<a href="%s/addle.php?show=play&id=%d">Du hast unser Addle-Game verloren.</a>', SITE_URL, $id)
	                  	);
                  	}else{
                  		Messagesystem::sendMessage(
	                  		$msg_from,
	                  		$msg_to,
	                  		'-- Addle -- (autom. Nachricht)',
	                  		sprintf('<a href="%s/addle.php?show=play&id=%d">Unser Addle-Game ging unentschieden aus.</a>', SITE_URL, $id)
	                  	);
                  	}
                  }
               }
               
            } else { // wenn $user->id nicht der nächste Spieler ist:
            	header("Location: addle.php?error=Du%20bist%20nicht%20dran"); exit();
            }
         }
         /*========================================
         Addle KI - start
         ========================================*/
  	  		use_ki($id);
         /*========================================
         Addle KI -  end
         ========================================*/
      }
      
      header("Location: addle.php?game_id=$id&amp;show=spiele");
      
   }



switch ($_GET['do']) {
  case 'new':	($_GET['user_id'] > 0) ? newGame($_GET['user_id']) : header("Location: addle.php?error=Fehler%20beim%20Erstellen"); break;
  case 'play':	($_GET['game_id'] > 0) ? doPlay($_GET['game_id'], $_GET['choose']) : header("Location: addle.php?error=Fehler"); break;
  default:		header("Location: addle.php");
}

?>