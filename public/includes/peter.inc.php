<?php
/**
 * Peter Funktionen
 *
 * Hier ist die Hauptklasse zum Peter Spiel zu finden,
 * sowie all seine Funktionen.
 *
 * @package zorg\Games\Peter
 */

/**
 * File includes
 * @include config.inc.php required
 * @include forum.inc.php
 * @include usersystem.inc.php required
 */
require_once __DIR__.'/config.inc.php';
include_once INCLUDES_DIR.'forum.inc.php';
require_once INCLUDES_DIR.'usersystem.inc.php';

/**
 * Peter Klasse
 *
 * Dies ist die Hauptklasse zum Peter Spiel
 *
 * @package zorg\Games\Peter
 * @version 3.0
 * @since 1.0 `[z]Duke` Class added
 * @since 2.0 `[z]domi`
 * @since 3.0 `18.08.2018` `IneX` Moved function for pending Peter-Games of a User as part of the Class
 */
class peter
{
	/**
	 * Class Vars
	 *
	 * @var int $game_id
	 * @var object $lc Last Card
	 * @var int $playersNum
	 * @var string $wunschFarbe
	 * @var array $next_zug_messagesubjects
	 */
	public $game_id = 0;
	public $lc = null;
	public $playersNum;
	public $wunschFarbe;
	private static $next_zug_messagesubjects = [
				 'Du bisch dra i &uuml;sem Peter Spiel'
				,'Maaach mol din Zug im Peter!'
				,'Hallo? Spielsch no mit im Peter oder wa?'
				,'Peter ruft!'
				,'Du Peter, spiel mol din Peter Zug!'
				,'Blah blah blah isch din Zug im Peter blah blah'
				,'Spiel Peter oder i segs dim Mami!'
			];

	/**
	 * Peter Klassenkonstruktor
	 *
	 * @param Game_ID $game_id
	 * @return void
	 */
	function __construct($game_id=null)
	{
		if (isset($_POST['players']) && is_numeric($_POST['players']) && $_POST['players'] > 0) $this->playersNum = (int)$_POST['players'];
		if (isset($_POST['wunsch']) && is_string($_POST['wunsch']) && strlen($_POST['wunsch']) >= 5) $this->wunschFarbe = (string)$_POST['wunsch'];

		if (isset($game_id) && is_numeric($game_id) && $game_id > 0)
		{
			$this->game_id = (int)$game_id;
			$this->lc = $this->lastcard();
		}
	}

	/**
	 * Rosenverkäufer
	 *
	 * Prüft und loggt den Rosenverkäufer ein, gibt beim Prüfen zurück ob er angemeldet ist
	 *
	 * @return int
	 * @param string $mode
	 */
	static function rosenverkaufer($mode='login')
	{
		global $db;

		/** Login Modus = Rosenverkäufer zufällig einloggen */
		if($mode === 'login')
		{
			/** würfeln ;) */
			$rand = rand(1,100);

			/** Wenn 23 gewürfelt wird... */
			if($rand === 23)
			{
				/** ...und Rosenverkäufer heute noch nicht online war */
				$sql = 'SELECT UNIX_TIMESTAMP(lastlogin) as lastlogin FROM user
						WHERE id=? AND UNIX_TIMESTAMP(lastlogin) < UNIX_TIMESTAMP(CAST(?-INTERVAL 7 DAY AS DATE))';
				$result = $db->query($sql, __FILE__, __LINE__, __METHOD__, [ROSENVERKAEUFER, timestamp(true)]);

				if ($db->num($result) !== FALSE)
				{
					/** ...Rosenverkäufer einloggen */
					$sql = 'UPDATE user SET currentlogin=?, activity=?, lastlogin=currentlogin WHERE id=?';
					$db->query($sql, __FILE__, __LINE__, __METHOD__, [timestamp(true), timestamp(true), ROSENVERKAEUFER]);
				} else {
					/** Return 0 so nothing will happen... */
					return 0;
				}
			}
		}
		/** Sonst prüfen ob der Rosenverkäufer eingeloggt ist */
		else {
			$sql = 'SELECT UNIX_TIMESTAMP(activity) as act FROM user WHERE id=? AND (UNIX_TIMESTAMP(activity)+?) > UNIX_TIMESTAMP(?)';
			$result = $db->query($sql, __FILE__, __LINE__, __METHOD__, [ROSENVERKAEUFER, USER_TIMEOUT, timestamp(true)]);
			$rosen = intval($db->num($result));

			return $rosen;
		}
	}

	/**
	 * Karten Ausgeben
	 *
	 * Gibt die Karten den Spielern aus
	 *
	 * @param int $gameId Pass $this->game_id
	 * @param int $numPlayers Pass number of Player for the $gameId
	 * @return void
	 */
	function ausgeben($gameId, $numPlayers)
	{
		global $db;

		/** DEPRECATED Game selektieren */
		/*$sql = 'SELECT *
				FROM peter_games
				WHERE game_id = '.$this->game_id;
		$result = $db->query($sql, __FILE__, __LINE__, __METHOD__);
		$rs = $db->fetch($result);*/

		/** DEPRECATED anzahl spieler ermitteln */
		//$players = $rs['players'];

		/** Wenn die Karten aufgehen */
		if((36 % $numPlayers) === 0)
		{
			/** Alle Karten selektieren */
			$sql = 'SELECT * FROM peter';
		}
		/** Wenn die Karten nicht aufgehen */
		else {
			/** Alle Karten selektieren ausser Eichel 6 (id 11) */
			$sql = 'SELECT * FROM peter WHERE card_id<>11';
		}

		/** karten selektieren */
		$result = $db->query($sql, __FILE__, __LINE__, __METHOD__);
		$num_cards = $db->num($result);
		while($rs = $db->fetch($result)) {
			$karten[] = $rs['card_id'];
		}

		/** karten mischen */
		shuffle($karten);

		/** spieler selektieren */
		$sql = 'SELECT * FROM peter_players WHERE game_id=?';
		$result = $db->query($sql, __FILE__, __LINE__, __METHOD__, [$this->game_id]);
		while($rs = $db->fetch($result)) {
			$in = ($rs['join_id'] - 1);
			$pp[$in] = $rs['user_id'];
		}

		/** karten den spielern ausgeben */
		for($i = 0;$i<$num_cards;$i++)
		{
			$in = ($i % $numPlayers);
			$uu = $pp[$in];
			$sql = 'INSERT INTO peter_cardsets (game_id, card_id, user_id, status, datum) VALUES (?, ?, ?, "nicht gelegt", ?)';
			$db->query($sql, __FILE__, __LINE__, __METHOD__, [$this->game_id, $karten[$i], $uu, timestamp(true)]);
			$card[$uu][] = $karten[$i];
		}
		/** game status updaten */
		$sql = 'UPDATE peter_games set status="lauft" WHERE game_id=?';
		$db->query($sql, __FILE__, __LINE__, __METHOD__, [$this->game_id]);
	}

