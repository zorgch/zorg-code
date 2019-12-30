<?php
/**
 * Addle Zusatzfunktionen
 *
 * Beinhaltet diverse Zusatzfunktionen für Addle
 *
 * @author [z]biko
 * @version 1.0
 * @package zorg
 * @subpackage Addle
 */
/**
 * File includes
 * @include config.inc.php required
 * @include mysql.inc.php required
 */
require_once( __DIR__ .'/config.inc.php');
require_once( __DIR__ .'/mysql.inc.php');

/**
 * Anzahl offene Addle Spiele ermitteln
 *
 * Liefert den Text für die Startseite, wieviele Addle Games eines Benutzers noch offen sind.
 *
 * @author [z]biko
 * @version 2.0
 * @since 1.0 function added
 * @since 2.0 07.11.2018 code and sql-query optimizations, moved Constants to config.inc.php
 *
 * @param integer $user_id ID des Users
 * @global object $db Globales Class-Object mit allen MySQL-Methoden
 * @return integer Anzahl der offenen Spiele
 */
function getOpenAddleGames($user_id)
{
	global $db;
	if(isset($user_id))
	{
		/** Spieler am zug (nexttur) ist aktueller User und spiel ist nicht fertig */
		try {
			$sql = 'SELECT id FROM addle WHERE ( (player1 = '.$user_id.' AND nextturn = 1) OR (player2 = '.$user_id.' AND nextturn = 2) ) AND finish = 0';
			$result = $db->query($sql);
			$openGames = $db->num($result);
			return $openGames;
		} catch(Exception $e) {
			error_log($e->getMessage());
			return false;
		}
	}
}

/**
 * Ganz alte Addle Spiele entfernen
 *
 * Entfernt ganz alte Addle spiele (> 3 Monate [15 Wochen])
 *
 * @author [z]biko
 * @version 1.0
 * @since 1.0 function added
 * @since 2.0 15.11.2018 updated to use new $notifcation Class & some code and query optimizations
 *
 * @global object $db Globales Class-Object mit allen MySQL-Methoden
 * @global object $notification Globales Class-Object mit allen Notification-Methoden
 * @return boolean Returns true or false depending on successful execution
 */
function addle_remove_old_games() {
	global $db, $notification;
	
	try {
		$e = $db->query('SELECT * FROM addle WHERE finish=0 and UNIX_TIMESTAMP(NOW()) - date > (60*60*24*7*15)', __FILE__, __LINE__, __FUNCTION__);
		while ($d = $db->fetch($e)) {
			if ($d['nextturn'] == 1) {
				$winner = $d['player2'];
				$looser = $d['player1'];
				$winner_score = 'score2';
				$looser_score = 'score1';
			} else {
				$winner = $d['player1'];
				$looser = $d['player2'];
				$winner_score = 'score1';
				$looser_score = 'score2';
			}
			/** Addle-Game finishen & DWZ updaten */
			$result = $db->update('addle', ['id', $d['id']], ['finish' => 1, $winner_score => 1, $looser_score => 0], __FILE__, __LINE__, __METHOD__);
			if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> $db->update(addle_dwz) $result: %d %s', __METHOD__, __LINE__, $result, ($result > 0 ? 'updates' : 'no change')));
			_update_dwz($d['id']);

			/** Den $Looser benachrichtigen */
			$notification_text_looser = t('message-game-forceclosed', 'addle', [ SITE_URL, $d['id'], 'verloren', 'du', 'hast' ]);
			$notification_status_looser = $notification->send($looser, 'games', ['from_user_id'=>$winner, 'subject'=>t('message-subject', 'addle'), 'text'=>$notification_text_looser, 'message'=>$notification_text_looser]);
			if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> $notification_status_looser: %s', __METHOD__, __LINE__, ($notification_status_looser == 'true' ? 'true' : 'false')));

			/** Den $Winner benachrichtigen */
			$notification_text_winner = t('message-game-forceclosed', 'addle', [ SITE_URL, $d['id'], 'gewonnen', 'ich', 'habe' ]);
			$notification_status_winner = $notification->send($winner, 'games', ['from_user_id'=>$looser, 'subject'=>t('message-subject', 'addle'), 'text'=>$notification_text_winner, 'message'=>$notification_text_winner]);
			if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> $notification_status_winner: %s', __METHOD__, __LINE__, ($notification_status_winner == 'true' ? 'true' : 'false')));
		}
		return true;
	}
	catch (Exception $e) {
		user_error($e->getMessage(), E_USER_ERROR);
		return false;
	}
}


