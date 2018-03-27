<?php
/**
 * Hunting z (Game)
 * 
 * Im Hunting z (Hz) versuchen die Spieler, "Inspectors",
 * den mysteriösen Mr. Z daran zu hindern, auf bestimmte
 * Felder zu gelangen und so das Spiel für sich zu ent-
 * scheiden.
 * Das Spiel benutzt folgende Tabellen aus der DB:
 * 		hz_aims, hz_dwz, hz_games, hz_maps, hz_players,
 *		hz_routes, hz_sentinels, hz_stations, hz_tracks
 *
 * @author [z]biko
 * @package Zorg
 * @subpackage HuntingZ
 *
 * @todo Sollte das alles hier nicht in einer Class untergebracht werden?
 */
/**
 * @include main.inc.php 		Main Functions
 * @include activities.inc.php 	(DEPRECATED) Activities Functions and Stream
 * @include messagesystem.inc.php (DEPRECATED) Messagesystem einbinden für Funktionen die Benachrichtigungen absetzen
 * @include usersystem.inc.php 	(DEPRECATED) Usersystem einbinden für alle Benutzerbezogenen Funktionen (z.B. UserID -> Username umwandeln)
 * @include util.inc.php		(DEPRECATED) Utilities einbinden für Handling diverser Spezialfunktionen (z.B. URLs erzeugen)
 * @include forum.inc.php		(DEPRECATED) Forum einbinden für Handling der Commenting Funktionalität einzelner Hunting z Spiele
 * @include strings.inc.php		(DEPRECATED) Strings die im Zorg Code benutzt werden
 */
require_once( __DIR__ .'/main.inc.php');

/**
 * @const IMGPATH		Pfad zu den Bildern fürs Hunting Z
 * @const MAX_HZ_GAMES	In sovielen Hz-Spielen kann ein Spieler maximal gleichzeitig teilnehmen
 * @const TURN_TIME		So lange haben Spieler Zeit für ihren Spielzug
 * @const TURN_COUNT	Nach so vielen Zügen gibts neues Geld
 * @const TURN_ADD_MONEY So viel Geld gibts nach TURN_COUNT Spielzügen
 */			
define("IMGPATH", "/images/hz/");
define("MAX_HZ_GAMES", 5);
define("TURN_TIME", 60*60*24*3);
define("TURN_COUNT", 4);
define("TURN_ADD_MONEY", 10);
	
/**
 * Hunting z Spiel löschen
 * 
 * Löscht ein Hz Spiel aus der Datenbank
 * 
 * @author [z]biko
 * @version 1.0
 *
 * @param integer $gid ID des Hunting z Spiels
 * @global array $db Array mit allen MySQL-Datenbankvariablen
 * @global array $user Array mit allen Uservariablen
 */
function hz_close_game ($gid) {
	global $db, $user;
	
	$e = $db->query("SELECT g.id FROM hz_games AS g, hz_players AS z WHERE g.id=$gid AND g.id=z.game AND g.state='open' AND z.type='z' AND z.user='$user->id'", __FILE__, __LINE__);
	$d = $db->fetch($e);
	if ($d) {
		$db->query("DELETE FROM hz_games WHERE id=$d[id]", __FILE__, __LINE__);
		$db->query("DELETE FROM hz_players WHERE game=$d[id]", __FILE__, __LINE__);
	}
}

/**
 * Neues Hunting z Spiel
 * 
 * Erzeugt ein neues Hz Spiel
 * 
 * @author [z]biko
 * @version 1.0
 *
 * @param integer $map ID der Karte auf welcher das neue Spiel stattfindet
 * @global array $db Array mit allen MySQL-Datenbankvariablen
 * @global array $user Array mit allen Uservariablen
 */
function start_new_game ($map) {
	global $db, $user;
	
	if (!$user->id) user_error(t('error-newgame-not-logged-in'), E_USER_ERROR);
	
	try {
		/** get number of games the users has opened (i.e. is mister z) */
		$own_games = $db->fetch($db->query(
			"SELECT count(p.user) AS anz
			FROM hz_players AS p JOIN hz_games AS g ON p.game=g.id
			WHERE g.state!='finished' AND p.type='z' AND p.user='$user->id'",
			__FILE__, __LINE__
		));
		
		/** too many games open already */
		if ($own_games['anz'] > MAX_HZ_GAMES) {
			user_error(t('error-game-max-limit-reached'), E_USER_ERROR);
		
		/** user can still open new games */
		} else {
			$e = $db->query("SELECT * FROM hz_maps WHERE id='$map' AND state='active'", __FILE__, __LINE__);
			$d = $db->fetch($e);
			if ($d) {
				$game = $db->query("INSERT INTO hz_games (date, map, round) VALUES (NOW(), $d[id], 1)", __FILE__, __LINE__);
				$db->query("INSERT INTO hz_players (game, user, station) VALUES ($game, $user->id, ".get_start_station($game).")", __FILE__, __LINE__);
				
				/** Activity Eintrag auslösen */
				Activities::addActivity($user->id, 0, t('activity-newgame', 'hz', [ $d['name'], SITE_URL, $game ]), 'hz');
				
			}else{
				user_error(t('unknown-map', 'hz', $map), E_USER_ERROR);
			}
		}
	} catch (Exception $e) {
		user_error($e->getMessage(), E_USER_ERROR);
	}
}