	/**
	 * Nächster Spieler
	 *
	 * Schaltet den nächsten Player in einem Spiel frei
	 *
	 * @author ?
	 * @version 4.0
	 * @since 1.0 method added
	 * @since 2.0 22.08.2011/IneX added (random) Message Notification to next Player
	 * @since 3.0 code & query optimizations
	 * @since 4.0 `25.11.2018` updated to use new $notifcation Class & some code and query optimizations
	 *
	 * @param integer $act_player User-ID des aktuellen Spielers
	 * @param integer $players Anzahl Spieler (num_players)
	 * @global object $db Globales Class-Object mit allen MySQL-Methoden
	 * @global object $notification Globales Class-Object mit allen Notification-Methoden
	 * @return boolean
	 */
	function next_player($act_player, $players)
	{
		global $db, $notification;

		/** Join ID des aktuellen Spielers ermitteln */
		$sql = 'SELECT join_id FROM peter_players pp WHERE pp.user_id=? AND pp.game_id=?';
		$rq = $db->query($sql, __FILE__, __LINE__, __METHOD__, [$act_player, $this->game_id]);
		$rr = $db->fetch($rq);

		/** Join ID inkrementieren oder wenn max players erreicht wurde wieder bei 1 anfangen */
		$next_join_id = ($rr['join_id'] == $players) ? 1 : $rr['join_id'] + 1;

		$sql = 'SELECT user_id, make FROM peter_players pp WHERE pp.game_id=? AND pp.join_id=?';
		$rr = $db->query($sql, __FILE__, __LINE__, __METHOD__, [$this->game_id, $next_join_id]);
		$rr = $db->fetch($rr);

		/** Prüfen ob der Spieler nicht bereits fertig ist */
		if($rr['make'] == "fertig") {
			$this->next_player($rr['user_id'],$players);
		}

		/** Nächster Spieler freischalten */
		$sql = 'UPDATE peter_games set next_player=?, last_activity=? WHERE game_id=?';
		$db->query($sql, __FILE__, __LINE__, __METHOD__, [$rr['user_id'], timestamp(true), $this->game_id]);

		/** Sendet dem nächsten Spieler eine (random) Message, damit er weiss, dass er dran ist */
		$text = 'I ha min Zug gmacht i &uuml;sem Peter Spiel, etz bisch du wieder dra!<br/><br/>&#8594; <a href="'.SITE_URL.'/peter.php?game_id='.$this->game_id.'">Mach doooo!</a>';
		$rand_subject = self::$next_zug_messagesubjects[array_rand(self::$next_zug_messagesubjects,1)];
		//Messagesystem::sendMessage($act_player, $rr['user_id'], $rand_subject, $text);
		$notification_status = $notification->send($act_player, 'games', ['from_user_id'=>$rr['user_id'], 'subject'=>$rand_subject, 'text'=>$text, 'message'=>$text]);
		if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> $notification_status: %s', __METHOD__, __LINE__, ($notification_status == 'true' ? 'true' : 'false')));

		return true;
	}

	/**
	 * Peter Aktionen & Checks ausführen
	 *
	 * Game erstellen & starten, Wunsch setzen, Nächster Spieler setzen
	 *
	 * @return void
	 */
	function exec_peter()
	{
		global $db;

		/** Neues Peter Game starten */
		if(isset($this->playersNum))
		{
			/** Prüfen das players nicht 6 übersteigt */
			if ($this->playersNum >= 6) $players = 6;
			/** Wenns weniger sind als zwei, werden zwei verwendet */
			elseif ($this->playersNum <= 1) $players = 2;
			/** Alle anderen Werte sind OK */
			else $players = $this->playersNum;

			/** Game aufmachen */
			$this->peteruuf($players);
		}

		/** Wenn ein Wunsch da ist ;) */
		if (isset($this->wunschFarbe))
		{
			$this->set_wunsch($this->wunschFarbe);
			header('Location: '.getChangedURL('game_id='.$this->game_id));
			exit();
		}

		if (isset($this->game_id))
		{
			/** Prüfen ob in dem spiel nicht autom. der letzte spieler wieder am zug ist */
			if (isset($this->lc['value']) && is_numeric($this->lc['value']) && $this->lc['value'] > 0) $this->auto_nextplayer();

			/** Inaktive Spieler übergehen */
			$sql = 'SELECT game_id, players, next_player, last_activity FROM peter_games
					WHERE (UNIX_TIMESTAMP(?)-(86400*2)) > UNIX_TIMESTAMP(last_activity) AND status="lauft"';
			$result = $db->query($sql, __FILE__, __LINE__, __METHOD__, [timestamp(true)]);
			$old_game_id = $this->game_id;

			/** Nächster Spieler freischalten */
			while($rs = $db->fetch($result))
			{
				$this->game_id = $rs['game_id'];
				$this->next_player($rs['next_player'], $rs['players']);
			}
			$this->game_id = $old_game_id;
		}
	}

	/**
	 * Farbe wünschen
	 *
	 * Setzt einen Wunsch für ein Game
	 *
	 * @return void
	 * @param string $wunsch
	 */
	function set_wunsch($wunsch)
	{
		global $db, $user;

		/** Prüfen ob noch ein Wunsch gsetzt werden kann */
		if(!$this->checkwunsch())
		{
			/** Wunsch setzten */
			// TODO change to $db->insert()
			$sql = 'INSERT into peter_wunsche (game_id, card_id, user_id, wunsch, datum) VALUES (?, ?, ?, ?, ?)';
			$db->query($sql, __FILE__, __LINE__, __METHOD__, [$this->game_id, $this->lc['card_id'], $user->id, $wunsch, timestamp(true)]);

			/** Anzahl Players ermitteln */
			$sql = 'SELECT players FROM peter_games WHERE game_id=?';
			$result = $db->query($sql, __FILE__, __LINE__, __METHOD__, [$this->game_id]);
			$rs = $db->fetch($result);

			/** Nächster Spieler freischalten */
			$this->next_player($user->id, $rs['players']);
		}
	}

	/**
	 * Spiel erstellen
	 *
	 * Eröffnet ein Spiel, und joint den erstellen autom.
	 *
	 * @return void
	 * @param integer $players num_players
	 */
	function peteruuf($players)
	{
		global $db, $user;

		/** Neues Spiel erstellen */
		$sql = 'INSERT INTO peter_games (players, next_player, status, last_activity) VALUES (?, ?, "offen", ?)';
		$game_id = $db->query($sql, __FILE__, __LINE__, __METHOD__, [$players, $user->id, timestamp(true)]);

		/** ...und user grad joinen */
		$sql = 'INSERT INTO peter_players (game_id, join_id, user_id) VALUES (?, 1, ?)';
		$db->query($sql, __FILE__, __LINE__, __METHOD__, [$game_id, $user->id]);

		/** Activity Eintrag auslösen */
		Activities::addActivity($user->id, 0, t('activity-newgame', 'peter', [ SITE_URL, $game_id ]), 'pt');
	}

	/**
	 * Ausstehende Peter Züge
	 * Gibt die Anzahl ausstehenden Peter züge zurück
	 *
	 * @author [z]Duke
	 * @author [z]domi
	 * @author IneX
	 * @version 3.0
	 * @since 1.0 function added
	 * @since 2.0 `18.08.2018` function moved as method of peter()-Class
	 * @since 3.0 `06.09.2018` function returns now only game_id if >0 open Games are found - or 0 if none
	 *
	 * @see header.php
	 * @global object $db Globales Class-Object mit allen MySQL-Methoden
	 * @global object $user Globales Class-Object mit den User-Methoden & Variablen
	 * @return boolean True/false depening if query result exists or not
	 */
	public static function peter_zuege()
	{
		global $db, $user;

		/** Nur wenn user eingeloggt ist */
		if($user->is_loggedin())
		{
			/** Anzahl offener Peter Züge des Users holen */
			$sql = 'SELECT COUNT(*) num_open, game_id FROM peter_games WHERE next_player=? AND status = "lauft" GROUP BY game_id';
			$peter_games = $db->fetch($db->query($sql, __FILE__, __LINE__, __METHOD__, [$user->id]));
			return (!empty($peter_games) && $peter_games['num_open'] > 0 ? $peter_games : 0);
		} else {
			return false;
		}
	}