/**
 * Addle DWZ Highscore anzeigen
 * 
 * Zeigt die Highscore Liste der Addle DWZ an
 * 
 * @author [z]biko
 * @version 1.0
 *
 * @param integer $anzahl Anzahl Zeilen, welche ausgegeben werden sollen
 * @global object $db Globales Class-Object mit allen MySQL-Methoden
 * @return string Gibt einen String mit dem HTML-Code der Highscore Liste zurueck
 */
function highscore_dwz($anzahl)
{
	global $db;

	$sql = 'SELECT u.id, u.username, dwz.rank, dwz.score dwz, (dwz.prev_rank-dwz.rank) tendenz, '
	  . '     count( IF ( u.id = a.player1 && a.score1 > a.score2 || u.id = a.player2 && a.score2 > a.score1, 1, NULL ) ) g, '
	  . '     count( IF ( ( u.id = a.player1 || u.id = a.player2 ) && a.score1 = a.score2, 1, NULL ) ) u, '
	  . '     count( IF ( u.id = a.player1 && a.score1 < a.score2 || u.id = a.player2 && a.score2 < a.score1, 1, NULL ) ) v'
	  . '   FROM addle_dwz dwz, user u, addle a'
	  . '   WHERE u.id = dwz.user && a.finish=1'
	  . '   GROUP BY u.id'
	  . '   ORDER BY dwz.rank ASC '
	  . '   LIMIT 0, '.$anzahl;
	$e = $db->query($sql, __FILE__, __LINE__, __FUNCTION__);

	$html = '<h1>Addle Highscores</h1>
			<table>
				<tr>
					<th colspan=2>&nbsp;</th>
					<th align="left">User &nbsp; &nbsp;</th>
					<th align="right">Punkte &nbsp; &nbsp; &nbsp; &nbsp;</th>
					<th align="right">G &nbsp; &nbsp;</th>
					<th align="right">U &nbsp; &nbsp;</th>
					<th align="right">V &nbsp;</th>
				</tr></thead>
				<tbody>';

	while ($d = $db->fetch($e))
	{
		if ($d['tendenz'] > 0)
		{
			$pic = IMAGES_DIR.'arr_up.gif';
			$bgcolor = 'bgcolor="#00BD7A"';
		} elseif ($d['tendenz'] < 0) {
			$pic = IMAGES_DIR.'arr_down.gif';
			$bgcolor = 'bgcolor="#ff7777"';
		} else {
			$pic = "/images/arr_straight.gif";
			$bgcolor = null;
		}

		$html .= '<tr>
					<td '.$bgcolor.' align="right">'.$d['rank'].'. </td>
					<td '.$bgcolor.' align="left"><img src="'.$pic.'"> </td>
					<td '.$bgcolor.' align="left"><a href="/addle.php?show=archiv&uid='.$d['id'].'">'.$d['username'].'</A> &nbsp;</td>
					<td '.$bgcolor.' align="right">'.$d['dwz'].' &nbsp; &nbsp; &nbsp; &nbsp;</td>
					<td '.$bgcolor.' align="right">'.$d['g'].' &nbsp;&nbsp;</td>
					<td '.$bgcolor.' align="right">'.$d['u'].' &nbsp;&nbsp;</td>
					<td '.$bgcolor.' align="right">'.$d['v'].' &nbsp;</td>
			     </tr>';
	}

	$html .= '</tbody></table>';

	return $html;
}


