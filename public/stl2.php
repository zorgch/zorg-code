<?php
/**
 * Shoot The Lamber v2 (Game)
 *
 * Shoot The Lamber ist ein Schiffchen-Versenken-Klon auf Zorg
 * mySQL Tables:
 *
 *	Haupttable:
 *		stl:
 *			game_id (primary key)
 *				spiel nummer
 *			game_size (max. 23, min. 5)
 *				spielfeld grösse Anzahl x Anzahl
 *			status int
 *				0 = wurde erstellt, spieler werden gesucht
 *				1 = läuft
 *				2 = beendet
 *			winner_team int
 *				0 = team red
 *				1 = team blue
 *			creater_id
 *				userID des spielerstellers (spiel-admin)
 *			num_players (min. 6, max. 24)
 *				anzahl spieler
 *			game_title
 *				spielname
 *
 *	Spieler Table:
 *		stl_players:
 *			user_id
 *				user ID aus der user table
 *			team_id
 *				team id bei dem der spieler mitglied ist.
 *			game_id
 *				spiel nummer
 *			last_shoot
 *				datum an dem der spieler zuletzt geschossen hat.
 *			torpedos
 *				Anzahl verbleibende Torpedos
 *
 *	Schiffs- und treffer positionen
 *		stl_positions:
 *			pos_id (primary key)
 *				positions id
 *			game_id
 *				spiel nummer
 *			grid_x
 *				x koordinate
 *			grid_y
 *				y koordinate
 *			hit_user_id
 *				spieler id von dem hier ein torpedo gekommen ist, 0 bedeut kein schuss bis jetzt
 *			hit_team_id
 *				team_id vom topedo ;-)
 *			ship_user_id
 *				spieler id vom besitzer des schiffs, 0 bedeutet kein schiff
 *			ship_team_id
 *				team_id vom besitzer des schiffs
 *			shoot_date
 *				datum an dem der spieler das torpedo geschossen hat.
 *
 * @version 2.0
 * @since 2.0 `[z]milamber` File added
 *
 * @package zorg\Games\STL
 */

/**
 * File includes
 */
require_once __DIR__.'/includes/config.inc.php';
require_once INCLUDES_DIR.'stl.inc.php';
require_once INCLUDES_DIR.'smarty.inc.php';

/**
 * Shoot The Lamber v2 (Game)
 *
 * @version 2.0
 * @since 2.0 `[z]milamber` Class added
 *
 * @package zorg\Games\STL
 */
class stlv2
{
	/**
	 * Class Vars
	 */
	public $game = null;
	public $data = [];
	public $config = [];
	public $shoot = null;
	public $case = null;
	public $view = null;
	public $add = [];
	public $team_id = null;
	public $message = null;

