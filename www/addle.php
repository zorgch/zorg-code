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
 * @author [z]keep3r
 * @date 16.05.2003
 * @package zorg\Games\Addle
 */

/**
 * File includes
 * @include main.inc.php required
 * @include addle.inc.php required
 * @include core.model.php required
 */
require_once( __DIR__ . '/includes/main.inc.php');
require_once( __DIR__ . '/includes/addle.inc.php');
require_once( __DIR__ . '/models/core.model.php');

/**
 * Initialise MVC Model
 */
$model = new MVC\Addle();

/**
 * Addle KI Einsetzen
 * 
 * Aktiviert die KI fuer ein bestimmtes Spiel
 * 
 * @author [z]bert
 * @version 2.0
 * @since 1.0 function added
 * @since 1.5 function optimized
 * @since 2.0 07.11.2018 code and sql-query optimizations
 *
 * @see BARBARA_HARRIS
 * @param integer $game_id ID des Addle Spiels
 */
function use_ki($game_id) {
	global $db;
	$ki = false;
	$sql = 'SELECT player1 FROM addle WHERE id = '.$game_id;
	$result = $db->query($sql, __FILE__, __LINE__, __FUNCTION__);
	$rs = $db->fetch($result);
	if($rs['player1'] == BARBARA_HARRIS) {
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
 * @version 2.0
 * @since 1.0 function added
 * @since 1.5 function optimized
 * @since 2.0 07.11.2018 code and sql-query optimizations
 */
function selectoption($inputname, $size, $valuearray, $array2="",$selected="", $addhtml = "") {
	if(is_array($valuearray)) {
		$html = '<select name="'.$inputname.'" size="'.$size.'" class="select" '.$addhtml.'>'."\n";
		if(is_array($array2)) {
			for($i=0;$i<=count($array2)-1;$i++) {
				$html .= '<option value="'.$valuearray[$i].'" ';
				if($valuearray[$i] == $selected || $array2[$i] == $selected) {
					$html .= ' class="selected" selected';
				}
				$html .= '>'.$array2[$i].'</option>'."\n";
			}
		} else {
			foreach($valuearray as $key => $value) {
				$html .= '<option value="'.$key.'"';
				if($key == $selected || $value == $selected) {
					$html .= ' class="selected" selected';
				}
				$html .= '>'.$value.'</option>'."\n";
			}
		}
		$html .= '</select>'."\n";
		return $html;
	}
}


/**
 * Neues Addle Spiel
 * 
 * Erzeugt ein neues Addle Spiel
 * 
 * @author [z]bert, [z]keep3r
 * @version 3.0
 * @since 1.0 function added
 * @since 2.0 KI added
 * @since 3.0 07.11.2018 code and sql-query optimizations, moved Constants to config.inc.php
 *
 * @see config.inc.php
 * @see MAX_ADDLE_GAMES
 * @param integer $player ID des Gegners
 * @global object $db Globales Class-Object mit allen MySQL-Methoden
 * @global object $user Globales Class-Object mit den User-Methoden & Variablen
 * @global object $smarty Globales Class-Object mit allen Smarty-Methoden
 * @global object $notification Globales Class-Object mit allen Notification-Methoden
 */
function newgame($player) {
	global $db, $user, $smarty, $notification;

	try {
		$anz = $db->fetch($db->query('SELECT count(*) anz FROM addle WHERE finish=0 AND ((player1='.$user->id.' AND player2='.$player.') OR (player1='.$player.' AND player2='.$user->id.'))',
			__FILE__, __LINE__, 'SELECT FROM addle'));
		if ($anz['anz'] > MAX_ADDLE_GAMES) user_error(t('error-game-max-limit-reached'), E_USER_NOTICE);

		$e = $db->query('SELECT addle FROM user WHERE id='.$player, __FILE__, __LINE__, 'SELECT FROM user');
		$d = $db->fetch($e);
	} catch(Exception $e) {
		error_log($e->getMessage());
		user_error(t('error-newgame'), E_USER_ERROR);
		exit;
	}

	if (!$player || $player == $user->id || $d['addle'] !=1) {
		user_error(t('error-newgame'), E_USER_ERROR);
		exit;
	}

	// create board
	/* zahlenverteilung:
		1:	8x
		2:	8x
		3:	9x
		4:	8x
		5:	4x
		6:	4x
		7:	4x
		8:	4x
		9:	4x
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
		} else {
				$rnd = mt_rand(0, sizeof($zahlen)-1);
		}
		$board .= $zahlen[$rnd];
		array_splice($zahlen, $rnd, 1);
	}
	$row = mt_rand(0,7);

	// db-entry
	$gameid = $db->query('INSERT INTO addle (date, player1, player2, data, nextrow) VALUES (UNIX_TIMESTAMP(NOW()), '.$player.', '.$user->id.', "'.$board.'", '.$row.')', __FILE__, __LINE__, __FUNCTION__);
	$db->query('UPDATE user SET addle="1" WHERE id='.$user->id, __FILE__, __LINE__, __FUNCTION__);
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
		$notification_text = t('neue-herausforderung', 'addle', [ SITE_URL, $gameid ]);
		$notification_status = $notification->send($player, 'games', ['from_user_id'=>$user->id, 'subject'=>t('message-subject', 'addle'), 'text'=>$notification_text, 'message'=>$notification_text]);
		if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> $notification_status: %s', __METHOD__, __LINE__, ($notification_status == 'true' ? 'true' : 'false')));
		//Messagesystem::sendMessage($user->id, $player, t('message-subject', 'addle'), t('neue-herausforderung', 'addle', [ SITE_URL, $gameid ]));
	} catch (Exception $e) {
		user_error($e->getMessage(), E_USER_ERROR);
	}

	header('Location: ?show=play&id='.$gameid);
	exit;
}


/**
 * Alle offenen Addle Spiele
 * 
 * Listet alle offenen Addle Spiele auf
 * 
 * @author [z]bert
 * @version 2.0
 * @since 1.0 function added
 * @since 2.0 07.11.2018 code and sql-query optimizations
 *
 * @global object $db Globales Class-Object mit allen MySQL-Methoden
 * @global object $user Globales Class-Object mit den User-Methoden & Variablen
 */
function games()
{
	global $db, $user;

	echo '<h3>Laufende Spiele</h3>';

	if ($user->is_loggedin())
	{
		$e = $db->query('SELECT * FROM addle WHERE ((player1='.$user->id.' AND nextturn=1) OR (player2='.$user->id.' AND nextturn=2)) AND finish=0', __FILE__, __LINE__, __FUNCTION__);
	} else {
		$e = $db->query('SELECT * FROM addle WHERE finish=0', __FILE__, __LINE__, __FUNCTION__);
	}
	$num = $db->num($e);

	if (!empty($num) && $num !== false && $num > 0)
	{
		$i = 1;
		while ($d = $db->fetch($e)) {
			/** Eingeloggte User */
			if ($d['player1'] == $user->id || $d['player2'] == $user->id) printf('<b><a style="color:red;" href="?show=play&id=%1$d">Game #%1$d - vs. %2$s</a></b>', $d['id'], $user->id2user($d['player'.($d['player1'] == $user->id ? 2 : 1)]));

			/** Nicht eingeloggte User */
			else printf('<b><a href="?show=play&id=%d">%s vs. %s</a></b>', $d['id'], $user->id2user($d['player1']), $user->id2user($d['player2']));
			if ($i < $num) echo ', ';
			$i++;
		}
	} else {
		echo '<b>Keine laufenden Addle Spiele</b>';
	}

	if ($user->is_loggedin())
	{
		$e = $db->query('SELECT * FROM addle WHERE ((player1='.$user->id.' AND nextturn=2) OR (player2='.$user->id.' AND nextturn=1)) AND finish=0', __FILE__, __LINE__, __FUNCTION__);
		$num = $db->num($e);
		if (!empty($num) || $num > 0)
		{
			echo '<h3>Warten auf deinen Gegner</h3>';
			$i = 1;
			while ($d = $db->fetch($e)) {
				if ($d['player1'] != $user->id) {
						$otherpl = $d['player1'];
				} else {
						$otherpl = $d['player2'];
				}
				printf('<a href="?show=play&id=%1$d">Game #%1$d - <b>%2$s</b></a>', $d['id'], $user->id2user($otherpl, true));
				if ($i < $num) $out .= ', ';
				$i++;
			}
		}
	}
}


/**
 * Addle Hauptseite
 * 
 * Erzeugt die Hauptseite zu Addle mit einer generellen Spielübersicht
 * 
 * @author [z]bert
 * @version 2.0
 * @since 1.0 function added
 * @since 2.0 07.11.2018 code and sql-query optimizations
 *
 * @global object $db Globales Class-Object mit allen MySQL-Methoden
 * @global object $user Globales Class-Object mit den User-Methoden & Variablen
 * @global object $smarty Globales Class-Object mit allen Smarty-Methoden
 */
function overview() {
	global $db, $user, $smarty;

	/** New Addle Game-Formular anzeigen */
	if ($user->is_loggedin())
	{ ?>
		<h3>Neues Spiel</h3>
		<form action="?show=overview&do=new" method="post">
			<fieldset>
				<label>Gegen&nbsp;
				<?php
					$sql = 'SELECT username, id FROM user WHERE addle="1" AND id <> '.$user->id.' ORDER BY username ASC';
					$result = $db->query($sql, __FILE__, __LINE__, __FUNCTION__);
					$numResult = $db->num($result);
					while($rs = $db->fetch($result)) {
							$values[] = $rs['id'];
							$texts[] = $rs['username'];
					}
					mt_srand((double)microtime()*1000000);
					$preselectId = mt_rand(1, $numResult);
					echo selectoption('id',1,$values,$texts,$values[$preselectId]);
				?>
				</label>
				<input type="submit" class="button" value="play">
			</fieldset>
		</form>
	<?php
	}

	/** Laufende Addle Games auflisten */
	games();
	
	echo '<h3>Anleitung</h3>';
	echo t('howto', 'addle');
}


/**
 * Addle Spielzug ausführen
 * 
 * Verarbeitet einen Addle Spielzug
 * 
 * @author [z]bert, [z]keep3r
 * @version 3.0
 * @since 1.0 function added
 * @since 2.0 KI added
 * @since 3.0 07.11.2018 code and sql-query optimizations
 *
 * @param integer $id ID des Addle Spiels
 * @param integer $choose ID des Feldes innerhalb des Addle Spiels $id
 * @global object $db Globales Class-Object mit allen MySQL-Methoden
 * @global object $user Globales Class-Object mit den User-Methoden & Variablen
 * @global object $notification Globales Class-Object mit allen Notification-Methoden
 */
function doplay($id, $choose) {
	global $db, $user, $notification;

	/** Validate passed parameters */
	if (empty($id) || !is_numeric($id) || $id <= 0) user_error(t('error-game-invalid'), E_USER_ERROR);
	if ($choose === '' || !is_numeric($choose) || is_array($choose) || $choose < 0 || $choose > 7) user_error(t('error-game-player-unknown'), E_USER_ERROR);
	if ($choose === 0 || $choose === '0') $choose = 0;
	if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> doplay(): %d, %d', __METHOD__, __LINE__, $id, $choose));

	if ($id)
	{
		try {
			$e = $db->query('SELECT * FROM addle WHERE id='.$id, __FILE__, __LINE__, __FUNCTION__);
			$d = $db->fetch($e);
		} catch(Exception $e) {
			error_log($e->getMessage());
			user_error(t('error-game-invalid'), E_USER_ERROR);
			exit;
		}
		if ($d) { //&& $choose>=0 && $choose<=7) { <- wird schon in der parameter validierung abgefragt
			if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> $d: %s', __METHOD__, __LINE__, print_r($d,true)));
			if ($d['player'.$d['nextturn']] == $user->id) {
				if ($d['nextturn'] == 1) {
					$x = $choose;
					$y = $d['nextrow'];
					$nextturn = 2;
				} else {
					$x = $d['nextrow'];
					$y = $choose;
					$nextturn = 1;
				}
				$num = $y*8+$x;
				$act = substr($d['data'], $num, 1);
				if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> Action: %d, %d, %d, %d', __METHOD__, __LINE__, $d['player'.$d['nextturn']], $d['nextturn'], $nextturn, $num));
				if ($act != '.' && $act) {
					/** score, data change */
					$score = $d['score'.$d['nextturn']] + ord($act)-96;
					$data = substr($d['data'], 0, $num) . "." . substr($d['data'], $num+1);

					/** check, ob fertig */
					$finish = 1;
					if ($nextturn == 1) {
						for ($i=0; $i<8; $i++) {
							if (substr($data, ($choose*8+$i), 1) != ".") {
								$finish = 0;
							}
						}
					} else {
						for ($i=0; $i<8; $i++) {
							if (substr($data, ($i*8+$choose), 1) != ".") {
								$finish = 0;
							}
						}
					}
					/** db entry zug */
					$sql = 'UPDATE addle 
							SET 
								date=UNIX_TIMESTAMP(NOW()), 
								score'.$d['nextturn'].'='.$score.', 
								data="'.$data.'", 
								nextturn='.$nextturn.', 
								nextrow='.$choose.', 
								finish='.$finish.',
								last_pick_data = "'.(ord($act)-96).'", 
								last_pick_row = '.$d['nextrow'].'
							WHERE id='.$id;
					$result = $db->query($sql, __FILE__, __LINE__, __FUNCTION__);
					if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> db entry zug $sql: %s => %s', __METHOD__, __LINE__, ($result?'SUCCESS':'ERROR'),$sql));

					/** Notification */
					$notification_text = t('message-your-turn', 'addle', [ SITE_URL, $id]);
					$notification_status = $notification->send($d['player'.$nextturn], 'games', ['from_user_id'=>$user->id, 'subject'=>t('message-subject', 'addle'), 'text'=>$notification_text, 'message'=>$notification_text]);
					if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> $notification_status: %s', __METHOD__, __LINE__, ($notification_status == 'true' ? 'true' : 'false')));

					/** Addle Game is finished */
					if ($finish) {
						_update_dwz($id);

						/** send finish message */
						if ($nextturn == 1) {
							$msg_from = $d['player2'];
							$msg_to = $d['player1'];
						} else {
							$msg_from = $d['player1'];
							$msg_to = $d['player2'];
						}

						if ($d['score'.$nextturn] > $d['score'.$d['nextturn']]) {
							$notification_subject = t('message-subject', 'addle');
							$notification_text = t('message-game-finish', 'addle', [ SITE_URL, $id, 'gewonnen']);
							$notification_status = $notification->send($msg_to, 'games', ['from_user_id'=>$msg_from, 'subject'=>$notification_subject, 'text'=>$notification_text, 'message'=>$notification_text]);
							if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> $notification_status: %s', __METHOD__, __LINE__, ($notification_status == 'true' ? 'true' : 'false')));
						} elseif ($d['score'.$nextturn] < $d['score'.$d['nextturn']]) {
							$notification_subject = t('message-subject', 'addle');
							$notification_text = t('message-game-finish', 'addle', [ SITE_URL, $id, 'gewonnen']);
							$notification_status = $notification->send($msg_to, 'games', ['from_user_id'=>$msg_from, 'subject'=>$notification_subject, 'text'=>$notification_text, 'message'=>$notification_text]);
							if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> $notification_status: %s', __METHOD__, __LINE__, ($notification_status == 'true' ? 'true' : 'false')));
						} else {
							$notification_subject = t('message-subject', 'addle');
							$notification_text = t('message-game-unentschieden', 'addle', [ SITE_URL, $id, 'gewonnen']);
							$notification_status = $notification->send($msg_to, 'games', ['from_user_id'=>$msg_from, 'subject'=>$notification_subject, 'text'=>$notification_text, 'message'=>$notification_text]);
							if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> $notification_status: %s', __METHOD__, __LINE__, ($notification_status == 'true' ? 'true' : 'false')));
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
		Addle KI -	end
		========================================*/
	}
}


/**
 * Addle Spiel anzeigen
 * 
 * Zeigt ein spezifisches Addle Spiel an
 * 
 * @author [z]bert, [z]keep3r
 * @version 3.0
 * @since 1.0 function added
 * @since 2.0 KI added
 * @since 3.0 07.11.2018 code and sql-query optimizations
 *
 * @param integer $id ID des Addle Spiels
 * @global object $db Globales Class-Object mit allen MySQL-Methoden
 * @global object $user Globales Class-Object mit den User-Methoden & Variablen
 */
function play($id=0)
{
	global $db, $user, $smarty, $model;

	/** Validate passed $id */
	if (empty($id) || !$id || !is_numeric($id) || $id <= 0)
	{
		overview();
		exit;
	}
	$sql = 'SELECT a.*, d1.score dwz1, d1.rank dwzr1, d2.score dwz2, d2.rank dwzr2 
			FROM addle a 
			LEFT JOIN addle_dwz d1 ON d1.user=a.player1 
			LEFT JOIN addle_dwz d2 ON d2.user=a.player2 
			WHERE a.id='.$id;
	$e = $db->query($sql, __FILE__, __LINE__, __FUNCTION__);
	if ($db->num($e) != 1) {
		http_response_code(404); // Set response code 404 (not found)
		user_error(t('error-game-invalid', 'global', $id), E_USER_ERROR);
		exit;
	}
	$d = $db->fetch($e);

	$sidebarHtml = '<center>
	<table cellspacing="0" cellpadding="5">
		<tr>
			<td>';
				if($d['player'.$d['nextturn']] == $d['player1']) {
					$piccolor = 'red';
				} else {
					$piccolor = ($sun === 'up' ? BORDERCOLOR : '');
				}
	$sidebarHtml .= '<table bgcolor="'.$piccolor.'" cellpadding="5" width="150" style="text-align:center;">
					<tr>
						<td>'.$user->userprofile_link($d['player1'], ['pic' => true, 'username' => true, 'clantag' => true, 'link' => true]).'</td>
					</tr>
					<tr>
						<td>
							<a href="?show=dwz"><small>[DWZ '.$d['dwz1'].' / Pos. '.$d['dwzr1'].']</small></a>
							&nbsp;
							<a href="?show=archiv&uid='.$d['player1'].'"><small>[game&nbsp;archive)</small></a>
						</td>
					</tr>
				</table>
				<table cellspacing="0" cellpadding="0" border="0" style="font-size: xx-large;" width="100%">
						<tr>
							<td align="center"><font size="6">'.$d['score1'].'<br><br></td>
						</tr>
						<tr>
							<td align="center"><font size="6">'.$d['score2'].'</td>
						</tr>
				</table>';
				if($d['player'.$d['nextturn']] == $d['player2']) {
					$piccolor = 'red';
				} else {
					$piccolor = ($sun === 'up' ? BORDERCOLOR : '');
				}
	$sidebarHtml .= '<table bgcolor="'.$piccolor.'" cellpadding="5" width="150" style="text-align:center;">
					<tr>
						<td>'.$user->userprofile_link($d['player2'], ['pic' => true, 'username' => true, 'clantag' => true, 'link' => true]).'</td>
					</tr>
					<tr>
						<td>
							<a href="?show=dwz"><small>[DWZ '.$d['dwz2'].' / Pos. '.$d['dwzr2'].']</small></a>
							&nbsp;
							<a href="?show=archiv&uid='.$d['player2'].'"><small>[game&nbsp;archive)</small></a>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table></center>';
	$smarty->assign('sidebarHtml', $sidebarHtml);
	$model->showGame($smarty, $game_id);
	$smarty->display('file:layout/head.tpl');
	?>

	<center>
	<h2 style="font-size: x-large;">
		<?php if (!$d['finish']) {
			echo t('next', 'addle', $user->id2user($d['player'.$d['nextturn']], true));
		} else {
			if ($d['score1'] == $d['score2']) {
				echo t('unentschieden', 'addle');
			} else {
				if ($d['score1'] > $d['score2']) {
					$winner = $user->id2user($d['player1']);
				} else {
					$winner = $user->id2user($d['player2']);
				}
				echo t('gewinner', 'addle', $winner);
			}
		} ?>
	</h2>
	<?php if ($d['finish'] && $d['score1']!=$d['score2'])
	{
		if ($d['score1'] > $d['score2']) {
			$winner = $user->id2user($d['player1']);
		} else {
			$winner = $user->id2user($d['player2']);
		}
		echo '<span class="tiny">'.t('gewinner-dwz', 'addle', [ $winner, $d['dwz_dif'] ]).'</span>';
	} ?>
	<table cellspacing="0" cellpadding="5">
		<tr>
			<td style="text-align: center;">

				<table cellspacing="0" cellpadding="2" style="border-collapse:collapse;" bgcolor='<?php echo TABLEBORDERCOLOR?>'>	<?php
					for ($y=0; $y<8; $y++) {
						?><tr><?php
						for ($x=0; $x<8; $x++) {
							if (($d['nextturn']==1 && $y==$d['nextrow']) || ($d['nextturn']==2 && $x==$d['nextrow'])) {
								$bgcolor = NEWCOMMENTCOLOR;
							} else {
								$bgcolor = TABLEBACKGROUNDCOLOR;
							} ?>
							<td class="addletd" width='40' height='40' align='center' valign='center' bgcolor='<?php echo $bgcolor?>'>
								<?php $act = substr($d['data'], ($y*8+$x), 1);
								if ($act == '.') {
									if ($d['last_pick_data']) {
										if ($d['nextturn']==1 && $x==$d['last_pick_row'] && $y==$d['nextrow']
											|| $d['nextturn']==2 && $y==$d['last_pick_row'] && $x==$d['nextrow']
										) {
											echo '<font color="gray"><i>'.$d['last_pick_data'].'</i></font>';
										} else {
											echo '&nbsp;';
										}
									}
								} else {
									/* Debugging
									if (DEVELOPMENT) error_log(sprintf('player1: %d vs %d', $d['player1'], $user->id));
									if (DEVELOPMENT) error_log(sprintf('player2: %d vs %d', $d['player2'], $user->id));
									if (DEVELOPMENT) error_log(sprintf('nextturn: %d', $d['nextturn']));
									if (DEVELOPMENT) error_log(sprintf('$x: %d | $y: %d', $x, $y));
									if (DEVELOPMENT) error_log(sprintf('nextrow: %d', $d['nextrow']));
									if (DEVELOPMENT) error_log('============');
									*/
									$out = '<b>'.(ord($act)-96).'</b>';
									if ($d['player1']==$user->id && $d['nextturn']==1 && $y==$d['nextrow'] && $d['finish']==0) {
										$out = "<a href='?show=play&do=play&id=".$id."&choose=".$x."'>$out</a>";
									} elseif ($d['player2']==$user->id && $d['nextturn']==2 && $x==$d['nextrow'] && $d['finish']==0) {
										$out = "<a href='?show=play&do=play&id=".$id."&choose=".$y."'>$out</a>";
									}
									echo $out;
								} ?>
							</td><?php
						}
						?></tr><?php
					} ?>
				</table>

			</td>
		</tr>
	</table>
	</center>

	<?php
	//games(); DISABLED weils scheisse auf Mobile aussieht im Responsive, da nicht in Sidebar...

	/* keep3r's KI-Testing...
	* @see evil_max()
	*/
	if (sanitize_userinput($_GET['debug']) === 'true' && $user->typ >= USER_MEMBER) {
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


/**
 * Addle Highscore
 * 
 * Gibt die Highscore Liste von Addle aus
 * 
 * @author [z]bert
 * @version 1.0
 * @since 1.0 function added
 * @since 2.0 07.11.2018 code and sql-query optimizations
 *
 * @global object $db Globales Class-Object mit allen MySQL-Methoden
 * @global object $user Globales Class-Object mit den User-Methoden & Variablen
 */
function highscore() {
	global $db, $user, $smarty;
	
	$e = $db->query('SELECT * FROM addle WHERE finish=1', __FILE__, __LINE__, __FUNCTION__);
	$score = array();
	$win = array();
	$loose = array();
	$unent = array();
	$usr = array();
	while ($d = $db->fetch($e)) {
		$usr[$d['player1']] = $d['player1'];
		$usr[$d['player2']] = $d['player2'];
		if ($d['score1'] > $d['score2']) {
				$score[$d['player1']] += 3;
				$score[$d['player2']] += 0;
				$win[$d['player1']]++;
				$win[$d['player2']] += 0;
				$loose[$d['player1']] += 0;
				$loose[$d['player2']]++;
				$unent[$d['player1']] += 0;
				$unent[$d['player2']] += 0;
		}elseif ($d['score2'] > $d['score1']) {
				$score[$d['player1']] += 0;
				$score[$d['player2']] += 3;
				$win[$d['player1']] += 0;
				$win[$d['player2']]++;
				$loose[$d['player1']]++;
				$loose[$d['player2']] += 0;
				$unent[$d['player1']] += 0;
				$unent[$d['player2']] += 0;
		} else {
				$score[$d['player1']]++;
				$score[$d['player2']]++;
				$unent[$d['player1']]++;
				$unent[$d['player2']]++;
				$win[$d['player1']] += 0;
				$win[$d['player2']] += 0;
				$loose[$d['player1']] += 0;
				$loose[$d['player2']] += 0;
		}
	}
	$keys = array_keys($usr);
	for ($i=0; $i<sizeof($keys); $i++) {
		/** old score calculation
		 * nachteile: wenn user nur 1 spiel gemacht hat und dieses gewonnen hat, war er zuoberst in der rangliste... 
		$anz = $win[$keys[$i]] + $loose[$keys[$i]] + $unent[$keys[$i]];
		$sc = $score[$keys[$i]] / $anz;
		$score[$keys[$i]] = round($sc * 100 / 3);
		*/
		
		/** new score calculation */
		//$score[$keys[$i]] = round(($win[$keys[$i]]+1) / ($loose[$keys[$i]]+1) * 100);
		$score[$keys[$i]] = round($score[$keys[$i]] * ($win[$keys[$i]]+1) / ($loose[$keys[$i]]+1));
	}
	array_multisort($score, SORT_NUMERIC, SORT_DESC, $win, SORT_NUMERIC, SORT_DESC, $unent, SORT_NUMERIC, SORT_DESC, $loose, SORT_NUMERIC, SORT_ASC, $usr);
	?>
	<table>
		<thead><tr>
			<th>&nbsp;</th>
			<th>User &nbsp; &nbsp;</th>
			<th>Punkte &nbsp; &nbsp; &nbsp; &nbsp;</th>
			<th align="right">G &nbsp; &nbsp;</th>
			<th align="right">U &nbsp; &nbsp;</th>
			<th align="right">V &nbsp;</th>
		</tr></thead>
		<tbody>
		<?php for ($i=0; $i<sizeof($usr); $i++) {
				if ($i%2 == 0) {
					$bgcolor = "bgcolor='". TABLEBACKGROUNDCOLOR ."'";
				} else {
					$bgcolor = "";
				}?>
				<tr>
					<td <?php echo $bgcolor?> align="right"><?php echo $i+1?>. &nbsp;</td>
					<td <?php echo $bgcolor?> align="left"><?php echo $user->id2user($usr[$i])?> &nbsp;</td>
					<td <?php echo $bgcolor?> align="right"><?php echo $score[$i]?> &nbsp; &nbsp; &nbsp; &nbsp;</td>
					<td <?php echo $bgcolor?> align="right"><?php echo $win[$i]?> &nbsp;&nbsp;</td>
					<td <?php echo $bgcolor?> align="right"><?php echo $unent[$i]?> &nbsp;&nbsp;</td>
					<td <?php echo $bgcolor?> align="right"><?php echo $loose[$i]?> &nbsp;</td>
				</tr> <?php
		}	?>
		</tbody>
	</table>
<?php
}


/**
 * Addle Spiele-Archiv
 * 
 * Listet alte Addle Spiele auf
 * 
 * @author [z]bert
 * @version 1.0
 * @since 1.0 function added
 * @since 2.0 07.11.2018 code and sql-query optimizations
 *
 * @global object $db Globales Class-Object mit allen MySQL-Methoden
 * @global object $user Globales Class-Object mit den User-Methoden & Variablen
 */
function archiv() {
	global $db, $user, $smarty;

	if ((!$_GET['uid'] || $_GET['uid'] <= 0) && $user->is_loggedin()) $uid = $user->id;
	elseif (!$_GET['uid'] || $_GET['uid'] <= 0 || !is_numeric($_GET['uid'])) {
		http_response_code(404); // Set response code 404 (not found)
		echo t('error-game-player-unknown');
		exit;
	}
	else $uid = sanitize_userinput($_GET['uid']);

	$e = $db->query('SELECT * FROM addle_dwz WHERE user='.$uid, __FILE__, __LINE__, __FUNCTION__);
	$d = $db->fetch($e);
	?>
	<div align="center">
	<h3>Spieler Stats für <?php echo $user->id2user($uid)?></h3>
	<h4>DWZ Punkte:&nbsp;<b><?php echo $d['score']?></b> (Rank #<?php echo $d['rank']?>)</h4>
	<table>
		<tr class='title'>
				<td>Gegner &nbsp; &nbsp;</td>
				<td>letzter Zug &nbsp; &nbsp; </td>
				<td><?php echo $user->id2user($uid)?> &nbsp; &nbsp;</td>
				<td>Gegner P. &nbsp; &nbsp;</td>
				<td>Ausgang</td>
				<td>&nbsp;</td>
		</tr>	<?php
		
		$e = $db->query('SELECT * FROM addle WHERE (player1='.$uid.' OR player2='.$uid.') ORDER BY date DESC', __FILE__, __LINE__, __FUNCTION__);
		$i = 0;
		while ($d = $db->fetch($e))
		{
			if ($d['player1'] == $uid) {
				$ich = 1;
				$gegner = 2;
			} else {
				$ich = 2;
				$gegner = 1;
			}
			?>
			<tr>
				<td align="left"><a href="?show=archiv&uid=<?php echo $d['player'.$gegner];?>"><?php echo $user->id2user($d['player'.$gegner])?></a> &nbsp; &nbsp;</td>
				<td align="left"><?php echo datename($d['date'])?> &nbsp; &nbsp;</td>
				<td align='right'><?php echo $d['score'.$ich]?> &nbsp; &nbsp;</td>
				<td align='right'><?php echo $d['score'.$gegner]?> &nbsp; &nbsp;</td>
				<td><?php
					if (!$d['finish']) {
						echo '-';
					}elseif ($d['score'.$ich] > $d['score'.$gegner]) {
						echo '<b>gewonnen</b>';
					}elseif ($d['score'.$gegner] > $d['score'.$ich]) {
						echo 'verloren';
					} else {
						echo 'unentschieden';
					}	?>
				</td>
				<td align="left"> &nbsp; <a href="?show=play&id=<?php echo $d['id']?>">ansehen</a></td>
			</tr><?php
			$i++;
		} ?>
	</table>
	</div> <?php
}

/*
if ($user->is_loggedin())
{
*/
	/** Validate GET-Parameters */
	if (!empty($_GET['id'])) $game_id = sanitize_userinput($_GET['id']);
	if (!empty($_GET['do'])) $addle_action = sanitize_userinput($_GET['do']);
	if (!empty($_GET['choose'])) $addle_choose = sanitize_userinput($_GET['choose']);
	if (!empty($_GET['show'])) $show_page = sanitize_userinput($_GET['show']);

	/** Addle Actions */
	if ($user->is_loggedin() && !empty($addle_action))
	{
		switch ($addle_action)
		{
			case 'new': newgame($_POST['id']); break;
			case 'play': doplay($game_id, $addle_choose); break;
		}
	}

	/** Addle Views */
	//$smarty->assign('tplroot', array('page_title' => 'Addle'));
	//echo menu('zorg');
	//echo menu('games');
	//echo menu('addle');

	switch ($show_page)
	{
		case 'play':
			play($game_id);
			break;

		case 'howto':
			$model->showHowto($smarty);
			$smarty->display('file:layout/head.tpl');
			echo t('howto', 'addle');
			break;

		case 'highscore':
			//$smarty->assign('tplroot', array('page_title' => 'Addle Highscores'));
			$model->showHighscore($smarty);
			$smarty->display('file:layout/head.tpl');
			highscore(); 
			break;

		case 'dwz':
			//$smarty->assign('tplroot', array('page_title' => 'Addle DWZ'));
			$model->showDwz($smarty);
			$smarty->display('file:layout/head.tpl');
			echo highscore_dwz(999); 
			break;

		case 'archiv':
			//$smarty->assign('tplroot', array('page_title' => 'Addle Archiv'));
			$model->showArchive($smarty);
			$smarty->display('file:layout/head.tpl');
			archiv(); 
			break;

		default: // will also apply to: case 'overview'
			//$smarty->assign('tplroot', array('page_title' => 'Addle'));
			$model->showOverview($smarty);
			$smarty->display('file:layout/head.tpl');
			echo '<h2>Addle</h2>';
			overview();
			/*if ($user->is_loggedin())
			{
				$e = $db->query('SELECT * FROM addle WHERE ((player1='.$user->id.' AND nextturn=1) OR (player2='.$user->id.' AND nextturn=2)) AND finish=0', __FILE__, __LINE__, 'SELECT * FROM addle');
				$d = $db->fetch($e);
				play($d['id']);
			}*/
	}

/** User is not logged in */
/*
} else {
	echo menu('zorg');
	echo menu('games');
	echo '<h2 style="font-size:large; font-weight: bold">Wenn du <a href="'.SITE_URL.'/profil.php?do=anmeldung" title="Account für Zorg.ch erstellen">eingeloggt</a> wärst könntest du gegen Spresim batteln.</h2><img border="0" src="/files/396/aficks.jpg">';
}
*/

$smarty->display('file:layout/footer.tpl');

ob_end_flush();