/**
 * Addle DWZ Punkte aktualisieren
 * 
 * Aktualisiert die DWZ Punkte eines Benutzers
 * 
 * @author [z]biko
 * @version 2.0
 * @since 1.0 function added
 * @since 2.0 07.11.2018 code and sql-query optimizations, moved Constants to config.inc.php
 *
 * @see config.inc.php
 * @see ADDLE_BASE_POINTS, ADDLE_MAX_POINTS_TRANSFERABLE
 * @param integer $user_id ID des Users
 * @global object $db Globales Class-Object mit allen MySQL-Methoden
 */
function _update_dwz($user_id) {
	global $db;

	$prev_score_2 = $prev_score_1 = ADDLE_BASE_POINTS;

	$e = $db->query('SELECT * FROM addle WHERE id='.$user_id.' AND finish=1', __FILE__, __LINE__, __FUNCTION__);
	$d = $db->fetch($e);
	if (!$d) user_error("Invalid Addle Game-ID", E_USER_ERROR);

	if ($d['score1'] > $d['score2']) $p1 = 1;
	elseif ($d['score1'] < $d['score2']) $p1 = 0;
	else $p1 = 0.5;
	$p2 = 1 - $p1;

	$e = $db->query('SELECT * FROM addle_dwz WHERE user='.$d['player1'], __FILE__, __LINE__, __FUNCTION__);
	$d1 = $db->fetch($e);
	if ($d1) {
		$dwz1 = $d1['score'];
		$prev_score_1 = $dwz1;
	}
	else $dwz1 = ADDLE_BASE_POINTS;
	$e = $db->query('SELECT score FROM addle_dwz WHERE user='.$d['player2'], __FILE__, __LINE__, __FUNCTION__);
	$d2 = $db->fetch($e);
	if ($d2) {
		$dwz2 = $d2[score];
		$prev_score_2 = $dwz2;
	}
	else $dwz2 = ADDLE_BASE_POINTS;

	$prob1 = 1 / (pow(10, (($dwz2 - $dwz1) / 400)) + 1) ;
	$prob2 = 1 / (pow(10, (($dwz1 - $dwz2) / 400)) + 1) ;

	$dif1 = round (ADDLE_MAX_POINTS_TRANSFERABLE * ($p1 - $prob1));
	$dif2 = round (ADDLE_MAX_POINTS_TRANSFERABLE * ($p2 - $prob2));

	$dwz1 += $dif1;
	$dwz2 += $dif2;

	if ($d1) $db->query('UPDATE addle_dwz SET score='.$dwz1.', prev_score='.$prev_score_1.' WHERE user='.$d['player1'], __FILE__, __LINE__, __FUNCTION__);
	else $db->query('INSERT INTO addle_dwz (user, score, prev_score) VALUES ('.$d['player1'].', '.$dwz1.', '.$prev_score_1.')', __FILE__, __LINE__, __FUNCTION__);
	if ($d2) $db->query('UPDATE addle_dwz SET score='.$dwz2.', prev_score='.$prev_score_2.' WHERE user='.$d['player2'], __FILE__, __LINE__, __FUNCTION__);
	else $db->query('INSERT INTO addle_dwz (user, score, prev_score) VALUES ('.$d['player2'].', '.$dwz2.', '.$prev_score_2.')', __FILE__, __LINE__, __FUNCTION__);

	/** dwz_dif für game */
	$result = $db->update('addle', ['id', $user_id, 'finish', 1], ['dwz_dif' => abs($dif1)], __FILE__, __LINE__, __METHOD__);
	if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> $db->update(addle) $result: %d %s', __METHOD__, __LINE__, $result, ($result > 0 ? 'updates' : 'no change')));

	/** rank update */
	$e = $db->query('SELECT * FROM addle_dwz ORDER BY score DESC', __FILE__, __LINE__, __FUNCTION__);
	$i = 1;
	$prev_score = 0;
	$rank = 0;
	while ($upd = $db->fetch($e)) {
		if ($upd['score'] != $prev_score) {
			$rank = $i;
		}

		if ($upd['user'] == $d['player1'] || $upd['user'] == $d['player2']) {
			$prev_rank = $upd['rank'];
		} else {
			$prev_rank = null;
		}

		$result = $db->update('addle_dwz', ['user', $upd['user']], ['rank' => $rank, 'prev_rank' => $prev_rank], __FILE__, __LINE__, __METHOD__);
		if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> $db->update(addle_dwz) $result: %d %s', __METHOD__, __LINE__, $result, ($result > 0 ? 'updates' : 'no change')));

		$prev_score = $upd['score'];
		++$i;
	}
}



