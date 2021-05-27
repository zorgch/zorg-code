<?php
/**
 * Shoot the Lamber (Game)
 *
 * Shoot The Lamber ist ein Schiffchen-Versenken-Klon auf Zorg
 *	mySQL Tables:

	Haupttable:
		stl:
			game_id (primary key)
				spiel nummer
			game_size (max. 23, min. 5)
				spielfeld grösse Anzahl x Anzahl
			status int
				0 = wurde erstellt, spieler werden gesucht
				1 = läuft
				2 = beendet
			winner_team int
				0 = team red
				1 = team blue
			creater_id
				userID des spielerstellers (spiel-admin)
			num_players (min. 6, max. 24)
				anzahl spieler
			game_title
				spielname

	Spieler Table:
		stl_players:
			user_id
				user ID aus der user table
			team_id
				team id bei dem der spieler mitglied ist.
			game_id
				spiel nummer
			last_shoot
				datum an dem der spieler zuletzt geschossen hat.

	Schiffs- und treffer positionen
		stl_positions:
			pos_id (primary key)
				positions id
			game_id
				spiel nummer
			grid_x
				x koordinate
			grid_y
				y koordinate
			hit_user_id
				spieler id von dem hier ein torpedo gekommen ist, 0 bedeut kein schuss bis jetzt
			hit_team_id
				team_id vom topedo ;-)
			ship_user_id
				spieler id vom besitzer des schiffs, 0 bedeutet kein schiff
			ship_team_id
				team_id vom besitzer des schiffs
			shoot_date
				datum an dem der spieler das torpedo geschossen hat.
 *
 * @author [z]milamber
 * @package zorg\Games\STL
 */
/**
 * File includes
 * @include config.inc.php
 * @include mysql.inc.php
 * @include usersystem.inc.php
 * @include util.inc.php
 */
require_once dirname(__FILE__).'/config.inc.php';
require_once INCLUDES_DIR.'mysql.inc.php';
require_once INCLUDES_DIR.'usersystem.inc.php';
include_once INCLUDES_DIR.'util.inc.php';


/**
 * Shoot The Lamber Klasse
 *
 * @author [z]milamber
 * @version 1.0
 * @package zorg\Games\STL
 */
class stl {

	/**
	 * Shoot The Lamber Spielfeld
	 * Klassenkonstruktor, generiert autom. die ganze ausgabe...(Game & Overview)
	 *
	 * @author [z]milamber
	 *
	 * @global object $db Globales Class-Object mit allen MySQL-Methoden
	 * @global object $user Globales Class-Object mit den User-Methoden & Variablen
	 * @return stl
	 */
	function __construct() {
		global $db, $user;
		//Feldchengrösse
		$this->case = 20;
		//Posts
		$this->exec();

		/** Legende */
		$this->data['legende'] = '<br><br><br>
		<table><tr><td align="center" style="text-align: center" colspan="2">
		<h3>Legende</h3>
		</td></tr><tr><td align="left">
			<table><tr>
			<td bgcolor="#00FF00" style="width:'.$this->case.'px;height:'.$this->case.'px; text-align: center;">
			<b style="font-size:14px;"><a href="#">^</a></b>
			</td></tr></table>
			<table><tr>
			<td bgcolor="#FFFF00" style="width:'.$this->case.'px;height:'.$this->case.'px; text-align: center;">
			<b style="font-size:14px;"><a href="#">^</a></b>
			</td></tr></table>
		</td><td align="left">
		Das Feld mit der vollen Team Farbe (Grün oder Gelb) ist deine eigene Position
		</td></tr><tr><td align="left">
			<table><tr>
			<td bgcolor="#CCFFCC" style="width:'.$this->case.'px;height:'.$this->case.'px; text-align: center;">
			<b style="font-size:14px;"><a href="#">^</a></b>
			</td></tr></table>
			<table><tr>
			<td bgcolor="#FFFFCC" style="width:'.$this->case.'px;height:'.$this->case.'px; text-align: center;">
			<b style="font-size:14px;"><a href="#">^</a></b>
			</td></tr></table>
		</td><td align="left">
		Die Felder mit der blassen Team Farbe sind deine Teammitglieder
		</td></tr><tr><td align="left">
			<table><tr>
			<td bgcolor="#000000" style="width:'.$this->case.'px;height:'.$this->case.'px; text-align: center;">
			<b style="font-size:14px;"><a href="#">^</a></b>
			</td></tr></table>
		</td><td align="left">
		Schwarz sind deine Torpedos, die _nicht_ getroffen haben
		</td></tr><tr><td align="left">
			<table><tr>
			<td bgcolor="#666666" style="width:'.$this->case.'px;height:'.$this->case.'px; text-align: center;">
			<b style="font-size:14px;"><a href="#">^</a></b>
			</td></tr></table>
		</td><td align="left">
		Grau sind Torpedos von deinen Teammitgliedern die _nicht_ getroffen haben
		</td></tr><tr><td align="left">
			<table><tr>
			<td bgcolor="#FF0000" style="width:'.$this->case.'px;height:'.$this->case.'px; text-align: center;">
			<b style="font-size:14px;"><a href="#">^</a></b>
			</td></tr></table>
		</td><td align="left">
		Rot sind deine Treffer
		</td></tr><tr><td align="left">
			<table><tr>
			<td bgcolor="#FFCCCC" style="width:'.$this->case.'px;height:'.$this->case.'px; text-align: center;">
			<b style="font-size:14px;"><a href="#">^</a></b>
			</td></tr></table>
		</td><td align="left">
		Blasses Rot sind Treffer deiner Teammitglieder
		</td></tr><tr><td align="left">
			<table><tr>
			<td bgcolor="#0000FF" style="width:'.$this->case.'px;height:'.$this->case.'px; text-align: center;">
			<b style="font-size:14px;"><a href="#">^</a></b>
			</td></tr></table>
		</td><td align="left">
		Volles Blau sind erfolglose Zielversuche vom Feind
		</td></tr><tr><td align="left">
			<table><tr>
			<td bgcolor="'.MENUCOLOR1.'" style="width:'.$this->case.'px;height:'.$this->case.'px; text-align: center;">
			<b style="font-size:14px;"><a href="#">^</a></b>
			</td></tr></table>
			<table><tr>
			<td bgcolor="'.MENUCOLOR2.'" style="width:'.$this->case.'px;height:'.$this->case.'px; text-align: center;">
			<b style="font-size:14px;"><a href="#">^</a></b>
			</td></tr></table>
		</td><td align="left">
		Meerfarbene Felder mit einer Namensabkürzung kennzeichnen gesunkene Teammitglieder
		</td></tr></table>';

		if(!empty($_GET['game_id']) && $_GET['game_id'] > 0) {
			$sql = 'SELECT * FROM stl WHERE game_id = '.$_GET['game_id'];
			$result = $db->query($sql,__FILE__,__LINE__,__METHOD__);

			/** Game-ID exists (Game can be loaded) */
			if ($db->num($result) == 1 && $user->is_loggedin()) {
				$this->data['stl'] = $db->fetch($result);

				$sql = 'SELECT
							team_id
						FROM stl_players
						WHERE
							user_id = '.$user->id.'
							AND
							game_id = '.$this->data['stl']['game_id'];
				$result = $db->query($sql,__FILE__,__LINE__,__METHOD__);
				$rs = $db->fetch($result);
				$this->data['team_id'] = $rs['team_id'];

				$this->data['msg'][1] = 'Kommandant, ein Rettungsboot hat keine Torpedos!';
				$this->data['msg'][2] = 'Kommandant, dieses Ziel ist uninteressant!';
				$this->data['msg'][3] = 'Torpedos geladen und bereit!';
				$this->data['msg'][4] = 'Kommandant, man schiesst nicht auf die eigenen Leute!';
				$this->data['msg'][5] = 'Kommandant, Sie müssen warten bis die Torpedorohre nachgeladen sind!';
				$this->data['msg'][6] = 'Ihre Mannschaft lädt gerade die Torpedorohre, %s wieder schiessbereit!';
				$this->data['msg'][7] = 'Kommandant, die Schlacht ist vorbei!';

				$this->game();

			/** Invalid Game-ID - Game couldn't be loaded */
			} else {
				user_error(t('error-game-invalid', 'global', $_GET['game_id']), E_USER_NOTICE);
			}

		/** No $_GET['game_id'] Game-ID passed */
		} else {
			$this->overview();
			//user_error(t('error-game-invalid', 'global', $_GET['game_id']), E_USER_NOTICE);
		}
	}


