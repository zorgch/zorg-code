<?php
/**
 * zorg Wettbüro
 *
 * @author freiländer
 * @package zorg\Wetten
 */

/**
 * Wettbüro Klasse
 */
class wetten
{
	static function exec()
	{
		global $db, $user;
		if(isset($_POST) && count($_POST) > 0)
		{
			if($_POST['wette'] && $_POST['einsatz'] && is_numeric($_POST['dauer']) && $_POST['titel'])
			{
				/** Neue Wette eintragen */
				$sql = '
				INSERT into wetten
					(
						wette,
						einsatz,
						user_id,
						datum,
						dauer,
						status,
						titel
					)
				VALUES
					(
						"'.addslashes(strip_tags($_POST['wette'],"<a> <b> <i> <u> <img>")).'",
						"'.addslashes(strip_tags($_POST['einsatz'],"<a> <b> <i> <u> <img>")).'",
						'.$user->id.',
						NOW(),
						'.$_POST['dauer'].',
						"offen",
						"'.addslashes(strip_tags($_POST['titel'])).'"
					)';
				$db->query($sql,__FILE__,__LINE__);

				/** Teilnehmer eintrag */
				$sql = '
				INSERT into wetten_teilnehmer
					(
						wetten_id,
						user_id,
						seite,
						datum
					)
				VALUES
					(
						'.$db->lastid().',
						'.$user->id.',
						"wetter",
						NOW()
					)';
				$db->query($sql,__FILE__,__LINE__);

				header('Location: '.getURL(false,false).'?eintrag=1');
				exit;
			}

			/** Wette starten */
			if ($_GET['id'] && $_POST['start'] && $_POST['dauer']) {
				$sql = '
				UPDATE wetten
				SET
					status = "laeuft",
					start = NOW(),
					ende = ADDDATE(DATE(NOW()),'.$_POST['dauer'].')
				WHERE
					id = '.$_GET['id'].'
					AND
					user_id = '.$user->id;
				$db->query($sql,__FILE__,__LINE__);

			}
			
			/** Wette schliessen */
			elseif ($_GET['id'] && $_POST['schliessen'])
			{
				$sql = "
				UPDATE wetten
				SET
					status = 'geschlossen',
					geschlossen = NOW()
				WHERE
					id = '$_GET[id]'
					AND
					user_id = '$_SESSION[user_id]'
				";
				$db->query($sql,__FILE__,__LINE__);
			}
		}

		if(isset($_GET['do']) && $_GET['id']) {
			/** Wette als Wetter joinen */
			if($_GET['do'] == "wjoin") {
				$sql = "
				SELECT
					*
				FROM wetten_teilnehmer
				WHERE
					user_id = '$_SESSION[user_id]'
					AND
					wetten_id = '$_GET[id]'
				";
				$result = $db->query($sql,__FILE__,__LINE__);
				if(!$db->num($result)) {
					$sql = "
					INSERT 	into wetten_teilnehmer
						(
							wetten_id,
							user_id,
							seite,
							datum
						)
					VALUES
						(
							'".$_GET['id']."',
							'".$user->id."',
							'wetter',
							now()
						)
					";
					$db->query($sql,__FILE__,__LINE__);
				}
			}

			/** wette unjoinen */
			if($_GET['do'] == "unjoin") {
				$sql = "
				DELETE FROM
				wetten_teilnehmer
				WHERE
					user_id = '$_SESSION[user_id]'
					AND
					wetten_id = '$_GET[id]'
				";
				$db->query($sql,__FILE__,__LINE__);
			}

			/** wette als gegner joinen */
			if($_GET['do'] == "gjoin") {
				$sql = "
				SELECT
					*
				FROM wetten_teilnehmer
				WHERE
					user_id = '$_SESSION[user_id]'
					AND
					wetten_id = '$_GET[id]'
				";
				$result = $db->query($sql,__FILE__,__LINE__);
				if(!$db->num($result)) {
					$sql = "
					INSERT 	into wetten_teilnehmer
						(
							wetten_id,
							user_id,
							seite,
							datum
						)
					VALUES
						(
							'".$_GET['id']."',
							'".$user->id."',
							'gegner',
							now()
						)
					";
					$db->query($sql,__FILE__,__LINE__);
				}
			}
		}
	}