	/**
	 * Offene Peter Spiele anzeigen
	 *
	 * Gibt die offenen Spiele zurück
	 *
	 * @version 2.0
	 * @since 1.0 method added
	 * @since 2.0 code, queries and functions optimized
	 *
	 * @return string HTML-Output
	 */
	function offene_spiele()
	{
		global $db, $user;

		$html = '<h3>Offene Spiele</h3>';
		$sql = 'SELECT * FROM peter_games WHERE status="offen"';
		$result = $db->query($sql, __FILE__, __LINE__, __METHOD__);

		if ($db->num($result) > 0)
		{
			$html .= "<table>
						<thead>
							<tr>
								<th>Spiel</th>
								<th>Spieler</th>
								<th>Status</th>
							</tr>
						</thead>
						<tbody>";

			while($rs = $db->fetch($result))
			{
				$html .= "<tr><td>".$rs['game_id']."</td><td>";

				$sql = 'SELECT * FROM peter_players pp WHERE pp.game_id=? ORDER by pp.join_id ASC';
				$resulti = $db->query($sql, __FILE__, __LINE__, __METHOD__, [$rs['game_id']]);

				$gejoint = FALSE;
				$players = [];
				while ($rsi = $db->fetch($resulti)) {
					$players[] = $user->userprofile_link($rsi['user_id'], ['pic' => false, 'link' => TRUE, 'username' => true, 'clantag' => true]);
					if (intval($rsi['user_id']) === $user->id) $gejoint = TRUE;
				}
				$playerListe = implode(', ', $players);
				$html .= '[' . count($players) . '/' . $rs['players'] . '] ' . $playerListe;
				$html .= '</td>';

				if ($gejoint == FALSE)
				{
					$html .= '<td><a href="?game_id='.$rs['game_id'].'" class="button"><b>'.t('game-join').'</b></a></td></tr>';
				} else {
					$numWaitingFor = $rs['players']-count($players);
					$html .= sprintf('<td><i>%s %s</i></td></tr>', t('game-your-game'), ($numWaitingFor > 0 ? t('waiting-for-num-players', 'peter', [$numWaitingFor]) : ''));
				}
			}
			$html .= '</tbody></table>';
		} else {
			$html .= '<b>Nada...</b> Aber starte doch ä neus! ;)';
		}

		return $html;
	}

	/**
	 * Laufende Spiele
	 * Gibt alle laufenden Spiele zurück
	 *
	 * @author [z]Duke
	 * @author [z]domi
	 * @author IneX
	 * @version 3.0
	 * @since 1.0 method added
	 * @since 2.0 fixed SQL-Query, added Code docu
	 * @since 3.0 `05.09.2018` added method param $return_html to allow also non-view output as return
	 *
	 * @param boolean $return_html TRUE=return view (HTML), FALSE=only IDs of running Peter games - Default: TRUE
	 * @global object $db Globales Class-Object mit allen MySQL-Methoden
	 * @global object $user Globales Class-Object mit den User-Methoden & Variablen
	 * @return string|array
	 */
	function laufende_spiele($return_html=true)
	{
		global $db, $user;

		$sql = 'SELECT pg.game_id, pg.next_player, pg.players,
					(SELECT join_id FROM peter_players WHERE game_id=pp.game_id AND user_id=pg.next_player) join_id
				FROM peter_players pp LEFT JOIN peter_games pg ON pg.game_id=pp.game_id WHERE pg.status = "lauft" GROUP BY pg.game_id';
		$result = $db->query($sql, __FILE__, __LINE__, __METHOD__);

		if ($return_html)
		{
			$html = '<h3>Alle laufenden Spiele</h3>';

			if ($db->num($result) > 0)
			{
				$html .= "<table cellpadding='2' cellspacing='1' bgcolor='".BORDERCOLOR."'>
						<tr><td class='title' bgcolor='".TABLEBACKGROUNDCOLOR."'>
						Spiel ID
						</td><td class='title' bgcolor='".TABLEBACKGROUNDCOLOR."'>
						Spieler
						</td><td class='title' bgcolor='".TABLEBACKGROUNDCOLOR."'>
						am Zug
						</td></tr>";

				while($rs = $db->fetch($result))
				{
					$spieler = '';
					$sql = 'SELECT pp.user_id FROM peter_players pp WHERE pp.game_id=? ORDER by pp.join_id ASC';
					$resulti = $db->query($sql, __FILE__, __LINE__, __METHOD__, [$rs['game_id']]);
					while($rsi = $db->fetch($resulti)) {
						$spieler .= $user->link_userpage($rsi['user_id']).", ";
					}

					$html .= "<tr align='left' bgcolor='".BACKGROUNDCOLOR."'>
								<td>
									<a href='".htmlentities($_SERVER['PHP_SELF'])."?game_id=".$rs['game_id']."'>".$rs['game_id']."</a>
								</td>
								<td>".$spieler."</td>
								<td bgcolor='".BACKGROUNDCOLOR."'>
									".$user->link_userpage($rs['next_player'])."
								</td>
							</tr>";
				}
				$html .= "</table>";
			} else {
				$html .= '<b>None.</b> Alles erlediget... oder niermert het bock zum spiele *schulterzuck*';
			}
		} else {
			while($rs = $db->fetch($result))
			{
				$runningGames[] = [ 'game_id' => $rs['game_id'], 'current_player' => $rs['next_player'], 'join_id' => $rs['join_id'], 'total_players' => $rs['players'] ];
			}
			if (DEVELOPMENT === true) error_log(sprintf('[DEBUG] <%s:%d> $runningGames[]: %s', __METHOD__, __LINE__, print_r($runningGames,true)));
		}
		return ($return_html ? $html : $runningGames);
	}

	/**
	 * Meine offene Spiele
	 *
	 * Gibt die laufenden Spiele eines Benutzers zurück
	 *
	 * @since 2.0
	 * @version 1.0
	 * @author IneX
	 *
	 * @global object $db Globales Class-Object mit allen MySQL-Methoden
	 * @global object $user Globales Class-Object mit den User-Methoden & Variablen
	 * @return string
	 */
	function meine_laufende_spiele($user_id)
	{
		global $db, $user;

		if (!empty($user_id) && $user_id > 0)
		{
			$sql = 'SELECT pg.game_id, pg.next_player FROM peter_players pp
					LEFT JOIN peter_games pg ON pg.game_id = pp.game_id
					WHERE pg.status = "lauft" AND pp.user_id=?';
			$result = $db->query($sql, __FILE__, __LINE__, __METHOD__, [$user_id]);

			$html = '<h3>Meine Spiele</h3>';
			if ($db->num($result) > 0)
			{
				$html .= "<table cellpadding='2' cellspacing='1' bgcolor='".BORDERCOLOR."'>
				<tr><td class='title' bgcolor='".TABLEBACKGROUNDCOLOR."'>
				Spiel ID
				<td class='title' bgcolor='".TABLEBACKGROUNDCOLOR."'>
				Spieler
				</td><td class='title' bgcolor='".TABLEBACKGROUNDCOLOR."'>
				am Zug
				</td></tr>";

				while($rs = $db->fetch($result)) {
					$spieler = '';
					$sql = 'SELECT pp.user_id FROM peter_players pp WHERE pp.game_id=? ORDER by pp.join_id ASC';
					$resulti = $db->query($sql, __FILE__, __LINE__, __METHOD__, [$rs['game_id']]);
					while($rsi = $db->fetch($resulti)) {
						$spieler .= $user->link_userpage($rsi['user_id']).", ";
					}
					$col = ($rs['next_player'] == $user_id) ? '#FF0000' : '#'.BACKGROUNDCOLOR;

					$html .= "<tr align='left' bgcolor='".$col."'>
								<td>
									<strong><a href='".htmlentities($_SERVER['PHP_SELF'])."?game_id=".$rs['game_id']."'>".$rs['game_id']."</a></strong>
								</td>
								<td>".$spieler."</td>
								<td bgcolor='".BACKGROUNDCOLOR."'>
									".$user->link_userpage($rs['next_player'])."
								</td>
							</tr>";
				}
				$html .= "</table>";
			} else {
				$html .= '<b>Keine...</b> *strohballen roll*';
			}
			return $html;
		} else {
			return false;
		}
	}