	/**
	 * STL Teams
	 * Liest die Teams zu einem Spiel und speichert die in klassen vars
	 *
	 * @global object $db Globales Class-Object mit allen MySQL-Methoden
	 * @global object $user Globales Class-Object mit den User-Methoden & Variablen
	 * @return void
	 */
	function teams() {
		global $db, $user;

		$sql = 'SELECT
					stl_players.user_id as user_id,
					stl_players.team_id as team_id,
					stl_positions.*
				FROM stl_players
					INNER JOIN stl_positions
						ON stl_players.user_id = stl_positions.ship_user_id
				WHERE
					stl_players.game_id = '.$this->data['stl']['game_id'].'
					AND stl_positions.game_id = '.$this->data['stl']['game_id'].'
				ORDER BY hit_user_id ASC';
		$result = $db->query($sql,__FILE__,__LINE__,__METHOD__);

		while($rs = $db->fetch($result))
		{
			if($rs['team_id'] == 0) {
				$this->data['team_gelb'] .= sprintf('<%2$s>%1$s</%2$s><br>', $user->userprofile_link($rs['user_id'], ['pic' => TRUE, 'username' => TRUE, 'clantag' => TRUE]), ($rs['hit_user_id'] > 0 ? 'del' : 'b'));
			} else {
				$this->data['team_gruen'] .= sprintf('<%2$s>%1$s</%2$s><br>', $user->userprofile_link($rs['user_id'], ['pic' => TRUE, 'username' => TRUE, 'clantag' => TRUE]), ($rs['hit_user_id'] > 0 ? 'del' : 'b'));
			}
		}
	}

	/**
	 * Prüfen ob Spieler bereits gejoined
	 * Ermittelt ob ein Spieler bei einem Spiel bereits mitspielt (wenn nein, spielt er JETZT mit)
	 *
	 * @global object $db Globales Class-Object mit allen MySQL-Methoden
	 * @global object $user Globales Class-Object mit den User-Methoden & Variablen
	 * @return void
	 */
	function check4join() {
		global $db, $user;
		$sql = 'SELECT
					game_id
				FROM stl
				WHERE status = 0
				AND game_id = '.$this->data['stl']['game_id'];
		$result = $db->query($sql,__FILE__,__LINE__,__METHOD__);
		if($db->num($result)) {
			$sql = 'SELECT
						user_id
					FROM stl_players
					WHERE
						game_id = '.$this->data['stl']['game_id'].'
						AND user_id = '.$user->id;
			$result = $db->query($sql);
			//wenn spieler noch nicht eingetragen ist
			if(!$db->num($result)) {
				$sql = 'INSERT into stl_players (user_id, game_id)
						VALUES ('.$user->id.','.$this->data['stl']['game_id'].')';
				$db->query($sql,__FILE__,__LINE__,__METHOD__);

				/** Activity Eintrag auslösen */
				Activities::addActivity($user->id, 0, t('activity-joingame', 'stl', [ SITE_URL, $this->data['stl']['game_id'], $this->config['game_title'] ]), 'sl');
			}
		}
	}