//==============================================================================
//Addle KI: stamp & cylander
//==============================================================================
$max_depth = 5;

/**
 * KI - höchste Punktzahl wählen
 * 
 * Ermittelt, welches Feld die KI nehmen soll,
 * um möglichst viele Punkte zu machen aber dem
 * Gegner nur kleine Punkte zur Wahl zu lassen
 * 
 * @author [z]stamp & [z]cylander
 * @version 1.0
 *
 * @param integer $game_data_array
 * @param integer $row
 * @param integer $score_self
 * @param integer $score_chind
 * @param integer $depth
 * @param integer $mode
 * @return integer ??
 */
function evil_max($game_data_array, $row, $score_self, $score_chind, $depth, $mode) {
	$max_depth = 5;
	if($depth>0) {
		if($mode == 1) {
			for($i=0;$i<8;$i++) {
				//$row_data_string = substr($game_data,($row * 8),8);
				$row_data[$i] = $game_data_array[$row*8 + $i];
			}
		} elseif($mode == 2) {
			for($i=0;$i<8;$i++) {
				$row_data[$i] = $game_data_array[($i * 8) + $row];	
			}
		}
		
		//$row_data = preg_split("[]", $row_data_string, 0, PREG_SPLIT_NO_EMPTY); 
		//$game_data_array = preg_split("[]", $game_data, 0, PREG_SPLIT_NO_EMPTY); 
		
		//$p_st = mt();
		for($i=0;$i<8;$i++)  {
			if($row_data[$i] != ".") {

				$new_game_data = $game_data_array;
				
				if($mode == 1) {
					$new_game_data[$row * 8 + $i] = ".";
				} elseif ($mode == 2){
					$new_game_data[($i * 8) + $row] = ".";	
				}

				//$new_game_data = join($new_game_data,"");
				$max[$i] =  evil_min($new_game_data , $i,($score_self + ord($row_data[$i]) - 96), $score_chind, ($depth-1), $mode);
			} else {
				$max[$i] = ".";
			}
			
		}
		//$total = mt() - $p_st;
		if(/*($_SESSION['s_userid'] == 1 || $_SESSION['s_userid'] == 8) &&*/ $depth == $max_depth) {
			foreach($max as $key) {
				echo $key." ";	
			}
			echo "<br />";
		//	echo $total."<br />";
		}
		$to_select = 23;
		$max_check = 500;
		for($i=0;$i<8;$i++) {
			if($max[$i] != "." && $max_check < $max[$i]) {
				$max_check = $max[$i];
				$to_select = $i;
			}	
		}
		$max_end = $score_self - $score_chind + ord($row_data[$to_select]) - 96;
		
		if($to_select == 23 && $depth != $max_depth) {
			return $score_self - $score_chind + 2000;	
		}
		if($depth == $max_depth) {
			if($mode == 1) {
			$game_data_array[$row*8 + $to_select] = ".";
			}
			else $game_data_array[$row + $to_select*8] = ".";
			$new_data['game_data'] = join($game_data_array, "");
			$new_data['row'] = $to_select;
			$new_data['score'] = ($score_self + ord($row_data[$to_select])) - 96;
		
			return $new_data;
		} else {
			return $max_check;
		}
	}
	return $score_self - $score_chind + 2000;
}