	/**
	 * Klassenkonstruktor, generiert autom. die ganze ausgabe...(Game & Overview)
	 *
	 * @return stlv2
	 */
	function __construct($game_id=null, $shoot_field=null) {
		global $db, $user;
		//Feldchengrösse
		$this->case = 20;
		//Sichtweite
		$this->view = 3;
		//Posts
		self::exec();

		/** Game */
		if (!empty($game_id)) $this->game = $game_id;
		if (!empty($shoot_field)) $this->shoot = $shoot_field;

		if($this->game > 0) {
			$sql = 'SELECT * FROM stl WHERE game_id=?';
			$result = $db->query($sql, __FILE__, __LINE__, __METHOD__, [$this->game]);
			$this->data['stl'] = $db->fetch($result);

			$sql = 'SELECT team_id FROM stl_players WHERE user_id=? AND game_id=?';
			$result = $db->query($sql, __FILE__, __LINE__, __METHOD__, [$user->id, $this->game]);
			$rs = $db->fetch($result);
			$this->data['team_id'] = $rs['team_id'];

			$this->data['msg'][1] = "Kommandant, ein Rettungsboot hat keine Torpedos!";
			$this->data['msg'][2] = "Kommandant, dieses Ziel ist uninteressant!";
			$this->data['msg'][3] = "Torpedos geladen und bereit!";
			$this->data['msg'][4] = "Kommandant, man schiesst nicht auf die eigenen Leute!";
			$this->data['msg'][5] = "Kommandant, Sie müssen warten bis die Torpedorohre nachgeladen sind!";
			$this->data['msg'][6] = "Ihre Mannschaft lädt gerade die Torpedorohre, in ".(60-date("i"))." Minuten ist es soweit";

			self::game();
		}
		//Legende:
		$this->data['legende'] = "<br><br><br>
		<table><tr><td align='center' style='text-align: center' colspan='2'>
		<B>Legende</B>
		</td></tr><tr><td align='left'>
			<table><tr>
			<td bgcolor='#00FF00' style='width:".$this->case."px;height:".$this->case."px; text-align: center;'>
			<b style='font-size:14px;'><a href='#'>^</a></b>
			</td></tr></table>
			<table><tr>
			<td bgcolor='#FFFF00' style='width:".$this->case."px;height:".$this->case."px; text-align: center;'>
			<b style='font-size:14px;'><a href='#'>^</a></b>
			</td></tr></table>
		</td><td align='left'>
		Das Feld mit der vollen Team Farbe (Grün oder Gelb) ist deine eigene Position
		</td></tr><tr><td align='left'>
			<table><tr>
			<td bgcolor='#CCFFCC' style='width:".$this->case."px;height:".$this->case."px; text-align: center;'>
			<b style='font-size:14px;'><a href='#'>^</a></b>
			</td></tr></table>
			<table><tr>
			<td bgcolor='#FFFFCC' style='width:".$this->case."px;height:".$this->case."px; text-align: center;'>
			<b style='font-size:14px;'><a href='#'>^</a></b>
			</td></tr></table>
		</td><td align='left'>
		Die Felder mit der blassen Team Farbe sind deine Teammitglieder
		</td></tr><tr><td align='left'>
			<table><tr>
			<td bgcolor='#000000' style='width:".$this->case."px;height:".$this->case."px; text-align: center;'>
			<b style='font-size:14px;'><a href='#'>^</a></b>
			</td></tr></table>
		</td><td align='left'>
		Schwarz sind deine Torpedos, die _nicht_ getroffen haben
		</td></tr><tr><td align='left'>
			<table><tr>
			<td bgcolor='#666666' style='width:".$this->case."px;height:".$this->case."px; text-align: center;'>
			<b style='font-size:14px;'><a href='#'>^</a></b>
			</td></tr></table>
		</td><td align='left'>
		Grau sind Torpedos von deinen Teammitgliedern die _nicht_ getroffen haben
		</td></tr><tr><td align='left'>
			<table><tr>
			<td bgcolor='#FF0000' style='width:".$this->case."px;height:".$this->case."px; text-align: center;'>
			<b style='font-size:14px;'><a href='#'>^</a></b>
			</td></tr></table>
		</td><td align='left'>
		Rot sind deine Treffer
		</td></tr><tr><td align='left'>
			<table><tr>
			<td bgcolor='#FFCCCC' style='width:".$this->case."px;height:".$this->case."px; text-align: center;'>
			<b style='font-size:14px;'><a href='#'>^</a></b>
			</td></tr></table>
		</td><td align='left'>
		Blasses Rot sind Treffer deiner Teammitglieder
		</td></tr><tr><td align='left'>
			<table><tr>
			<td bgcolor='#0000FF' style='width:".$this->case."px;height:".$this->case."px; text-align: center;'>
			<b style='font-size:14px;'><a href='#'>^</a></b>
			</td></tr></table>
		</td><td align='left'>
		Volles Blau sind erfolglose Zielversuche vom Feind
		</td></tr><tr><td align='left'>
			<table><tr>
			<td bgcolor='#".MENUCOLOR1."' style='width:".$this->case."px;height:".$this->case."px; text-align: center;'>
			<b style='font-size:14px;'><a href='#'>^</a></b>
			</td></tr></table>
			<table><tr>
			<td bgcolor='#".MENUCOLOR2."' style='width:".$this->case."px;height:".$this->case."px; text-align: center;'>
			<b style='font-size:14px;'><a href='#'>^</a></b>
			</td></tr></table>
		</td><td align='left'>
		Meerfarbene Felder mit einer Namensabkürzung kennzeichnen gesunkene Teammitglieder
		</td></tr></table>";

		self::overview();
	}



	/**
	 * Liest die Teams zu einem Spiel und speichert die in klassen vars
	 *
	 * @return void
	 */
	function teams() {
		global $db;

		$sql = 'SELECT user.username as username, user.id as user_id, stl_players.team_id as team_id, stl_positions.*
				FROM stl_players LEFT JOIN user ON user.id = stl_players.user_id
				INNER JOIN stl_positions ON stl_players.user_id = stl_positions.ship_user_id
				WHERE stl_players.game_id = ? AND stl_positions.game_id = ?';
		$result = $db->query($sql, __FILE__, __LINE__, __METHOD__, [$this->game, $this->game]);
		$add1 = "<b>";
		while($rs = $db->fetch($result)) {
			$add1 = ($rs['hit_user_id']) ? "<b style='text-decoration: line-through'>" : "<b>";
			if($rs['team_id'] == 0) {
				$this->data['team_gelb'] .= $add1.$rs['username']."</b><br>";
			} else {
				$this->data['team_gruen'] .= $add1.$rs['username']."</b><br>";
			}
		}

	}