	/**
	 * Prüfen ob Spielende möglich
	 * Prüft ob ein Spiel beendet werden kann und ermittelt das Gewinner Team
	 *
	 * @global object $db Globales Class-Object mit allen MySQL-Methoden
	 * @global object $user Globales Class-Object mit den User-Methoden & Variablen
 	 * @return void
	 */
	function check4finish() {
		global $db, $user;

		$sql = 'SELECT
					pos_id
				FROM stl_positions
				WHERE
					game_id = '.$this->data['stl']['game_id'].'
					AND
					ship_team_id = 1
					AND
					hit_team_id = 0
					AND
					ship_user_id <> 0
					AND
					hit_user_id <> 0';
		$win_team_gelb = $db->query($sql,__FILE__,__LINE__,__METHOD__);

		$sql = 'SELECT
					pos_id
				FROM stl_positions
				WHERE
					game_id = '.$this->data['stl']['game_id'].'
					AND
					ship_team_id = 0
					AND
					hit_team_id = 1
					AND
					ship_user_id <> 0
					AND
					hit_user_id <> 0';
		$win_team_gruen = $db->query($sql,__FILE__,__LINE__,__METHOD__);

		if($db->num($win_team_gelb) == ($this->data['stl']['num_players'] / 2)) {
			/*$sql = 'UPDATE stl
						set
						status = 2,
						winner_team = 0
					WHERE
						game_id = '.$_GET['game_id'];
			$db->query($sql,__FILE__,__LINE__,__METHOD__);*/
			if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> $game_id %d: %s', __METHOD__, __LINE__, $this->data['stl']['game_id'], t('activity-won-gelb', 'stl', [ SITE_URL, $this->data['stl']['game_id'], $this->config['game_title'] ])));
			$result = $db->update('stl', ['game_id', $this->data['stl']['game_id']], ['status' => 2, 'winner_team' => 0],__FILE__,__LINE__,__METHOD__);

			/** Activity Eintrag auslösen */
			if (!empty($result) && $result > 0) Activities::addActivity($user->id, 0, t('activity-won-gelb', 'stl', [ SITE_URL, $this->data['stl']['game_id'], $this->config['game_title'] ]), 'sl');
		}
		if($db->num($win_team_gruen) == ($this->data['stl']['num_players'] / 2)) {
			/*$sql = 'UPDATE stl
						set
						status = 2,
						winner_team = 1
					WHERE
						game_id = '.$_GET['game_id'];
			$db->query($sql,__FILE__,__LINE__,__METHOD__);*/
			if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> $game_id %d: %s', __METHOD__, __LINE__, $this->data['stl']['game_id'], t('activity-won-gruen', 'stl', [ SITE_URL, $this->data['stl']['game_id'], $this->config['game_title'] ])));
			$result = $db->update('stl', ['game_id', $this->data['stl']['game_id']], ['status' => 2, 'winner_team' => 1],__FILE__,__LINE__,__METHOD__);

			/** Activity Eintrag auslösen */
			if (!empty($result) && $result > 0) Activities::addActivity($user->id, 0, t('activity-won-gruen', 'stl', [ SITE_URL, $this->data['stl']['game_id'], $this->config['game_title'] ]), 'sl');
		}
	}

	/**
	 * Prüft ein Spiel auf Spielstart
	 *
	 * Prüft ob ein Spiel gestartet werden kann, erstellt grid und weisst die Spieler zufällig einem Team und einem Feld zu
	 *
	 * @uses timestamp()
	 * @return void
	 */
	function check4start()
	{
		global $db;

		$sql = 'SELECT count(user_id) as num FROM stl_players WHERE game_id = '.$this->data['stl']['game_id'];
		$result = $db->query($sql,__FILE__,__LINE__,__METHOD__);
		$rs = $db->fetch($result);
		if($rs['num'] == $this->config['num_players']) {
			$sql = 'SELECT *
					FROM stl_players
					WHERE game_id = '.$this->data['stl']['game_id'];
			$result = $db->query($sql,__FILE__,__LINE__,__METHOD__);
			while($rs = $db->fetch($result)) {
				$players[] = $rs['id'];
			}
			shuffle($players);
			//for($i=0;$i<=count($players);$i++) {
			$i = 0;
			foreach ($players as $player_index)
			{
				$team = ($i % 2);
				$shoot_date = timestamp(true, time()-5000);
				$db->update('stl_players', ['id', $player_index], ['team_id' => $team, 'last_shoot' => $shoot_date],__FILE__,__LINE__,__METHOD__);
				$i++;
			}

			$sql = 'SELECT * FROM stl_players WHERE game_id = '.$this->data['stl']['game_id'];
			$result = $db->query($sql,__FILE__,__LINE__,__METHOD__);
			while($rs = $db->fetch($result)) {

				//grid array erstellen
				$grid_x_array = range(1,$this->config['game_size']);
				$grid_y_array = range(1,$this->config['game_size']);

				//x position
				$num = 1;
				$rand_x = array();
				while($num == 1) {
					srand(microtime()*1000000);
					$rand = rand(0,$this->config['game_size'] - 1);
					if(!array_search($rand,$rand_x)) {
						$rand_x[] = $rand;
						$grid_x = $grid_x_array[$rand];
						$num = 0;
					}
				}
				//y position
				$num = 1;
				$rand_y = array();
				while($num == 1) {
					srand(microtime()*1000000);
					$rand = rand(0,$this->config['game_size'] - 1);
					if(!array_search($rand,$rand_y)) {
						$rand_y[] = $rand;
						$grid_y = $grid_y_array[$rand];
						$num = 0;
					}
				}
				//position
				$sql = 'INSERT INTO stl_positions (game_id, grid_x, grid_y, ship_user_id, shoot_date)
						VALUES ('.$this->data['stl']['game_id'].','.$grid_x.','.$grid_y.','.$rs['user_id'].', NOW())';
				$db->query($sql,__FILE__,__LINE__,__METHOD__);

			}

			/** team_id in positions table schreiben */
			$sql = 'SELECT
						team_id,
						game_id,
						user_id
					FROM stl_players
					WHERE game_id = '.$this->data['stl']['game_id'];
			$result = $db->query($sql,__FILE__,__LINE__,__METHOD__);
			while($rs = $db->fetch($result))
			{
				$sql = 'UPDATE stl_positions
						SET
							ship_team_id = '.$rs['team_id'].'
						WHERE
							ship_user_id = '.$rs['user_id'].'
							AND game_id = '.$this->data['stl']['game_id'];
				$db->query($sql,__FILE__,__LINE__,__METHOD__);
			}

			/** game starten */
			$sql = 'UPDATE stl set status = 1 WHERE game_id = '.$this->data['stl']['game_id'];
			$db->query($sql,__FILE__,__LINE__,__METHOD__);

			//header("Location: http://".$_SERVER['SERVER_NAME'].$_SERVER['PHP_SELF']."?do=game&game_id=$_GET[game_id]&".session_name()."=".session_id());
			header('Location: '.base64_decode(getURL(false)).'?do=game&game_id='.$this->data['stl']['game_id']);
			exit;
		}
	}

