<?php
/**
 * Addle (Game)
 * 
 * Das Addle Spiel wurde am 16. Mai 2003 von [z]biko
 * geschrieben und anschliessend laufend verbessert.
 * Das Spiel nutz folgende Tabellen in der Datenbank:
 *		addle, addle_dwz
 *
 * @author [z]biko
 * @date 16.05.2003
 * @version 1.5
 * @package Zorg
 * @subpackage Addle
 */
/**
 * File Includes
 */
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/main.inc.php');


/**
 * Addle KI Einsetzen
 * 
 * Aktiviert die KI fuer ein bestimmtes Spiel
 * 
 * @author [z]bert
 * @version 1.5
 *
 * @param integer $game_id ID des Addle Spiels
 */
function use_ki($game_id) {
	global $db;
	$ki = false;
	$sql = "SELECT player1 FROM addle WHERE id = '$game_id'";
	$result = $db->query($sql, __FILE__, __LINE__);
	$rs = $db->fetch($result);
	if($rs['player1'] == 59) {
		$ki = true;
	}
}


/**
 * HTML-Auswahlmenü ausgeben
 * 
 * Gibt ein HTML-Auswahlmenü aus (<option></option>) - benutzt
 * für die Spielerauswahl um ein neues Spiel zu starten.
 * 
 * @author [z]bert
 * @version 1.5
 */
function selectoption($inputname, $size, $valuearray, $array2="",$selected="", $addhtml = "") {
if(is_array($valuearray)) {
	$html = "<select name=\"".$inputname."\" size=\"".$size."\" class=\"select\" $addhtml>\n";
	if(is_array($array2)) {
		for($i=0;$i<=count($array2)-1;$i++) {
			$html .= "<option value='$valuearray[$i]' ";
			if($valuearray[$i] == $selected || $array2[$i] == $selected) {
				$html .= " class='selected' selected";
			}
			$html .= ">".$array2[$i]."</option>\n";
		}
	} else {
		foreach($valuearray as $key => $value) {
			$html .= "<option value=\"".$key."\"";
			if($key == $selected || $value == $selected) {
				$html .= " class=\"selected\" selected";
			}
			$html .= ">".$value."</option>\n";
		}
	}
	$html .= "</select>\n";
	return $html;
 }
}


/**
 * Neues Addle Spiel
 * 
 * Erzeugt ein neues Addle Spiel
 * 
 * @author [z]biko
 * @version 1.0
 *
 * @param integer $player ID des Gegners
 * @global array $db Array mit allen MySQL-Datenbankvariablen
 * @global array $user Array mit allen Uservariablen
 */