	/**
	 * Check und join user zu einem Game
	 *
	 * Ermittelt ob ein Spieler bei einem Spiel bereits mitspielt (wenn nein, spielt er JETZT mit)
	 *
	 * @return void
	 */
	function check4join() {
		global $db, $user;
		$sql = 'SELECT game_id FROM stl WHERE status = 0 AND game_id=?';
		$result = $db->query($sql, __FILE__, __LINE__, __METHOD__, [$this->game]);
		if($db->num($result)) {
			$sql = 'SELECT user_id FROM stl_players WHERE game_id=? AND user_id=?';
			$result = $db->query($sql, __FILE__, __LINE__, __METHOD__, [$this->game, $user->id]);
			//wenn spieler noch nicht eingetragen ist
			if(!$db->num($result)) {
				$sql = 'INSERT into stl_players (user_id, game_id) VALUES (?, ?)';
				$db->query($sql, __FILE__, __LINE__, __METHOD__, [$user->id, $this->game]);
			}
		}
	}

	/**
	 * Prüft ob ein Spiel beendet werden kann und ermittelt das Gewinner Team
	 *
	 * @return void
	 */
	function check4finish() {
		global $db;

		$sql = 'SELECT pos_id FROM stl_positions WHERE game_id=? AND ship_team_id=1 AND hit_team_id=0 AND ship_user_id<>0 AND hit_user_id<>0';
		$win_team_gelb = $db->query($sql, __FILE__, __LINE__, __METHOD__, [$this->game]);

		$sql = 'SELECT pos_id FROM stl_positions WHERE game_id=? AND ship_team_id=0 AND hit_team_id=1 AND ship_user_id<>0 AND hit_user_id<>0';
		$win_team_gruen = $db->query($sql, __FILE__, __LINE__, __METHOD__, [$this->game]);

		if($db->num($win_team_gelb) == ($this->data['stl']['num_players'] / 2)) {
			$sql = 'UPDATE stl SET status=2, winner_team=0 WHERE game_id=?';
			$db->query($sql, __FILE__, __LINE__, __METHOD__, [$this->game]);
		}
		if($db->num($win_team_gruen) == ($this->data['stl']['num_players'] / 2)) {
			$sql = 'UPDATE stl SET status = 2, winner_team = 1 WHERE game_id=?';
			$db->query($sql, __FILE__, __LINE__, __METHOD__, [$this->game]);
		}
	}

	/**
	 * Check ob Spiel gestartet werden kann
	 *
	 * Prüft ob ein Spiel gestartet werden kann, erstellt grid und weisst die Spieler zufällig einem Team und einem Feld zu
	 *
	 * @return void
	 */
	function check4start() {
		global $db;

		$sql = 'SELECT count(user_id) as num FROM stl_players WHERE game_id=?';
		$result = $db->query($sql, __FILE__, __LINE__, __METHOD__, [$this->game]);
		$rs = $db->fetch($result);
		if($rs['num'] == $this->config['num_players']) {
			$sql = 'SELECT * FROM stl_players WHERE game_id=?';
			$result = $db->query($sql, __FILE__, __LINE__, __METHOD__, [$this->game]);
			while($rs = $db->fetch($result)) {
				$players[] = $rs['id'];
			}
			shuffle($players);
			for($i=0;$i<=count($players);$i++) {
				$team = ($i % 2);
				$shoot_date = timestamp(true, time()-5000);
				$sql = 'UPDATE stl_players set team_id=?, last_shoot=? WHERE id=?';
				$db->query($sql, __FILE__, __LINE__, __METHOD__, [$team, $shoot_date, $players[$i]]);
			}
			$sql = 'SELECT * FROM stl_players WHERE game_id=?';
			$result = $db->query($sql, __FILE__, __LINE__, __METHOD__, [$this->game]);
			while($rs = $db->fetch($result))
			{
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
				$sql = 'INSERT into stl_positions (game_id, grid_x, grid_y, ship_user_id, shoot_date) VALUES (?, ?, ?, ?, ?)';
				$db->query($sql, __FILE__, __LINE__, __METHOD__, [$this->game, $grid_x, $grid_y, $rs['user_id'], timestamp(true)]);

			}

			//team_id in positions table schreiben
			$sql = 'SELECT team_id, game_id, user_id FROM stl_players WHERE game_id=?';
			$result = $db->query($sql, __FILE__, __LINE__, __METHOD__, [$this->game]);
			while($rs = $db->fetch($result)) {
				$sql = 'UPDATE stl_positions SET ship_team_id=? WHERE ship_user_id=? AND game_id=?';
				$db->query($sql, __FILE__, __LINE__, __METHOD__, [$rs['team_id'], $rs['user_id'], $this->game]);
			}
			//game starten
			$sql = 'UPDATE stl set status=1 WHERE game_id=?';
			$db->query($sql, __FILE__, __LINE__, __METHOD__, [$this->game]);

			header("Location: ".SITE_URL."/stl2.php?do=game&game_id=".$this->game);
			exit;
		}
	}