	/**
	 * STL Spiel
	 * Hauptfunktion, erstellt das gesamte Spielfeld und drum herum
	 *
	 * @global object $db Globales Class-Object mit allen MySQL-Methoden
	 * @global object $user Globales Class-Object mit den User-Methoden & Variablen
	 * @return void
	 */
	function game()
	{
		global $db, $user;
		$sql = 'SELECT * FROM stl WHERE game_id = '.$this->data['stl']['game_id'];
		$result = $db->query($sql,__FILE__,__LINE__,__METHOD__);
		$this->config = $db->fetch($result);

		if (!empty($this->config['game_title'])) $this->data['game'] .= '<h2>'.$this->config['game_title'].'</h2>';

		/** Joinstatus - spiel läuft noch nicht */
		if($this->config['status'] == 0)
		{
			//Prüfen ob der User bereits gejoint hat, wenn nicht wird gejoint
			$this->check4join();
			//Prüfen ob das game gestartet werden kann (genügend spieler)
			$this->check4start();

			$sql = 'SELECT user_id
					FROM stl_players
					WHERE game_id ='.$this->data['stl']['game_id'];
			$result = $db->query($sql,__FILE__,__LINE__,__METHOD__);

			$this->data['game'] .= '<div>';
			$this->data['game'] .= '<h4>Spieler bis jetzt:</h4>';
			$num = 0;
			while($rs = $db->fetch($result)) {
				$this->data['game'] .= $user->userprofile_link($rs['user_id'], ['pic' => TRUE, 'username' => TRUE, 'clantag' => TRUE]);
				$num++;
			}
			$this->data['game'] .= '<br><br>';
			$this->data['game'] .= '<div class="alert info"><strong>'.($this->config['num_players'] - $num).'/'.$this->config['num_players'].' Spieler fehlen noch...</strong></div>';
			$this->data['game'] .= '<small>Spiel wird bei vollständiger Spielerzahl automatisch gestartet.<br>Der Spieler wird zufällig einem Team und einer Position auf dem Spielfeld zugewiesen</small>';
			$this->data['game'] .= '</div>';

		}

		/** Team anzeige & grid wenn spiel läuft */
		if($this->config['status'] > 0)
		{
			//Prüfen ob das Spiel beendet werden kann (status<2) - wenn es noch nicht beendet ist (status=2)
			if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> STL-Game Status: %d', __METHOD__, __LINE__, $this->data['stl']['status']));
			if ($this->data['stl']['status'] < 2) $this->check4finish();

			//Teams zuweisung ausführen und daten generieren
			$this->teams();

			//Grid Infos
			$sql = 'SELECT
						stl_positions.grid_x as grid_x,
						stl_positions.grid_y as grid_y,
						stl_positions.hit_user_id as hit_user_id,
						stl_positions.hit_team_id as hit_team_id,
						stl_positions.ship_user_id as ship_user_id,
						stl_positions.ship_team_id as ship_team_id,
						stl_positions.shoot_date as shoot_date,
						stl_players.team_id as team_id,
						user.username as username
					FROM stl_positions
						LEFT JOIN stl_players
							ON
							stl_players.game_id = stl_positions.game_id
							AND
							stl_players.user_id = stl_positions.ship_user_id
						LEFT JOIN user
							ON
							user.id = stl_positions.ship_user_id
					WHERE
						stl_positions.game_id = '.$this->data['stl']['game_id'];
			$result = $db->query($sql,__FILE__,__LINE__,__METHOD__);
			//erstellung eines daten-arrays (ist einfachen beim erstellen des grids
			while($rs = $db->fetch($result)) {
				$this->data['game_data'][$rs['grid_y']][$rs['grid_x']] = $rs;
			}

			//Team ID zuweisungen
			$sql = 'SELECT
						team_id
					FROM stl_players
					WHERE
						game_id = '.$this->data['stl']['game_id'].'
						AND
						user_id = '.$user->id;
			$result = $db->query($sql);
			//team zuweisung
			if($db->num($result)) {
				$rs = $db->fetch($result);
				$this->team_id = $rs['team_id'];
			} else {
				$this->team_id = 2;
			}

			//Messages
			if($_GET['msg']) {
				//Bei übergabe
				$msg = $this->data['msg'][$_GET['msg']];
			//normalerweise
			} else {
				//Prüfen ob der Spieler getroffen wurde
				$sql = 'SELECT
							hit_user_id
						FROM stl_positions
						WHERE
							ship_user_id = '.$user->id.'
							AND
							game_id = '.$this->data['stl']['game_id'];
				$result = $db->query($sql,__FILE__,__LINE__,__METHOD__);
				$rs = $db->fetch($result);
				//Wenn der spieler noch im spiel ist
				if($rs['hit_user_id'] == 0) {
					/** Prüfen wann Seine Torpedos wieder geladen sind (kein query-result = schussbereit) */
					$sql = 'SELECT
								 game_id
								,UNIX_TIMESTAMP(last_shoot) as last_shoot
								,UNIX_TIMESTAMP(last_shoot+INTERVAL 1 HOUR) as next_shoot
							FROM stl_players
							WHERE
								game_id = '.$this->data['stl']['game_id'].'
								AND user_id = '.$user->id.'
								AND last_shoot > (NOW() - INTERVAL 1 HOUR)';
					$result = $db->query($sql,__FILE__,__LINE__,__METHOD__);

					/** Zuweisung der Message wenn geladen wird oder nicht */
					if ($db->num($result)) {
						/** Torpedorohre werden noch geladen - Schuss war vor < 1 Stunde */
						$shotData = $db->fetch($result);
						$time_to_next_shoot = timename($shotData['next_shoot']);//($result['next_shoot']-$result['last_shoot'])/60;
						$msg = sprintf($this->data['msg'][6], $time_to_next_shoot);
					} else {
						/** Torpedorohre sind geladen - Schuss war vor > 1 Stunde */
						$msg = $this->data['msg'][3];
					}

				//Wenn der Spieler abgeschossen wurde.
				} else {
					$msg = ($this->data['stl']['status'] < 2 ? $this->data['msg'][1] : $this->data['msg'][7]);
				}
			}
			//Prüfen ob das Spiel fertig ist.
			if($this->data['stl']['status'] == 2) {
				$winner = ($this->data['stl']['winner_team']) ? "Grün" : "Gelb";
				$this->data['game'] .= '<h3 style="text-align:center">Team '.$winner.' hat gewonnen!</h3><br><br><br><br>';
			}

			//Spielfeld & HTML
			$this->data['game'] .= '
			<table width="100%" cellpadding="4" cellspacing="1" class="border stl" align="center">
			<tr><td align="center" bgcolor="#FFFF00">
			<b>Team Gelb</b>
			</td>
			<td align="center" bgcolor="'.BORDERCOLOR.'" style="text-align: center;">
			<b>
			<b>'.$msg.'</b>
			</td><td align="center" bgcolor="#00FF00">
			<b>Team Grün</b>
			</td>
			</tr>
			<tr><td align="right" valign="top" bgcolor="#FFFFCC">
			'.$this->data['team_gelb'].'
			</td><td align="right" valign="middle" bgcolor="'.BORDERCOLOR.'">';


			//Spielfeld
			for($y = $this->data['stl']['game_size'];$y>=0;$y--)
			{
				$this->data['game'] .=  '
				<table cellpadding="5" cellspacing="0" align="center">
				<tr>';
				for($x = 1;$x<=$this->data['stl']['game_size'];$x++) {
					if($y != 0) {
						$this->add[3] = "^";
						if((($x+$y) % 2) == 1) {
							$this->add[0] = 'bgcolor="'.MENUCOLOR1.'"';
							$this->add[1] = '<sup>';
							$this->add[2] = '</sup>';
						} else {
							$this->add[0] = 'bgcolor="'.MENUCOLOR2.'"';
						}
						$links = ($this->data['stl']['status'] == 1 ? true : false);
						//=============================================================================
						//Eigene Position
						//=============================================================================
						if($this->data['game_data'][$y][$x]['ship_user_id'] == $user->id) {
							$this->add[0] = ($this->data['game_data'][$y][$x]['team_id'] ? 'bgcolor="#00FF00"' : 'bgcolor="#FFFF00"');
							$links = false;
						}

						//=============================================================================
						//Positionen an denen eigene torpedos erfolgreich detonierten
						//=============================================================================
						if(
						$this->data['game_data'][$y][$x]['hit_user_id'] == $user->id
						&& $this->data['game_data'][$y][$x]['ship_user_id'] != 0
						){
							$this->add[0] = 'bgcolor="#FF0000"';
							$this->add[3] = "<small>".substr($this->data['game_data'][$y][$x]['username'],0,2)."</small>";
							$links = false;
						}

						//=============================================================================
						//Positionen an denen befreundete torpedos erfolgreich detonierten
						//=============================================================================
						if(
						$this->data['game_data'][$y][$x]['hit_team_id'] == $this->data['team_id']
						&& $this->data['game_data'][$y][$x]['hit_user_id'] != 0
						&& $this->data['game_data'][$y][$x]['hit_user_id'] != $user->id
						&& $this->data['game_data'][$y][$x]['ship_team_id'] != $this->data['team_id']
						&& $this->data['game_data'][$y][$x]['ship_user_id'] != 0
						){
							$this->add[0] = 'bgcolor="#FFCCCC"';
							$this->add[3] = "<small>".substr($this->data['game_data'][$y][$x]['username'],0,2)."</small>";
							$links = false;
						}

						//=============================================================================
						//Positionen an denen eigene torpedos erfolglos detonierten
						//=============================================================================
						if(
						$this->data['game_data'][$y][$x]['hit_user_id'] == $user->id
						&& $this->data['game_data'][$y][$x]['ship_user_id'] == 0
						) {
							$this->add[0] = 'bgcolor="#000000"';
							$links = false;
						}

						//=============================================================================
						//Positionen an denen befreundete torpedos erfolglos detonierten
						//=============================================================================
						if(
						$this->data['game_data'][$y][$x]['hit_team_id'] == $this->data['team_id']
						&& $this->data['game_data'][$y][$x]['hit_user_id'] != $user->id
						&& $this->data['game_data'][$y][$x]['hit_user_id'] != 0
						&& $this->data['game_data'][$y][$x]['ship_user_id'] == 0
						) {
							$this->add[0] = 'bgcolor="#666666"';
							$links = false;
						}

						//=============================================================================
						//Positionen an denen feindliche torpedos erfolglos detonierten
						//=============================================================================
						if(
						$this->data['game_data'][$y][$x]['hit_team_id'] != $this->data['team_id']
						&& $this->data['game_data'][$y][$x]['hit_user_id'] != $user->id
						&& $this->data['game_data'][$y][$x]['hit_user_id'] != 0
						&& $this->data['game_data'][$y][$x]['ship_user_id'] == 0
						) {
							$this->add[0] = 'bgcolor="#0000FF"';
							$links = false;
						}

						//=============================================================================
						//Positionen an denen befreundete Schiffe position bezogen haben
						//=============================================================================
						if(
						$this->data['game_data'][$y][$x]['ship_user_id'] != $user->id
						&& $this->data['game_data'][$y][$x]['ship_user_id'] != ""
						&& $this->data['game_data'][$y][$x]['hit_user_id'] == 0
						&& $this->data['game_data'][$y][$x]['team_id'] == $this->team_id
						) {
							$this->add[0] = ($this->data['game_data'][$y][$x]['team_id'] ? "bgcolor='#CCFFCC'" : "bgcolor='#FFFFCC'");
							$this->add[3] = "<small>".substr($this->data['game_data'][$y][$x]['username'],0,2)."</small>";
							$links = true;
						}

						//=============================================================================
						//Positionen an denen befreundete Schiffe gesunken sind
						//=============================================================================
						if(
						$this->data['game_data'][$y][$x]['ship_user_id'] != $user->id
						&& $this->data['game_data'][$y][$x]['ship_user_id'] != ""
						&& $this->data['game_data'][$y][$x]['hit_user_id'] != 0
						&& $this->data['game_data'][$y][$x]['team_id'] == $this->team_id
						) {
							//$this->add[0] = 'bgcolor="#FFBAAB"';
							$this->add[3] = "<small>".substr($this->data['game_data'][$y][$x]['username'],0,2)."</small>";
							$links = false;
						}

						//Wenn links gesetzt werden sollen
						if($links == TRUE) {
							$this->data['game'] .= '<td '.$this->add[0].' onClick="document.location.href=\'?do=game&game_id='.$this->data['stl']['game_id'].'&shoot='.$x.','.$y.'\'" align="center" valign="middle" style="width:'.$this->case.'px;height:'.$this->case.'px; text-align: center;">
							<a href="?do=game&game_id='.$this->data['stl']['game_id'].'&shoot='.$x.','.$y.'" style="text-decoration: none;"  align="center">
							<b style="font-size:14px;">
							'.$this->add[1].$this->add[3].$this->add[2].'
							</b>
							</a>
							</td>';
						//Wenn auf dem Feld bereits was ist (keine links)
						} else {
							$this->data['game'] .= '
							<td '.$this->add[0].' align="center" valign="middle" style="width:'.$this->case.'px;height:'.$this->case.'px; text-align: center;">
							<b style="font-size:14px;">
							'.$this->add[1].$this->add[3].$this->add[2].'
							</b>
							</td>';
						}
					}
				}
				$this->data['game'] .= "</tr>";
			}
			$this->data['game'] .= '
			</table>
			</td>
			<td align="right" valign="top" bgcolor="#CCFFCC">
			'.$this->data['team_gruen'].'
			</td>
			</tr>
			</table>';
		}
	}