/**
 * Start-Stationen der Spieler festlegen
 * 
 * Setzt die Spieler zu Beginn des Spiels randomized auf eine der Stationen auf der Map
 * 
 * @author [z]biko
 * @version 1.0
 *
 * @param integer $game ID des Hunting z Spiels
 * @global array $db Array mit allen MySQL-Datenbankvariablen
 * @global array $user Array mit allen Uservariablen
 * @return ID des Hunting z Spiels
 */
function get_start_station ($game) {
	global $db, $user;
	
	$e = $db->query("SELECT * FROM hz_games WHERE id=$game", __FILE__, __LINE__);
	$d = $db->fetch($e);
	
	//select a random station out of the unoccupied, non-goal stations
	if ($d) {			
		$e = $db->query("SELECT s.id
				FROM hz_games AS g
				LEFT JOIN hz_stations AS s ON g.map=s.map
				LEFT JOIN hz_aims AS a ON a.station=s.id and a.map=s.map
				LEFT JOIN hz_players AS p ON p.game = g.id and p.station=s.id
				where g.id=$game AND a.map IS NULL AND p.station IS NULL
				order by rand()",
				__FILE__, __LINE__);
		$d = $db->fetch($e);
		if (!$d) user_error(t('unknown-start', 'hz', $game), E_USER_ERROR);
		return $d['id'];
	}else{
		user_error(t('error-game-invalid', 'global', $game), E_USER_ERROR);
	}				
}


/**
 * Join Game
 * 
 * Fügt einen neuen Spieler dem Spiel hinzu
 * 
 * @author [z]biko
 * @version 1.0
 *
 * @param integer $game ID des Hunting z Spiels
 * @global array $db Array mit allen MySQL-Datenbankvariablen
 * @global array $user Array mit allen Uservariablen
 */