	/**
	 * Game ausgeben
	 *
	 * Hauptfunktion, erstellt das gesamte Spielfeld und drum herum
	 *
	 * @return void
	 */
	function game() {
		global $db, $user;

		$sql = 'SELECT * FROM stl WHERE game_id=?';
		$result = $db->query($sql, __FILE__, __LINE__, __METHOD__, [$this->game]);
		$this->config = $db->fetch($result);

		//Joinstatus, spiel läuft noch nicht
		if($this->config['status'] == 0) {
			//Prüfen ob der User bereits gejoint hat, wenn nicht wird gejoint
			self::check4join();
			//Prüfen ob das game gestartet werden kann (genügend spieler)
			self::check4start();

			$sql = 'SELECT stl_players.user_id as user_id, user.username as username FROM stl_players LEFT JOIN user ON user.id = stl_players.user_id WHERE game_id=?';
			$result = $db->query($sql, __FILE__, __LINE__, __METHOD__, [$this->game]);

			$this->data['game'] .= "<div align='center'><b>Spieler bis jetzt:</b><br>";
			$num = 0;
			while($rs = $db->fetch($result)) {
				$this->data['game'] .= $rs['username']."<br />";
				$num++;
			}
			$this->data['game'] .= "<br><b>".
				($this->config['num_players'] - $num)
				." Spieler fehlen noch</b><br>
				<small>(Spiel wird bei vollständiger Spielerzahl automatisch gestarten. <br>
				Der Spieler wird zufällig einem Team und einer Position auf dem Spielfeld zugewiesen)</small><br></div>";

		}
		//Team anzeige & grid wenn spiel läuft
		if($this->config['status'] > 0) {
			//Prüfen ob das Spiel beendet werden kann.
			self::check4finish();

			//Teams zuweisung ausführen und daten generieren
			self::teams();

			//Grid Infos
			$sql = 'SELECT stl_positions.grid_x as grid_x, stl_positions.grid_y as grid_y, stl_positions.hit_user_id as hit_user_id, stl_positions.hit_team_id as hit_team_id, stl_positions.ship_user_id as ship_user_id, stl_positions.ship_team_id as ship_team_id, stl_positions.shoot_date as shoot_date, stl_players.team_id as team_id, user.username as username FROM stl_positions LEFT JOIN stl_players ON stl_players.game_id=stl_positions.game_id AND stl_players.user_id=stl_positions.ship_user_id LEFT JOIN user ON user.id=stl_positions.ship_user_id WHERE stl_positions.game_id=?';
			$result = $db->query($sql, __FILE__, __LINE__, __METHOD__, [$this->data['stl']['game_id']]);
			//erstellung eines daten-arrays (ist einfachen beim erstellen des grids
			while($rs = $db->fetch($result)) {
				$this->data['game_data'][$rs['grid_y']][$rs['grid_x']] = $rs;
			}

			//Team ID zuweisungen
			$sql = 'SELECT team_id FROM stl_players WHERE game_id=? AND user_id=?';
			$result = $db->query($sql, __FILE__, __LINE__, __METHOD__, [$this->data['stl']['game_id'], $user->id]);
			//team zuweisung
			if($db->num($result)) {
				$rs = $db->fetch($result);
				$this->team_id = $rs['team_id'];
			} else {
				$this->team_id = 2;
			}

			//Messages
			if(!empty($this->message)) {
				//Bei übergabe
				$msg = $this->data['msg'][$this->message];
			//normalerweise
			} else {
				//Prüfen ob der Spieler getroffen wurde
				$sql = 'SELECT hit_user_id FROM stl_positions WHERE ship_user_id=? AND game_id=?';
				$result = $db->query($sql, __FILE__, __LINE__, __METHOD__, [$user->id, $this->game]);
				$rs = $db->fetch($result);
				//Wenn der spieler noch im spiel ist
				if($rs['hit_user_id'] == 0) {
					//Prüfen wann Seine Torpedos wieder geladen sind
					$sql = 'SELECT game_id FROM stl_players WHERE game_id=? AND user_id=? AND HOUR(last_shoot) <> HOUR(?)';
					$result = $db->query($sql, __FILE__, __LINE__, __METHOD__, [$this->game, $user->id, timestamp(true)]);

					//Zuweisung der Message wenn geladen wird oder nicht
					$msg = ($db->num($result)) ? $this->data['msg'][3] : $this->data['msg'][6];

				//Wenn der Spieler abgeschossen wurde.
				} else {
					$msg = $this->data['msg'][1];
				}
			}
			//Prüfen ob das Spiel noch läuft.
			if($this->data['stl']['status'] == 2) {
				$winner = ($this->data['stl']['winner_team']) ? "Grün" : "Gelb";
				$this->data['game'] .= "<h1 style='text-align:center'>Team ".$winner." hat gewonnen!</h1><br><br><br><br>";
			}

			//Spielfeld & HTML
			$this->data['game'] .= "<table width='100%' cellpadding='4' cellspacing='1' class='border' align='center'>
			<tr><td align='center' bgcolor='#FFFF00'>
			<b>Team Gelb</b>
			</td>
			<td align='center' bgcolor='#".BORDERCOLOR."' style='text-align: center;'>
			<b>
			".$this->data['stl']['game_title']."</b><br><b>"
			.$msg."</b>
			</td><td align='center' bgcolor='#00FF00'>
			<b>Team Grün</b>
			</td>
			</tr>
			<tr><td align='left' valign='top' bgcolor='#FFFFCC'>
			".$this->data['team_gelb']."
			</td><td align='center' valign='middle' bgcolor='#".BORDERCOLOR."'>";

			//sichtbar
			$sql = 'SELECT grid_x, grid_y FROM stl_positions WHERE game_id=? AND ship_user_id=?';
			$result = $db->query($sql, __FILE__, __LINE__, __METHOD__, [$this->game, $user->id]);
			$rs = $db->fetch($result);

			$max_x = $rs['grid_x'] + $this->view;
			$min_x = $rs['grid_x'] - $this->view;

			$max_y = $rs['grid_y'] + $this->view;
			$min_y = $rs['grid_y'] - $this->view;

			//um die ecken wegzuschneiden (hat da jemand ne gescheitere methode ?)
			for($x = $min_x;$x <= $max_x;$x++) {
				for($y = $min_y;$y <= $max_y;$y++) {
					$shoot = $x.",".$y;
					if(
					$shoot != $max_x.",".$min_y
					&&
					$shoot != $max_x.",".$max_y
					&&
					$shoot != $min_x.",".$max_y
					&&
					$shoot != ($max_x-1).",".$max_y
					&&
					$shoot != ($min_x+1).",".$max_y
					&&
					$shoot != $max_x.",".($max_y-1)
					&&
					$shoot != $max_x.",".($min_y+1)
					&&
					$shoot != $min_x.",".($min_y+1)
					&&
					$shoot != $min_x.",".($max_y-1)
					&&
					$shoot != ($min_x+1).",".$min_y
					&&
					$shoot != ($max_x-1).",".$min_y
					)
					{
						$range[] = $shoot;
					}
				}
			}

			//Spielfeld
			for($y = $this->data['stl']['game_size'];$y>=0;$y--) {

				$this->data['game'] .=  "<table cellpadding='5' cellspacing='0' align='center'><tr>";
				for($x = 1;$x<=$this->data['stl']['game_size'];$x++) {
					if($y != 0) {

						if((($x+$y) % 2) == 1) {
							$this->add[0] = "bgcolor='#151515'";
							$this->add[1] = "<sup>";
							$this->add[2] = "</sup>";
						} else {
							$this->add[0] = "bgcolor='#000000'";
						}

						$this->add[3] = "~";
						$links = false;
						//=============================================================================
						//Sichtbarer Bereich:
						//=============================================================================

						if(array_search($x.",".$y,$range)) {
							$this->add[3] = "^";
							$links = true;
							if((($x+$y) % 2) == 1) {
								$this->add[0] = "bgcolor='#".MENUCOLOR1."'";
								$this->add[1] = "<sup>";
								$this->add[2] = "</sup>";
							} else {
								$this->add[0] = "bgcolor='#".MENUCOLOR2."'";
							}
						}


						if(array_search($x.",".$y,$range)) {
							//=============================================================================
							//Eigene Position
							//=============================================================================
							if($this->data['game_data'][$y][$x]['ship_user_id'] == $user->id) {
								$this->add[0] = "bgcolor='#FFFFFF' ";
							}

							//=============================================================================
							//Positionen an denen eigene torpedos erfolgreich detonierten
							//=============================================================================
							if(
							$this->data['game_data'][$y][$x]['hit_user_id'] == $user->id
							&& $this->data['game_data'][$y][$x]['ship_user_id'] != 0
							){
								$this->add[0] = "bgcolor='#FF0000'";
								$this->add[3] = "<small>".substr($this->data['game_data'][$y][$x]['username'],0,2)."</small>";

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
								$this->add[0] = "bgcolor='#FFCCCC'";
								$this->add[3] = "<small>".substr($this->data['game_data'][$y][$x]['username'],0,2)."</small>";

							}

							//=============================================================================
							//Positionen an denen eigene torpedos erfolglos detonierten
							//=============================================================================
							if(
							$this->data['game_data'][$y][$x]['hit_user_id'] == $user->id
							&& $this->data['game_data'][$y][$x]['ship_user_id'] == 0

							) {
								$this->add[0] = "bgcolor='#000000'";

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
								$this->add[0] = "bgcolor='#666666'";

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
								$this->add[0] = "bgcolor='#0000FF'";

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
								//$this->add[0] = "bgcolor='#FFBAAB'";
								$this->add[3] = "<small>".substr($this->data['game_data'][$y][$x]['username'],0,2)."</small>";

							}
						}

						//Wenn links gesetzt werden sollen
						if($links == TRUE) {
							$this->data['game'] .= "<td ".$this->add[0]." onClick=\"document.location.href='?do=game&game_id=".$this->game."&shoot=".$x.",".$y."'\" align='center' valign='middle' style='width:".$this->case."px;height:".$this->case."px; text-align: center;'>
							<a href='?do=game&game_id=".$this->game."&shoot=".$x.",".$y."' style='text-decoration: none;'  align='center'>
							<b style='font-size:14px;'>
							".$this->add[1].$this->add[3].$this->add[2]."
							</b>
							</a>
							</td>";
						//Wenn auf dem Feld bereits was ist (keine links)
						} else {
							$this->data['game'] .= "<td ".$this->add[0]." align='center' valign='middle' style='width:".$this->case."px;height:".$this->case."px; text-align: center;'>
							<b style='font-size:14px;'>
							".$this->add[1].$this->add[3].$this->add[2]."
							</b>
							</td>";
						}
					}
				}
				$this->data['game'] .= "</tr>";
			}
			$this->data['game'] .= "</table>
			</td>
			<td align='right' valign='top' bgcolor='#CCFFCC'>
			".$this->data['team_gruen']."
			</td>
			</tr>
			</table>";
		}
	}