	/**
	* Spieleübersicht anzeigen
	* Übersichts funktion, zeigt alle offenen und joinbaren spiele eines users
	*
	* @author [z]milamber
	* @author IneX
	* @version 2.0
	* @since 1.0 function added
	* @since 2.0 `18.08.2018` overview() wird nur ausgegeben, wenn usersystem::islogged_in() = true
	*
	* @global object $db Globales Class-Object mit allen MySQL-Methoden
	* @global object $user Globales Class-Object mit den User-Methoden & Variablen
	* @return void
	*/
	function overview() {
		global $db, $user;

		if ($user->is_loggedin())
		{
			/** selektiert games bei denen ich mitmache */
			$sql = 'SELECT
						stl.game_id as game_id,
						stl.game_title as game_title,
						stl.game_size as game_size,
						stl.num_players as num_players,
						stl.status as status,
						user.username as username,
						HOUR(stl_players.last_shoot) as last_shoot,
						HOUR(now()) as akt
					FROM stl
						LEFT JOIN stl_players
							ON
							stl_players.game_id = stl.game_id
						LEFT JOIN user
							ON
							user.id = stl.creator_id
					WHERE
						stl_players.user_id = '.$user->id.'
						AND
						stl.status <> 2
					ORDER by stl.status DESC';
			$result = $db->query($sql,__FILE__,__LINE__,__METHOD__);

			/** wenns spiele gibt bei denen ich mitmachen */
			if($db->num($result)) {
				$this->data['overview'] = '<div align="center">
				<h3>Deine aktiven Spiele:<br><small style="font-weight:normal;">(hier spielst du mit)</small></h3>';

				while($rs = $db->fetch($result)) {
					if($rs['status'] == 1 && $rs['last_shoot'] != $rs['akt']) {
						$add = " style='color:#FF0000; font-weight:bold;'";
					} elseif($rs['status'] == 1) {
						$add = " style='font-weight:bold;'";
					} else {
						$add = "";
					}
					$this->data['overview'] .=
					"<a $add href='?do=game&amp;game_id=$rs[game_id]'>".strip_tags($rs['game_title'])." <small>(".$rs['game_size']." x ".$rs['game_size']."), ".$rs['num_players']."</small></a><br/>";
				}
				$this->data['overview'] .= "<br><br><br>";
			}

			/** selektiert games bei denen ich nicht mitmache und noch joinen kann */
			$sql = 'SELECT
						stl.game_id as game_id,
						stl.game_title as game_title,
						stl.game_size as game_size,
						stl.num_players as num_players,
						stl.status as status,
						user.username as username
					FROM stl
						LEFT JOIN stl_players
							ON
							stl_players.game_id = stl.game_id
						LEFT JOIN user
							ON
							user.id = stl.creator_id
					WHERE
						stl_players.user_id <> '.$user->id.'
						AND
						stl.status = 0';
			$result = $db->query($sql,__FILE__,__LINE__,__METHOD__);

			/** wenns games gibt wo ich nicht mitmache */
			if($db->num($result)) {
				$this->data['overview'] .= '<h3>Offene Spiele:<br><small style="font-weight:normal;">(hier könnt ihr noch joinen, klicken um zu joinen)</small></h3>';

				while($rs = $db->fetch($result)) {
					if($old != $rs['game_id']) {
						$this->data['overview'] .= '<a href="?do=game&amp;game_id='.$rs['game_id'].'">'.strip_tags($rs['game_title']).' <small>('.$rs['game_size'].' x '.$rs['game_size'].'), '.$rs['num_players'].'</small></a><br />';
					}
					$old = $rs['game_id'];
				}
				$this->data['overview'] .= '<br><br><br>';
			}

			$this->data['overview'] .= '
				<form action="'.base64_decode(getURL()).'" method="post">
					<table>
					<tr><td align="center" colspan="2">
						<h3>Neues Spiel starten</h3>
					</td></tr>
					<tr><td align="left">
						Spielname:
					</td><td align="left">
						<input type="text" name="game_title" size="20" class="text">
					</tr></td>
					<tr><td align="left">
						Anzahl Spieler:
					</td><td align="left" valign="top">
						<input type="text" name="num_players" size="4" class="text"> <sub>(min. 6, max. 24)</sub>
					</td></tr><tr><td align="left">
						Spielfeldgrösse:
					</td><td align="left" valign="top">
						<input type="text" name="game_size" size="4" class="text"> <sub>(min. 5, max. 23)</sub>
					</td></tr><tr><td align="center" colspan="2">
						<input type="submit" value="erstellen" class="button">
					</td></tr>
					</table>
				</form>
			</div>';
		}
	}

