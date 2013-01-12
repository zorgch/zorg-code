<?php



class wetten {

	function exec() {
		global $db;
		if(count($_POST)) {
			if($_POST['wette'] && $_POST['einsatz'] && is_numeric($_POST['dauer']) && $_POST['titel']) {

				//wette eintragen
				$sql = "
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
						'".addslashes(strip_tags($_POST['wette'],"<a> <b> <i> <u> <img>"))."',
						'".addslashes(strip_tags($_POST['einsatz'],"<a> <b> <i> <u> <img>"))."',
						".$_SESSION['user_id'].",
						now(),
						'".$_POST['dauer']."',
						'offen',
						'".addslashes(strip_tags($_POST['titel']))."'
					)";
				$db->query($sql,__FILE__,__LINE__);

				//teilnehmer eintrag
				$sql = "
				INSERT into wetten_teilnehmer
					(
						wetten_id,
						user_id,
						seite,
						datum
					)
				VALUES
					(
						'".$db->lastid()."',
						".$_SESSION['user_id'].",
						'wetter',
						NOW()
					)";
				$db->query($sql,__FILE__,__LINE__);

				header("Location: http://www.zorg.ch/wetten.php?eintrag=1");
			}

			if ($_GET['id'] && $_POST['start'] && $_POST['dauer']) {
				$sql = "
				UPDATE wetten
				SET
					status = 'laeuft',
					start = NOW(),
					ende = ADDDATE(DATE(NOW()),$_POST[dauer])
				WHERE
					id = '$_GET[id]'
					AND
					user_id = '$_SESSION[user_id]'
				";
				$db->query($sql,__FILE__,__LINE__);

			}
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

		if($_GET['do'] && $_GET['id']) {
			//Wette als Wetter joinen
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
							'".$_SESSION['user_id']."',
							'wetter',
							now()
						)
					";
					$db->query($sql,__FILE__,__LINE__);
				}
			}

			//wette unjoinen
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

			//wette als gegner joinen
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
							'".$_SESSION['user_id']."',
							'gegner',
							now()
						)
					";
					$db->query($sql,__FILE__,__LINE__);
				}
			}
		}
	}



	function listopen() {
		global $db;
		
		$wetter = array();
		$gegner = array();
		
		$sql = "
		SELECT
			w.*,
			u.clan_tag,
			u.username,
			UNIX_TIMESTAMP(w.datum) as datum
		FROM wetten w
		LEFT JOIN user u
			ON u.id = w.user_id
		WHERE w.status = 'offen'
		ORDER by w.datum DESC";
		$result = $db->query($sql,__FILE__,__LINE__);
		echo "
		<br />
		<table width='700' cellpadding='4' cellspacing='1' bgcolor='#".BORDERCOLOR."'>
		<tr align='center'  bgcolor='#".BORDERCOLOR."'><td colspan='6'>
		<b>Offene Wetten</b>
		</td></tr>
		<tr align='left'  bgcolor='#".BORDERCOLOR."'><td>
		<b>Wettstarter</b>
		</td><td>
		<b>Titel</b>
		</td><td>
		<b>Einsatz</b>
		</td><td>
		<b>Wetter</b>
		</td><td>
		<b>Gegner</b>
		</td><td>
		<b>Datum</b>
		</td></tr>";

		while($rs = $db->fetch($result)) {
			$sqli = "
			SELECT
				u.clan_tag,
				u.username,
				wt.seite
			FROM wetten_teilnehmer wt
			LEFT JOIN user u
				ON u.id = wt.user_id
			WHERE wt.wetten_id = $rs[id]";
			$resulti = $db->query($sqli,__FILE__,__LINE__);
			while ($rsi = $db->fetch($resulti)) {
				if($rsi['seite'] == "wetter") {
					array_push($wetter, $rsi['clan_tag'].$rsi['username']);
					//$wetter .= " ".$rsi['clan_tag'].$rsi['username'];
				} else {
					array_push($gegner, $rsi['clan_tag'].$rsi['username']);
					//$gegner .= " ".$rsi['clan_tag'].$rsi['username'];
				}
			}

			echo "
			<tr bgcolor='#".TABLEBACKGROUNDCOLOR."'><td>
			".$rs['clan_tag'].$rs['username']."
			</td><td>
			<a href='?id=".$rs['id']."'>".stripslashes($rs['titel'])."</a>
			</td><td>
			".stripslashes($rs['einsatz'])."
			</td><td>";
			
			echo ($wetter = implode(", ", $wetter));
			
			echo "
			</td><td>";
			
			echo ($gegner = implode(", ", $gegner));
			
			echo "</td><td>
			".datename($rs['datum'])."
			</td></tr>";
		}

		echo "
		</table>";
	}
	

	function listlaufende() {
		global $db;
		
		$wetter = array();
		$gegner = array();
		
		$sql = "
		SELECT
			w.*,
			u.clan_tag,
			u.username,
			UNIX_TIMESTAMP(w.datum) as datum
		FROM wetten w
		LEFT JOIN user u
			ON u.id = w.user_id
		WHERE w.status = 'laeuft'
		ORDER by w.datum DESC";
		$result = $db->query($sql,__FILE__,__LINE__);
		echo "
		<br />
		<table width='700' cellpadding='4' cellspacing='1' bgcolor='#".BORDERCOLOR."'>
		<tr align='center'  bgcolor='#".BORDERCOLOR."'><td colspan='6'>
		<b>Laufende Wetten</b>
		</td></tr>
		<tr align='left'  bgcolor='#".BORDERCOLOR."'><td>
		<b>Wettstarter</b>
		</td><td>
		<b>Titel</b>
		</td><td>
		<b>Einsatz</b>
		</td><td>
		<b>Wetter</b>
		</td><td>
		<b>Gegner</b>
		</td><td>
		<b>Datum</b>
		</td></tr>";

		while($rs = $db->fetch($result)) {
			$sqli = "
			SELECT
				u.clan_tag,
				u.username,
				wt.seite
			FROM wetten_teilnehmer wt
			LEFT JOIN user u
				ON u.id = wt.user_id
			WHERE wt.wetten_id = $rs[id]";
			$resulti = $db->query($sqli,__FILE__,__LINE__);
			while ($rsi = $db->fetch($resulti)) {
				if($rsi['seite'] == "wetter") {
					array_push($wetter, $rsi['clan_tag'].$rsi['username']);
					//$wetter .= " ".$rsi['clan_tag'].$rsi['username'];
				} else {
					array_push($gegner, $rsi['clan_tag'].$rsi['username']);
					//$gegner .= " ".$rsi['clan_tag'].$rsi['username'];
				}
			}

			echo "
			<tr bgcolor='#".TABLEBACKGROUNDCOLOR."'><td>
			".$rs['clan_tag'].$rs['username']."
			</td><td>
			<a href='?id=".$rs['id']."'>".stripslashes($rs['titel'])."</a>
			</td><td>
			".stripslashes($rs['einsatz'])."
			</td><td>";
			
			echo ($wetter = implode(", ", $wetter));
			
			echo "
			</td><td>";
			
			echo ($gegner = implode(", ", $gegner));
			
			echo "
			</td><td>
			".datename($rs['datum'])."
			</td></tr>";
		}

		echo "
		</table>";
	}
	
	
	function listclosed() {
		global $db;
		
		$wetter = array();
		$gegner = array();
		
		$sql = "
		SELECT
			w.*,
			u.clan_tag,
			u.username,
			UNIX_TIMESTAMP(w.datum) as datum
		FROM wetten w
		LEFT JOIN user u
			ON u.id = w.user_id
		WHERE w.status = 'geschlossen'
		ORDER by w.datum DESC";
		$result = $db->query($sql,__FILE__,__LINE__);
		echo "
		<br />
		<table width='700' cellpadding='4' cellspacing='1' bgcolor='#".BORDERCOLOR."'>
		<tr align='center'  bgcolor='#".BORDERCOLOR."'><td colspan='6'>
		<b>Geschlossen Wetten</b>
		</td></tr>
		<tr align='left'  bgcolor='#".BORDERCOLOR."'><td>
		<b>Wettstarter</b>
		</td><td>
		<b>Titel</b>
		</td><td>
		<b>Einsatz</b>
		</td><td>
		<b>Wetter</b>
		</td><td>
		<b>Gegner</b>
		</td><td>
		<b>Datum</b>
		</td></tr>";

		while($rs = $db->fetch($result)) {
			$sqli = "
			SELECT
				u.clan_tag,
				u.username,
				wt.seite
			FROM wetten_teilnehmer wt
			LEFT JOIN user u
				ON u.id = wt.user_id
			WHERE wt.wetten_id = $rs[id]";
			$resulti = $db->query($sqli,__FILE__,__LINE__);
			while ($rsi = $db->fetch($resulti)) {
				if($rsi['seite'] == "wetter") {
					array_push($wetter, $rsi['clan_tag'].$rsi['username']);
					//$wetter .= " ".$rsi['clan_tag'].$rsi['username'];
				} else {
					array_push($gegner, $rsi['clan_tag'].$rsi['username']);
					//$gegner .= " ".$rsi['clan_tag'].$rsi['username'];
				}
			}

			echo "
			<tr bgcolor='#".TABLEBACKGROUNDCOLOR."'><td>
			".$rs['clan_tag'].$rs['username']."
			</td><td>
			<a href='?id=".$rs['id']."'>".stripslashes($rs['titel'])."</a>
			</td><td>
			".stripslashes($rs['einsatz'])."
			</td><td>";
			
			echo ($wetter = implode(", ", $wetter));
			
			echo "
			</td><td>";
			
			echo ($gegner = implode(", ", $gegner));
			
			echo "</td><td>
			".datename($rs['datum'])."
			</td></tr>";
		}

		echo "
		</table>";
	}
	

	function newform() {

		echo "
		<br />
		<form action='$_SERVER[PHP_SELF]' method='post'>
		<table class='border'>
		<tr><td>
		<b>Neue Wette eintragen</b><br />
		<br />
		<small>
		Eine Wette wird erst gestartet<br />
		 wenn der Wetter (das bist du, wenn <br />
		 du eine Wette einträgst) die Wette startet.<br />
		</small>
		<br />
		</td></tr>
		<tr><td>
		<b>Wett Titel:<b> <br />
		<input type='text' name='titel' class='text' size='40'>
		</td></tr><tr><td>
		<b>Wette: </b><br />
		<textarea name='wette' cols='40' rows='5' class='text'></textarea>
		</td></tr><tr><td>
		<b>Wetteinsatz: </b><br />
		<textarea name='einsatz' cols='40' rows='3' class='text'></textarea>
		</td></tr><tr><td>
		<b><small>Gültigkeit (in Tagen ab Wettstart, 0 steht für unbegrenzt):</small></b>
		<br />
		<input type='text' name='dauer' class='text' size='4'>
		</td></tr><tr><td>
		<br />
		<small>
		Eine Wette ist beendet wenn, beide Parteien <br />
		sich auf einen Sieg oder eine Niederlage<br />
		 einigen können.<br />
		</small>
		<br />
		<input type='submit' value='Wette eintragen' class='button'>
		</td></tr>
		</table>
		</form>";

	}

	function get_wette ($id) {
		global $db, $user;
		
		$wetter = array();
		$gegner = array();
		$html = "";
		
		$sql = "
		SELECT *
			,UNIX_TIMESTAMP(datum) as datum
			,UNIX_TIMESTAMP(start) as startdatum
			,UNIX_TIMESTAMP(ende) as enddatum
			,UNIX_TIMESTAMP(geschlossen) as geschlossen
		FROM wetten
		WHERE id = '$id'";
		if(!$rs = $db->fetch($db->query($sql,__FILE__,__LINE__)))
		{
			die("<h2><font color='red'>Diese Wette gibts nicht!</font></h2>
				<a href='wetten.php'>&lt;&lt; Zur&uuml;ck</a>");
		}
		else
		{
			
			$sqli = "
			SELECT *
			FROM wetten_teilnehmer
			WHERE wetten_id = $rs[id]";
			$resulti = $db->query($sqli,__FILE__,__LINE__);
			
			while ($rsi = $db->fetch($resulti)) {
				if($rsi['seite'] == "wetter") {
					array_push($wetter, usersystem::link_userpage($rsi['user_id']));
					//$wetter .= " ".$rsi['clan_tag'].$rsi['username'];
					if($rsi['user_id'] == $_SESSION['user_id']) {
						$wjoin = 1;
					}
				} else {
					array_push($gegner, usersystem::link_userpage($rsi['user_id']));
					//$gegner .= " ".$rsi['clan_tag'].$rsi['username'];
					if($rsi['user_id'] == $_SESSION['user_id']) {
						$gjoin = 1;
					}
				}
			}
	
			if($_SESSION['user_id'] != $rs['user_id'] && $user->typ != USER_NICHTEINGELOGGT) {
				if(!$gjoin && !$wjoin) {
					$gg = "<a href='?id=$id&do=gjoin'>join</a>";
					$ww = "<a href='?id=$id&do=wjoin'>join</a>";
				} else {
					if($gjoin) {
						$gg = "<a href='?id=$id&do=unjoin'>unjoin</a>";
						$ww = "";
					} else {
						$gg = "";
						$ww = "<a href='?id=$id&do=unjoin'>unjoin</a>";
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
			<h1>".stripslashes($rs['titel'])."</h1>
			</td></tr><tr><td valign=\"top\">
			<b>Wettnummer</b>
			</td><td>
			".$rs['id']."
			</td></tr><tr><td valign=\"top\">
			<b>Wettstarter</b>
			</td><td>
			".usersystem::link_userpage($rs['user_id'])."
			</td></tr><tr><td valign=\"top\">
			<b>Wetter</b>
			</td><td>
			";
			
			// Alle Wetter ausgeben
			$anzwetter = count($wetter);
			$html .= ($wetter = implode(", ", $wetter));
			
			$html .= ($anzwetter > 0 && $ww <> "") ? " | " : "";
			$html .= $ww."
			</td></tr><tr><td valign=\"top\">
			<b>Gegner</b>
			</td><td>
			";
			
			// Alle Wett-Gegner ausgeben
			$anzgegner = count($gegner);
			$html .= ($gegner = implode(", ", $gegner));
			
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
			$html .= ($rs['enddatum'] < time() && $rs['status'] != 'geschlossen') ? "<font color='red'><b>".date("d.m.Y", $rs['enddatum'])."</b></font>" : "<b>".date("d.m.Y", $rs['enddatum'])."</b>";
			$html .=  " (".$rs['dauer']." Tage ab ".date("d.m.Y", $rs['startdatum']).")
			</td></tr><tr><td valign=\"top\">
			<b>Status</b>
			</td><td>";
	
			
			switch ($rs['status'])
			{
				case 'offen':
					if($_SESSION['user_id'] == $rs['user_id'])
					{
						$html .= "
						<form action='$_SERVER[PHP_SELF]?id=$id' method='post'>
							<input type='hidden' name='start' value=1>
							<input type='hidden' name='dauer' value='$rs[dauer]'>
							<input type='submit' value='starten' class='button'>
						</form>";
					}
					else
					{
						$html .= $rs['status'];
					}
					break;
					
				case 'laeuft':
					if($_SESSION['user_id'] == $rs['user_id'])
					{
						
						$html .= "
						<form action='$_SERVER[PHP_SELF]?id=$id' method='post'>
							<input type='hidden' name='schliessen' value=1>
							<input type='submit' value='schliessen' class='button'>
						</form>";
					}
					else
					{
						$html .= $rs['status'];
					}
					break;
				
				case 'geschlossen':
					$html .= "<font color='green'><b>".$rs['status']." @ ".date("d.m.Y", $rs['geschlossen'])."</b></font>";
					break;
				
				default:
					$html .= $rs['status'];
			}
	
			$html .= "
			</td></tr>
			<tr><td colspan='2' align='center'>
			<a href='wetten.php'>&lt;&lt; Zur&uuml;ck</a>
			</td></tr>
			</table>";
			
			echo $html;
			
		}

	}
}

?>