	static function listopen() {
		global $db, $user;
		
		$wetter = array();
		$gegner = array();
		
		$sql = 'SELECT w.*, UNIX_TIMESTAMP(w.datum) as datum 
				FROM wetten w 
				WHERE w.status = "offen" 
				ORDER by w.datum DESC';
		$result = $db->query($sql, __FILE__, __LINE__, __METHOD__);
		echo '<h2>Offene Wetten</h2>
		<table width="700" cellpadding="4" cellspacing="1" bgcolor="'.BORDERCOLOR.'">
		<tr align="left"  bgcolor="'.BORDERCOLOR.'">
			<td><b>Wettstarter</b>
			</td><td><b>Titel</b>
			</td><td><b>Einsatz</b>
			</td><td><b>Wetter</b>
			</td><td><b>Gegner</b>
			</td><td><b>Datum</b></td>
		</tr>';

		while($rs = $db->fetch($result))
		{
			$sqli = 'SELECT wt.user_id, wt.seite 
					 FROM wetten_teilnehmer wt 
					 WHERE wt.wetten_id = '.$rs['id'];
			$resulti = $db->query($sqli, __FILE__, __LINE__, __METHOD__);
			while ($rsi = $db->fetch($resulti)) {
				$username = $user->id2user($rsi['user_id']);
				if($rsi['seite'] === 'wetter') {
					array_push($wetter, $username);
				} else {
					array_push($gegner, $username);
				}
			}

			echo "<tr bgcolor='".TABLEBACKGROUNDCOLOR."'>
			<td>".$username."</td>
			<td><a href='?id=".$rs['id']."'>".stripslashes($rs['titel'])."</a></td>
			<td>".stripslashes($rs['einsatz'])."</td><td>";
			echo (count($wetter) > 0 ? implode(', ', (array)$wetter) : 'keine');
			echo '</td><td>';
			echo (count($gegner) > 0 ? implode(', ', (array)$gegner) : 'keine');
			echo "</td><td>
			".datename($rs['datum'])."
			</td></tr>";
		}

		echo '</table>';
	}
	