	/**
	* Neues Spiel erstellen
	* Prüft ob ein neues Spiel erstellt werden will
	*
	* @FIXME Für creator in stl_players added: mysql_last_insert_id() nehmen (jetzt nimmt es immer letztes Spiel, auch wenn INSERT into stl failed...)
	*
	* @global object $db Globales Class-Object mit allen MySQL-Methoden
	* @global object $user Globales Class-Object mit den User-Methoden & Variablen
	* @return void
	*/
	function exec() {
		global $db, $user;

		$go = false;
		//Wenn POST ist
		if(count($_POST) > 1) {
			if($_POST['num_players'] && $_POST['game_title'] && $_POST['game_size']) {

				//spielfeld grösse korrekturen
				$game_size = ($_POST['game_size'] > 23) ? 23 : $_POST['game_size'];
				$game_size = ($game_size < 5) ? 5 : $game_size;

				//anzahl spieler korrekturen
				$num_players = ($_POST['num_players'] > 24) ? 24 : $_POST['num_players'];
				$num_players = ($num_players < 6) ? 6 : $num_players;

				//wenn spieler anzahl ungerade ist
				if(($num_players % 2) == 1) { $num_players++; }

				//game erstellen
				$sql = 'INSERT into stl
							(game_size, status, creator_id, game_title, num_players)
						VALUES
							('.$game_size.',0,'.$user->id.',"'.$_POST['game_title'].'",'.$num_players.')';
				$db->query($sql,__FILE__,__LINE__,__METHOD__);

				//creator automatisch als spieler im neu erstellten game eintragen.
				$sql = 'SELECT game_id
						FROM stl
						WHERE creator_id = '.$user->id.'
						ORDER by game_id DESC
						LIMIT 0,1';
				$result = $db->query($sql,__FILE__,__LINE__,__METHOD__);
				$rs = $db->fetch($result);
				$sql = 'INSERT into stl_players
							(game_id, user_id)
						VALUES
							('.$rs['game_id'].', '.$user->id.')';
				$db->query($sql,__FILE__,__LINE__,__METHOD__);

				//setzte redirect
				$go = true;

				/** Activity Eintrag auslösen */
				Activities::addActivity($user->id, 0, t('activity-newgame', 'stl', [ $_POST['game_title'], $num_players/2, SITE_URL, $rs['game_id'] ]), 'sl');
			}
		}
		if($go === true) {
			header('Location: '.base64_decode(getURL(false)));
			exit;
		}
	}