/**
 * KI - möglichst kleine Punktzahl
 * 
 * Ermittelt, welches das kleinste Feld für die KI ist,
 * um dem Gegner möglichst wenig Punkte zur Wahl zu lassen
 * 
 * @author [z]stamp & [z]cylander
 * @version 1.0
 *
 * @param integer $game_data_array
 * @param integer $row
 * @param integer $score_self
 * @param integer $score_chind
 * @param integer $depth
 * @param integer $mode
 * @global object $db Globales Class-Object mit allen MySQL-Methoden
 * @return integer ??
 */
function evil_min($game_data_array, $row, $score_self, $score_chind, $depth, $mode) {
	global $db;

	if($depth >0){
		if($mode == 2) for($i=0;$i<8;$i++) $row_data[$i] = $game_data_array[$row*8 + $i];
		else for ($i=0;$i<8;$i++) $row_data[$i] = $game_data_array[$row + $i*8];
		for($i=0;$i<8;$i++)  {
			if($row_data[$i] != ".") {
				$new_game_data = $game_data_array;
				if($mode == 2) $new_game_data[$row+$i*8] = ".";
				else $new_game_data[$row*8+$i] = ".";
				//$new_game_data = join($new_game_data,"");
				$min[$i] =  evil_max($new_game_data , $i, $score_self, ($score_chind + ord($row_data[$i]) - 96), ($depth-1), $mode);
				
			} else {
				$min[$i] = ".";
			}
			
		}
		/*foreach($min as $key) {
			echo $key." ";	
		}
		echo "<br />";
		*/
		//	echo $total."<br />";
		
		$to_select = 23;
		$min_check = 3000;
		for($i=0;$i<8;$i++) {
			if( $min[$i] != "." && $min_check > $min[$i]) {
				$min_check = $min[$i];
				$to_select = $i;
			}		
		}
		$min_end = $score_self - $score_chind - ord($row_data[$to_select]) + 96;
		
		if($to_select == 23) {
			return $score_self - $score_chind + 2000;	
		}
		return $min_check;
		
	}
	return $score_self - $score_chind + 2000;
}

try {
	$sql = 'SELECT * FROM addle WHERE ((player1 = '.BARBARA_HARRIS.' OR player2 = '.BARBARA_HARRIS.') AND (player1 = 1 OR player2 = 1) AND finish = 0)';
	$result = $db->query($sql,__LINE__,__FILE__);
	while($rs = $db->fetch($result)) {
		$data = $rs['data'];
		$nextturn = $rs['nextturn'];
		$nextrow = $rs['nextrow'];
		$game_id = $rs['id'];
		$new_nextturn = $nextturn;
		$checker = 0;
	
		$data = preg_split("[]", $data, 0, PREG_SPLIT_NO_EMPTY);
		 
		if($rs['player1'] == BARBARA_HARRIS && $nextturn == 1) {
			$mode = 1;
			$score_self = $rs['score1'];
		    $score_chind = $rs['score2'];
		    $new_nextturn = 2;
			$new_data = evil_max($data , $nextrow , $score_self, $score_chind,$max_depth, $mode);
			$checker = 1;
			$my_score = "score1";
		} elseif($rs['player2'] == BARBARA_HARRIS && $nextturn == 2) {
			$mode = 2;
			$score_self = $rs['score2'];
			$score_chind = $rs['score1'];
			$new_nextturn = 1;
			$new_data = evil_max($data , $nextrow , $score_self, $score_chind,$max_depth, $mode);
			$checker = 1;
			$my_score = "score2";
		}
	
		if ($checker == 1) {
			$sql = 'UPDATE addle 
					SET 
						data = "'.$new_data['game_data'].'",
						nextturn = '.$new_nextturn.',
						nextrow = "'.$new_data['row'].'", 
						'.$my_score.' = '.$new_data['score'].',
						date = NOW()';
			if($new_data['row'] != 23) {
				$sql_add = '';
			} else {
				$sql_add = ', finish = 1 ';
			}	
			$sql = $sql.$sql_add.' WHERE id = '.$game_id;
			echo $sql;
			$db->query($sql,__LINE__,__FILE__,'UPDATE addle');
		}
	}
} catch(Exception $e) {
	error_log($e->getMessage());
}