	/**
	 * Joint einen Spieler in ein offenes Peter Game
	 *
	 * @version 1.1
	 * @since 1.0 method added
	 * @since 1.1 `01.09.2019` `IneX` Changed query: INSERT INTO => REPLACE INTO
	 * @param integer $players Max. Number of Players for game
	 * @return void
	 */
	function peter_join($players)
	{
		global $db, $user;

		/** Anzahl bisher gejointe ermitteln */
		if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> Peter Game #%d - Players: %d', __METHOD__, __LINE__, $this->game_id, $players));
		$sql = 'SELECT COUNT(*) as num_players FROM peter_players WHERE game_id=?';
		$result = $db->fetch($db->query($sql, __FILE__, __LINE__, __METHOD__, [$this->game_id]));
		//$num = $db->num($result) + 1;
		if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> Peter Game #%d - Num Players: %d', __METHOD__, __LINE__, $this->game_id, $result['num_players']));
		$numJoin = $result['num_players'] + 1;
		if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> Peter Game #%d - Join as: %d', __METHOD__, __LINE__, $this->game_id, $numJoin));

		/** player eintragen */
		if ($numJoin <= $players)
		{
			$sql = 'REPLACE INTO peter_players (game_id, join_id, user_id) VALUES (?, ?, ?)';
			$db->query($sql, __FILE__, __LINE__, __METHOD__, [$this->game_id, $numJoin, $user->id]);

			/** prüfen ob game gestartet werden soll */
			if ($numJoin === $players)
			{
				if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> Peter Game #%d - %d Players vs. %d my Join', __METHOD__, __LINE__, $this->game_id, $players, $numJoin));
				/** Karten ausgeben */
				$this->ausgeben($this->game_id, $players); // Pass $game_id & num $players
				header('Location: '.getChangedURL('game_id='.$this->game_id));
				exit();
			} else {
				if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> Peter Game #%d - No Start yet: Players %d vs. my Join %d', __METHOD__, __LINE__, $this->game_id, $players, $numJoin));
			}
		}
		header('Location: '.getURL(false, false));
		exit();
	}

	/**
	 * Gibt alle wichtigen Infos zur zuletzt gelegten Karte als Array zurück
	 *
	 * @version 1.1
	 * @since 1.0 method added
	 * @since 1.1 `14.10.2020` `IneX` fixed PHP Fatal error: Cannot use object of type mysqli_result as array
	 *
	 * @return Array
	 */
	function lastcard()
	{
		global $db;
		$sql = 'SELECT p.description, p.value, p.col, pc.card_id, pc.spezial, pc.user_id
				FROM peter_cardsets pc LEFT JOIN peter p ON p.card_id = pc.card_id
				WHERE pc.game_id=? AND pc.status="gelegt" ORDER by pc.datum DESC LIMIT 1';
		$res = $db->fetch($db->query($sql, __FILE__, __LINE__, __METHOD__, [$this->game_id]));

		if(!empty($res['spezial']) && $res['spezial'] === 'rosen')
		{
			$sql = 'SELECT p.description, p.value, p.col, pc.card_id, pc.spezial, pc.user_id
					FROM peter_cardsets pc LEFT JOIN peter_spezialregeln p ON p.card_id = pc.card_id
					WHERE pc.game_id=? AND pc.status="gelegt" AND p.typ="rosen" ORDER by pc.datum DESC LIMIT 1';
			$res = $db->fetch($db->query($sql, __FILE__, __LINE__, __METHOD__, [$this->game_id]));
		}

		return $res;
	}

	/**
	 * Spielerstatus anhand nicht gelegter Karten ermitteln
	 *
	 * Gibt den Status (anzahl noch verbleibende Karten) zu jedem Spieler zurück
	 *
	 * @global object $db Globales Class-Object mit allen MySQL-Methoden
	 * @global object $user Globales Class-Object mit den User-Methoden & Variablen
	 * @return string
	 */
	function spielerstatus()
	{
		global $db, $user;

		$sql = 'SELECT pp.join_id, pp.user_id, tt.num_cards
				FROM (SELECT COUNT(pc.card_id) as num_cards, pc.user_id as user_id FROM peter_cardsets pc WHERE pc.game_id=? AND pc.status = "nicht gelegt" GROUP by pc.user_id) as tt LEFT JOIN peter_players pp ON pp.user_id = tt.user_id
				WHERE pp.game_id=? ORDER by join_id ASC';
		$result = $db->query($sql, __FILE__, __LINE__, __METHOD__, [$this->game_id, $this->game_id]);

		$html = "<table cellpadding='2' cellspacing='1' bgcolor='".BORDERCOLOR."'><tr>";
		while($rs = $db->fetch($result)) {
			$html .= "<td bgcolor='".TABLEBACKGROUNDCOLOR."'>".$user->id2user($rs['user_id'])." : <b>".$rs['num_cards']."</b> Karten</td>";
		}
		$html .= "</tr></table>";

		return $html;
	}