	/**
	 * Führt die Torpedo schüsse aus, und prüft ob der User das auch darf
	 *
	 * @global object $db Globales Class-Object mit allen MySQL-Methoden
	 * @global object $user Globales Class-Object mit den User-Methoden & Variablen
	 * @return void
	 */
	function shoot() {
		global $db, $user;

		$sql = 'SELECT
					game_id
				FROM stl_players
				WHERE
					game_id = '.$this->data['stl']['game_id'].'
					AND
					user_id = '.$user->id.'
					AND
					HOUR(last_shoot) <> HOUR(now())';
		$result = $db->query($sql,__FILE__,__LINE__,__METHOD__);

		//Prüfen ob der Spieler schiessen darf und ob das Spiel den passenden Status hat
		if($db->num($result) && $this->data['stl']['status'] == 1) {
			$sql = 'SELECT
						hit_user_id
					FROM stl_positions
					WHERE
						game_id = '.$this->data['stl']['game_id'].'
						AND
						ship_user_id = '.$user->id;
			$result = $db->query($sql,__FILE__,__LINE__,__METHOD__);
			$rs = $db->fetch($result);
			//Prüfen ob der Spieler nicht gesunken ist
			if($rs['hit_user_id'] == 0) {
				$x_grid = substr($_GET['shoot'],0,strrpos($_GET['shoot'],','));
				$y_grid = substr($_GET['shoot'],strrpos($_GET['shoot'],',')+1);

				//Prüfen ob ein Datensatz mit diesen Grid koords bereits besteht
				$sql = 'SELECT *
						FROM stl_positions
						WHERE
							grid_x = '.$x_grid.'
							AND grid_y = '.$y_grid.'
							AND game_id = '.$this->data['stl']['game_id'];
				$result = $db->query($sql,__FILE__,__LINE__,__METHOD__);
				//wenn JA wird der bestehende Datensatz geupdatet
				if($db->num($result)) {
					$rs = $db->fetch($result);
					//Prüft ob Ziel keine verbündete sind
					if($rs['ship_team_id'] != $this->data['team_id']) {
						if($rs['hit_user_id'] == 0) {
							$sql = 'UPDATE stl_positions
										set
											hit_user_id = '.$user->id.',
											hit_team_id = '.$this->data['team_id'].',
											shoot_date = now()
									WHERE
										pos_id = '.$rs['pos_id'];
							$db->query($sql,__FILE__,__LINE__,__METHOD__);

							//last_shoot neu setzen
							$sql = 'UPDATE stl_players
										SET last_shoot = now()
									WHERE
										game_id = '.$this->data['stl']['game_id'].'
										AND user_id = '.$user->id;
							$db->query($sql,__FILE__,__LINE__,__METHOD__);

							$this->check4finish();
						} else {
							$msg = 2;
						}
					} else {
						$msg = 4;
					}
				//Wenn NEIN werden diese koords im Grid erstellt
				} else {
					//Neue Position im Grid erstellen
					$sql = 'INSERT into stl_positions
								(game_id, grid_x, grid_y, hit_user_id, hit_team_id, shoot_date)
							VALUES
								('.$this->data['stl']['game_id'].','.$x_grid.','.$y_grid.','.$user->id.','.$this->data['team_id'].',now())';
					$db->query($sql,__FILE__,__LINE__,__METHOD__);

					//last_shoot neu setzen
					$sql = 'UPDATE stl_players
								SET last_shoot = now()
							WHERE
								game_id = '.$this->data['stl']['game_id'].'
								AND user_id = '.$user->id;
					$db->query($sql,__FILE__,__LINE__,__METHOD__);
				}
			} else {
				//echo "gesunken";
				$msg = ($this->data['stl']['status'] < 2 ? 1 : 5);
			}
		} else {
			$msg = ($this->data['stl']['status'] < 2 ? 5 : 0);
		}
		if(!isset($msg)) {
			//header("Location: http://".$_SERVER['SERVER_NAME'].$_SERVER['PHP_SELF']."?do=game&game_id=$_GET[game_id]&".session_name()."=".session_id());
			header('Location: '.base64_decode(getURL(false)).'?do=game&game_id='.$this->data['stl']['game_id']);
			exit;
		} else {
			//header("Location: http://".$_SERVER['SERVER_NAME'].$_SERVER['PHP_SELF']."?do=game&game_id=$_GET[game_id]&msg=$msg&".session_name()."=".session_id());
			header('Location: '.base64_decode(getURL(false)).'?do=game&game_id='.$this->data['stl']['game_id'].'&msg='.$msg);
			exit;
		}
	}


