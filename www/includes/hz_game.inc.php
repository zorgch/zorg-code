<?php
/**
 * Hunting z (Game)
 *
 * Im Hunting z (Hz) versuchen die Spieler, "Inspectors",
 * den mysteriösen Mr. z daran zu hindern, auf bestimmte
 * Felder zu gelangen und so das Spiel für sich zu ent-
 * scheiden.
 * Das Spiel benutzt folgende Tabellen aus der DB:
 * 		hz_aims, hz_dwz, hz_games, hz_maps, hz_players,
 *		hz_routes, hz_sentinels, hz_stations, hz_tracks
 *
 * @author [z]biko
 * @package zorg
 * @subpackage HuntingZ
 *
 * @TODO Sollte das alles hier nicht in einer Class untergebracht werden?
 */
/**
 * File includes
 * @include main.inc.php 		Main Functions
 */
require_once dirname(__FILE__).'/main.inc.php';

/**
 * @const URLPATH_HZ_IMAGES	Pfad zu den Bildern fürs Hunting Z
 * @const HZ_MAX_GAMES		In sovielen Hz-Spielen kann ein Spieler maximal gleichzeitig teilnehmen
 * @const HZ_TURN_TIME		So lange haben Spieler Zeit für ihren Spielzug
 * @const HZ_TURN_COUNT		Nach so vielen Zügen gibts neues Geld
 * @const HZ_TURN_ADD_MONEY	So viel Geld gibts nach TURN_COUNT Spielzügen
 */
if (!defined('URLPATH_HZ_IMAGES')) define('URLPATH_HZ_IMAGES', (isset($_ENV['URLPATH_HZ_IMAGES']) ? $_ENV['URLPATH_HZ_IMAGES'] : null));
if (!defined('HZ_MAX_GAMES')) define('HZ_MAX_GAMES', (isset($_ENV['HZ_MAX_GAMES']) ? (int)$_ENV['HZ_MAX_GAMES'] : 5));
if (!defined('HZ_TURN_TIME')) define('HZ_TURN_TIME', (isset($_ENV['HZ_TURN_TIME']) ? (int)$_ENV['HZ_TURN_TIME'] : 60*60*24*3));
if (!defined('HZ_TURN_COUNT')) define('HZ_TURN_COUNT', (isset($_ENV['HZ_TURN_COUNT']) ? (int)$_ENV['HZ_TURN_COUNT'] : 4));
if (!defined('HZ_TURN_ADD_MONEY')) define('HZ_TURN_ADD_MONEY', (isset($_ENV['HZ_TURN_ADD_MONEY']) ? (int)$_ENV['HZ_TURN_ADD_MONEY'] : 10));

/**
 * Hunting z Spiel löschen
 *
 * Löscht ein Hz Spiel aus der Datenbank
 *
 * @author [z]biko
 * @version 1.0
 * @since 1.0 function added
 *
 * @param integer $gid ID des Hunting z Spiels
 * @global object $db Globales Class-Object mit allen MySQL-Methoden
 * @global object $user Globales Class-Object mit den User-Methoden & Variablen
 */
function hz_close_game ($gid) {
	global $db, $user;

	$e = $db->query('SELECT g.id FROM hz_games AS g, hz_players AS z WHERE g.id='.$gid.' AND g.id=z.game AND g.state="open" AND z.type="z" AND z.user='.$user->id, __FILE__, __LINE__, __FUNCTION__);
	$d = $db->fetch($e);
	if ($d) {
		$db->query('DELETE FROM hz_games WHERE id='.$d['id'], __FILE__, __LINE__, __FUNCTION__);
		$db->query('DELETE FROM hz_players WHERE game='.$d['id'], __FILE__, __LINE__, __FUNCTION__);
	}
}

/**
 * Neues Hunting z Spiel
 *
 * Erzeugt ein neues Hz Spiel
 *
 * @author [z]biko
 * @version 1.0
 * @since 1.0 function added
 *
 * @param integer $map ID der Karte auf welcher das neue Spiel stattfindet
 * @global object $db Globales Class-Object mit allen MySQL-Methoden
 * @global object $user Globales Class-Object mit den User-Methoden & Variablen
 */
function start_new_game ($map) {
	global $db, $user;

	/** Validate function parameters */
	if (!$user->is_loggedin()) user_error(t('error-newgame-not-logged-in'), E_USER_ERROR);

	/** get number of games the users has opened (i.e. is mister z) */
	$own_games = $db->fetch($db->query( // FIXME gibt immer 0 zurück?!
		'SELECT count(p.user) AS anz
		 FROM hz_players AS p JOIN hz_games AS g ON p.game=g.id
		 WHERE g.state!="finished" AND p.type="z" AND p.user='.$user->id,
		__FILE__, __LINE__
	));

	/** too many games open already */
	if (isset($own_games['anz']) && $own_games['anz'] >= HZ_MAX_GAMES) {
		user_error(t('error-game-max-limit-reached'), E_USER_ERROR);
	}
	/** user can still open new games */
	else {
		$e = $db->query('SELECT * FROM hz_maps WHERE id='.$map.' AND state="active"', __FILE__, __LINE__, __FUNCTION__);
		$d = $db->fetch($e);
		if ($d) {
			$game = $db->query('INSERT INTO hz_games (date, map, round) VALUES (NOW(), '.$d['id'].', 1)', __FILE__, __LINE__, __FUNCTION__);
			$db->query('INSERT INTO hz_players (game, user, station) VALUES ('.$game.', '.$user->id.', '.get_start_station($game).')', __FILE__, __LINE__, __FUNCTION__);

			/** Activity Eintrag auslösen */
			Activities::addActivity($user->id, 0, t('activity-newgame', 'hz', [ $d['name'], SITE_URL, $game ]), 'hz');
		} else {
			user_error(t('unknown-map', 'hz', $map), E_USER_ERROR);
		}
	}
}

