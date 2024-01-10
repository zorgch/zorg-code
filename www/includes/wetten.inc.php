<?php
/**
 * zorg Wettbüro
 *
 * @package zorg\Wetten
 */

/**
 * File includes
 */
require_once __DIR__.'/config.inc.php';
require_once INCLUDES_DIR.'mysql.inc.php';
require_once INCLUDES_DIR.'usersystem.inc.php';

/**
 * Wettbüro Klasse
 *
 * @version 2.0
 * @since 1.0 `freiländer` Class added
 * @since 2.0 `IneX` Various modifications and refactorings
 */
class wetten
{
	/**
	 * Execute Wettbüro Actions.
	 * Stuff like adding a new Wette, join/unjoin a Wette, etc...
	 *
	 * @version 2.0
	 * @since 1.0 `freiländer` Method added
 	 * @since 2.0 `IneX` Various modifications and code / sql refactorings
	 */
	static function exec()
	{
		global $db, $user;

		if($user->is_loggedin())
		{
			$wetteId = (isset($wette) ? $wette : (filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?? 0)); // $_GET['id']
			$doAction = filter_input(INPUT_GET, 'do', FILTER_DEFAULT, FILTER_REQUIRE_SCALAR) ?? null; // $_GET['do']

			if (count($_POST) > 0)
			{
				$wettetitel = filter_input(INPUT_POST, 'titel', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? null; // $_POST['titel']
				$wette = filter_input(INPUT_POST, 'wette', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? null; // $_POST['wette']
				$wetteinsatz = filter_input(INPUT_POST, 'einsatz', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? null; // $_POST['einsatz']
				$wettedauer = filter_input(INPUT_POST, 'dauer', FILTER_VALIDATE_INT) ?? 0; // $_POST['dauer']
				$starten = filter_input(INPUT_POST, 'start', FILTER_VALIDATE_BOOLEAN) ?? false; // $_POST['start'] = "1"
				$schliessen = filter_input(INPUT_POST, 'schliessen', FILTER_VALIDATE_BOOLEAN) ?? false; // $_POST['schliessen'] = "1"

				/** Neue Wette eintragen */
				if($wettedauer > 0 && !empty($wette) && !empty($wetteinsatz) && !empty($wettetitel))
				{
					$sql = 'INSERT into wetten (wette, einsatz, user_id, datum, dauer, status, titel) VALUES (?, ?, ?, ?, ?, "offen", ?)';
					$neueWetteId = $db->query($sql, __FILE__, __LINE__, __METHOD__, [$wette, $wetteinsatz, $user->id, timestamp(true), $wettedauer, $wettetitel]);

					/** Teilnehmer eintrag */
					$sql = 'INSERT into wetten_teilnehmer (wetten_id, user_id, seite, datum) VALUES (?, ?, "wetter", ?)';
					$db->query($sql, __FILE__, __LINE__, __METHOD__, [$neueWetteId, $user->id, timestamp(true)]);

					/** Activity Eintrag auslösen */
					Activities::addActivity($user->id, 0, t('activity-neuewette', 'wetten', [ SITE_URL, $neueWetteId, $wettetitel ]), 'w');

					header('Location: '.getURL(false,false).'?eintrag=1');
					exit;
				}

				/** Wette starten */
				if ($starten === true && $wetteId > 0 && $wettedauer > 0)
				{
					$sql = 'UPDATE wetten SET status="laeuft", start=?, ende=ADDDATE(DATE(?),?) WHERE id=? AND user_id=?';
					$db->query($sql, __FILE__, __LINE__, __METHOD__, [timestamp(true), timestamp(true), $wettedauer, $wetteId, $user->id]);
				}

				/** Wette schliessen */
				elseif ($schliessen === true && $wetteId > 0)
				{
					$sql = 'UPDATE wetten SET status="geschlossen", geschlossen=? WHERE id=? AND user_id=?';
					$db->query($sql, __FILE__, __LINE__, __METHOD__, [timestamp(true), $wetteId, $user->id]);

					/** Activity Eintrag auslösen */
					Activities::addActivity($user->id, 0, t('activity-wette-done', 'wetten', [ SITE_URL, $wetteId ]), 'w');
				}
			}

			switch ($doAction)
			{
				/** Wette als Wetter joinen */
				case "wjoin":
					$sql = 'SELECT * FROM wetten_teilnehmer WHERE user_id=? AND wetten_id=?';
					$result = $db->query($sql, __FILE__, __LINE__, __METHOD__, [$user->id, $wetteId]);
					if($db->num($result) > 0)
					{
						$sql = 'INSERT INTO wetten_teilnehmer (wetten_id, user_id, seite, datum) VALUES (?, ?, "wetter", ?)';
						$db->query($sql, __FILE__, __LINE__, __METHOD__, [$wetteId, $user->id, timestamp(true)]);
					}
					break;

				/** wette unjoinen */
				case "unjoin":
					$sql = 'DELETE FROM wetten_teilnehmer WHERE user_id=? AND wetten_id=?';
					$db->query($sql, __FILE__, __LINE__, __METHOD__, [$user->id, $wetteId]);
					break;

				/** wette als gegner joinen */
				case "gjoin":
					$sql = 'SELECT * FROM wetten_teilnehmer WHERE user_id=? AND wetten_id=?';
					$result = $db->query($sql, __FILE__, __LINE__, __METHOD__, [$user->id, $wetteId]);
					if($db->num($result) > 0)
					{
						$sql = 'INSERT INTO wetten_teilnehmer (wetten_id, user_id, seite, datum) VALUES (?, ?, "gegner", ?)';
						$db->query($sql, __FILE__, __LINE__, __METHOD__, [$wetteId, $user->id, timestamp(true)]);
					}
					break;

			}
		}
	}

	/**
	 * Return a list of Wetten, based on Status.
	 *
	 * @version 1.0
	 * @since 1.0 `04.01.2024` `IneX` Method added
	 *
	 * @param string $status The status of relevant Wetten: "offen"(default), "laeuft", "geschlossen".
	 * @return void Echos HTML directly, no return value
	 */
	static function listwetten($status="offen")
	{
		global $db, $user;

		$alleWettstati = ['offen', 'laeuft', 'geschlossen'];
		$datumSpalte = 'Behauptet';

		if (in_array($status, $alleWettstati))
		{
			switch ($status)
			{
				case "laeuft":
					echo '<h2>Laufende Wetten</h2>';
					$datumSpalte = 'Läuft seit';
					break;

				case "geschlossen":
					echo '<h2>Abgeschlossene Wetten</h2>';
					$datumSpalte = 'Beendet';
					break;

				default: // same as "offen" (Default)
					echo '<h2>Offene Wetten</h2>';

			}

			/** Query all relevant Wetten and include Wetter & Gegner Counts */
			$sql = 'SELECT w.*, UNIX_TIMESTAMP(w.datum) as datum,
						(SELECT COUNT(*) FROM wetten_teilnehmer wt WHERE wt.wetten_id = w.id AND wt.seite = "wetter") AS anzahl_wetter,
						(SELECT COUNT(*) FROM wetten_teilnehmer wt WHERE wt.wetten_id = w.id AND wt.seite = "gegner") AS anzahl_gegner
					FROM wetten w WHERE w.status=? ORDER BY w.datum DESC';
			$result = $db->query($sql, __FILE__, __LINE__, __METHOD__, [$status]);

			if ($db->num($result) > 0)
			{
				echo '<table class="border">
						<thead class="title">
							<tr align="left">
								<td>Titel</td>
								<td class="hide-mobile">Einsatz</td>
								<td class="hide-mobile">Wettstarter</td>
								<td>Dafür</td>
								<td>Dagegen</td>
								<td>'.$datumSpalte.'</td>
							</tr>
						</thead>
						<tbody>';

				while($rs = $db->fetch($result))
				{
					$wettstarter = $user->userprofile_link($rs['user_id'], ['username' => true, 'clantag' => false, 'link' => true, 'pic' => false]);

					echo '<tr>';
					echo '<td>
							<a href="?id='.$rs['id'].'">'.stripslashes($rs['titel']).'</a>
						</td>';
					echo '<td>'.
							text_width(stripslashes($rs['einsatz']), 25, '&hellip;', true, true).
						'</td>';
					echo '<td>'.
							$wettstarter.
						'</td>';
					echo '<td>'.
							($rs['anzahl_wetter'] > 0 ? $rs['anzahl_wetter'] : '-').
						'</td>';
					echo '<td>'.
							($rs['anzahl_gegner'] > 0 ? $rs['anzahl_gegner'] : '-').
						'</td>';
					echo '<td>'.
							($status === 'offen' ? datename($rs['datum']) : timename($rs['datum'])).
						'</td>';
					echo '</tr>';
				}

				echo '</tbody>
				</table>';
			}
			/** Keine Wetten gefunden */
			else {
				echo '<p>Keine Wette gefunden.</p>';
			}
		}
		/** Ungültiger Wettstatus */
		else {
			echo '<p>Ungültiger Wettstatus abgefragt.</p>';
		}
	}

	/**
	 * Liste offener Wetten.
	 * Alle neuen/ungestarteten Wetten.
	 *
	 * @deprecated Use wetten::listwetten("offen") instead
	 *
	 * @version 1.1
	 * @since 1.0 `freiländer` Method added
	 * @since 1.1 `IneX` Code and SQL-Query optimized
	 *
	 * @return void
	 */
	static function listopen()
	{
		global $db, $user;

		$sql = 'SELECT w.*, UNIX_TIMESTAMP(w.datum) as datum FROM wetten w WHERE w.status = "offen" ORDER by w.datum DESC';
		$result = $db->query($sql, __FILE__, __LINE__, __METHOD__);

		if ($db->num($result) > 0)
		{
			echo '<h2>Offene Wetten</h2>
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
				/** Wetter & Gegner bei jedem Wette-Eintrag neu initialisieren */
				$wettstarter = intval($rs['user_id']);
				$wetter = [];
				$gegner = [];

				$sqli = 'SELECT wt.user_id, wt.seite FROM wetten_teilnehmer wt WHERE wt.wetten_id=?';
				$resulti = $db->query($sqli, __FILE__, __LINE__, __METHOD__, [$rs['id']]);
				while ($rsi = $db->fetch($resulti))
				{
					$username = $user->userprofile_link($rsi['user_id'], ['username' => true, 'clantag' => false, 'link' => true, 'pic' => false]);
					if($rsi['seite'] === 'wetter') {
						$wetter[] = $username;
					} else {
						$gegner[] = $username;
					}
					/** Lass uns ein paar SQL-Queries sparen im hierauf folgenden Code... */
					if (intval($rsi['user_id']) === $wettstarter) $wettstarter = $username;
				}

				echo '<tr bgcolor="'.TABLEBACKGROUNDCOLOR.'">';
				//echo '<td class="hide-mobile">'.(is_numeric($wettstarter) ? $user->userprofile_link($rs['user_id'], ['username' => true, 'clantag' => true, 'link' => true, 'pic' => false]) : $wettstarter).'</td>
				echo '<td>'.$wettstarter.'</td>
				<td><a href="?id='.$rs['id'].'">'.stripslashes($rs['titel']).'</a></td>
				<td>'.text_width(stripslashes($rs['einsatz']), 25, '&hellip;', true, true).'</td><td>';
				echo (count($wetter) > 0 ? implode(', ', (array)$wetter) : 'keine');
				echo '</td><td>';
				echo (count($gegner) > 0 ? implode(', ', (array)$gegner) : 'keine');
				echo '</td><td>'.datename($rs['datum']).'</td>';
				echo '</tr>';
			}

			echo '</table>';
		}
		/** Keine Wetten gefunden */
		else {
			if ($user->is_loggedin()) echo '<p>Keine offene Wette wo du noch mitwetten kannst.</p>';
			else echo 'Keine offene Wette gefunden. <a href="?showlogin=true">Logge dich ein</a> um eine anzureissen!</p>';

		}
	}

	/**
	 * Liste laufender Wetten.
	 * Alle gestarteten Wetten.
	 *
	 * @deprecated Use wetten::listwetten("laeuft") instead
	 *
	 * @version 1.1
	 * @since 1.0 `freiländer` Method added
	 * @since 1.1 `IneX` Code and SQL-Query optimized
	 *
	 * @return void
	 */
	static function listlaufende()
	{
		global $db, $user;

		$sql = 'SELECT w.*, UNIX_TIMESTAMP(w.datum) as datum FROM wetten w WHERE w.status = "laeuft" ORDER by w.datum DESC';
		$result = $db->query($sql, __FILE__ ,__LINE__, __METHOD__);

		if ($db->num($result) > 0)
		{
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
				/** Wetter & Gegner bei jedem Wette-Eintrag neu initialisieren */
				$wetter = array();
				$gegner = array();

				$sqli = 'SELECT wt.user_id, wt.seite FROM wetten_teilnehmer wt WHERE wt.wetten_id=?';
				$resulti = $db->query($sqli, __FILE__, __LINE__, __METHOD__, [$rs['id']]);
				while ($rsi = $db->fetch($resulti)) {
					$username = $user->userprofile_link($rsi['user_id'], ['username' => true, 'clantag' => true, 'link' => true, 'pic' => false]);
					if($rsi['seite'] === 'wetter') {
						array_push($wetter, $username);
					} else {
						array_push($gegner, $username);
					}
				}

				echo '<tr bgcolor="'.TABLEBACKGROUNDCOLOR.'">
				<td>'.$user->userprofile_link($rs['user_id'], ['username' => true, 'clantag' => true, 'link' => true, 'pic' => false]).'</td>
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
		/** Keine Wetten gefunden */
		else {
			echo '<p>Zur Zeit läuft gerade keine Wette mehr...</p>';
		}
	}

	/**
	 * Liste geschlossener Wetten.
	 * Alle alten/abgeschlossenen Wetten.
	 *
	 * @deprecated Use wetten::listwetten("geschlossen") instead
	 *
	 * @version 1.1
	 * @since 1.0 `freiländer` Method added
	 * @since 1.1 `IneX` Code and SQL-Query optimized
	 *
	 * @return void
	 */
	static function listclosed()
	{
		global $db, $user;

		$sql = 'SELECT w.*, UNIX_TIMESTAMP(w.datum) as datum FROM wetten w WHERE w.status = "geschlossen" ORDER by w.datum DESC';
		$result = $db->query($sql, __FILE__, __LINE__, __METHOD__);

		if ($db->num($result) > 0)
		{
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
				/** Wetter & Gegner bei jedem Wette-Eintrag neu initialisieren */
				$wetter = array();
				$gegner = array();

				$sqli = 'SELECT wt.user_id, wt.seite FROM wetten_teilnehmer wt WHERE wt.wetten_id=?';
				$resulti = $db->query($sqli, __FILE__, __LINE__, __METHOD__, [$rs['id']]);
				while ($rsi = $db->fetch($resulti)) {
					$username = $user->userprofile_link($rsi['user_id'], ['username' => true, 'clantag' => true, 'link' => true, 'pic' => false]);
					if($rsi['seite'] === 'wetter') {
						array_push($wetter, $username);
					} else {
						array_push($gegner, $username);
					}
				}

				echo '<tr bgcolor="'.TABLEBACKGROUNDCOLOR.'">
						<td>'.$user->userprofile_link($rs['user_id'], ['username' => true, 'clantag' => true, 'link' => true, 'pic' => false]).'</td>
						<td><a href="?id='.$rs['id'].'">'.stripslashes($rs['titel']).'</a></td>
						<td>'.text_width(stripslashes($rs['einsatz']), 25, '&hellip;', true, true).'</td><td>';
						echo (count($wetter) > 0 ? implode(', ', (array)$wetter) : 'keine');
						echo '</td><td>';
						echo (count($gegner) > 0 ? implode(', ', (array)$gegner) : 'keine');
						echo '</td><td>'.datename($rs['datum']).'</td>
					</tr>';
			}

			echo '</table>';
		}
		/** Keine Wetten gefunden */
		else {
			echo '<p>Keine fertige Wette gefunden.</p>';
		}
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

		$sql = 'SELECT *
					,UNIX_TIMESTAMP(datum) as datum
					,UNIX_TIMESTAMP(start) as startdatum
					,UNIX_TIMESTAMP(ende) as enddatum
					,UNIX_TIMESTAMP(geschlossen) as geschlossen
				FROM wetten
				WHERE id=?';
		if(!$rs = $db->fetch($db->query($sql, __FILE__, __LINE__, __METHOD__, [$id])))
		{
			/** Wette nicht gefunden */
			$smarty->assign('error', ['type' => 'warn', 'dismissable' => 'false', 'title' => 'Diese Wette gibts nicht!', 'message' => '<a class="tiny" href="wetten.php">&lt;&lt; Zur&uuml;ck</a>']);
			http_response_code(404); // Set response code 404 (not found) and exit.
			$smarty->display('file:layout/elements/block_error.tpl');
			exit;
		}
		else {
			/** Wette gefunden - Details & Daten abfragen */
			$sqli = 'SELECT * FROM wetten_teilnehmer WHERE wetten_id=?';
			$resulti = $db->query($sqli,__FILE__,__LINE__,__METHOD__,[$rs['id']]);

			while ($rsi = $db->fetch($resulti))
			{
				if($rsi['seite'] == "wetter") {
					array_push($wetter, $user->userprofile_link($rsi['user_id'], ['username' => true, 'clantag' => false, 'link' => true, 'pic' => false]));
					if($user->is_loggedin() && $rsi['user_id'] === $user->id) $wjoin = true;
				} else {
					array_push($gegner, $user->userprofile_link($rsi['user_id'], ['username' => true, 'clantag' => false, 'link' => true, 'pic' => false]));
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
			<h1>&laquo;".stripslashes($rs['titel'])."&raquo; (Wette&nbsp;#".$rs['id'].")</h1>
			<table class=\"shadedcells\" cellpadding=\"10\">
			<tr><td valign=\"top\"
				<b>Wettstarter</b>
			</td><td colspan=\"2\">
				".$user->userprofile_link($rs['user_id'], ['username' => true, 'clantag' => true, 'link' => true, 'pic' => false])."
			</td></tr><tr><td valign=\"top\">
				<b>Wette</b>
			</td><td colspan=\"2\">
				".nl2br(stripslashes($rs['wette']))."
			</td></tr><tr><td valign=\"top\">
				<b>Einsatz</b>
			</td><td colspan=\"2\">
				".nl2br(stripslashes($rs['einsatz']))."
			</td></tr><tr>
				<td></td>
				<td valign=\"top\">
					<p><b>Wetten <i>DAFÜR</i></b></p>
			";//</td><td>

			// Alle Wetter ausgeben
			$anzwetter = count($wetter);
			$html .= ($wetter = implode(', ', $wetter));

			$html .= ($anzwetter > 0 && $ww <> "") ? " | " : "";
			$html .= $ww."
				</td>
				<td valign=\"top\">
					<p><b>Wetten <i>DAGEGEN</i></b></p>
			";//</td><td>

			// Alle Wett-Gegner ausgeben
			$anzgegner = count($gegner);
			$html .= ($gegner = implode(', ', $gegner));

			$html .= ($anzgegner > 0 && $gg <> "") ? " | " : "";
			$html .= $gg."
			</td></tr><tr><td valign=\"top\">
				<b>Ende</b>
			</td><td colspan=\"2\">";
				if ($rs['status'] === 'laeuft' || $rs['status'] === 'geschlossen') $html .= ($rs['enddatum'] < time() && $rs['status'] != 'geschlossen') ? "<font color='red'><b>".timename($rs['enddatum'])."</b></font>" : "<b>".timename($rs['enddatum'])."</b>";
			$html .=  " (".$rs['dauer']." Tage".($rs['status'] === 'laeuft' || $rs['status'] === 'geschlossen' ? " ab ".datename($rs['startdatum']) : ' Dauer geplant').")
			</td></tr><tr><td valign=\"top\">
				<b>Status</b>
			</td><td colspan=\"2\">";

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

			$html .= '<h3>Standpunkte verargumentieren</h3>'; // CommentingSystem Title

			echo $html;

			// Wetten Commenting ------------------------------------------
			Forum::printCommentingSystem('w', $id);
			// End Commenting -------------------------------------------
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
		$sql = 'SELECT titel FROM wetten WHERE id=? LIMIT 1';
		$rs = $db->fetch($db->query($sql, __FILE__, __LINE__, __METHOD__, [$wetteId]));
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
		$sql = 'SELECT user_id FROM wetten WHERE id=? LIMIT 1';
		$rs = $db->fetch($db->query($sql, __FILE__, __LINE__, __METHOD__, [$wetteId]));
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
		$sql = 'SELECT wette FROM wetten WHERE id=? LIMIT 1';
		$rs = $db->fetch($db->query($sql, __FILE__, __LINE__, __METHOD__, [$wetteId]));
		if (!$rs) return false;
		else return $rs['wette'];
	}
}