	/**
	 * Offene STL-Spiele
	 * Gibt die Anzahl offener Spiele als Link zum ersten Spiel zurueck
	 *
	 * @author [z]milamber
	 * @author IneX
	 * @version 2.0
	 * @since 1.0 function added
	 * @since 2.0 `18.08.2018` function refactored & reactivated
	 *
	 * @global object $db Globales Class-Object mit allen MySQL-Methoden
	 * @global object $user Globales Class-Object mit den User-Methoden & Variablen
	 * @return string HTML-Link zum nächsten offenen STL-Spiel
	 */
	public static function getOpenSTLGames()
	{
		global $db, $user;

		$count = 0;
		if ($user->is_loggedin())
		{
			$sql = 'SELECT
					 stlg.game_id
					FROM
						 stl_players AS stlp
						,stl AS stlg
					LEFT JOIN
						stl_players stljp ON stljp.game_id=stlg.game_id
						AND stljp.user_id='.$user->id.'
					WHERE
						stlg.status=0
						AND stlp.game_id=stlg.game_id
						AND stlg.creator_id<>'.$user->id.'
						AND stljp.user_id IS NULL
					GROUP BY stlg.game_id';
			$result = $db->query($sql,__FILE__,__LINE__,__METHOD__);
			$count = ($result ? $db->num($result) : 0);
			$next = $db->fetch($result);
			return ( $count > 0 ? '<a href="/stl.php?do=game&game_id='.$next['game_id'].'">'.$count.' open STL game'.($count > 1 ? 's' : '').'</a>' : '' );
		} else {
			return null;
		}
	}


	/**
	 * Offene STL-Spielzüge des Users
	 * Gibt die Anzahl offener Spielzüge - bei denen der User mitspielt - aus, als HTML-Link zum nächsten Spielzug
	 *
	 * @author [z]milamber
	 * @author IneX
	 * @version 2.0
	 * @since 1.0 function added
	 * @since 2.0 `18.08.2018` function refactored & reactivated
	 *
	 * @global object $db Globales Class-Object mit allen MySQL-Methoden
	 * @global object $user Globales Class-Object mit den User-Methoden & Variablen
	 * @return string HTML-Link zum nächsten pendenten STL-Spielzug
	 */
	public static function getOpenSTLLink()
	{
		global $db, $user;

		$count = 0;
		if ($user->is_loggedin())
		{
			$sql = 'SELECT
						 stl.game_id AS game_id
						,HOUR( pl.last_shoot) AS last_shoot
					FROM stl
						LEFT JOIN stl_players pl ON pl.game_id = stl.game_id
						LEFT JOIN stl_positions p ON stl.game_id = p.game_id
					WHERE
						pl.user_id='.$user->id.'
						AND stl.status=1
						AND p.ship_user_id='.$user->id.'
						AND p.hit_user_id=0
						AND last_shoot < (NOW() - INTERVAL 1 HOUR)';
			$result = $db->query($sql,__FILE__,__LINE__,__METHOD__);
			$count = ($result ? $db->num($result) : 0);
			$next = $db->fetch($result);
			return ( $count > 0 ? '<a href="/stl.php?do=game&game_id='.$next['game_id'].'">'.$count.' STL-shot'.($count > 1 ? 's' : '').'</a>' : '' );
		} else {
			return null;
		}
	}
}