/**
 * Start-Stationen der Spieler festlegen
 *
 * Setzt die Spieler zu Beginn des Spiels randomized auf eine der Stationen auf der Map
 *
 * @author [z]biko
 * @version 1.0
 * @since 1.0 function added
 *
 * @param integer $game ID des Hunting z Spiels
 * @global object $db Globales Class-Object mit allen MySQL-Methoden
 * @global object $user Globales Class-Object mit den User-Methoden & Variablen
 * @return ID des Hunting z Spiels
 */
function get_start_station ($game) {
	global $db, $user;

	$e = $db->query('SELECT * FROM hz_games WHERE id='.$game, __FILE__, __LINE__, __FUNCTION__);
	$d = $db->fetch($e);

	/** select a random station out of the unoccupied, non-goal stations */
	if ($d) {
		$e = $db->query('SELECT s.id
						FROM hz_games AS g
						LEFT JOIN hz_stations AS s ON g.map=s.map
						LEFT JOIN hz_aims AS a ON a.station=s.id and a.map=s.map
						LEFT JOIN hz_players AS p ON p.game = g.id and p.station=s.id
						where g.id='.$game.' AND a.map IS NULL AND p.station IS NULL
						order by rand()',
						__FILE__, __LINE__, __FUNCTION__);
		$d = $db->fetch($e);
		if (!$d) user_error(t('unknown-start', 'hz', $game), E_USER_ERROR);
		return $d['id'];
	} else {
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
 * @since 1.0 function added
 *
 * @param integer $game ID des Hunting z Spiels
 * @global object $db Globales Class-Object mit allen MySQL-Methoden
 * @global object $user Globales Class-Object mit den User-Methoden & Variablen
 */
function join_game ($game) {
	global $db, $user;

	if (!$user->id) trigger_error(t('error-play-not-logged-in'), E_USER_ERROR);

	$e = $db->query('SELECT g.*, m.players awaitingplayers, count(numpl.user) numplayers, if(p.user IS NULL, 0, 1) joined
					FROM hz_players numpl, hz_maps m, hz_games g
					LEFT JOIN hz_players p ON p.game=g.id AND p.user='.$user->id.'
					WHERE g.id='.$game.' AND g.state="open" AND numpl.game=g.id AND m.id=g.map
					GROUP BY numpl.game', __FILE__, __LINE__, __FUNCTION__);
	$d = $db->fetch($e);
	if ($d) {
		if (!$d['joined']) {
			$db->query('INSERT INTO hz_players (game, user, type, station) VALUES ('.$game.', '.$user->id.', "'.$d['numplayers'].'", '.get_start_station($game).')', __FILE__, __LINE__, __FUNCTION__);

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
 * @since 1.0 function added
 *
 * @param integer $game ID des Hunting z Spiels
 * @global object $db Globales Class-Object mit allen MySQL-Methoden
 * @global object $user Globales Class-Object mit den User-Methoden & Variablen
 */
function unjoin_game ($game) {
	global $db, $user;

	$e = $db->query('SELECT * FROM hz_games g, hz_players p WHERE g.id='.$game.' AND p.game=g.id AND p.user='.$user->id.' AND p.type!="z"', __FILE__, __LINE__, __FUNCTION__);
	$d = $db->fetch($e);
	if ($d) {
		$db->query('DELETE FROM hz_players WHERE user='.$user->id.' AND game='.$game, __FILE__, __LINE__, __FUNCTION__);
	}
}

/**
 * Spiel starten
 *
 * Startet ein Hunting z Spiel nachdem genügend Inspectors beigetreten sind
 *
 * @author [z]biko
 * @version 1.0
 * @since 1.0 function added
 *
 * @param integer $game ID des Hunting z Spiels
 * @global object $db Globales Class-Object mit allen MySQL-Methoden
 */
function start_game ($game) {
	global $db;

	$e = $db->query('SELECT count(p.user) numplayers, m.players+1 awaitingplayers
					 FROM hz_games g, hz_maps m, hz_players p
					 WHERE g.map=m.id AND g.id=p.game AND g.id='.$game.'
					 GROUP BY p.user',
					__FILE__, __LINE__, __FUNCTION__
		);
	$d = $db->fetch($e);
	if ($d) {
		$db->query('UPDATE hz_games SET state="running", turndate=NOW() WHERE id='.$game, __FILE__, __LINE__, __FUNCTION__);
		$rights = array();
		$e = $db->query('SELECT * FROM hz_players WHERE game='.$game.' AND type!="z"', __FILE__, __LINE__, __FUNCTION__);
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
 * @version 1.1
 * @since 1.0 `[z]biko` function added
 * @since 1.1 `18.04.2020` `IneX` Migrate to mysqli_
 *
 * @param integer $game ID des Hunting z Spiels
 * @param array $ticket Array mit den verschiedenen Arten von Stationen
 * @global object $db Globales Class-Object mit allen MySQL-Methoden
 * @global object $user Globales Class-Object mit den User-Methoden & Variablen
 * @return string HTML mit klickbaren Map-Buttons der möglichen, ansteuerbaren Stationen
 */
function ticket_map ($game, $ticket='all')
{
	global $db, $user;

	if (!in_array($ticket, array('taxi', 'ubahn', 'bus', 'black', 'all'))) user_error(t('invalid-ticket', 'hz', $ticket), E_USER_ERROR);
	if ($ticket == 'black') $where_ticket = 'p.type="z"';
	elseif ($ticket == 'all') $where_ticket = '(p.type="z" OR r.type!="black")';
	else $where_ticket = 'r.type="'.$ticket.'"';

	/** Clickable image <map>-Overlay only for logged in Users */
	$ret = '';
	if ($user->is_loggedin()) $ret .= '<map name="moves">';

	/** Select all possible destinations from the current location, excluding taken ones, but including z's station */
	$sql = 'SELECT
				s.*,
				p.type,
				p.money,
				r.type routetype
			 FROM hz_games g
			 JOIN hz_players p ON p.game = g.id
			 JOIN hz_routes r ON r.map = g.map
			   AND (r.end = p.station
			   OR r.start = p.station)
			 LEFT JOIN hz_players other ON other.game = g.id
			   AND IF(r.start = p.station, r.end, r.start) = other.station
			 LEFT JOIN hz_stations s ON s.map = g.map
			   AND s.id = IF(r.start = p.station, r.end, r.start)
			 WHERE g.id = '.$game.'
			   '.($user->is_loggedin() ? 'AND p.user = '.$user->id : null).'
			   AND '.$where_ticket.'
			   AND (other.user IS NULL OR other.type = "z")';
	$e = $db->query($sql, __FILE__, __LINE__, __FUNCTION__);
	if (empty($db->num($e))) user_error(t('invalid-ticket', 'hz', $ticket), E_USER_ERROR);

	while ($d = $db->fetch($e))
	{
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
	if ($user->is_loggedin()) $ret .= '</map>';

	return $ret;
}

/**
 * Kosten pro Spielzug
 *
 * Gibt die Kosten pro Station zurück
 *
 * @author [z]biko
 * @version 1.0
 * @since 1.0 function added
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
 * @version 1.1
 * @since 1.0 `biko` function added
 * @since 1.1 `22.05.2021` `IneX` Changed SQL-response for `allowed` & adjusted Function return
 *
 * @param integer $game ID des Hunting z Spiels
 * @param integer $uid ID des Users welcher den Spielzug macht
 * @global object $db Globales Class-Object mit allen MySQL-Methoden
 * @global object $user Globales Class-Object mit den User-Methoden & Variablen
 * @return bool Boolean (True/False) ob gewünschter Spielzug erlaubt ist oder nicht
 */
function turn_allowed ($game, $uid=0)
{
	global $db, $user;

	if (empty($uid) && $user->is_loggedin()) $uid = $user->id;

	$e = $db->query('SELECT if((g.nextturn="z" && me.type="z" || g.nextturn="players" && me.type!="z" && me.turndone="0")
					  && state="running", "allowed", 0) allowed
					FROM hz_games g
					LEFT JOIN hz_players me on me.game=g.id
					WHERE g.id='.$game.' AND me.user='.$uid.' LIMIT 0,1'
					,__FILE__, __LINE__, __FUNCTION__);
	$d = $db->fetch($e);

	return ($d['allowed'] === 'allowed' ? true : false);
}

/**
 * Spieler aussetzen
 *
 * Automatisches Stehenbleiben des Spielers bei Überschreiten der Zeit für seinen Zug
 *
 * @author [z]biko
 * @version 1.0
 * @since 1.0 function added
 *
 * @uses turn_stay()
 * @global object $db Globales Class-Object mit allen MySQL-Methoden
 * @return void
 */
function hz_turn_passing()
{
	global $db;

	if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> Checking hz_turn_passing()', __FUNCTION__, __LINE__));
	$e = $db->query('SELECT g.id, p.user
					 FROM hz_games g
					 JOIN hz_players p
					  ON p.game=g.id
					 WHERE g.state="running"
					  AND UNIX_TIMESTAMP(NOW())-UNIX_TIMESTAMP(g.turndate) > '.HZ_TURN_TIME.'
					  AND if(g.nextturn="z" && p.type="z"
						OR g.nextturn="players" && p.type!="z" && p.turndone="0", "1", "0") = "1"',
					__FILE__, __LINE__, __FUNCTION__);
	while ($game = $db->fetch($e))
	{
		if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> Go to turn_stay(%d, %d)', __FUNCTION__, __LINE__, $game['id'], $game['user']));
		turn_stay($game['id'], $game['user']);
	}
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
 * @since 1.0 function added
 * @since 2.0 `15.11.2018` updated to use new $notifcation Class & some code and query optimizations
 *
 * @uses finish_mails(), timestamp()
 * @param integer $game ID des Hunting z Spiels
 * @param integer $uid ID des Users welcher den finalen Spielzug macht - Default: null
 * @global object $db Globales Class-Object mit allen MySQL-Methoden
 * @global object $user Globales Class-Object mit den User-Methoden & Variablen
 * @global object $notification Globales Class-Object mit allen Notification-Methoden
 */
function turn_finalize ($game, $uid=null)
{
	global $db, $user, $notification;

	if (empty($uid) && $user->is_loggedin()) $uid = $user->id;

	/** Mark Player as "turndone=1" */
	if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> Updating turndone=1 on game %d => user %d)', __FUNCTION__, __LINE__, $game, $uid));
	$db->query('UPDATE hz_players SET turndone="1"
				WHERE game='.$game.' AND user='.$uid.' AND type!="z"',
				  __FILE__, __LINE__, 'UPDATE turn_finalize()');

	/** Get updated Game Infos including if Inspector Station = Mr. z Station (Game = finished ;)) */
	$e = $db->query('SELECT g.*, SUM(a.score)-g.z_score player_score, m.players totalplayers,
					 IF (pl.user IS NOT NULL || z_score >= (SUM(a.score)-g.z_score), "true", "false") finished
					 FROM hz_maps m, hz_games g
					 LEFT JOIN hz_aims a ON a.map = g.map
					 LEFT JOIN hz_players z ON z.game = g.id AND z.type="z"
					 LEFT JOIN hz_players pl ON pl.game = g.id AND pl.station = z.station AND pl.type!= "z"
					 WHERE g.id ='.$game.' AND m.id = g.map
					 GROUP BY a.map, pl.user',
					__FILE__, __LINE__, __FUNCTION__);
	$d = $db->fetch($e);
	if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> SELECT FROM hz_maps: %s', __FUNCTION__, __LINE__, print_r($d,true)));

	if ($d !== false)
	{
		/** Count turns by Inspectors */
		$sql = 'SELECT count(*) num FROM hz_players WHERE type!="z" AND turndone="1" AND game='.$d['id'];
		$turndone = $db->fetch($db->query($sql, __FILE__, __LINE__, __FUNCTION__));
		if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> Count turns by Inspectors: %s', __FUNCTION__, __LINE__, print_r($turndone,true)));

		/**
		 * Mr. z hat den Zug gespielt - Inspectors sind dran:
		 */
		if ($d['nextturn'] === 'z' && $d['finished'] === 'false')
		{
			if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> update(hz_games()): game=%d | nextturn=players | turndate=%s', __FUNCTION__, __LINE__, $game, timestamp(true)));
			$query = $db->update('hz_games', ['id', $game], ['nextturn' => 'players', 'turndate' => timestamp(true)], __FILE__, __LINE__, __FUNCTION__);
			if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> UPDATE hz_games: %s', __FUNCTION__, __LINE__, ($query?'OK':'ERR')));

			/** Inspectors benachrichtigen */
			$i = $db->query('SELECT user FROM hz_players WHERE type!="z" AND game='.$d['id'], __FILE__, __LINE__, __FUNCTION__);
			while($inspectors = $db->fetch($i))
			{
				/** Notification */
				$notification_text = t('message-your-turn-inspectors', 'hz', [SITE_URL, $game]);
				$notification_status = $notification->send($inspectors['user'], 'games', ['from_user_id'=>$uid, 'subject'=>t('message-subject', 'hz'), 'text'=>$notification_text, 'message'=>$notification_text]);
				if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> $notification_status "%s" from uid=%s to z=%s for Inspectors=%s', __METHOD__, __LINE__, ($notification_status===true?'true':'false'), $uid, $z, print_r($inspectors['user'],true)));
			}
		}

		/**
		 * Die Inspectors haben den Zug gespielt - Mr. z ist dran:
		 */
		elseif ($d['nextturn'] === 'players' && $d['totalplayers'] === $turndone['num'] && $d['finished'] === 'false')
		{
			$query = $db->query('UPDATE hz_games SET
								 round=(round+1), nextturn="z", turndate=NOW(), turncount=(turncount+1)%'.HZ_TURN_COUNT.'
								 WHERE id='.$game, __FILE__, __LINE__, __FUNCTION__);
			if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> update(hz_games(%s)): game=%d | nextturn=z | turndate=%s', __FUNCTION__, __LINE__, $query, $game, timestamp(true)));

			/** add money and reset 'turndone' */
			if ($d['turncount']+1 == HZ_TURN_COUNT) $add = HZ_TURN_ADD_MONEY;
			else $add = 0;

			$db->query('UPDATE hz_players SET turndone="0", money=money+'.$add.' WHERE game='.$game, __FILE__, __LINE__, __FUNCTION__);

			/** Mr. z benachrichtigen */
			$z = $db->fetch($db->query('SELECT user FROM hz_players WHERE game='.$d['id'].' AND type="z" LIMIT 0,1', __FILE__, __LINE__, __FUNCTION__));
			if (!empty($z['user']) && is_numeric($z['user'])) {
				$notification_text = t('message-your-turn-mrz', 'hz', [SITE_URL, $game]);
				$notification_status = $notification->send($z['user'], 'games', ['from_user_id'=>$uid, 'subject'=>t('message-subject', 'hz'), 'text'=>$notification_text, 'message'=>$notification_text]);
				if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> turn_finalize() triggers $notification->send() with status "%s" for Mr.Z from uid=%s to z=%d', __METHOD__, __LINE__, ($notification_status===true?'true':'false'), $uid, $z['user']));
			} else {
				error_log('[ERROR] "Mr. z benachrichtigen" failed because db->query() for z returned: '.$z['user']);
			}
		}

		/** Niemand ist dran (nur für Error-Logging...) */
		elseif (DEVELOPMENT === true) {
			error_log(sprintf('[ERROR] <%s:%d> Es scheint niemand den Zug gemacht zu haben. Interessant, interessant,... %s', __FUNCTION__, __LINE__, print_r($d,true)));
		}

		/** Game ist fertig */
		if ($d['finished'] === 'true')
		{
			if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> Game finished: %s', __FUNCTION__, __LINE__, ($d['finished']==='true'?'true':'false')));
			$db->update('hz_games', ['id', $game], ['state' => 'finished'], __FILE__, __LINE__, __FUNCTION__);
			_update_hz_dwz($game);
			Thread::setRights('h', $game, USER_ALLE);
			if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> Go to finish_mails(%d)', __FUNCTION__, __LINE__, $game));
			finish_mails($game);
		}
	} else {
		user_error(t('invalid-turn', 'hz', $game), E_USER_ERROR);
	}
}


/**
 * Spielzug "Stehenbleiben"
 *
 * Führt alle Queries aus für den Spielzug "Stehenbleiben"
 *
 * @author [z]biko
 * @version 2.0
 * @since 1.0 function added
 * @since 2.0 `25.11.2018` code & query optimizations
 *
 * @uses turn_finalize()
 * @param integer $game ID des Hunting z Spiels
 * @param integer $uid ID des Users welcher den Spielzug macht - Default: null
 * @global object $db Globales Class-Object mit allen MySQL-Methoden
 * @global object $user Globales Class-Object mit den User-Methoden & Variablen
 */
function turn_stay($game, $uid=null)
{
	global $db, $user;

	if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> Doing turn_stay(%d, %d)', __FUNCTION__, __LINE__, $game, $uid));
	if (empty($uid) && $user->is_loggedin()) $uid = $user->id; // uid, so that the "overdue" turns can be triggered by everybody

	if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> Check if turn_allowed(%d, %d)', __FUNCTION__, __LINE__, $game, $uid));
	if (!turn_allowed($game, $uid)) user_error(t('error-game-notyourturn'), E_USER_WARNING);

	/** look up the round in the game the player is in */
	$e = $db->query('SELECT g.round round, p.type playertype
					 FROM hz_games g
					 JOIN hz_players p ON p.game=g.id
					 WHERE p.user='.$uid.' AND g.id='.$game,
					__FILE__, __LINE__, __FUNCTION__);
	$d = $db->fetch($e);

	if ($d['playertype'] === 'z') {
		if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> turn_stay(): playertype === z', __FUNCTION__, __LINE__));
		$db->query('INSERT INTO hz_tracks
					 (game, ticket, station, nr, player)
					 VALUES ('.$game.', "stay", "0", "'.$d['round'].'", "z")',
					 __FILE__, __LINE__, __FUNCTION__);
	} else {
		if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> turn_stay(): playertype === player', __FUNCTION__, __LINE__));
		$e = $db->query('SELECT *
						 FROM hz_players
						 WHERE game='.$game.' AND user='.$uid,
						__FILE__, __LINE__, __FUNCTION__);
		$s = $db->fetch($e);
		$db->query('INSERT INTO hz_tracks
					 (game, ticket, station, nr, player)
					 VALUES ('.$game.', "stay", "'.$s['station'].'", "'.$d['round'].'", "'.$d['playertype'].'")',
					 __FILE__, __LINE__, __FUNCTION__);
	}

	if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> Go to turn_finalize(%d, %d)', __FUNCTION__, __LINE__, $game, $uid));
	turn_finalize($game, $uid);
}

/**
 * Benachrichtigungen bei Spielende
 *
 * Erstellt und verschickt alle notwendigen Benachrichtigungen beim Beenden eines Hunting z Spiels
 *
 * @author [z]biko
 * @author IneX
 * @version 4.1
 * @since 1.0 function added
 * @since 2.0 updated mechnism and messages come from Strings-Array now
 * @since 3.0 `15.11.2018` updated sendMessage() to new $notification-Class, plus other code & query optimizations
 * @since 4.0 `20.11.2018` Fixed Bug #764: Hz Finish-Messages sind "verdreht"
 * @since 4.1 `13.08.2021` Fixed SQL-Error SELECT list is not in GROUP BY clause & PHP-Error in_array() expects parameter 2 to be array
 *
 * @param integer $game ID des Hunting z Spiels
 * @global object $db Globales Class-Object mit allen MySQL-Methoden
 * @global object $user Globales Class-Object mit den User-Methoden & Variablen
 * @global object $notification Globales Class-Object mit allen Notification-Methoden
 */
function finish_mails ($game)
{
	global $db, $user, $notification;

	/** Validate function parameters */
	if (!$user->id) trigger_error(t('error-play-not-logged-in'), E_USER_ERROR);

	/** Andere Players des $game aus der DB abfragen */
	$e = $db->query('SELECT if(g.z_score >= (sum(a.score)-g.z_score), "z", "players") winner, p.*
					 FROM hz_games g, hz_players p, hz_aims a
					 WHERE g.id='.$game.' AND p.game='.$game.' AND p.user!='.$user->id.' AND a.map=g.map
					 GROUP BY a.map, p.turndone, p.station, p.type, p.user, p.money',
					__FILE__, __LINE__, __FUNCTION__);
	/**
	 * Aktueller Player ist immer $user->id, hier also andere Players loopen & Notifications schicken
	 *
	 * Beispiel DB-Records Ergebnis:
	 *
	 *	 Game wo Players gewonnen haben nach Zug von $user->id:
		 ---
		 winner		game 	user	type	station		money	turndone
		 players	375	 	1		2		7			7		0
		 players	375	 	2		z		5			18		0
		 players	375	 	11		1		5			12		1
	 *
	 *   Game wo Mr z gewonnen hat nach Zug von $user->id:
		 ---
		 winner		game 	user	type	station		money	turndone
		 z			753	 	52		2		14			36		0
		 z			753	 	351		1		5			12		0
	 *   ===
	 *
	 * A) Inspector $user->id hat Zug gemacht, und Inspectors haben Spiel gewonnen (Mr. z hat verloren)
	 * 	  => Mr.Z benachrichtigen
	 * 	  => andere Inspectors benachrichtigen
	 * B) Inspector $user->id hat Zug gemacht, aber Mr. z hat Spiel gewonnen (Inspectors haben verloren)
	 * 	  => Mr.Z benachrichtigen
	 * 	  => andere Inspectors benachrichtigen
	 * C) Mr. z $user->id hat Zug gemacht, und Spiel als Mr. z gewonnen
	 * 	  => Inspectors benachrichtigen
	 * D) Mr. z $user->id hat Zug gemacht, aber Spiel verloren (Inspectors haben gewonnen)
	 * 	  => Inspectors benachrichtigen
	 */
	while ($d = $db->fetch($e))
	{
		/** Winner setzen: 'z' oder 'players' */
		$winner = $d['winner'];
		$inspectors = array();

		/**
		 * A) Inspector $user->id hat Zug gemacht, und Inspectors haben Spiel gewonnen (Mr. z hat verloren)
		 * => Mr.Z benachrichtigen
		 */
		if ($winner === 'players' && $d['type'] === 'z')
		{
			$mrz = $d['user'];
			$notification_text = t('message-game-lost-mrz', 'hz', [ SITE_URL, $game ]);
			$notification_status = $notification->send($d['user'], 'games', ['from_user_id'=>$user->id, 'subject'=>t('message-subject', 'hz'), 'text'=>$notification_text, 'message'=>$notification_text]);
			if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> finish_mails() with status "%s" for Players from Player=%s to Mrz=%d', __METHOD__, __LINE__, ($notification_status===true?'true':'false'), $user->id, $d['user']));
		}

		/**
		 * A) Inspector $user->id hat Zug gemacht, und Inspectors haben Spiel gewonnen (Mr. z hat verloren)
		 * oder D) Mr. z $user->id hat Zug gemacht, aber Spiel verloren (Inspectors haben gewonnen)
		 * => andere Inspectors benachrichtigen
		 */
		if ($winner === 'players' && is_numeric($d['type']) && $d['type'] > 0)
		{
			$inspectors[] = $d['user'];
			$notification_text = t('message-game-won-inspectors', 'hz', [ SITE_URL, $game ]);
			$notification_status = $notification->send($d['user'], 'games', ['from_user_id'=>$user->id, 'subject'=>t('message-subject', 'hz'), 'text'=>$notification_text, 'message'=>$notification_text]);
			if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> finish_mails() with status "%s" for Players from User=%s to User=%d', __METHOD__, __LINE__, ($notification_status===true?'true':'false'), $user->id, $d['user']));
		}

		/**
		 * B) Inspector $user->id hat Zug gemacht, aber Mr. z hat Spiel gewonnen (Inspectors haben verloren)
		 * => Mr.Z benachrichtigen
		 */
		if ($winner === 'z' && $d['type'] === 'z')
		{
			$mrz = $d['user'];
			$notification_text = t('message-game-won-mrz', 'hz', [ SITE_URL, $game ]);
			$notification_status = $notification->send($d['user'], 'games', ['from_user_id'=>$user->id, 'subject'=>t('message-subject', 'hz'), 'text'=>$notification_text, 'message'=>$notification_text]);
			if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> finish_mails() with status "%s" for Mr. z from Player=%s to Mrz=%d', __METHOD__, __LINE__, ($notification_status===true?'true':'false'), $user->id, $d['user']));
		}

		/**
		 * B) Inspector $user->id hat Zug gemacht, aber Mr. z hat Spiel gewonnen (Inspectors haben verloren)
		 * oder C) Mr. z $user->id hat Zug gemacht, und Spiel als Mr. z gewonnen
		 * => andere Inspectors benachrichtigen
		 */
		if ($winner === 'z' && is_numeric($d['type']) && $d['type'] > 0)
		{
			$inspectors[] = $d['user'];
			$notification_text = t('message-game-lost-inspectors', 'hz', [ SITE_URL, $game ]);
			$notification_status = $notification->send($d['user'], 'games', ['from_user_id'=>$user->id, 'subject'=>t('message-subject', 'hz'), 'text'=>$notification_text, 'message'=>$notification_text]);
			if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> finish_mails() with status "%s" for Players from User=%s to User=%d', __METHOD__, __LINE__, ($notification_status===true?'true':'false'), $user->id, $d['user']));
		}
	}

	/** Activity-Eintrag zum $game auslösen */
	if ($winner === 'z' && $mrz > 0) Activities::addActivity($mrz, 0, t('activity-won-mrz', 'hz', [ SITE_URL, $game ]), 'hz');
	elseif ($winner === 'z' && !in_array($user->id, $inspectors, true)) Activities::addActivity($user->id, 0, t('activity-won-mrz', 'hz', [ SITE_URL, $game ]), 'hz');
	elseif ($winner === 'players' && !in_array($user->id, $inspectors, true) && empty($mrz)) Activities::addActivity($inspectors[0], 0, t('activity-won-inspectors-them', 'hz', [ SITE_URL, $game ]), 'hz');
	elseif ($winner === 'players' && $mrz > 0) Activities::addActivity($user->id, 0, t('activity-won-inspectors-me', 'hz', [ SITE_URL, $game ]), 'hz');
}


/**
 * Spielzug ausführen
 *
 * Führt alle Queries aus für einen generellen Spielzug
 *
 * @author [z]biko
 * @version 1.0
 * @since 1.0 function added
 *
 * @param integer $game ID des Hunting z Spiels
 * @param string $ticket String mit Art der gewählten Fortbewegung
 * @param integer $station Integer der ID der gewählten Destinations-Station
 * @global object $db Globales Class-Object mit allen MySQL-Methoden
 * @global object $user Globales Class-Object mit den User-Methoden & Variablen
 */
function turn_move ($game, $ticket, $station)
{
	global $db, $user;

	if (!$user->is_loggedin()) trigger_error(t('error-play-not-logged-in'), E_USER_ERROR);
	if (!turn_allowed($game)) user_error(t('error-game-notyourturn'), E_USER_ERROR);
	if (!in_array($ticket, array('taxi', 'ubahn', 'bus', 'black'))) user_error(t('invalid-ticket', 'hz', $ticket), E_USER_ERROR);

	if ($ticket === 'black') $where_ticket = 'p.type="z"';
	else $where_ticket = 'r.type="'.$ticket.'"';

	$e = $db->query(
	/* "SELECT g.id, p.type playertype, count(t.nr) tracks, a.score,
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
		'SELECT g.id, p.type playertype, g.round tracks, a.score,
		  IF (a.station IS NOT NULL && MAX(at.nr) IS NULL , "1", "0") aim_catch,
		  IF (sen.station IS NOT NULL || a.station IS NOT NULL && MAX(at.nr) IS NULL , "1", "0") seen
		  FROM hz_games g
		  LEFT JOIN hz_tracks t ON t.game = g.id
			AND t.player="z"
		  LEFT JOIN hz_sentinels sen ON sen.game = g.id
			AND sen.station='.$station.'
		  LEFT JOIN hz_aims a ON a.map = g.map
			AND a.station='.$station.'
		  LEFT JOIN hz_tracks at ON at.game = g.id
			AND at.station='.$station.'
			AND at.player = "z"
		  LEFT JOIN hz_players p ON p.user='.$user->id.'
			AND p.game=g.id
		  LEFT JOIN hz_routes r ON r.map = g.map
			AND '.$where_ticket.'
		  LEFT JOIN hz_players other ON other.station='.$station.'
			AND other.game = g.id
			AND IF (p.type = "z" || p.type != "z" && other.type != "z", "1", "0") = "1"
		  WHERE g.id='.$game.'
			AND (p.money-'.turn_cost($ticket).') >=0
			AND other.user IS NULL
			AND (r.start='.$station.' && r.end=p.station || r.end='.$station.' && r.start=p.station)
		  GROUP BY t.game, p.type'
		,__FILE__, __LINE__, __FUNCTION__);
	$d = $db->fetch($e);
	if ($d)
	{
		$db->query('UPDATE hz_players SET station='.$station.', money=(money-'.turn_cost($ticket).')
					WHERE game='.$d['id'].' AND user='.$user->id, __FILE__, __LINE__, __FUNCTION__);
		if ($d['playertype'] === 'z')
		{
  			if ($d['seen']) $track_station = $station;
			else $track_station = 0;

			$db->query('INSERT INTO hz_tracks (game, ticket, station, nr, player)
						VALUES ('.$game.', "'.$ticket.'", '.$track_station.', '.$d['tracks'].', "z")'
						,__FILE__, __LINE__, __FUNCTION__);

			if ($d['aim_catch']) {
				$db->query('UPDATE hz_games SET z_score=(z_score+'.$d['score'].') WHERE id='.$game, __FILE__, __LINE__, __FUNCTION__);
			}
		} else {
			$db->query('INSERT INTO hz_tracks (game, ticket, station, nr, player)
						VALUES ('.$game.', "'.$ticket.'", '.$station.', '.$d['tracks'].', "'.$d['playertype'].'")'
						,__FILE__, __LINE__, __FUNCTION__);
		}

	} else {
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
 * @since 1.0 function added
 *
 * @param integer $game ID des Hunting z Spiels
 * @param integer $uid ID des Users welcher den Spielzug macht
 * @global object $db Globales Class-Object mit allen MySQL-Methoden
 * @global object $user Globales Class-Object mit den User-Methoden & Variablen
 */
function turn_sentinel ($game)
{
	global $user, $db;

	if (!$user->is_loggedin()) trigger_error(t('error-play-not-logged-in'), E_USER_ERROR);
	if (!turn_allowed($game)) user_error(t('error-game-notyourturn'), E_USER_ERROR);

	/** get the player's station and, in the same run, find out if he's got enough money */
	$e = $db->query('SELECT g.id, p.money, p.station, p.type playertype, g.round tracknr
					FROM hz_games g
					JOIN hz_players p
					  ON p.game=g.id
					WHERE g.id='.$game.'
					  AND p.user='.$user->id.'
					  AND p.money-'.turn_cost('sentinel').' >= 0
					  AND p.type!="z"
					LIMIT 0,1'
					,__FILE__, __LINE__, __FUNCTION__);
	$d = $db->fetch($e);
	if ($d !== false)
	{
		$db->query('INSERT INTO hz_sentinels (game, station)
					VALUES ('.$game.', '.$d['station'].')', __FILE__, __LINE__, __FUNCTION__);
		$db->query('UPDATE hz_players SET money=money-'.turn_cost('sentinel').'
				 	WHERE game='.$game.' AND user='.$user->id, __FILE__, __LINE__, __FUNCTION__);
		$db->query('INSERT INTO hz_tracks (game, ticket, station, nr, player)
		  			VALUES ('.$game.', "sentinel", '.$d['station'].', '.$d['tracknr'].', '.$d['playertype'].')'
					,__FILE__, __LINE__, __FUNCTION__);
	} else {
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
 * @since 1.0 function added
 *
 * @global object $db Globales Class-Object mit allen MySQL-Methoden
 * @global object $user Globales Class-Object mit den User-Methoden & Variablen
 * @return Integer mit Anzahl der laufenden Hz Spiele
 */
function hz_running_games () {
	global $db, $user;

	$e = $db->query(
		"SELECT count(*) anz
		FROM hz_games g, hz_players p
		WHERE p.game=g.id AND p.user='$user->id' AND g.state='running'
		AND if(g.nextturn='z' && p.type='z' || g.nextturn='players' && p.type!='z' && p.turndone='0', '1', '0') = '1'",
		__FILE__, __LINE__, __FUNCTION__
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
 * @since 1.0 function added
 *
 * @global object $db Globales Class-Object mit allen MySQL-Methoden
 * @global object $user Globales Class-Object mit den User-Methoden & Variablen
 * @return Integer mit Anzahl der offenen Spiele
 */
function hz_open_games () {
	global $db, $user;
	// return 0; // workaround by lukas 29.09.05 13:14
	$e = $db->query(
		"SELECT count(*) anz
		FROM hz_players AS z, hz_games AS g
		LEFT JOIN hz_players p ON p.game=g.id AND p.user='$user->id'
		WHERE g.state='open' AND z.game=g.id AND z.type='z' AND z.user!='$user->id' AND p.user IS NULL",
		__FILE__, __LINE__, __FUNCTION__
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
 * @version 1.1
 * @since 1.0 function added
 * @since 1.1 `13.08.2021` IneX` Fixed SQL-error SELECT list is not in GROUP BY clause and contains nonaggregated column z.user
 *
 * @param integer $gid ID des Hunting z Spiels
 * @global object $db Globales Class-Object mit allen MySQL-Methoden
 */
function _update_hz_dwz ($gid)
{
	global $db;

	define('BASE_POINTS', 1600);
	define('MAX_POINTS_TRANSFERABLE', 32);

	$players = array();

	$e = $db->query('SELECT g.*, sum(a.score)-g.z_score i_score, z.user z, d.score zdwz
					 FROM hz_games g, hz_aims a, hz_players z
					 LEFT JOIN hz_dwz d ON d.user=z.user
					 WHERE g.id='.$gid.' AND g.state="finished" AND a.map=g.map AND z.game=g.id AND z.type="z"
					 GROUP BY g.id, z.user',
					__FILE__, __LINE__, __FUNCTION__
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

	$e = $db->query('SELECT d.score, p.user
					 FROM hz_games g, hz_players p
					 LEFT JOIN hz_dwz d ON d.user=p.user
					 WHERE p.game=g.id AND p.type!="z"
					 AND g.id='.$gid,
					__FILE__, __LINE__, __FUNCTION__
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

	$tusr = $db->fetch($db->query('SELECT * FROM hz_dwz WHERE user='.$g['z'], __FILE__, __LINE__, __FUNCTION__));
	if ($tusr) $db->query('UPDATE hz_dwz SET score='.($zdwz+$difz).', prev_score='.$prev_score_z.' WHERE user='.$g['z'], __FILE__, __LINE__, __FUNCTION__);
	else $db->query('INSERT INTO hz_dwz (user, score, prev_score) VALUES ('.$g['z'].', '.($zdwz+$difz).', '.$prev_score_z.')', __FILE__, __LINE__, __FUNCTION__);
	foreach ($idwz as $key => $val) {
		$tusr = $db->fetch($db->query('SELECT * FROM hz_dwz WHERE user='.$key, __FILE__, __LINE__, __FUNCTION__));
		if ($tusr) $db->query('UPDATE hz_dwz SET score='.($val+$difi_avg).', prev_score='.$prev_score_i[$key].' WHERE user='.$key, __FILE__, __LINE__, __FUNCTION__);
		else $db->query('INSERT INTO hz_dwz (user, score, prev_score) VALUES ('.$key.', '.($val+$difi_avg).', '.$prev_score_i[$key].')', __FILE__, __LINE__, __FUNCTION__);
	}

	/** dwz_dif für game */
	$db->query('UPDATE hz_games SET dwz_dif='.abs($difz).' WHERE id='.$gid.' AND state="finished"', __FILE__, __LINE__, __FUNCTION__);

	/** rank update */
	$e = $db->query('SELECT * FROM hz_dwz ORDER BY score DESC', __FILE__, __LINE__, __FUNCTION__);
	$i = 1;
	$prev_score = 0;
	$rank = 0;

	while ($upd = $db->fetch($e))
	{
		if ($upd['score'] != $prev_score)
		{
			$rank = $i;
		}

		if (in_array($upd['user'], $players))
		{
			$prev_rank = ', prev_rank='.$upd['rank'];
		}else{
			$prev_rank = '';
		}

		$db->query('UPDATE hz_dwz SET rank='.$rank.$prev_rank.' WHERE user='.$upd['user'], __FILE__, __LINE__, __FUNCTION__);

		$prev_score = $upd['score'];
		++$i;
	}
}