	/**
	 * Games Übersicht anzeigen
	 *
	 * zeigt alle offenen und joinbaren spiele eines users
	 *
	 * @return void
	 */
	function overview() {
		global $db, $user;
			//selektiert games bei denen ich mitmache
			$sql = 'SELECT stl.game_id as game_id, stl.game_title as game_title, stl.game_size as game_size, stl.num_players as num_players, stl.status as status, user.username as userame, HOUR(stl_players.last_shoot) as last_shoot, HOUR(now()) as akt FROM stl LEFT JOIN stl_players ON stl_players.game_id = stl.game_id LEFT JOIN user ON user.id = stl.creator_id WHERE stl_players.user_id = ? AND stl.status <> 2 ORDER by stl.status DESC';
			$result = $db->query($sql, __FILE__, __LINE__, __METHOD__, [$user->id]);

			//wenns spiele gibt bei denen ich mitmachen
			if($db->num($result)) {
				$this->data['overview'] = "<div align='center'><b>Spiele:<br><small>(hier spielst du mit)</small></b><br>";

				while($rs = $db->fetch($result)) {
					if($rs['status'] == 1 && $rs['last_shoot'] != $rs['akt']) {
						$add = " style='color:#FF0000; font-weight:bold;'";
					} elseif($rs['status'] == 1) {
						$add = " style='font-weight:bold;'";
					} else {
						$add = "";
					}
					$this->data['overview'] .= "<a ".$add." href='?do=game&amp;game_id=".$rs['game_id']."'>".strip_tags($rs['game_title'])." <small>(".$rs['game_size']." x ".$rs['game_size']."), ".$rs['num_players']."</small></a><br/>";
				}
				$this->data['overview'] .= "<br><br><br>";
			}

			//selektiert games bei denen ich nicht mitmache und noch joinen kann
			$sql = 'SELECT stl.game_id as game_id, stl.game_title as game_title, stl.game_size as game_size, stl.num_players as num_players, stl.status as status, user.username as userame FROM stl LEFT JOIN stl_players ON stl_players.game_id = stl.game_id LEFT JOIN user ON user.id = stl.creator_id WHERE stl_players.user_id <> ? AND stl.status = 0';
			$result = $db->query($sql, __FILE__, __LINE__, __METHOD__, [[$user->id]]);

			//wenns games gibt wo ich nicht mitmache
			if($db->num($result)) {
				$this->data['overview'] .= "<b>Offene Spiele: <br><small>(hier könnt ihr noch joinen, klicken um zu joinen)</small></b><br />";

				while($rs = $db->fetch($result)) {
					if($old != $rs['game_id']) { // FIXME Where is $old coming from?
						$this->data['overview'] .= "<a href='?do=game&amp;game_id=".$rs['game_id']."'>".strip_tags($rs['game_title'])." <small>(".$rs['game_size']." x ".$rs['game_size']."), ".$rs['num_players']."</small></a><br />";
					}
					$old = $rs['game_id'];
				}
				$this->data['overview'] .= "<br><br><br>";

			}

			$this->data['overview'] .= "<form action='".htmlspecialchars($_SERVER['PHP_SELF'])."' method='post'>
			<table>
			<tr><td align='center' colspan='2'>
			<b>Neues Spiel</b>
			</td></tr>
			<tr><td align='left'>
			Spielname:
			</td><td align='left'>
			<input type='text' name='game_title' size='20' class='text'>
			</tr></td>
			<tr><td align='left'>
			Anzahl Spieler: <br><sub>(min. 6, max. 24)</sub>
			</td><td align='left' valign='top'>
			<input type='text' name='num_players' size='4' class='text'>
			</td></tr><tr><td align='left'>
			Spielfeldgrösse :<br /><sub>(min. 5, max. 23)</sub>
			</td><td align='left' valign='top'>
			<input type='text' name='game_size' size='4' class='text'>
			</td></tr><tr><td align='left' colspan='2'>
			<input type='submit' value='erstellen' class='button'>
			</td></tr></table></form></div>";

	}