function join_game ($game) {
	global $db, $user;
	
	if (!$user->id) trigger_error(t('error-play-not-logged-in'), E_USER_ERROR);
	
	$e = $db->query(
		"SELECT g.*, m.players awaitingplayers, count(numpl.user) numplayers, if(p.user IS NULL, 0, 1) joined 
		FROM hz_players numpl, hz_maps m, hz_games g
		LEFT JOIN hz_players p ON p.game=g.id AND p.user='$user->id'
		WHERE g.id=$game AND g.state='open' AND numpl.game=g.id AND m.id=g.map
		GROUP BY numpl.game", __FILE__, __LINE__);
	$d = $db->fetch($e);
	if ($d) {
		if (!$d['joined']) {
			$db->query("INSERT INTO hz_players (game, user, type, station) VALUES ($game, $user->id, '$d[numplayers]', ".get_start_station($game).")", __FILE__, __LINE__);
			
			// Activity Eintrag auslösen
			Activities::addActivity($user->id, 0, t('activity-joingame', 'hz', [ SITE_URL, $game ]), 'hz');
			
			if ($d['numplayers'] == $d['awaitingplayers']) {
				start_game ($game);
			}
		}else{
			user_error(t('error-game-already-joined'), E_USER_ERROR);
		}
	}else{
		user_error(t('error-game-invalid', 'global', $game), E_USER_ERROR);
	}
}

/**
 * Unjoin Game
 * 
 * Sofern ein Spiel noch nicht gestartet ist, kann ein User das Spiel auch wieder verlassen
 * 
 * @author [z]biko
 * @version 1.0
 *
 * @param integer $game ID des Hunting z Spiels
 * @global array $db Array mit allen MySQL-Datenbankvariablen
 * @global array $user Array mit allen Uservariablen
 */
function unjoin_game ($game) {
	global $db, $user;
	
	$e = $db->query("SELECT * FROM hz_games g, hz_players p WHERE g.id=$game AND p.game=g.id AND p.user='$user->id' AND p.type!='z'", __FILE__, __LINE__);
	$d = $db->fetch($e);
	if ($d) {
		$db->query("DELETE FROM hz_players WHERE user=$user->id AND game=$game", __FILE__, __LINE__);
	}
}

/**
 * Spiel starten
 * 
 * Startet ein Hunting z Spiel nachdem genügend Inspectors beigetreten sind
 * 
 * @author [z]biko
 * @version 1.0
 *
 * @param integer $game ID des Hunting z Spiels
 * @global array $db Array mit allen MySQL-Datenbankvariablen
 */
function start_game ($game) {
	global $db;
	
	$e = $db->query(
		"SELECT count(p.user) numplayers, m.players+1 awaitingplayers
		FROM hz_games g, hz_maps m, hz_players p 
		WHERE g.map=m.id AND g.id=p.game AND g.id=$game
		GROUP BY p.user",
		__FILE__, __LINE__);
	$d = $db->fetch($e);
	if ($d) {
		$db->query("UPDATE hz_games SET state='running', turndate=now() WHERE id=$game", __FILE__, __LINE__);
		$rights = array();
		$e = $db->query("SELECT * FROM hz_players WHERE game='$game' AND type!='z'", __FILE__, __LINE__);
		while ($d = $db->fetch($e)) $rights[] = $d['user'];
		Thread::setRights('h', $game, $rights);
	}else{
		user_error(t('error-game-invalid', 'global', $game), E_USER_ERROR);
	}
}

/**
 * Ticket Map
 * 
 * Generiert eine HTML-Map mit den klickbaren Stationen für den Spieler, auf welche er akutell fortfahren kann
 * 
 * @author [z]biko
 * @version 1.0
 *
 * @param integer $game ID des Hunting z Spiels
 * @param array $ticket Array mit den verschiedenen Arten von Stationen
 * @global array $db Array mit allen MySQL-Datenbankvariablen
 * @global array $user Array mit allen Uservariablen
 * @return HTML mit klickbaren Map-Buttons der möglichen, ansteuerbaren Stationen
 */
function ticket_map ($game, $ticket='all') {
	global $db, $user;
	
	if (!in_array($ticket, array("taxi", "ubahn", "bus", "black", 'all'))) user_error(t('invalid-ticket', 'hz', $ticket), E_USER_ERROR);
	if ($ticket == "black") $where_ticket = "p.type='z'";
	elseif ($ticket == 'all') $where_ticket = "(p.type='z' OR r.type!='black')";
	else $where_ticket = "r.type='$ticket'";
	
	
	$ret = '<map name="moves">';
	$e = $db->query( //select all possible destinations from the current location, excluding taken ones, but including z's station
			 "SELECT s. * , p.type, p.money, r.type routetype
			 FROM hz_games g
			 JOIN hz_players p ON p.game = g.id
			 JOIN hz_routes r ON r.map = g.map
			   AND (r.end = p.station
			   OR r.start = p.station)
			 LEFT JOIN hz_players other ON other.game = g.id
			   AND IF(r.start = p.station, r.end, r.start) = other.station
			 LEFT JOIN hz_stations s ON s.map = g.map
			   AND s.id = IF(r.start = p.station, r.end, r.start)
			 WHERE g.id = '$game'
			   AND p.user = '$user->id'
			   AND $where_ticket
			   AND (other.user IS NULL
			   OR other.type = 'z')",
		__FILE__, __LINE__
	);
	if (mysql_num_rows($e) == 0) user_error(t('invalid-ticket', 'hz', $ticket), E_USER_ERROR);
	
	while ($d = $db->fetch($e)) {
		if ($d['money'] >= turn_cost($d['routetype'])) {
			if ($ticket == 'all') $vkt = $d['routetype'];
			else $vkt = $ticket;
			switch ($vkt) {
				case 'taxi': $vk = 'dem Taxi'; break;
				case 'bus': $vk = 'dem Bus'; break;
				case 'ubahn': $vk = 'der U-Bahn'; break;
				case 'black': $vk = 'dem Black-Ticket'; break;
			}				

			if ($d['type'] != 'z' && $ticket=='black') user_error(t('invalid-ticket', 'hz', $ticket), E_USER_ERROR);
			$x = $d['x'];
			$y = $d['y'];
			$ret .= '<area shape="rect" coords="'.($x-20).','.($y-15).','.($x+20).','.($y+15).'" '.
				'href="/actions/hz_turn.php?ticket='.$d['routetype'].'&move='
						.$d['id'].'&'.url_params().'"'.
				'alt="Mit '.$vk.' hier hin fahren ('.turn_cost($vkt).'$)" '.
						 'title="Mit '.$vk.' hier hin fahren ('.turn_cost($vkt).'$)">';
		}
	}
	
	$ret .= '</map>';
	return $ret;
}

/**
 * Kosten pro Spielzug
 * 
 * Gibt die Kosten pro Station zurück
 * 
 * @author [z]biko
 * @version 1.0
 *
 * @param string $type Stations-Art um Preis zu berechnen
 * @return Integer des Wertes für die gewählte Stations-Art
 */
function turn_cost ($type) {
	switch ($type) {
		case 'taxi': return 1; break;
		case 'bus':  return 3; break;
		case 'ubahn':  return 6; break;
		case 'black':  return 10; break;
		case 'sentinel':  return 10; break;
		default: user_error(t('invalid-turn', 'hz', $type), E_USER_ERROR);
	}
}

/**
 * Spielzug Validität prüfen
 * 
 * Prüft ob ein abgesetzter Spielzug eines Spielers auch valide ist und ausgeführt werden darf
 * 
 * @author [z]biko
 * @version 1.0
 *
 * @param integer $game ID des Hunting z Spiels
 * @param integer $uid ID des Users welcher den Spielzug macht
 * @global array $db Array mit allen MySQL-Datenbankvariablen
 * @global array $user Array mit allen Uservariablen
 * @return Boolean (True/False) ob gewünschter Spielzug erlaubt ist oder nicht
 */
function turn_allowed ($game, $uid=0) {
	global $db, $user;
	
	if (!$uid) $uid = $user->id;
	
	$e = $db->query(
		"SELECT if((g.nextturn='z' && me.type='z' || g.nextturn='players' && me.type!='z' && me.turndone='0')
		  && state='running', '1', '0') allowed
		FROM hz_games g
		LEFT JOIN hz_players me on me.game=g.id
		WHERE g.id='".$game."'
		AND me.user='".$uid."'",
		__FILE__, __LINE__
	);
	$d = $db->fetch($e);
		
	return $d['allowed'];
}