function newgame($player) {
  global $db, $user, $smarty;

  $anz = $db->fetch($db->query(
  	"SELECT count(*) anz FROM addle WHERE finish=0 AND ((player1='$user->id' AND player2='$player') OR (player1='$player' AND player2='$user->id'))",
  	__FILE__, __LINE__
  ));
  if ($anz['anz'] > MAX_ADDLE_GAMES) user_error("No more games versus '$player' possible", E_USER_NOTICE);
  
  $e = $db->query("SELECT addle FROM user WHERE id='$player'", __FILE__, __LINE__);
  $d = $db->fetch($e);
  
  if (!$player || $player == $_SESSION['user_id'] || $d['addle']!=1) {
     user_error("Cannot create Addle game ", E_USER_ERROR);
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
  $gameid = $db->query("INSERT INTO addle (date, player1, player2, data, nextrow) VALUES (UNIX_TIMESTAMP(NOW()), $player, $_SESSION[user_id], '$board', $row)", __FILE__, __LINE__);
  $db->query("UPDATE user SET addle='1' WHERE id=$user->id", __FILE__, __LINE__);
  /*========================================
     Addle KI - start
  ========================================*/
  if($player == 59) {
  	//include_once($_SERVER['DOCUMENT_ROOT']."/addle_ki.php");
  }
  /*========================================
     Addle KI - end
  ========================================*/
  
  /**
   * Notification - New Game
   */
  try {
	  $messageSubject = '-- New Addle Game -- (autom. Nachricht)';
	  $messageText = sprintf('Ich habe Dich zu <a href="%s/addle.php?show=play&id=%d">einem neuen Addle-Game</a> herausgefordert!', SITE_URL, $gameid);
	  Messagesystem::sendMessage($user->id, $player, $messageSubject, $messageText);
  } catch (Exception $e) {
  	  user_error($e->getMessage(), E_USER_ERROR);
  }
  
  header("Location: /addle.php?show=play&id=$gameid");
}


/**
 * Addle Anleitung
 * 
 * Gibt das How-to (Anleitung) zu Addle aus
 * 
 * @author [z]biko
 * @version 1.0
 *
 */
function howto() {
  ?>
     Ziel des Spiels Addle ist es, möglichst viele Punkte zu erziehlen. <br>
     Um Punkte zu bekommen, wähle ein Feld aus deiner markierten Linie aus. Du erhälst die entsprechende Punktzahl. Anschliessend
     darf dein Gegner von seiner markierten Linie auswählen. Die Linie wechselt jeweils von der Vertikalen in die Horizontalen
     deines gewählten Feldes, und umgekehrt. Der erste Spiele wählt immer von aus einer horizontalen Linie aus, der zweite immer
     aus einer vertikalen Linie. Das Spiel ist fertig, wenn ein Spieler kein Feld mehr aus seiner Linie auswählen kann.
     <br />Die Spielerin Barabara Harris ist eine KI, ihr spielt dabei also gegen den Computer.
  <?
}


/**
 * Alle offenen Addle Spiele
 * 
 * Listet alle offenen Addle Spiele auf
 * 
 * @author [z]biko
 * @version 1.0
 *
 * @global array $db Array mit allen MySQL-Datenbankvariablen
 * @global array $user Array mit allen Uservariablen
 */
function games() {
  global $db, $user;
  
  ?><b>Spiele: </b><?
  
  $e = $db->query("SELECT * FROM addle WHERE ((player1=$_SESSION[user_id] AND nextturn=1) OR (player2=$_SESSION[user_id] AND nextturn=2)) AND finish='0'", __FILE__, __LINE__);
  $out = "";
  while ($d = mysql_fetch_array($e)) {
     if ($d[player1] != $_SESSION['user_id']) {
        $otherpl = $d[player1];
     }else{
        $otherpl = $d[player2];
     }
     $out .= "<b><a style='color:red;' href='addle.php?show=play&id=".$d[id]."'>". $user->id2user($otherpl). "</a></b>, ";
  }
  
  $e = $db->query("SELECT * FROM addle WHERE ((player1=$_SESSION[user_id] AND nextturn=2) OR (player2=$_SESSION[user_id] AND nextturn=1)) AND finish='0'", __FILE__, __LINE__);
  while ($d = mysql_fetch_array($e)) {
     if ($d[player1] != $_SESSION['user_id']) {
        $otherpl = $d[player1];
     }else{
        $otherpl = $d[player2];
     }
     $out .= "<a href='addle.php?show=play&id=".$d[id]."'>". $user->id2user($otherpl). "</a>, ";
  }
  $out = substr($out, 0, -1);
  echo "$out <br>";
}


/**
 * Addle Hauptseite
 * 
 * Erzeugt die Hauptseite zu Addle mit einer generellen Spielübersicht
 * 
 * @author [z]biko
 * @version 1.0
 *
 * @global array $db Array mit allen MySQL-Datenbankvariablen
 */
function overview() {
  global $db, $smarty;
  
  //echo head(0, 'Addle');
  $smarty->assign('tplroot', array('page_title' => 'Addle'));
  $smarty->display('file:layout/head.tpl');
  echo menu("zorg");
  echo menu("games");
  echo menu("addle");
  
  ?>
  <b>Neues Spiel:</b> <br>
  <form action="addle.php?show=overview&do=new" method='post'>  <?
     $sql = "SELECT username, id FROM user WHERE addle='1' AND id <> '$_SESSION[user_id]' ORDER by username ASC";
     $result = $db->query($sql, __FILE__, __LINE__);
     while($rs = $db->fetch($result)) {
        $values[] = $rs['id'];
        $texts[] = $rs['username'];
     }
     echo selectoption("id",1,$values,$texts); ?>
     &nbsp; &nbsp;
     <input type='submit' class='button' value='play'>
  </form>
  <br>
  <?
  games();?>
  <br><br>
  <b>Anleitung:</b><br> <?
  howto();
}


/**
 * Addle Spielzug ausführen
 * 
 * Verarbeitet einen Addle Spielzug
 * 
 * @author [z]biko
 * @version 1.0
 *
 * @param integer $id ID des Addle Spiels
 * @param integer $choose ID des Feldes innerhalb des Addle Spiels $id
 * @global array $db Array mit allen MySQL-Datenbankvariablen
 */
function doplay($id, $choose) {
  global $db;

  if ($id) {
     $e = $db->query("SELECT * FROM addle WHERE id=$id", __FILE__, __LINE__);
     $d = mysql_fetch_array($e);
     if ($d && $choose>=0 && $choose<=7) {
        if ($d['player'.$d[nextturn]] == $_SESSION['user_id']) {
           if ($d[nextturn] == 1) {
              $x = $choose;
              $y = $d[nextrow];
              $nextturn = 2;
           }else{
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
              // db entry zug
              $db->query(
              	"UPDATE addle 
              	SET 
              		date=UNIX_TIMESTAMP(NOW()), 
              		score$d[nextturn]=$score, 
              		data='$data', 
              		nextturn=$nextturn, 
              		nextrow=$choose, 
              		finish='$finish',
              		last_pick_data = '".(ord($act)-96)."', 
              		last_pick_row = '$d[nextrow]'
              	WHERE id=$id", __FILE__, __LINE__);
              
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
}


/**
 * Addle Spiel anzeigen
 * 
 * Zeigt ein spezifisches Addle Spiel an
 * 
 * @author [z]biko
 * @version 1.0
 *
 * @param integer $id ID des Addle Spiels
 * @global array $db Array mit allen MySQL-Datenbankvariablen
 * @global array $user Array mit allen Uservariablen
 */
function play($id=0) {
  global $db, $user, $smarty;
  
  if (!$id) {overview();}
  else{

  $e = $db->query(
  	"SELECT a.*, d1.score dwz1, d1.rank dwzr1, d2.score dwz2, d2.rank dwzr2
  	FROM addle a 
  	LEFT JOIN addle_dwz d1 ON d1.user=a.player1
  	LEFT JOIN addle_dwz d2 ON d2.user=a.player2
  	WHERE a.id=$id"
  	, __FILE__, __LINE__
  );
  if ($db->num($e) != 1) {http_error(404);exit;}
  $d = mysql_fetch_array($e);
  //echo head(0, "Addle");
  $smarty->assign('tplroot', array('page_title' => 'Addle'));
  $smarty->display('file:layout/head.tpl');
  echo menu("zorg");
  echo menu("games");
  echo menu("addle");
  
  ?>
  <center>
  <table cellspacing='0' cellpadding='5'>
     <tr>
        <td style="text-align: center;">
        <div style="font-size: x-large;">
        <?
           if (!$d[finish]) {
              echo $user->id2user($d["player".$d[nextturn]]). " ist am Zug";
           }else{
              if ($d[score1] == $d[score2]) {
                 echo "unentschieden";
              }else{
                 if ($d[score1] > $d[score2]) {
                    echo $user->id2user($d[player1]);
                 }else{
                    echo $user->id2user($d[player2]);
                 }
                 echo " hat gewonnen";
              }
           }  ?>
        <br />
        </div>
        <?
        	if ($d['finish'] && $d['score1']!=$d['score2']) {
        		echo "<br />";
        		if ($d[score1] > $d[score2]) {
                 echo $user->id2user($d[player1]);
              }else{
                 echo $user->id2user($d[player2]);
              }
              echo " hat $d[dwz_dif] DWZ-Punkte gewonnen.";
        	}
        	?>
        </div>
        		 
        </td>
        <td rowspan="2">
        <? if($d['player'.$d['nextturn']] == $d['player1']) {
        		$piccolor = "red";
            }else{
            	$piccolor = BORDERCOLOR;
            }
            ?>
        	<table bgcolor="<?=$piccolor?>" cellpadding="5" width="150">
        		<tr><td><?=$user->link_userpage($d['player1'], true, true)?></td></tr>
        		<tr><td>
           <?='<a href="/addle.php?show=archiv&uid='.$d[player1].'">'.$user->id2user($d[player1]).'</a> <br /><small>(DWZ '.$d['dwz1'].' / '.$d['dwzr1'].'.)</small>'?>
        	</td></tr></table>
        	
           <table cellspacing='0' cellpadding='0' border='0' style="font-size: xx-large;" width="100%">
              <tr>
                 <td align="center"><font size="6"><?=$d[score1]?><br /><br /></td>
              </tr>
              <tr>
                 <td align="center"><font size="6"><?=$d[score2]?></td>
              </tr>
           </table>
           <? if($d['player'.$d['nextturn']] == $d['player2']) {
           	$piccolor = "red";
           }else{
           	$piccolor = BORDERCOLOR;
           }
           ?>
        	<table bgcolor="<?=$piccolor?>" cellpadding="5" width="150">
        		<tr><td><?=$user->link_userpage($d['player2'], true, true)?></td></tr>
        		<tr><td>
           <?='<a href="/addle.php?show=archiv&uid='.$d[player2].'">'.$user->id2user($d[player2]).'</a> <br /><small>(DWZ '.$d['dwz2'].' / '.$d['dwzr2'].'.)</small>'?>
           </td></tr></table>
        </td>
      </tr>
      <tr><td style="text-align: center;">
      
  
  <table cellspacing='0' cellpadding='2' style="border-collapse:collapse;" bgcolor='<?=TABLEBORDERCOLOR?>'>  <?
     for ($y=0; $y<8; $y++) {
        ?><tr><?
        for ($x=0; $x<8; $x++) {
           if (($d[nextturn]==1 && $y==$d[nextrow]) || ($d[nextturn]==2 && $x==$d[nextrow])) {
              $bgcolor = NEWCOMMENTCOLOR;
           }else{
              $bgcolor = TABLEBACKGROUNDCOLOR;
           } ?>
           <td class="addletd" width='40' height='40' align='center' valign='center' bgcolor='<?=$bgcolor?>'>   <?
              $act = substr($d[data], ($y*8+$x), 1);
              if ($act == '.') {
              	if ($d['last_pick_data']) {
              		if ($d['nextturn']==1 && $x==$d['last_pick_row'] && $y==$d['nextrow']
              			|| $d['nextturn']==2 && $y==$d['last_pick_row'] && $x==$d['nextrow']
              		) {
              			echo "<font color='gray'><i>$d[last_pick_data]</i></font>";
              		}else{
                 		echo "&nbsp;";
              		}
              	}
              }else{
                 $out = "<b>". (ord($act)-96). "</b>";
                 if ($d[player1]==$_SESSION['user_id'] && $d[nextturn]==1 && $y==$d[nextrow] && $d['finish']==0) {
                    $out = "<a href='addle.php?show=play&do=play&id=".$id."&choose=".$x."'>$out</a>";
                 }else if ($d[player2]==$_SESSION['user_id'] && $d[nextturn]==2 && $x==$d[nextrow] && $d['finish']==0) {
                    $out = "<a href='addle.php?show=play&do=play&id=".$id."&choose=".$y."'>$out</a>";
                 }
                 echo $out;
              }  ?>
           </td>  <?
        }
        ?></tr><?
     } ?>
  </table>
  
        </td>
     </tr>
  </table>

  <br><br><?
  games();

if ($_SESSION[user_id] == 52){
    $data = $d['data'];
	$nextrow = $d['nextrow'];
	$game_id = $d['id'];
	$mode = 1;
	$score_self = $d['score1'];
	$score_chind = $d['score2'];
	
	$new_data = evil_max($data , $nextrow , $score_self, $score_chind,5, $mode);
	//echo "$data $nextrow $score_self $score_chind $mode<br>";
	echo $new_data['row'];
}

}
}


/**
 * Addle Highscore
 * 
 * Gibt die Highscore Liste von Addle aus
 * 
 * @author [z]biko
 * @version 1.0
 *
 * @global array $db Array mit allen MySQL-Datenbankvariablen
 * @global array $user Array mit allen Uservariablen
 */
function highscore() {
  global $db, $user, $smarty;
  
  //echo head(0, 'Addle');
  $smarty->assign('tplroot', array('page_title' => 'Addle Highscores'));
  $smarty->display('file:layout/head.tpl');
  echo menu("zorg");
  echo menu("games");
  echo menu("addle");
  
  $e = $db->query("SELECT * FROM addle WHERE finish='1'", __FILE__, __LINE__);
  $score = array();
  $win = array();
  $loose = array();
  $unent = array();
  $usr = array();
  while ($d = mysql_fetch_array($e)) {
     $usr[$d[player1]] = $d[player1];
     $usr[$d[player2]] = $d[player2];
     if ($d[score1] > $d[score2]) {
        $score[$d[player1]] += 3;
        $score[$d[player2]] += 0;
        $win[$d[player1]]++;
        $win[$d[player2]] += 0;
        $loose[$d[player1]] += 0;
        $loose[$d[player2]]++;
        $unent[$d[player1]] += 0;
        $unent[$d[player2]] += 0;
     }elseif ($d[score2] > $d[score1]) {
        $score[$d[player1]] += 0;
        $score[$d[player2]] += 3;
        $win[$d[player1]] += 0;
        $win[$d[player2]]++;
        $loose[$d[player1]]++;
        $loose[$d[player2]] += 0;
        $unent[$d[player1]] += 0;
        $unent[$d[player2]] += 0;
     }else{
        $score[$d[player1]]++;
        $score[$d[player2]]++;
        $unent[$d[player1]]++;
        $unent[$d[player2]]++;
        $win[$d[player1]] += 0;
        $win[$d[player2]] += 0;
        $loose[$d[player1]] += 0;
        $loose[$d[player2]] += 0;
     }
  }
  $keys = array_keys($usr);
  for ($i=0; $i<sizeof($keys); $i++) {
     /* old score calculation - nachteile: wenn user nur 1 spiel gemacht hat und dieses gewonnen hat, war er zuoberst in der rangliste... 
     $anz = $win[$keys[$i]] + $loose[$keys[$i]] + $unent[$keys[$i]];
     $sc = $score[$keys[$i]] / $anz;
     $score[$keys[$i]] = round($sc * 100 / 3);
     */
     
     // new score calculation
     //$score[$keys[$i]] = round(($win[$keys[$i]]+1) / ($loose[$keys[$i]]+1) * 100);
     
     $score[$keys[$i]] = round($score[$keys[$i]] * ($win[$keys[$i]]+1) / ($loose[$keys[$i]]+1));
  }
  array_multisort($score, SORT_NUMERIC, SORT_DESC, $win, SORT_NUMERIC, SORT_DESC, $unent, SORT_NUMERIC, SORT_DESC, $loose, SORT_NUMERIC, SORT_ASC, $usr);
  ?>
  <div align='center'>
  <table cellspacing='0' cellpadding='2' class='border'>
     <tr class='title'>
        <td>&nbsp;</td>
        <td>User &nbsp; &nbsp;</td>
        <td>Punkte &nbsp; &nbsp; &nbsp; &nbsp;</td>
        <td align='right'>G &nbsp; &nbsp;</td>
        <td align='right'>U &nbsp; &nbsp;</td>
        <td align='right'>V &nbsp;</td>
     </tr> <?
     for ($i=0; $i<sizeof($usr); $i++) {
        if ($i%2 == 0) {
           $bgcolor = "bgcolor='". TABLEBACKGROUNDCOLOR ."'";
        } else {
           $bgcolor = "";
        }?>
        <tr>
           <td <?=$bgcolor?> align='right'><?=$i+1?>. &nbsp;</td>
           <td <?=$bgcolor?> align="left"><?=$user->id2user($usr[$i])?> &nbsp;</td>
           <td <?=$bgcolor?> align='right'><?=$score[$i]?> &nbsp; &nbsp; &nbsp; &nbsp;</td>
           <td <?=$bgcolor?> align='right'><?=$win[$i]?> &nbsp;&nbsp;</td>
           <td <?=$bgcolor?> align='right'><?=$unent[$i]?> &nbsp;&nbsp;</td>
           <td <?=$bgcolor?> align='right'><?=$loose[$i]?> &nbsp;</td>
        </tr> <?
     }  ?>
  </table>
  </div><?
}


/**
 * Addle Spiele-Archiv
 * 
 * Listet alte Addle Spiele auf
 * 
 * @author [z]biko
 * @version 1.0
 *
 * @global array $db Array mit allen MySQL-Datenbankvariablen
 * @global array $user Array mit allen Uservariablen
 */
function archiv() {
  global $db, $user, $smarty;

  if (!$_GET[uid]) $uid = $user->id;
  else $uid = $_GET[uid];
  
  //echo head(0, 'Addle');
  $smarty->assign('tplroot', array('page_title' => 'Addle Archiv'));
  $smarty->display('file:layout/head.tpl');
  echo menu("zorg");
  echo menu("games");
  echo menu("addle");
  

  $e = $db->query("SELECT * FROM addle_dwz WHERE user=$uid", __FILE__, __LINE__);
  $d = $db->fetch($e);
  ?>
  <div align='center'>
  <H3>Spieler Stats für <?=$user->id2user($uid)?></H3>
  <table>
     <tr><td align="left">DWZ Punkte: &nbsp; </TD><TD align="right"><?=$d[score]?></td></tr>
     <tr><td align="left">DWZ Rank: </TD><td align="right"><?=$d[rank]?>.</td></tr>
  </table>
  <BR />
  <table cellspacing='0' cellpadding='2' class='border'>
     <tr class='title'>
        <td>Gegner &nbsp; &nbsp;</td>
        <td>letzter Zug &nbsp; &nbsp; </td>
        <td><?=$user->id2user($uid)?> &nbsp; &nbsp;</td>
        <td>Gegner P. &nbsp; &nbsp;</td>
        <td>Ausgang</td>
        <TD>&nbsp;</TD>
     </tr>  <?
     
     $e = $db->query("SELECT * FROM addle WHERE (player1=$uid OR player2=$uid) ORDER BY date DESC", __FILE__, __LINE__);
     $i = 0;
     while ($d = mysql_fetch_array($e)) {
        if ($d[player1] == $uid) {
           $ich = 1;
           $gegner = 2;
        }else{
           $ich = 2;
           $gegner = 1;
        }
        if ($i%2 == 0) {
           $bgcolor = "bgcolor='". TABLEBACKGROUNDCOLOR. "'";
        }else{
           $bgcolor = "";
        }
        ?>
        <tr>
           <td <?=$bgcolor?> align="left"><a href="addle.php?show=archiv&uid=<?=$d['player'.$gegner];?>"><?=$user->id2user($d['player'.$gegner])?></a> &nbsp; &nbsp;</td>
           <td <?=$bgcolor?> align="left"><?=datename($d['date'])?> &nbsp; &nbsp;</td>
           <td <?=$bgcolor?> align='right'><?=$d['score'.$ich]?> &nbsp; &nbsp;</td>
           <td <?=$bgcolor?> align='right'><?=$d['score'.$gegner]?> &nbsp; &nbsp;</td>
           <td <?=$bgcolor?>>  <?
              if (!$d[finish]) {
                 echo "-";
              }elseif ($d['score'.$ich] > $d['score'.$gegner]) {
                 echo "<b>gewonnen</b>";
              }elseif ($d['score'.$gegner] > $d['score'.$ich]) {
                 echo "verloren";
              }else{
                 echo "unentschieden";
              }  ?>
           </td>
           <TD <?=$bgcolor?> align="left"> &nbsp; <a href="/addle.php?show=play&id=<?=$d[id]?>">ansehen</A></TD>
        </tr>  <?
        $i++;
     } ?>
  </table>
  </div> <?
}

if (!$_SESSION['user_id']) {exit;}

switch ($_GET['do']) {
  case "new": newgame($_POST[id]); break;
  case "play": doplay($_GET[id], $_GET[choose]); break;
}


switch ($_GET['show']) {
  case "overview": overview(); break;
  case "play": play($_GET[id]); break;
  //case "howto": echo head("Addle: Anleitung"); howto(); break;
  case "howto": 
  	//echo head(0, 'Addle'); 
  	$smarty->assign('tplroot', array('page_title' => 'Addle How-to'));
  	$smarty->display('file:layout/head.tpl');
  	echo menu("zorg");
      echo menu("games");
      echo menu("addle");
      howto(); 
      break;
  case "highscore": 
  	highscore(); 
  	break;
  case "dwz": 
  	//echo head(0, 'Addle');
  	$smarty->assign('tplroot', array('page_title' => 'Addle DWZ'));
  	$smarty->display('file:layout/head.tpl');
  	echo menu("zorg");
      echo menu("games");
      echo menu("addle");
      echo highscore_dwz(999); 
      break;
  case "archiv": 
  	archiv(); 
  	break;
  default:
     $e = $db->query("SELECT * FROM addle WHERE ((player1=$_SESSION[user_id] AND nextturn=1) OR (player2=$_SESSION[user_id] AND nextturn=2)) AND finish=0", __FILE__, __LINE__);
     $d = mysql_fetch_array($e);
     play($d[id]);
}
//echo foot(7);
$smarty->display('file:layout/footer.tpl');

ob_end_flush();