	static function listlaufende() {
		global $db, $user;

		$wetter = array();
		$gegner = array();

		$sql = 'SELECT
					w.*,
					UNIX_TIMESTAMP(w.datum) as datum
				FROM wetten w
				WHERE w.status = "laeuft"
				ORDER by w.datum DESC';
		$result = $db->query($sql, __FILE__ ,__LINE__, __METHOD__);
		echo '<h2>Laufende Wetten</h2>
		<table width="700" cellpadding="4" cellspacing="1" bgcolor="'.BORDERCOLOR.'">
		<tr align="left"  bgcolor="'.BORDERCOLOR.'">
			<td><b>Wettstarter</b></td>
			<td><b>Titel</b></td>
			<td><b>Einsatz</b></td>
			<td><b>Wetter</b></td>
			<td><b>Gegner</b></td>
			<td><b>Datum</b></td>
		</tr>';

		while($rs = $db->fetch($result))
		{
			$sqli = 'SELECT wt.user_id, wt.seite 
					 FROM wetten_teilnehmer wt 
					 WHERE wt.wetten_id = '.$rs['id'];
			$resulti = $db->query($sqli, __FILE__, __LINE__, __METHOD__);
			while ($rsi = $db->fetch($resulti)) {
				$username = $user->id2user($rsi['user_id'], true);
				if($rsi['seite'] === 'wetter') {
					array_push($wetter, $username);
				} else {
					array_push($gegner, $username);
				}
			}

			echo '<tr bgcolor="'.TABLEBACKGROUNDCOLOR.'">
			<td>'.$username.'</td>
			<td><a href="?id='.$rs['id'].'">'.stripslashes($rs['titel']).'</a>
			</td>
			<td>'.stripslashes($rs['einsatz']).'</td>
			<td>';
			echo (count($wetter) > 0 ? implode(', ', (array)$wetter) : 'keine');
			echo '</td><td>';
			echo (count($gegner) > 0 ? implode(', ', (array)$gegner) : 'keine');
			echo '</td>
			<td>'.datename($rs['datum']).'</td>
			</tr>';
		}
		echo '</table>';
	}
	
	
	static function listclosed() {
		global $db, $user;
		
		$wetter = array();
		$gegner = array();
		
		$sql = 'SELECT w.*, UNIX_TIMESTAMP(w.datum) as datum 
				FROM wetten w 
				WHERE w.status = "geschlossen" 
				ORDER by w.datum DESC';
		$result = $db->query($sql, __FILE__, __LINE__, __METHOD__);
		echo '<h2>Abgeschlossene Wetten</h2>
			<table width="700" cellpadding="4" cellspacing="1" bgcolor="'.BORDERCOLOR.'">
				<tr align="left"  bgcolor="'.BORDERCOLOR.'">
				<td><b>Wettstarter</b></td>
				<td><b>Titel</b></td>
				<td><b>Einsatz</b></td>
				<td><b>Wetter</b></td>
				<td><b>Gegner</b></td>
				<td><b>Datum</b></td>
			</tr>';

		while($rs = $db->fetch($result))
		{
			$sqli = 'SELECT wt.user_id, wt.seite
					 FROM wetten_teilnehmer wt
					 WHERE wt.wetten_id = '.$rs['id'];
			$resulti = $db->query($sqli, __FILE__, __LINE__, __METHOD__);
			while ($rsi = $db->fetch($resulti)) {
				$username = $user->id2user($rsi['user_id'], true);
				if($rsi['seite'] === 'wetter') {
					array_push($wetter, $username);
				} else {
					array_push($gegner, $username);
				}
			}

			echo "<tr bgcolor='".TABLEBACKGROUNDCOLOR."'>
				<td>".$username."</td>
				<td><a href='?id=".$rs['id']."'>".stripslashes($rs['titel'])."</a></td>
				<td>".stripslashes($rs['einsatz'])."</td><td>";
				echo (count($wetter) > 0 ? implode(', ', (array)$wetter) : 'keine');
				echo '</td><td>';
				echo (count($gegner) > 0 ? implode(', ', (array)$gegner) : 'keine');
				echo "</td><td>".datename($rs['datum'])."</td>
			</tr>";
		}

		echo '</table>';
	}
	
	/**
	 * Formular um neue Wette einzutragen
	 *
	 * @version 1.1
	 * @since 1.0 `[z]cylander` method added
	 * @since 1.1 `09.09.2019` `IneX` changed echo to return() to assign output to Smarty
	 */
	static function newform()
	{
		return '
		<h2>Neue Wette eintragen</h2>
		<small>Eine Wette wird erst gestartet wenn der Wetter (das bist DU wenn du eine Wette einträgst) die offene Wette startet.</small>
		<form action="'.getURL(false,false).'" method="post">
		<table class="border">
		<tr><td>
		<b>Wett Titel:<b> <br />
		<input type="text" name="titel" class="text" size="40">
		</td></tr><tr><td>
		<b>Wette: </b><br />
		<textarea name="wette" cols="40" rows="5" class="text"></textarea>
		</td></tr><tr><td>
		<b>Wetteinsatz: </b><br />
		<textarea name="einsatz" cols="40" rows="3" class="text"></textarea>
		</td></tr><tr><td>
		<b><small>Gültigkeit (in Tagen ab Wettstart, 0 steht für unbegrenzt):</small></b>
		<br />
		<input type="text" name="dauer" class="text" size="4">
		</td></tr><tr><td>
		<br />
		<small>
		Eine Wette ist beendet wenn, <b>beide Parteien</b> <br />
		sich auf einen <b>Sieg oder eine Niederlage einigen</b> können.<br />
		</small>
		<br />
		<input type="submit" value="Wette eintragen" class="button">
		</td></tr>
		</table>
		</form>';
	}