/**
 * Spieler aussetzen
 * 
 * Automatisches Stehenbleiben des Spielers bei Überschreiten der Zeit für seinen Zug
 * 
 * @author [z]biko
 * @version 1.0
 *
 * @global array $db Array mit allen MySQL-Datenbankvariablen
 */
function hz_turn_passing () {
	global $db;
	
	$e = $db->query(
		"SELECT g.id, p.user
		FROM hz_games g
		JOIN hz_players p
		  ON p.game=g.id
		WHERE g.state='running'
		  AND UNIX_TIMESTAMP(now())-unix_timestamp(g.turndate) > ".TURN_TIME."
		  AND if(g.nextturn='z' && p.type='z'
			OR g.nextturn='players' && p.type!='z' && p.turndone='0', '1', '0') = '1'", 
		__FILE__, __LINE__
	);
	while ($game = $db->fetch($e)) turn_stay($game['id'], $game['user']);
}


/**
 * Spielzug abschliessen
 * 
 * Führt alle finalen Kalkulationen, Queries und Benachrichtigungen aus, nachdem ein Spielzug durchgeführt wurde
 * (z.B. prüft, ob das Spiel aufgrund des Spielzuges beendet wurde, etc.)
 * 
 * @author [z]biko
 * @author IneX
 * @version 2.0
 *
 * @param integer $game ID des Hunting z Spiels
 * @param integer $uid ID des Users welcher den finalen Spielzug macht
 * @global array $db Array mit allen MySQL-Datenbankvariablen
 * @global array $user Array mit allen Uservariablen
 */