	/**
	 * Prüft ob ein neues Spiel erstellt werden will
	 *
	 * @return void
	 */
	function exec()
	{
		global $db, $user;

		//Wenn POST ist
		if(count($_POST) > 1)
		{
			$redirectUrl = getURL(false, false);

			/** Validate inputs */
			$num_players = filter_input(INPUT_POST, 'num_players', FILTER_SANITIZE_NUMBER_INT) ?? 6;
			$game_size = filter_input(INPUT_POST, 'game_size', FILTER_SANITIZE_NUMBER_INT) ?? 5;
			$game_name = filter_input(INPUT_POST, 'game_title', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? 'STLv2 Game';

			//anzahl spieler korrekturen
			if ($num_players < 6) $num_players = 6;
			if ($num_players > 24) $num_players = 24;
			if (($num_players % 2) === 1) $num_players++;
			//spielfeld grösse korrekturen
			if ($game_size < 5) $game_size = 5;
			if ($game_size > 23) $game_size = 23;

			if($num_players >= 6 && $game_size >= 5 && !empty($game_name))
			{
				//game erstellen
				$sql = 'INSERT into stl (game_size, status, creator_id, game_title, num_players) VALUES (?, 0, ?, ?, ?)';
				$new_game_id = $db->query($sql, __FILE__, __LINE__, __METHOD__, [$game_size, $user->id, $game_name, $num_players]);

				if ($new_game_id > 0) {
					//setzte redirect
					$redirectUrl .= '?game_id='.$new_game_id;

					//creator automatisch als spieler im neu erstellten game eintragen.
					$sql = 'INSERT into stl_players (game_id, user_id) VALUES (?, ?)';
					$db->query($sql, __FILE__, __LINE__, __METHOD__, [$new_game_id, $user->id]);
				} else { $redirectUrl .= '?msg=Error%20joining%20game'; }
			} else { $redirectUrl .= '?msg=Error%20creating%20new%20game'; }

			header("Location: ".$redirectUrl);
			exit;
		}
	}

	/**
	 * Torpedo schiessen
	 *
	 * Führt die Torpedo schüsse aus, und prüft ob der User das auch darf
	 *
	 * @return void
	 */
	function shoot() {
		global $db, $user;

		$sql = 'SELECT game_id FROM stl_players WHERE game_id=? AND user_id=? AND HOUR(last_shoot)<>HOUR(?)';
		$result = $db->query($sql, __FILE__, __LINE__, __METHOD__, [$this->game, $user->id, timestamp(true)]);

		//Prüfen ob der Spieler schiessen darf und ob das Spiel den passenden Status hat
		if($db->num($result) && $this->data['stl']['status'] == 1) {
			$sql = 'SELECT hit_user_id FROM stl_positions WHERE game_id=? AND ship_user_id=?';
			$result = $db->query($sql, __FILE__, __LINE__, __METHOD__, [$this->game, $user->id]);
			$rs = $db->fetch($result);
			//Prüfen ob der Spieler nicht gesunken ist
			if($rs['hit_user_id'] == 0) {
				$x_grid = substr($this->shoot,0,strrpos($this->shoot,","));
				$y_grid = substr($this->shoot,strrpos($this->shoot,",")+1);

				//Prüfen ob ein Datensatz mit diesen Grid koords bereits besteht
				$sql = 'SELECT * FROM stl_positions WHERE grid_x = ? AND grid_y = ? AND game_id=?';
				$result = $db->query($sql, __FILE__, __LINE__, __METHOD__, [$x_grid, $y_grid, $this->game]);
				//wenn JA wird der bestehende Datensatz geupdatet
				if($db->num($result)) {
					$rs = $db->fetch($result);
					//Prüft ob Ziel keine verbündete sind
					if($rs['ship_team_id'] != $this->data['team_id']) {
						if($rs['hit_user_id'] == 0) {
							$sql = 'UPDATE stl_positions SET hit_user_id=?, hit_team_id=?, shoot_date=? WHERE pos_id=?';
							$db->query($sql, __FILE__, __LINE__, __METHOD__, [$user->id, $this->data['team_id'], timestamp(true), $rs['pos_id']]);

							//last_shoot neu setzen
							$sql = 'UPDATE stl_players SET last_shoot=? WHERE game_id=? AND user_id=?';
							$db->query($sql, __FILE__, __LINE__, __METHOD__, [timestamp(true), $this->game, $user->id]);

							self::check4finish();
						} else {
							$msg = 2;
						}
					} else {
						$msg = 4;
					}
				//Wenn NEIN werden diese koords im Grid erstellt
				} else {
					//Neue Position im Grid erstellen
					$sql = 'INSERT into stl_positions (game_id, grid_x, grid_y, hit_user_id, hit_team_id, shoot_date) VALUES (?, ?, ?, ?, ?, ?)';
					$db->query($sql, __FILE__, __LINE__, __METHOD__, [$this->game, $x_grid, $y_grid, $user->id, $this->data['team_id'], timestamp(true)]);

					//last_shoot neu setzen
					$sql = 'UPDATE stl_players SET last_shoot=? WHERE game_id=? AND user_id=?';
					$db->query($sql, __FILE__, __LINE__, __METHOD__, [timestamp(true), $this->game, $user->id]);
				}
			} else {
				//echo "gesunken";
				$msg = 1;
			}
		} else {
			$msg = 5;
		}
		if(!isset($msg)) {
			header("Location: ".SITE_URL."/stl2.php?do=game&game_id=".$this->game);
			exit;
		} else {
			header("Location: ".SITE_URL."/stl2.php?do=game&game_id=".$this->game."&msg=".urlencode($msg));
			exit;
		}
	}

}

$doAction = filter_input(INPUT_GET, 'do', FILTER_SANITIZE_SPECIAL_CHARS) ?? null;
$gameid = filter_input(INPUT_GET, 'game_id', FILTER_SANITIZE_NUMBER_INT) ?? null;
$shootat = filter_input(INPUT_GET, 'shoot', FILTER_SANITIZE_SPECIAL_CHARS) ?? null;
$message = filter_input(INPUT_GET, 'msg', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? null;

/** Load STLv2 */
$stl = new stlv2($gameid, $shootat);
if (!empty($message)) $stl->message = $message;

if($user->is_loggedin())
{
	if (isset($doAction))
	{
		switch ($doAction)
		{
			/** Show Game / and execute a shot */
			case 'game':
				if (isset($gameid)) $stl->game = $gameid;
				if(isset($shootat)) {
					$stl->shoot = $shootat;
					$stl->shoot();
				}
				$smarty->assign('tplroot', ['page_title' => 'Shoot the Lamber v2', 'menus' => ['games', 'zorg']]);
				$smarty->display('file:layout/head.tpl');
				echo $stl->data['game'];
				echo $stl->data['legende'];
				break;

			case "overview":
				$smarty->assign('tplroot', ['page_title' => 'Shoot the Lamber', 'menus' => ['games', 'zorg']]);
				$smarty->display('file:layout/head.tpl');
				echo $stl->data['overview'];
				echo $stl->data['legende'];
				break;

			case "reshuffle":
				$gameid = filter_input(INPUT_GET, 'game_id', FILTER_SANITIZE_NUMBER_INT) ?? null;
				$sql = 'DELETE FROM stl_positions WHERE game_id=?';
				$db->query($sql, __FILE__, __LINE__, __METHOD__, [$gameid]);
				$sql = 'UPDATE stl SET status = 0 WHERE game_id=?';
				$db->query($sql, __FILE__, __LINE__, __METHOD__, [$gameid]);
				header("Location: ".SITE_URL."/stl2.php?do=game&game_id=".$gameid);
				exit;
		}

	/** Default: Forward User into last open STL Game */
	} else {
		$sql = 'SELECT game_id FROM stl_players WHERE user_id=? ORDER by last_shoot DESC';
		$result = $db->query($sql, __FILE__, __LINE__, __METHOD__, [$user->id]);
		if($db->num($result)) {
			$rs = $db->fetch($result);
			header("Location: ".SITE_URL."/stl2.php?do=game&game_id=".$rs['game_id']);
			exit;
		} else {
			header("Location: ".SITE_URL."/stl2?do=overview");
			exit;
		}
	}

	$smarty->display('file:layout/footer.tpl');

/** Access denied for not-loggedin Visitors */
} else {
	$smarty->assign('tplroot', ['page_title' => 'Shoot the Lamber v2', 'menus' => ['games', 'zorg']]);
	$smarty->display('file:layout/head.tpl');
	echo "<b style='font-size:20px; color:#FF0000;'>Access denied!</b>";
}
