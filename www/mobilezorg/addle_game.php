<?php
/**
* Addle Game
* 
* Zeigt die ein spezifisches Addle Spiel an
* 
* @author IneX
* @version 1.0
* @package mobilezorg
* @subpackage addle
*
* @global array $user Globales Array mit allen Uservariablen
* @global array $db Globales Array mit allen MySQL-Datenbankvariablen
*
* @param integer $game_id ID des Addle Spiels, welches dargestellt werden soll
*/

include_once($_SERVER['DOCUMENT_ROOT'].'/includes/main.inc.php');

global $user, $db;

if ($user->typ == USER_NICHTEINGELOGGT || !isset($user->typ)) { header('Location: login.php'); }



$game_id = $_GET['game_id'];

// Query chosen Addle Game
$e = $db->query(
		"
		SELECT a.*, d1.score dwz1, d1.rank dwzr1, d2.score dwz2, d2.rank dwzr2
		FROM addle a 
		LEFT JOIN addle_dwz d1 ON d1.user=a.player1
		LEFT JOIN addle_dwz d2 ON d2.user=a.player2
		WHERE a.id=$game_id
		"
		, __FILE__, __LINE__
	  );
if ($db->num($e) != 1) { http_error(404); exit; }
$d = mysql_fetch_array($e);


?>


<ul id="addlegame" title="Spiel">
	<li><?php 
		if (!$d[finish]) {
			//echo $user->id2user($d["player".$d[nextturn]]). " ist am Zug";
			// HIER WEITERMACHEN: (SCORE ANZEIGEN) echo $d[score1]
			echo $user->id2user($d[player1]).": ".$d[score1];
			echo " / ";
			echo $user->id2user($d[player2]).": ".$d[score2];
		}else{
			if ($d[score1] == $d[score2]) {
				echo "Unentschieden";
			}else{
				if ($d[score1] > $d[score2]) {
					echo $user->id2user($d[player1]);
				}else{
					echo $user->id2user($d[player2]);
				}
				 echo " hat gewonnen";
			  }
		   } ?>
	</li>
	<li>
		<table cellspacing='0' cellpadding='2' style="font-size:14px; border-collapse:collapse; background-color:;">
		<?
		for ($y=0; $y<8; $y++) {
			?><tr><?
			for ($x=0; $x<8; $x++) {
			   if (($d[nextturn]==1 && $y==$d[nextrow]) || ($d[nextturn]==2 && $x==$d[nextrow])) {
				  $bgcolor = '#ffebb1';
			   }else{
				  $bgcolor = '#fff';
			   } ?>
			   <td style="border-style:solid; border-color:#000; border-width:1px; font-size:18pt; text-align:center; vertical-align:middle; width:32px; height:32px; background-color:<?=$bgcolor?>; color:#6e62ff;">
			   <?
				  $act = substr($d[data], ($y*8+$x), 1);
				  if ($act == '.') {
					if ($d['last_pick_data']) {
						if ($d['nextturn']==1 && $x==$d['last_pick_row'] && $y==$d['nextrow']
							|| $d['nextturn']==2 && $y==$d['last_pick_row'] && $x==$d['nextrow']
						) {
							echo "<font color='#ccc'><i>$d[last_pick_data]</i></font>";
						}else{
							echo "&nbsp;";
						}
					}
				  }else{
					 $out = "<b>". (ord($act)-96). "</b>";
					 if ($d[player1]==$_SESSION['user_id'] && $d[nextturn]==1 && $y==$d[nextrow] && $d['finish']==0) {
						$out = "<a class=\"addle\" href=\"addle_action.php?show=spiele&do=play&game_id=$game_id&choose=$x\" target=\"_self\">$out</a>";
					 }else if ($d[player2]==$_SESSION['user_id'] && $d[nextturn]==2 && $x==$d[nextrow] && $d['finish']==0) {
						$out = "<a class=\"addle\" href=\"addle_action.php?show=spiele&do=play&game_id=$game_id&choose=$y\" target=\"_self\">$out</a>";
					 }
					 echo $out;
				  }  ?>
			   </td>  <?
			}
			?></tr><?
		} ?>
		</table>
	</li>
</ul>