	/**
	 * Spielzug an nächsten Player weitergeben
	 *
	 * Aktiviert autom. den nächsten Spieler wenn keiner eine höhere Karte hat
	 *
	 * @author [z]Duke
	 * @author [z]domi
	 * @author IneX
	 * @version 3.0
	 * @since 1.0 method added
	 * @since 2.0 added feature to FORCE next player, if current player didn't play for a long time
	 * @since 3.0 `25.11.2018` updated to use new $notifcation Class & some code and query optimizations
	 *
	 * @param boolean|array $force_next_player Array=Array with game_id,next_player,players - or FALSE, if regular check. Default: FALSE
	 * @global object $db Globales Class-Object mit allen MySQL-Methoden
	 * @global object $notification Globales Class-Object mit allen Notification-Methoden
	 * @return boolean
	 */
	function auto_nextplayer($force_next_player=false)
	{
		global $db, $notification;
		if ($force_next_player === false)
		{
			/** Prüfen ob der Rosenverkäufer da ist */
			$rosen = $this->rosenverkaufer('check');

			/** Rosenverkäufer ist da */
			$params = [];
			if($rosen === 1 && isset($this->lc['user_id']))
			{
				$sql = 'SELECT * FROM peter_cardsets pc LEFT JOIN peter p ON p.card_id = pc.card_id
						WHERE game_id=? AND status = "nicht gelegt" AND col=2 AND user_id<>?';
				$params[] = $this->game_id;
				$params[] = $this->lc['user_id'];
				/** SQL-Query erklärt:
				 * - game_id 	= aktuelles Spiel
				 * - status		= nur nicht gelegte Karten der Spieler
				 * - col		= color (Farbe) nur Rosen (color '2')
				 * - user_id	= nicht der gleiche wie die letzte Karte gelegt hat
				 */

			/** Der Rosenverkäufer ist NICHT da */
			} elseif (isset($this->lc['value'])) {
				$sql = 'SELECT * FROM peter_cardsets pc LEFT JOIN peter p ON p.card_id = pc.card_id
						WHERE game_id=? AND status = "nicht gelegt" AND value>?';
				$params[] = $this->game_id;
				$params[] = $this->lc['value'];
				/** SQL-Query erklärt:
				 * - game_id = aktuelles Spiel
				 * - status	= nur nicht gelegte Karten der Spieler
				 * - value	= nur wer eine Karte mit GRÖSSEREM Wert als der letzte Spieler hat
				 */
			}
			$result = $db->query($sql, __FILE__, __LINE__, __METHOD__, $params);

			/**
			 * Wenn niemand eine höhere Karte hat
			 * (respektive auch keine Rosen wenn der Rosenverkäufer da ist...)
			 */
			if($db->num($result) == FALSE)
			{
				/** Letzten Spieler nochmals aktivieren */
				$sql = 'UPDATE peter_games SET next_player=?, last_activity=? WHERE game_id=?';
				$db->query($sql, __FILE__, __LINE__, __METHOD__, [$this->lc['user_id'], timestamp(true), $this->game_id]);
			}

			/** Karten Daten für die "Letzte gelegte Karte" neu laden */
			$this->lc = $this->lastcard();

		/** Zug auf nächsten Player erzwingen (benötigt valide User-ID) */
		} else {
			if (is_array($force_next_player) && count($force_next_player) > 0)
			{
				/**
				 * Nächsten Spieler finden
				 * (Join ID inkrementieren oder wenn max players erreicht wurde wieder bei 1 anfangen)
				 */
				$next_join_id = ($force_next_player['join_id'] == $force_next_player['total_players']) ? 1 : $force_next_player['join_id']+1;
				if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> $next_join_id: %d', __METHOD__, __LINE__, $next_join_id));
				if (!empty($next_join_id) && $next_join_id != $force_next_player['join_id'])
				{
					/** Check if last_activity is older than 7 days */
					$sql = 'SELECT user_id, make FROM peter_players
							WHERE game_id=? AND join_id=? AND DATE((SELECT last_activity FROM peter_games WHERE game_id=?))<(?-INTERVAL 7 DAY';
					$next_player = $db->fetch($db->query($sql, __FILE__, __LINE__, __METHOD__, [$force_next_player['game_id'], $next_join_id, $force_next_player['game_id'], timestamp(true)]));
					if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> $next_player: %s', __METHOD__, __LINE__, print_r($next_player,true)));

					/** Prüfen ob der Spieler nicht bereits fertig ist */
					if(!empty($next_player) && count($next_player) > 0 && $next_player['make'] != 'fertig')
					{
						/** Nächster Spieler freischalten */
						$sql = 'UPDATE peter_games SET next_player=?, last_activity=? WHERE game_id=?';
						if ($db->query($sql, __FILE__, __LINE__, __METHOD__, [$next_player['user_id'], timestamp(true), $force_next_player['game_id']]))
						{
							if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> Nächster Spieler freischalten: OK', __METHOD__, __LINE__));
							/** Sendet dem nächsten Spieler eine (random) Message, damit er weiss, dass er dran ist */
							$text = 'I bi z fuul gsii zum min Zug i &uuml;sem Peter Spiel zmache, drum bisch du etz wieder dra!<br/><br/>&#8594; <a href="'.SITE_URL.'/peter.php?game_id='.$force_next_player['game_id'].'">Mach doooo!</a>';
							$rand_subject = self::$next_zug_messagesubjects[array_rand(self::$next_zug_messagesubjects,1)];
							//if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> Messagesystem::sendMessage(): %s', __METHOD__, __LINE__, print_r([$force_next_player['current_player'], $next_player['user_id'], $rand_subject, $text],true)));
							//Messagesystem::sendMessage($force_next_player['current_player'], $next_player['user_id'], $rand_subject, $text);
							$notification_status = $notification->send($next_player['user_id'], 'games', ['from_user_id'=>$force_next_player['current_player'], 'subject'=>$rand_subject, 'text'=>$text, 'message'=>$text]);
							if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> $notification_status: %s', __METHOD__, __LINE__, ($notification_status == 'true' ? 'true' : 'false')));

							return true;
						}
					} else {
						error_log(sprintf('[NOTICE] <%s:%d> No next player found / next turn not due yet', __METHOD__, __LINE__));
						return false;
					}
				} else {
					error_log(sprintf('[NOTICE] <%s:%d> $next_join_id: No next Player found', __METHOD__, __LINE__));
					return false;
				}
			} else {
				error_log(sprintf('[NOTICE] <%s:%d> Passed $force_next_player is NOT an array(): %s', __METHOD__, __LINE__, $force_next_player));
				return false;
			}
		}
	}

	/**
	 * ermittelt ob ein Zug zulässig ist oder nicht
	 *
	 * @return bool
	 * @param $card_id Card_ID
	 */
	function regelcheck($card_id)
	{
		global $db, $user;

		/** Grundsätzlich den Zug einmal als falsch einstufen */
		$set = 0;

		/** Prüfen ob der Rosenverkäufer da ist */
		$rosen = $this->rosenverkaufer('check');

		/** Rosenverkäuferanpassungen */
		$regel_table = ($rosen == 1) ? "peter_spezialregeln" : "peter";
		$regel_add = ($rosen == 1) ? "AND typ='rosen'" : " ";

		/** Prüfen ob bereits eine Karte gelegt wurde */
		$sql = 'SELECT * FROM peter_cardsets pc WHERE pc.game_id=? AND pc.status = "gelegt"';
		$rr = $db->query($sql, __FILE__, __LINE__, __METHOD__, [$this->game_id]);

		/** Bei Spiel Anfang kann alles gelegt werden */
		if (!$db->num($rr)) $set = 1;

		/** Prüfen ob der User die Karte noch hat */
		$sql = 'SELECT * FROM peter_cardsets pc WHERE pc.game_id=? AND pc.user_id=? AND pc.card_id=? AND pc.status="nicht gelegt"';
		$rr = $db->query($sql, __FILE__, __LINE__, __METHOD__, [$this->game_id, $user->id, $card_id]);
		if($db->num($rr))
		{
			/** Prüfen ob Karte gesetzt werden darf */
			$sql = 'SELECT * FROM '.$regel_table.' WHERE card_id=? '.$regel_add;
			$ac = $db->fetch($db->query($sql, __FILE__, __LINE__, __METHOD__, [$card_id]));

			/** Wenn die zulegende Karte ein Zahl ist */
			if($ac['value'] <= 5 && $this->lc['value'] != 0)
			{
				/** Prüfen das die gleichen Farben gelegt werden */
				if($ac['col'] == $this->lc['col'])
				{
					/** prüfen das der wert der karte höher ist */
					if($ac['value'] > $this->lc['value'] ) $set = 1;
				}
			/** Wenn keine Zahl und kein Nichtraucher */
			} elseif($this->lc['value'] != 0 && $ac['value'] > 5) {
				/** Prüfen das der wert der zulegenden Karte höher ist */
				if($ac['value'] > $this->lc['value']) $set = 1;
			/** Wenn die letzte Karte ein Nichtraucher wae */
			} elseif($this->lc['value'] == 0) {
				/** wunsch selektieren */
				$wunsch = $this->get_wunsch();
				/** Prüfen ob aktuell zulegende karte eine zahl ist */
				if($ac['value'] <= 5)
				{
					/** prüfen ob die farbe der zahl mit dem wunsch übereinstimmt */
					if($ac['col'] == $wunsch['col_id']) {
						$set = 1;
					}
				/** Wenns keine Zahl ist */
				} else {
					$set = 1;
				}
				/** Wenn ein Nichtraucher draufgelegt werden soll */
				if($ac['value'] == 0) $set = 1;
			}
			/** Wenn die letzte geleget Karte vom gleichen Spieler ist wie die zulegende */
			if($this->lc['user_id'] === $user->id) $set = 1;
		}
		return $set;
	}

	/**
	 * Prüft ob bereits ein Wunsch zu einem Nichtraucher abgegeben wurde oder nicht
	 *
	 * @return bool
	 */
	function checkwunsch()
	{
		global $db;

		/** Wenn die zuletzt gelegte Karte ein Nichtraucher ist und wenn auch eine gelegt wurde */
		if(isset($this->lc['value']) && isset($this->lc['card_id']) && $this->lc['value'] === 0 && $this->lc['card_id'])
		{
			/** Prüfen ob bereits ein Wunsch zu dieser Karte vorliegt */
			$sql = 'SELECT pw.wunsch, pw.user_id FROM peter_wunsche pw WHERE pw.game_id=? AND pw.card_id=?';
			$result = $db->query($sql, __FILE__, __LINE__, __METHOD__, [$this->game_id, $this->lc['card_id']]);

			/** Wenn bereits ein Wunschvorliegt */
			if($db->num($result)) {
				return 1;
			} else {
				return 0;
			}
		} else {
			return 1;
		}
	}

	/**
	 * Gibt die Daten zum letzten Wunsch als Array zurück
	 *
	 * @global object $db Globales Class-Object mit allen MySQL-Methoden
	 * @global object $user Globales Class-Object mit den User-Methoden & Variablen
	 * @return array
	 */
	function get_wunsch()
	{
		global $db, $user;

		/** Ermittelt den Letzten Wunsch zu einem game */
		$sql = 'SELECT pw.wunsch, pw.user_id FROM peter_wunsche pw WHERE pw.game_id=? ORDER by datum DESC LIMIT 1';
		$result = $db->query($sql, __FILE__, __LINE__, __METHOD__, [$this->game_id]);
		$rs = $db->fetch($result);

		if (false !== $rs)
		{
			/** zuweisungs Array */
			$a_zw = array('Eichel' => 1, 'Rosen' => 2, 'Schellen' => 3, 'Schilten' => 4);

			/** Daten Array füllen */
			$data['col_id'] = $a_zw[$rs['wunsch']];
			$data['col'] = $rs['wunsch'];
			$data['username'] = $user->id2user($rs['user_id']);
		}
		return (isset($data) ? $data : null);
	}

	/**
	 * Führt einen Zug aus
	 *
	 * @version 1.1
	 * @since 1.0 method added
	 * @since 1.1 `04.12.2024` `IneX` Fixed optional parameter declared before required parameter implicitly treated as required
	 *
	 * @TODO Zug als Comment eintragen, damit man darüber diskutieren kann
	 *
	 * @return void
	 * @param Card_ID $card_id
	 * @param (karte|aus) $make
	 * @param num_players $players
	 */
	function zug($card_id=null, $make=null, $players=0)
	{
		global $db, $user;

		/** Prüfen ob der Rosenverkäufer umherschleicht */
		$rosen = $this->rosenverkaufer('check');
		$spezial = ($rosen === 1 ? 'rosen' : ''); // ENUM('rosen','')

		/** Wenn eine Karte gesetzt werden soll */
		if($make === 'karte')
		{
			/** prüfen ob der zug zulässig ist */
			if($this->regelcheck($card_id)) {
				/** Zug ausführen */
				$sql = 'UPDATE peter_cardsets SET datum=?, status="gelegt", spezial=? WHERE game_id=? AND card_id=?';
				$db->query($sql, __FILE__, __LINE__, __METHOD__, [timestamp(true), $spezial, $this->game_id, $card_id]);

				// TODO Zug als Comment eintragen, damit man darüber diskutieren kann
				/*
				$sql = "SELECT * FROM peter_cardsets WHERE card_id =?";
				$result = $db->query($sql, __FILE__, __LINE__, __METHOD__, [$card_id]);
				$rs = $db->fetch($result);
				$text = "Ich habe die Karte '".$rs[description]."' gelegt.";
				Forum::post($this->game_id, 'p', $user->id, $text, $msg_users='');
				*/

				/** Spieler Table updaten */
				$sql = 'UPDATE peter_players SET make=? WHERE user_id=? AND game_id=?';
				$db->query($sql, __FILE__, __LINE__, __METHOD__, [$make, $user->id, $this->game_id]);

				/** Prüfen ob nicht noch ein Wunsch nötig ist bevor der nächste am zug ist */
				if($this->checkwunsch())
				{
					/** Nächster Player aktivieren */
					$this->next_player($user->id, $players);
				}
			}
		}

		if($make === 'aus')
		{
			/** Zug als Comment eintragen, damit man darüber diskutieren kann */
			/*$text = "Ich setze diese Runde aus.";
			Forum::post($this->game_id, 'p', $user->id, $text, $msg_users='');
			 */

			/** Spieler Table updaten */
			$sql = 'UPDATE peter_players set make=? WHERE user_id=? AND game_id=?';
			$db->query($sql, __FILE__, __LINE__, __METHOD__, [$make, $user->id, $this->game_id]);

			$this->next_player($user->id, $players);

			/**
			 * Wenn der User noch weitere offen Züge hat, direkt weiterleiten
			 * Prüfen, ob noch Züge offen sind
			 */
			$sqli = 'SELECT game_id FROM peter_games WHERE next_player=? AND status="lauft"';
			$resulti = $db->query($sqli, __FILE__, __LINE__, __METHOD__, [$user->id]);
			$rsi = $db->fetch($resulti);

			/** Wenn noch offene Züge, dann direkt ins nächste Spiel weiterleiten */
			//$locationHeader = 'Location: '.SITE_URL.'/peter.php?game_id='.$rs[game_id];
			if ($db->num($resulti) > 0) header('Location: '.getChangedURL('game_id='.$rsi['game_id']));
			exit();
		}

		/** Prüfen ob Spiel beendet werden soll */
		$sql = 'SELECT * FROM peter_cardsets pc WHERE pc.game_id=? AND pc.user_id=? AND pc.status="nicht gelegt"';
		$rr = $db->query($sql, __FILE__, __LINE__, __METHOD__, [$this->game_id, $user->id]);

		/** Wenn das Spiel beendet werden kann */
		if(!$db->num($rr))
		{
			/** Spiel beenden */
			$sql = 'UPDATE peter_games SET status = "geschlossen", winner_id=? WHERE game_id=?';
			$db->query($sql, __FILE__, __LINE__, __METHOD__, [$user->id, $this->game_id]);

			/** Activity Eintrag auslösen */
			Activities::addActivity($user->id, 0, t('activity-won', 'peter', [ SITE_URL, $this->game_id ]), 'pt');
		}
	}

	/**
	 * Gibt das Formular zum Spiel erstellen zurück
	 *
	 * @return string HTML-Output
	 */
	function neu_form()
	{
		$html = '<h3>Neues Spiel</h3>
				<form action="'.getURL(false, false).'" method="post" style="display: flex;white-space: nowrap;align-items: center;">
					<fieldset>
						<label style="flex: 1;">Für Anzahl Spieler:
							<input type="number" min="2" max="6" name="players" style="width: 50px;">
							<br><span class="tiny">mindestens 2, maximal 6</span>
						</label>
						<input type="submit" name="maach" class="button" value="starten" style="flex: 2;">
					</fieldset>
				</form>';
		return $html;
	}

	/**
	 * Gibt das Cardset für einen User in einem Game als Array zurück
	 *
	 * @return Array
	 * @param int User_ID $user_id
	 */
	function player_cardset($user_id)
	{
		global $db;

		/** Prüfen ob der Rosenverkäufer da ist... */
		$rosen = $this->rosenverkaufer('check');

		/** Rosenverkäufer ist da */
		$params = [];
		if($rosen === 1 )
		{
			/** cardset für den betreffenden user im game selektieren und auf rosen regeln achten */
			$sql = 'SELECT * FROM peter_cardsets pc LEFT JOIN peter_spezialregeln p ON pc.card_id = p.card_id
					WHERE pc.user_id=? AND pc.game_id=? AND pc.status="nicht gelegt" AND p.typ="rosen" ORDER by p.value DESC';
			$params[] = $user_id;
			$params[] = $this->game_id;
		}
		else {
			/** Cardset für den User selektieren (normale regeln) */
			$sql = 'SELECT * FROM peter_cardsets pc LEFT JOIN peter p ON pc.card_id = p.card_id
					WHERE pc.user_id=? AND pc.game_id=? AND pc.status="nicht gelegt" ORDER by p.value DESC';
			$params[] = $user_id;
			$params[] = $this->game_id;
		}
		return $db->query($sql, __FILE__, __LINE__, __METHOD__, $params);
	}

	/**
	 * Gibt die Highscore Seite zurück
	 *
	 * @global object $db Globales Class-Object mit allen MySQL-Methoden
	 * @global object $user Globales Class-Object mit den User-Methoden & Variablen
	 * @return string HTML-Output
	 */
	function peterscore()
	{
		global $db, $user;

		$html = '<h2>Peter Haiskor</h2>
				<table>
					<thead><tr>
						<th>Spieler</th>
						<th>Anzahl Spiele</th>
						<th>Gewonnen</th>
					</tr></thead>
					<tbody>';

		/** Score Query */
		$sql = "SELECT gp.user_id, gp.num_games_played, COALESCE(gw.num_games, '0') as num_games
				FROM (SELECT pp.user_id, COUNT(pp.game_id) AS num_games_played FROM peter_players pp
					LEFT JOIN peter_games pg ON pg.game_id = pp.game_id WHERE pg.status = 'geschlossen' GROUP BY pp.user_id) AS gp
				LEFT JOIN (SELECT pg.winner_id, COUNT(pg.game_id) AS num_games FROM peter_games pg WHERE pg.status = 'geschlossen' GROUP BY pg.winner_id) AS gw ON gp.user_id = gw.winner_id ORDER BY num_games DESC";
		$result = $db->query($sql, __FILE__, __LINE__, __METHOD__);

		while($rs = $db->fetch($result))
		{
			$html .= '<tr>
						<td>'.$user->id2user($rs['user_id']).'</td>
						<td>'.$rs['num_games_played'].'</td>
						<td>'.$rs['num_games'].'</td>
					</tr>';
		}

		$html .= '</tbody></table>';

		return $html;
	}


	/**
	 * Gibt das Formular zum Wünschen einer Farbe zurück
	 *
	 * @return string
	 */
	function wunscher() {
		global $db;

		$html = "
		<form action='$_SERVER[PHP_SELF]?game_id=".$this->game_id."' method='post'>
		<table cellpadding='2' cellspacing='1' bgcolor='".BORDERCOLOR."'>
		<tr><td bgcolor='".BACKGROUNDCOLOR."'>
		<input type='radio' name='wunsch' value='Eichel'> Eichel
		</td><td bgcolor='".BACKGROUNDCOLOR."'>
		<input type='radio' name='wunsch' value='Rosen'> Rosen
		</td><td bgcolor='".BACKGROUNDCOLOR."'>
		<input type='radio' name='wunsch' value='Schellen'> Schellen
		</td><td bgcolor='".BACKGROUNDCOLOR."'>
		<input type='radio' name='wunsch' value='Schilten'> Schilten
		</td></tr bgcolor='".BACKGROUNDCOLOR."'>
		<tr><td colspan='4' align='right' bgcolor='".BACKGROUNDCOLOR."'>
		<input type='submit' value='wünschen' class='button'>
		</td></tr>
		</table>
		</form>";

		return $html;
	}

	/**
	 * Zeigt ein Spiel an
	 *
	 * @return string
	 * @param Array $gd
	 * @param Card_ID $card_id
	 * @param (karte|aus) $make
	 */
	function game($gd, $card_id=null, $make=null)
	{
		global $db, $user, $smarty;

		/** cardset für den betreffenden user im game selektieren */
		$resultp = $this->player_cardset($user->id);

		$html = null;
		$html .= '<h2>Peter Game #'.$this->game_id.'</h2>';
		if (isset($this->lc['description']))
		{
			$html .= '<h3>Aktuelle Karte: <strong>'.$this->lc['description'].(isset($this->lc['user_id']) ? '</strong> - gelegt von '.$user->id2user($this->lc['user_id'], true) : '').'</h3>
				<hr size="1" width="100%">
				<img src="?img=karten&game_id='.$this->game_id.'" title="'.$this->lc['description'].'" style="width: 100%;max-width: 100%;">';
		}

		/** Wenn das Spiel läuft */
		if ($gd['status'] === "lauft")
		{
			if (isset($gd['user_id'])) $html .= '<h5>Am Zug ist: '.$user->id2user($gd['username'], true).'</h5>';

			/** Wenn der Spieler noch Karten hat */
			if ($db->num($resultp))
			{
				/** zug als richtig ansehen */
				$zug = TRUE;

				/** Wenn ein nichtraucher gelegt wurde UND noch kein Wunsch vorliegt */
				if (!$this->checkwunsch() && $user->id == $this->lc['user_id'] && $this->lc['value'] == 0)
				{
					//Formular zum wünschen ausgeben */
					$html .= $this->wunscher();
					//zug falsen
					$zug = FALSE;

				/** Wenn ein nichtraucher gelegt wurde und ein Wunsch vorliegt */
				} elseif (isset($this->lc['value']) && $this->lc['value'] === 0 && $this->checkwunsch() && $this->lc['card_id']) {
					/** Daten über den Wunsch ermitteln */
					$wunsch = $this->get_wunsch();
					/** Wunsch anzeigen */
					//$html .= '<h5>'.$wunsch['username'].' hat '.$wunsch['col'].' gewünscht!</h5>';
					$smarty->assign('error', ['type' => 'info', 'dismissable' => 'false', 'title' => $wunsch['username'].' hat '.$wunsch['col'].' gewünscht!']);
				}

				if ($make)
				{
					/** Zug ausführen */
					$this->zug($card_id,$make,$gd['players']);
					//header("Location: http://".$_SERVER['SERVER_NAME'].$_SERVER['PHP_SELF']."?game_id=".$this->game_id."&".session_name()."=".session_id());
					header('Location: ?game_id='.$this->game_id);
					exit();
				}

				/** Wenn der Spieler am Zug ist, ANZEIGE */
				if ($gd['next_player'] === $user->id) $myTurnHtml = '<h4 class="blink">'.t('game-your-turn').'</h4>';

				// @TODO das könnte in $smarty->assign('sidebarHtml', $sidebarHtml) gehen! (IneX)
				$html .= "<br><hr size='1' width='100%'>"
					.$this->spielerstatus()
					."<br>"
					.(isset($myTurnHtml) ? $myTurnHtml : '')
					."<hr size='1' width='100%'>
					<h3>Meine Karten</h3>
					<br>";
				// END TODO

				/** Aussetzen Button anzeigen wenn der spieler am zug ist */
				if ($gd['next_player'] == $user->id)
				{
					$html .= "<form action='?game_id=".$this->game_id."&make=aus' method='post'>
								<input type='submit' value='aussetzen' class='button'>
							</form><br><br>";
				}

				/** Ausgabe der restlichen karten des Spielers */
				while($rs = $db->fetch($resultp))
				{
					if($gd['next_player'] == $user->id && $zug)
					{
						$html .= "<a href='?game_id=".$this->game_id."&card_id=$rs[card_id]&make=karte'>
								<img border='0' src='/images/peter/".$rs['card_id'].".gif' alt='$rs[description]' title='$rs[description]'>
								</a>";
					} else {
						$html .= "<img border='0' src='/images/peter/".$rs['card_id'].".gif' alt='$rs[description]' title='$rs[description]'>";
					}
				}

			}
		}
		/** Wenn Spiel beendet wurde */
		elseif($gd['status'] === 'geschlossen')
		{
			$sql = 'SELECT username FROM user WHERE id=?';
			$res = $db->query($sql, __FILE__, __LINE__, __METHOD__, [$gd['winner_id']]);
			$rs = $db->fetch($res);

			/** Gewinner anzeigen */
			//$html .= '<h4>Gewinner: '.$rs['username'].'</h4>';
			$smarty->assign('error', ['type' => 'success', 'dismissable' => 'false', 'title' => 'Gewinner: '.$rs['username']]);
		}

		/** thread ausgabe */
		ob_end_flush();
		ob_start();
		echo Forum::printCommentingSystem('p', $this->game_id);
		$html .= ob_get_contents();
		ob_end_clean();
		ob_start();

		return $html;
	}

	/**
	 * Kartenberg
	 *
	 * Gibt das Kartenberg Image komplett zurück
	 *
	 * @version 2.2
	 * @since 1.0 Method added
	 * @since 2.0 `IneX` Code optimizations
	 * @since 2.1 `18.04.2020` `IneX` Migrate to mysqli_
	 * @since 2.2 `19.10.2020` `IneX` Optimized SQL-Query and not passing resource to img_kartenberg()
	 *
	 * @global object $db Globales Class-Object mit allen MySQL-Methoden
	 * @global object $user Globales Class-Object mit den User-Methoden & Variablen
	 * @return resource img_handle
	 */
	function kartenberg()
	{
		global $db, $user;

		$sql = 'SELECT pc.card_id, pc.user_id, p.description FROM peter_cardsets pc LEFT JOIN peter p ON p.card_id = pc.card_id
				WHERE pc.game_id=? AND pc.status = "gelegt" ORDER by datum ASC';
		$result = $db->query($sql, __FILE__, __LINE__, __METHOD__, [$this->game_id]);
		$i = 0;

		if ($db->num($result) > 0)
		{
			while($rs = $db->fetch($result))
			{
				$usernameToPrint = $user->id2user($rs['user_id']);
				if ($i === 0) $img = $this->img_kartenberg($usernameToPrint, $rs['card_id'], 'create', 1);
				else $img = $this->img_kartenberg($usernameToPrint, $rs['card_id'], 'add', $i, $img);
				$i++;
			}
			$db->seek($result, 0);
			$i = 0;

			/** Schriftfarben anlegen */
			$red = hexdec(substr(FONTCOLOR,1,2));
			$green = hexdec(substr(FONTCOLOR,3,2));
			$blue = hexdec(substr(FONTCOLOR,5,2));
			$fontc = imagecolorallocate($img, $red, $green, $blue);

			while($rs = $db->fetch($result))
			{
				imagettftext($img,8,0,5,350+($i * 11),$fontc,PHP_IMAGES_DIR.'peter/verdana.ttf',$rs['description']);
				$i++;
			}
		} else {
			$img = imagecreatefromgif(PHP_IMAGES_DIR.'peter/jassteppich.gif');
		}
		return $img;
	}

	/**
	 * Bild der Karten
	 *
	 * Generiert die Kartenberge, legt ein Bild auf ein anderes
	 *
	 * @version 2.0
	 * @since 1.0 method added
	 * @since 1.1 `14.10.2020` `IneX` fixed non well formed numeric values & invalid characters passed
	 * @since 2.0 `19.10.2020` `IneX` fixed imagesx() expects parameter 1 to be resource (removed passing of $old_img)
	 *
	 * @return resource
	 * @param string $textToPlot Text (Username) der auf die Karte geschrieben werden soll
	 * @param int $card_id Card-ID
	 * @param string $mode Supported values: 'create' (früher 1) | 'add' (früher 2)
	 * @param int $depth
	 * @param resource $img_input (Optional) Existing image resource (for width calculations of new)
	 */
	function img_kartenberg($textToPlot, $card_id, $mode='add', $depth=0, $img_input=null)
	{
		global $db, $new_y_pos;

		if (!empty($img_input) && is_resource($img_input))
		{
			if(imagesx($img_input) > 600) $xx = 90;
			else $xx = 50;
		}

		/** Wenn das Image das erste ist */
		if ($mode === 'create')
		{
			/** start image erstellen */
			$first_img = imagecreatefromgif(PHP_IMAGES_DIR.'peter/'.$card_id.'.gif');

			/** Font color für Namen bestimmen */
			$fontc = imagecolorallocate($first_img, 0, 0, 0);

			/** Text auf Bild schreiben */
			imagettftext($first_img,7,0,13,9,$fontc,PHP_IMAGES_DIR.'peter/verdana.ttf',$textToPlot);

			return $first_img;
		}
		/** Wenn bereits ein Image besteht */
		elseif ($mode === 'add')
		{
			/** x-achsen verschiebung ermitteln */
			srand(microtime(true)*1000000);
			$x_verschiebung = rand(20,45);

			/** y-achsen verschiebung ermitteln */
			srand(microtime(true)*1000000);
			$y_verschiebung = rand(20,45);

			/** hinzuzufügende karte in ein image handle laden */
			$add_img = imagecreatefromgif(PHP_IMAGES_DIR.'peter/'.$card_id.'.gif');

			/** Schriftfarbe anlegen */
			$fontc = imagecolorallocate($add_img,0,0,0);

			/** Namen auf die Karten schreiben */
			imagettftext($add_img,7,0,13,9,$fontc,PHP_IMAGES_DIR.'peter/verdana.ttf',$textToPlot);

			/** w/h vom alten bild ermitteln */
			$o_width = imagesx($img_input);
			$o_height = imagesy($img_input);

			/** x pos der neuen karte ermitteln */
			srand(microtime(true)*1000000);
			/** rechts verschiebung */
			if(rand(0,100) > $xx) {
				$new_x_pos = ($o_width+$x_verschiebung) - 125;
			/** links verschiebung */
			} else {
				$new_x_pos = $o_width - 125 - $x_verschiebung;
			}
			$new_x_pos = ($new_x_pos < 0 ? 0 : $new_x_pos);

			/** y pos der neuen karten ermitteln */
			$new_y_pos = ($y_verschiebung + $new_y_pos);
			$new_h = (($new_y_pos) > ($o_height + $y_verschiebung - 195)) ? $new_y_pos + 195 : $o_height + $y_verschiebung;

			/** neues bild erstellen */
			$new_img = imagecreatetruecolor($o_width + $x_verschiebung, (int)$new_h);

			/** bg color */
			$red = hexdec(substr(BACKGROUNDCOLOR,1,2));
			$green = hexdec(substr(BACKGROUNDCOLOR,3,2));
			$blue = hexdec(substr(BACKGROUNDCOLOR,5,2));
			$bg = imagecolorallocate($new_img,$red,$green,$blue);

			/** bg color verwenden und bg füllen */
			imagefill($new_img,0,0,$bg);

			/** das alte bild ins neue kopieren */
			imagecopy($new_img,$img_input,0,0,0,0,$o_width,$o_height);

			/** neue karte ins bild einfügen */
			imagecopy($new_img,$add_img,$new_x_pos,$new_y_pos,0,0,125,195);

			return $new_img;
		}
	}
}

/** Rosenverkäufer einloggen */
if (is_object($user) && $user->typ >= USER_USER) peter::rosenverkaufer();