function turn_finalize ($game, $uid=0) {
	global $db, $user;

	if (!$uid) $uid = $user->id;

	try {
		$db->query("UPDATE hz_players
				SET turndone='1'
				WHERE game='".$game."'
				  AND user='".$uid."'
				  AND type!='z'", __FILE__, __LINE__, 'turn_finalize()');

		$e = $db->query(
			"SELECT g. * , sum( a.score )  - g.z_score player_score, m.players totalplayers,
			IF ( pl.user IS NOT NULL  || z_score >= sum( a.score ) - g.z_score,  '1',  '0' )finished
			FROM hz_maps m, hz_games g
			LEFT  JOIN hz_aims a ON a.map = g.map
			LEFT  JOIN hz_players z ON z.game = g.id AND z.type =  'z'
			LEFT  JOIN hz_players pl ON pl.game = g.id AND pl.station = z.station AND pl.type !=  'z'
			WHERE g.id =$game AND m.id = g.map
			GROUP  BY a.map",
			__FILE__, __LINE__, 'turn_finalize()'
		);
		$d = $db->fetch($e);
	} catch (Exception $e) {
		error_log($e->getMessage());
	}

	if ($d)
	{
		try {
			// Count turns by Inspectors
			$e = $db->query("SELECT count(*) num FROM hz_players WHERE game=".$d['id']." AND type!='z' AND turndone='1'", __FILE__, __LINE__, 'turn_finalize()');
				$turndone = $db->fetch($e);

			// Mr. Z hat den Zug gespielt - Inspectors sind dran:
			if ($d['nextturn'] == 'z' && !$d['finished'])
			{
				$db->query("UPDATE hz_games SET nextturn='players', turndate=now() WHERE id=$game", __FILE__, __LINE__, 'turn_finalize()');

				// Inspectors benachrichtigen
				$i = $db->query("SELECT user FROM hz_players WHERE game=".$d['id']." AND type!='z'", __FILE__, __LINE__, 'turn_finalize()');
				while($inspectors = $db->fetch($i))
				{
					error_log("[INFO] turn_finalize() triggers sendMessage() for Inspectors from uid=$uid to z=$z");
					Messagesystem::sendMessage($uid, $inspectors['user'], t('message-subject', 'hz'), t('message-your-turn', 'hz', [ SITE_URL, $game]));
				}

			// Die Inspectors haben den Zug gespielt - Mr. Z ist dran:
			} elseif ($d['nextturn'] == 'players' && $d['totalplayers'] == $turndone['num'] && !$d['finished']) {
				$db->query("UPDATE hz_games
							SET round=round+1, nextturn='z', turndate=now(),
							turncount=(turncount+1)%".TURN_COUNT."
							WHERE id=$game", __FILE__, __LINE__, 'turn_finalize()');

				// add money and reset 'turndone'
				if ($d['turncount']+1 == TURN_COUNT) $add = TURN_ADD_MONEY;
					else $add = 0;
				$db->query("UPDATE hz_players SET turndone='0', money=money+$add WHERE game=$game", __FILE__, __LINE__, 'turn_finalize()');

				// Mr. Z benachrichtigen
				$z = mysql_fetch_assoc($db->query("SELECT user FROM hz_players WHERE game=".$d['id']." AND type='z' LIMIT 0,1", __FILE__, __LINE__, 'turn_finalize()'));
				if (!empty($z) && is_numeric($z)) {
					error_log("[INFO] turn_finalize() triggers sendMessage() for Mr.Z from uid=$uid to z=$z");
					Messagesystem::sendMessage($uid, $z, t('message-subject', 'hz'), t('message-your-turn', 'hz', [ SITE_URL, $game]));
				} else {
					error_log('[ERROR] "Mr. Z benachrichtigen" failed because db->query() for z returned: '.$z);
				}
			}

			if ($d['finished'])
			{
				$db->query("UPDATE hz_games SET state='finished' WHERE id=$game", __FILE__, __LINE__);
				_update_hz_dwz($game);
				Thread::setRights('h', $game, USER_ALLE);
				finish_mails($game);
			}
		} catch (Exception $e) {
			error_log($e->getMessage());
		}
	}else{
		user_error(t('invalid-turn', 'hz', $game), E_USER_ERROR);
	}
}


/**
 * Spielzug "Stehenbleiben"
 * 
 * Führt alle Queries aus für den Spielzug "Stehenbleiben"
 * 
 * @author [z]biko
 * @version 1.0
 *
 * @param integer $game ID des Hunting z Spiels
 * @param integer $uid ID des Users welcher den Spielzug macht
 * @global array $db Array mit allen MySQL-Datenbankvariablen
 * @global array $user Array mit allen Uservariablen
 */
function turn_stay ($game, $uid=0) {
	global $db, $user;
	
	if (!$uid) $uid = $user->id; //uid, so that the "overdue" turns can be triggered by everybody
	
	if (!turn_allowed($game, $uid)) user_error(t('error-game-notyourturn'), E_USER_ERROR);
	
	$e = $db->query(//look up the round in the game the player is in
		"SELECT g.round round, p.type playertype
		 FROM hz_games g 
		 JOIN hz_players p
		   ON p.game=g.id
		 WHERE p.user='".$uid."'
		   AND g.id='".$game."'",
	 __FILE__, __LINE__);
	$d = $db->fetch($e);
	
	if ($d['playertype'] == 'z') {			
		$db->query("INSERT INTO hz_tracks
				 (game, ticket, station, nr, player)
						 VALUES ('".$game."', 'stay', '0', '".($d['round'])."', 'z')", __FILE__, __LINE__);
	}else{
		$e = $db->query("SELECT *
				 FROM hz_players
				 WHERE game='".$game."' AND user='".$uid."'", __FILE__, __LINE__);
		$s = $db->fetch($e);
		$db->query("INSERT INTO hz_tracks
				(game, ticket, station, nr, player)
						VALUES ('".$game."', 'stay', '".$s['station']."', ".($d['round']).", '$d[playertype]')", __FILE__, __LINE__);
	}
	
	turn_finalize($game, $uid);
}


/**
 * Benachrichtigungen bei Spielende
 * 
 * Erstellt und verschickt alle notwendigen Benachrichtigungen beim Beenden eines Hunting z Spiels
 * 
 * @author [z]biko
 * @version 1.0
 *
 * @param integer $game ID des Hunting z Spiels
 * @global array $db Array mit allen MySQL-Datenbankvariablen
 * @global array $user Array mit allen Uservariablen
 */
function finish_mails ($game) {
	global $db, $user;
	
	if (!$user->id) trigger_error(t('error-play-not-logged-in'), E_USER_ERROR);
	
	$e = $db->query(
		"SELECT if(g.z_score >= (sum(a.score)-g.z_score), 'z', 'players') winner, p.*
		FROM hz_games g, hz_players p, hz_aims a
		WHERE g.id=$game AND p.game=$game AND p.user!='$user->id' AND a.map=g.map
		GROUP BY a.map, p.user",
		__FILE__, __LINE__
	);
	while ($d = $db->fetch($e))
	{
		if ($d['winner'] == 'z' && $d['type'] == 'z')
		{
			$text = t('message-game-won-mrz', 'hz', [ SITE_URL, $game ]);
			
			// Activity Eintrag auslösen
			Activities::addActivity($user->id, 0, t('activity-won-mrz', 'hz', [ SITE_URL, $game ]), 'hz');
		}elseif ($d['winner'] == 'players' && $d['type'] == 'z') {
			$text = t('message-game-won-mrz', 'hz', [ SITE_URL, $game ]);
		}elseif ($d['winner'] == 'z' && $d['type'] != 'z') {
			$text = t('message-game-lost-inspectors', 'hz', [ SITE_URL, $game ]);
		}elseif ($d['winner'] == 'players' && $d['type'] != 'z') {
			$text = t('message-game-won-inspectors', 'hz', [ SITE_URL, $game ]);
			
			// Activity Eintrag auslösen
			Activities::addActivity($user->id, 0, t('activity-won-inspectors', 'hz', [ SITE_URL, $game ]), 'hz');
		}
		if ($text) {
			Messagesystem::sendMessage($user->id, $d['user'], t('message-subject', 'hz'), $text);
		}else{
			//Messagesystem::sendMessage(7, 7, "Hunting z ERROR (autom. Nachricht)", $text);
			user_error(t('error-game-finish-message', 'global', [ $game, $user->id, $d['winner'], $d['user'] ]), E_USER_ERROR);
		}
	}
}


/**
 * Spielzug ausführen
 * 
 * Führt alle Queries aus für einen generellen Spielzug
 * 
 * @author [z]biko
 * @version 1.0
 *
 * @param integer $game ID des Hunting z Spiels
 * @param integer $ticket String mit Art der gewählten Fortbewegung
 * @param integer $station Integer der ID der gewählten Destinations-Station
 * @global array $db Array mit allen MySQL-Datenbankvariablen
 * @global array $user Array mit allen Uservariablen
 */
function turn_move ($game, $ticket, $station) {
	global $db, $user;
	
	if (!$user->id) trigger_error(t('error-play-not-logged-in'), E_USER_ERROR);
	
	if (!turn_allowed($game)) user_error(t('error-game-notyourturn'), E_USER_ERROR);
	
	if (!in_array($ticket, array("taxi", "ubahn", "bus", "black")))
			user_error(t('invalid-ticket', 'hz', $ticket), E_USER_ERROR);
	if ($ticket == "black") $where_ticket = "p.type='z'";
	else $where_ticket = "r.type='$ticket'";
	
	$e = $db->query(
/*			"SELECT g.id, p.type playertype, count(t.nr) tracks, a.score,
		if (a.station IS NOT NULL && at.nr IS NULL, '1', '0') aim_catch,
		if (sen.station IS NOT NULL || a.station IS NOT NULL && at.nr IS NULL, '1', '0') seen
		FROM hz_games g, hz_players p, hz_routes r
		LEFT JOIN hz_tracks t ON t.game=g.id AND t.player='z'
		LEFT JOIN hz_sentinels sen ON sen.game=g.id AND sen.station=$station
		LEFT JOIN hz_aims a ON a.map=g.map AND a.station=$station
		LEFT JOIN hz_tracks at ON at.game=g.id AND at.station=$station AND at.player='z'
		LEFT JOIN hz_players other ON other.station=$station AND other.game=g.id
		AND if(p.type='z' || p.type!='z' && other.type!='z', '1', '0')='1'
		WHERE g.id='$game' AND p.game=g.id AND r.map=g.map AND p.user='$user->id' AND $where_ticket 
			AND p.money-".turn_cost($ticket)." >= 0 AND other.user IS NULL
			AND (r.start=$station && r.end=p.station || r.end=$station && r.start=p.station)
		GROUP BY t.game",*/
		"SELECT g.id, p.type playertype, g.round tracks, a.score,
		  IF (a.station IS NOT NULL && at.nr IS NULL , '1', '0')aim_catch,
		  IF (sen.station IS NOT NULL || a.station IS NOT NULL && at.nr IS NULL , '1', '0')seen
		  FROM hz_games g
		  LEFT JOIN hz_tracks t ON t.game = g.id
			AND t.player = 'z'
		  LEFT JOIN hz_sentinels sen ON sen.game = g.id
			AND sen.station =$station
		  LEFT JOIN hz_aims a ON a.map = g.map
			AND a.station =$station
		  LEFT JOIN hz_tracks at ON at.game = g.id
			AND at.station =$station
			AND at.player = 'z'
		  LEFT JOIN hz_players p ON p.user='$user->id'
			AND p.game = g.id
		  LEFT JOIN hz_routes r ON r.map = g.map
			AND $where_ticket
		  LEFT JOIN hz_players other ON other.station =$station
			AND other.game = g.id
			AND IF (
			  p.type = 'z' || p.type != 'z' && other.type != 'z', '1', '0'
			  ) = '1'
		  WHERE g.id = '$game'
			AND p.money -".turn_cost($ticket)." >=0
			AND other.user IS NULL
			AND (r.start=$station && r.end=p.station || r.end=$station && r.start=p.station)
		  GROUP BY t.game",
			
		__FILE__, __LINE__
	);
	$d = $db->fetch($e);
	if ($d) {
		$db->query("UPDATE hz_players
				SET station=$station, money=money-".turn_cost($ticket)."
				WHERE game=$d[id] AND user=$user->id", __FILE__, __LINE__);
		if ($d['playertype'] == 'z') {
  				if ($d['seen']) $track_station = $station;
			else $track_station = 0;
			
			$db->query("INSERT INTO hz_tracks 
					 (game, ticket, station, nr, player)
							 VALUES ($game, '$ticket', $track_station,
						 '".($d['tracks'])."', 'z')",
				   __FILE__, __LINE__);
			
			if ($d['aim_catch']) {
				$db->query("UPDATE hz_games SET z_score=z_score+$d[score] WHERE id=$game", __FILE__, __LINE__);
			}
		}else{
			$db->query("INSERT INTO hz_tracks
					 (game, ticket, station, nr, player)
			  VALUES ($game, '$ticket', $station,
				  '".$d['tracks']."', '$d[playertype]')", __FILE__, __LINE__);
		}
		
	}else{
		user_error(t('invalid-turn', 'hz', $game), E_USER_ERROR);
	}
	
	turn_finalize($game);
}

/**
 * Spielzug "Station Überwachen"
 * 
 * Führt alle Queries aus im Falle wo ein Inspector eine Station überwachen möchte
 * 
 * @author [z]biko
 * @version 1.0
 *
 * @param integer $game ID des Hunting z Spiels
 * @param integer $uid ID des Users welcher den Spielzug macht
 * @global array $db Array mit allen MySQL-Datenbankvariablen
 * @global array $user Array mit allen Uservariablen
 */
function turn_sentinel ($game) {
	global $user, $db;
	
	if (!$user->id) trigger_error(t('error-play-not-logged-in'), E_USER_ERROR);
	if (!turn_allowed($game)) user_error(t('error-game-notyourturn'), E_USER_ERROR);
	
	$e = $db->query( //get the player's station and, in the same run, find out if he's got 
			 //enough money
		"SELECT g.id, p.money, p.station, p.type playertype, g.round tracknr
		FROM hz_games g
		JOIN hz_players p
		  ON p.game=g.id
		WHERE g.id='".$game."'
		  AND p.user='".$user->id."'
		  AND p.money-".turn_cost("sentinel")." >= 0
		  AND p.type!='z' 
		LIMIT 0,1",
		__FILE__, __LINE__
	);
	$d = $db->fetch($e);
	if ($d) {
		$db->query("INSERT INTO hz_sentinels
				 (game, station)
						 VALUES ($game, $d[station])", __FILE__, __LINE__);
		$db->query("UPDATE hz_players
				 SET money=money-".turn_cost("sentinel")."
				 WHERE game=$game
				   AND user=$user->id", __FILE__, __LINE__);
		$db->query("INSERT INTO hz_tracks 
				 (game, ticket, station, nr, player)
		  VALUES ($game, 'sentinel', '$d[station]', $d[tracknr], '$d[playertype]')", __FILE__, __LINE__);
	}else{
		user_error(t('invalid-turn', 'hz', $game), E_USER_ERROR);
	}
	
	turn_finalize($game);
}


/**
 * Anzahl laufender Hz Spiele
 * 
 * Gibt die Anzahl laufender Hunting z Spiele aus
 * 
 * @author [z]biko
 * @version 1.0
 *
 * @global array $db Array mit allen MySQL-Datenbankvariablen
 * @global array $user Array mit allen Uservariablen
 * @return Integer mit Anzahl der laufenden Hz Spiele
 */
function hz_running_games () {
	global $db, $user;
	
	$e = $db->query(
		"SELECT count(*) anz
		FROM hz_games g, hz_players p 
		WHERE p.game=g.id AND p.user='$user->id' AND g.state='running'
		AND if(g.nextturn='z' && p.type='z' || g.nextturn='players' && p.type!='z' && p.turndone='0', '1', '0') = '1'",
		__FILE__, __LINE__
	);
	$d = $db->fetch($e);
	return $d['anz'];
}

/**
 * Anzahl offener Hz Spiele
 * 
 * Gibt die Anzahl offener Hunting z Spiele aus
 * 
 * @author [z]biko
 * @version 1.0
 *
 * @global array $db Array mit allen MySQL-Datenbankvariablen
 * @global array $user Array mit allen Uservariablen
 * @return Integer mit Anzahl der offenen Spiele
 */
function hz_open_games () {
	global $db, $user;
//		return 0; // workaround by lukas 29.09.05 13:14	
	$e = $db->query(
		"SELECT count(*) anz
		FROM hz_players AS z, hz_games AS g
		LEFT JOIN hz_players p ON p.game=g.id AND p.user='$user->id'
		WHERE g.state='open' AND z.game=g.id AND z.type='z' AND z.user!='$user->id' AND p.user IS NULL",
		__FILE__, __LINE__
	);
	$d = $db->fetch($e);
	return $d['anz'];
}


/**
 * DWZ Scores aktualisieren
 * 
 * Aktualisiert die DWZ Punkte der Spieler eines bestimmten Hz Spiels
 * 
 * @author [z]biko
 * @version 1.0
 *
 * @param integer $gid ID des Hunting z Spiels
 * @global array $db Array mit allen MySQL-Datenbankvariablen
 */
function _update_hz_dwz ($gid) {
	global $db;

	define("BASE_POINTS", 1600);
	define("MAX_POINTS_TRANSFERABLE", 32);

	$players = array();

	$e = $db->query(
		"SELECT g.*, sum(a.score)-g.z_score i_score, z.user z, d.score zdwz
		FROM hz_games g, hz_aims a, hz_players z
		LEFT JOIN hz_dwz d ON d.user=z.user
		WHERE g.id=$gid AND g.state='finished' AND a.map=g.map AND z.game=g.id AND z.type='z'
		GROUP BY g.id",
		__FILE__, __LINE__
	);
	$g = $db->fetch($e);

	$players[] = $g['z'];

	if ($g['z_score'] >= $g['i_score']) $pz = 1;
	else $pz = 0;
	$pi = 1 - $pz;

	if (isset($g['zdwz'])) {
		$zdwz = $g['zdwz'];
		$prev_score_z = $zdwz;
	}else{
		$zdwz = BASE_POINTS;
		$prev_score_z = BASE_POINTS;
	}

	$e = $db->query(
		"SELECT d.score, p.user
		FROM hz_games g, hz_players p
		LEFT JOIN hz_dwz d ON d.user=p.user
		WHERE p.game=g.id AND p.type!='z'
		AND g.id=$gid", 
		__FILE__, __LINE__
	);
	$idwz = array();
	$prev_score_i = array();
	while($is = $db->fetch($e)) {
		$players[] = $is['user'];
		if (isset($is['score'])) {
			$idwz[$is['user']] = $is['score'];
			$prev_score_i[$is['user']] = $is['score'];
		}else {
			$idwz[$is['user']] = BASE_POINTS;
			$prev_score_i[$is['user']] = BASE_POINTS;
		}
	}

	$idwz_avg = array_sum($idwz) / sizeof($idwz);

	$probz = 1 / (pow(10, (($idwz_avg - $zdwz) / 400)) + 1) ;
	$probi = 1 / (pow(10, (($zdwz - $idwz_avg) / 400)) + 1) ;

	$difz = round (MAX_POINTS_TRANSFERABLE * ($pz - $probz));
	$difi = round (MAX_POINTS_TRANSFERABLE * ($pi - $probi));
	$difi_avg = round (MAX_POINTS_TRANSFERABLE * ($pi - $probi) / sizeof($idwz));

	$tusr = $db->fetch($db->query("SELECT * FROM hz_dwz WHERE user=$g[z]", __FILE__, __LINE__));
	if ($tusr) $db->query("UPDATE hz_dwz SET score=".($zdwz+$difz).", prev_score=$prev_score_z WHERE user=$g[z]", __FILE__, __LINE__);
	else $db->query("INSERT INTO hz_dwz (user, score, prev_score) VALUES ($g[z], ".($zdwz+$difz).", $prev_score_z)", __FILE__, __LINE__);
	foreach ($idwz as $key => $val) {
		$tusr = $db->fetch($db->query("SELECT * FROM hz_dwz WHERE user=$key", __FILE__, __LINE__));
		if ($tusr) $db->query("UPDATE hz_dwz SET score=".($val+$difi_avg).", prev_score=$prev_score_i[$key] WHERE user=$key", __FILE__, __LINE__);
		else $db->query("INSERT INTO hz_dwz (user, score, prev_score) VALUES ($key, ".($val+$difi_avg).", $prev_score_i[$key])", __FILE__, __LINE__);
	}

	// dwz_dif für game
	$db->query("UPDATE hz_games SET dwz_dif=".abs($difz)." WHERE id=$gid AND state='finished'", __FILE__, __LINE__);

	// rank update
	$e = $db->query("SELECT * FROM hz_dwz ORDER BY score DESC", __FILE__, __LINE__);
	$i = 1;
	$prev_score = 0;
	$rank = 0;

	while ($upd = $db->fetch($e)) {
		if ($upd['score'] != $prev_score) {
			$rank = $i;
		}

		if (in_array($upd['user'], $players)) {
			$prev_rank = ", prev_rank=$upd[rank]";
		}else{
			$prev_rank = "";
		}

		$db->query("UPDATE hz_dwz SET rank=$rank $prev_rank WHERE user=$upd[user]", __FILE__, __LINE__);

		$prev_score = $upd['score'];
		++$i;
	}
}
	