	/**
	 * Wette laden und anzeigen
	 *
	 * @version 1.1
	 * @since 1.0 `[z]cylander` method added
	 * @since 1.1 `30.12.2019` `IneX` minor optimizations in error output & HTML
	 *
	 * @param integer $id ID der anzuzeigenden Wette
	 * @global object $db Globales Class-Object mit allen MySQL-Methoden
	 * @global object $user Globales Class-Object mit den User-Methoden & Variablen
	 * @global object $smarty Globales Class-Object mit allen Smarty-Methoden
	 * @return void Printed HTML-Output
	 */
	static function get_wette ($id)
	{
		global $db, $user, $smarty;

		$wetter = array();
		$gegner = array();
		$wjoin = false;
		$gjoin = false;
		$html = '';

		$sql = '
		SELECT *
			,UNIX_TIMESTAMP(datum) as datum
			,UNIX_TIMESTAMP(start) as startdatum
			,UNIX_TIMESTAMP(ende) as enddatum
			,UNIX_TIMESTAMP(geschlossen) as geschlossen
		FROM wetten
		WHERE id = '.$id;
		if(!$rs = $db->fetch($db->query($sql,__FILE__,__LINE__)))
		{
			/** Wette nicht gefunden */
			$smarty->assign('error', ['type' => 'warn', 'dismissable' => 'false', 'title' => 'Diese Wette gibts nicht!', 'message' => '<a class="tiny" href="wetten.php">&lt;&lt; Zur&uuml;ck</a>']);
			http_response_code(404); // Set response code 404 (not found) and exit.
			$smarty->display('file:layout/elements/block_error.tpl');
			exit;
		}
		else {
			/** Wette gefunden - Details & Daten abfragen */
			$sqli = 'SELECT *
					FROM wetten_teilnehmer
					WHERE wetten_id = '.$rs['id'];
			$resulti = $db->query($sqli,__FILE__,__LINE__);
			
			while ($rsi = $db->fetch($resulti))
			{
				if($rsi['seite'] == "wetter") {
					array_push($wetter, $user->link_userpage($rsi['user_id']));
					if($user->is_loggedin() && $rsi['user_id'] === $user->id) $wjoin = true;
				} else {
					array_push($gegner, $user->link_userpage($rsi['user_id']));
					if($user->is_loggedin() && $rsi['user_id'] === $user->id) $gjoin = true;
				}
			}

			if($user->is_loggedin() && $user->id != $rs['user_id'])
			{
				if(!$gjoin && !$wjoin) {
					$gg = '<a href="?id='.$id.'&do=gjoin">join</a>';
					$ww = '<a href="?id='.$id.'&do=wjoin">join</a>';
				} else {
					if($gjoin) {
						$gg = '<a href="?id='.$id.'&do=unjoin">unjoin</a>';
						$ww = '';
					} else {
						$gg = '';
						$ww = '<a href="?id='.$id.'&do=unjoin">unjoin</a>';
					}
				}
			} else {
				$gg = "";
				$ww = "";
			}

			$html .= "
			<br />
			<table width='600' cellpadding='10' cellspacing='0'>
			<tr><td colspan='2'>
			<h1>Wette #".$rs['id']." &laquo;".stripslashes($rs['titel'])."&raquo;</h1>
			</td></tr><tr><td valign=\"top\">
			<b>Wettstarter</b>
			</td><td>
			".$user->link_userpage($rs['user_id'])."
			</td></tr><tr><td valign=\"top\">
			<b>Wetter</b>
			</td><td>
			";

			// Alle Wetter ausgeben
			$anzwetter = count($wetter);
			$html .= ($wetter = implode(', ', $wetter));

			$html .= ($anzwetter > 0 && $ww <> "") ? " | " : "";
			$html .= $ww."
			</td></tr><tr><td valign=\"top\">
			<b>Gegner</b>
			</td><td>
			";

			// Alle Wett-Gegner ausgeben
			$anzgegner = count($gegner);
			$html .= ($gegner = implode(', ', $gegner));
			
			$html .= ($anzgegner > 0 && $gg <> "") ? " | " : "";
			$html .= $gg."
			</td></tr><tr><td valign=\"top\">
			<b>Wette</b>
			</td><td>
			".nl2br(stripslashes($rs['wette']))."
			</td></tr><tr><td valign=\"top\">
			<b>Einsatz</b>
			</td><td>
			".nl2br(stripslashes($rs['einsatz']))."
			</td></tr><tr><td valign=\"top\">
			<b>Ende</b>
			</td><td>";			
			if ($rs['status'] === 'laeuft' || $rs['status'] === 'geschlossen') $html .= ($rs['enddatum'] < time() && $rs['status'] != 'geschlossen') ? "<font color='red'><b>".timename($rs['enddatum'])."</b></font>" : "<b>".timename($rs['enddatum'])."</b>";
			$html .=  " (".$rs['dauer']." Tage".($rs['status'] === 'laeuft' || $rs['status'] === 'geschlossen' ? " ab ".datename($rs['startdatum']) : ' Dauer geplant').")
			</td></tr><tr><td valign=\"top\">
			<b>Status</b>
			</td><td>";

			switch ($rs['status'])
			{
				case 'offen':
					if($user->is_loggedin() && $user->id == $rs['user_id'])
					{
						$html .= '
						<form action="'.getURL(true,false).'" method="post">
							<input type="hidden" name="start" value="1">
							<input type="hidden" name="dauer" value="'.$rs['dauer'].'">
							<input type="submit" value="starten" class="button">
						</form>';
					}
					else
					{
						$html .= '<span class="blink">'.$rs['status'].'</span>';
					}
					break;
					
				case 'laeuft':
					if($user->is_loggedin() && $user->id == $rs['user_id'])
					{
						
						$html .= '
						<form action="'.getURL(true,false).'" method="post">
							<input type="hidden" name="schliessen" value="1">
							<input type="submit" value="schliessen" class="button">
						</form>';
					}
					else
					{
						$html .= '<span class="blink">'.$rs['status'].'</span>';
					}
					break;
				
				case 'geschlossen':
					$html .= '<font color="green"><b>'.$rs['status'].' @ '.date("d.m.Y", $rs['geschlossen']).'</b></font>';
					break;
				
				default:
					$html .= '<span class="blink">'.$rs['status'].'</span>';
			}

			$html .= '
			</td></tr>
			</table>';

			$html .= '<a href="'.getURL(false, false).'">&lt;&lt; zur&uuml;ck</a>';

			echo $html;
		}
	}
	
	/**
	 * Titel einer Wette holen
	 *
	 * @author IneX
	 * @version 1.0
	 * @since 1.0 `03.09.2019` `IneX` method added
	 *
	 * @param int $wette_id ID der Wette
	 * @return string Titel der Wette gemäss $wette_id
	 */
	public static function getTitle($wette_id)
	{
		global $db;

		$wetteId = (int)$wette_id;
		$sql = 'SELECT titel FROM wetten WHERE id='.$wetteId.' LIMIT 1';
		$rs = $db->fetch($db->query($sql, __FILE__, __LINE__, __METHOD__));
		if (!$rs) return false;
		else return $rs['titel'];
	}

	/**
	 * Starter einer Wette holen
	 *
	 * @author IneX
	 * @version 1.0
	 * @since 1.0 `03.09.2019` `IneX` method added
	 *
	 * @param int $wette_id ID der Wette
	 * @return int User-ID des Users der die Wette $wette_id gestartet hat
	 */
	public static function getWettstarter($wette_id)
	{
		global $db;

		$wetteId = (int)$wette_id;
		$sql = 'SELECT user_id FROM wetten WHERE id='.$wetteId.' LIMIT 1';
		$rs = $db->fetch($db->query($sql, __FILE__, __LINE__, __METHOD__));
		if (!$rs) return false;
		else return $rs['user_id'];
	}

	/**
	 * Text der Wette holen
	 *
	 * @author IneX
	 * @version 1.0
	 * @since 1.0 `03.09.2019` `IneX` method added
	 *
	 * @param int $wette_id ID der Wette
	 * @return string Text der Wette gemäss $wette_id
	 */
	public static function getWettetext($wette_id)
	{
		global $db;

		$wetteId = (int)$wette_id;
		$sql = 'SELECT wette FROM wetten WHERE id='.$wetteId.' LIMIT 1';
		$rs = $db->fetch($db->query($sql, __FILE__, __LINE__, __METHOD__));
		if (!$rs) return false;
		else return $rs['wette'];
	}
}
