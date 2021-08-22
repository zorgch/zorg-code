<?php
/**
 * Hunting z Game actions
 * @package zorg\Games\Hz
 */
global $db, $user, $smarty;

require_once dirname(__FILE__).'/../includes/util.inc.php';
require_once INCLUDES_DIR.'hz_game.inc.php';

if (isset($_GET['game']) && is_numeric($_GET['game']) && $_GET['game'] > 0) $gameid = (int)$_GET['game'];

/** wenn kein spiel angegeben: spiel auswählen, wo man am zug ist. */
if (!isset($gameid) && $user->is_loggedin())
{
	$e = $db->query('SELECT hzg.id
					 FROM hz_games hzg
					 LEFT JOIN hz_players p
						ON hzg.id=p.game
						AND p.user='.$user->id.'
					 WHERE hzg.state="running"
						AND IF(hzg.nextturn="z" AND p.type="z"
					    OR hzg.nextturn!="z" AND p.type!="z" AND p.turndone="0", "1", "0") = "1"
					 ORDER BY hzg.turndate DESC',
					__FILE__, __LINE__, 'Hz Spiel auswählen');
	$d = $db->fetch($e);
	$gameid = $avail_tickets['id'];
}

/** Game found */
if (isset($gameid) && !is_bool($gameid) && $gameid >= 1)
{
	/** general assigns */
	$smarty->assign('imgpath', IMGPATH);
	$smarty->assign('link_taxi', 'game='.$gameid.'&ticket=taxi');
	$smarty->assign('link_bus', 'game='.$gameid.'&ticket=bus');
	$smarty->assign('link_ubahn', 'game='.$gameid.'&ticket=ubahn');
	$smarty->assign('link_black', 'game='.$gameid.'&ticket=black');
	$smarty->assign('link_sentinel', 'game='.$gameid.'&do=sentinel');
	$smarty->assign('link_stay', 'game='.$gameid.'&do=stay');
	$smarty->assign('link_game', 'game='.$gameid.'');

	/** choose ticket */
	if (isset($_GET['ticket']) && is_string($_GET['ticket']))
	{
		$smarty->assign("ticket_choosen", 1);
		$smarty->assign("ticket_img", IMGPATH.'ticket_'.$_GET['ticket'].'.gif');
		switch ((string)$_GET['ticket']) {
			case 'taxi': $ticket_text = "das Taxi"; break;
			case 'bus': $ticket_text = "den Bus"; break;
			case 'ubahn': $ticket_text = "die U-Bahn"; break;
			case 'black': $ticket_text = "ein beliebiges Verkehrsmittel oder den Fluchtwagen"; break;
			default: user_error('Invalid ticket type "'.$_GET['ticket'].'"', E_USER_ERROR);
		}
		$smarty->assign("ticket_text", $ticket_text);
		$smarty->assign("ticket_cost", turn_cost($_GET['ticket']));
	}

	/** view */
	$e = $db->query('SELECT hzg.*, m.name mapname,
						z.user z, max(me.money) mymoney,
						max(mys.bus) mystation_bus,
						max(mys.ubahn) mystation_ubahn,
						max(me.station) mystation,
						sum(a.score) - hzg.z_score player_score,
						ceil((sum(a.score)-2*hzg.z_score)/2) missing_score,
						if(me.type="z", "z", if(me.type IS NULL, "guest", "player")) mytype,
						if(me.user IS NULL || hzg.state!="running", "0", "1") i_play,
						if(hzg.nextturn="z" && me.type="z" || hzg.nextturn="players" && me.type!="z" && me.turndone="0", "0", "1") myturndone,
						if(hzg.nextturn="z" && me.type="z" || hzg.nextturn="players" && me.type!="z" && me.turndone="0",
							'.TURN_COUNT.'-hzg.turncount, '.TURN_COUNT.'-hzg.turncount-1) turns_to_money,
						max(catcher.user) catcher
					FROM user u, hz_maps m, hz_games hzg
						LEFT JOIN hz_players me ON (me.user='.($user->is_loggedin() ? $user->id : 'null').' AND me.game=hzg.id)
						LEFT JOIN hz_stations mys ON (mys.id=me.station AND mys.map=hzg.map)
						LEFT JOIN hz_aims a ON a.map=hzg.map
						JOIN hz_players z ON z.game=hzg.id AND z.type="z"
						LEFT JOIN hz_players catcher ON (catcher.game=hzg.id AND catcher.type!="z" AND catcher.station=z.station)
					WHERE hzg.id='.$gameid.' AND u.id=z.user AND m.id=hzg.map
					GROUP BY a.map, z.user, me.type, me.turndone',
					__FILE__, __LINE__, 'Hz View Game Query');
	    $game = $db->fetch($e);

	if (!empty($game) && $game !== false)
	{
		if ($game['i_play']) {
			if (isset($_GET['ticket'])) $smarty->assign("ticket_map", ticket_map($gameid, $_GET['ticket']));
			else $smarty->assign("ticket_map", ticket_map($gameid));
		}

		$smarty->assign("game", $game);

		/** select players whose turn it is */
		$e = $db->query('SELECT p.*
						 FROM hz_games g
						 JOIN hz_players p
							ON p.game=g.id
						 WHERE g.id='.$gameid.'
							AND if(g.nextturn="z" && p.type="z"
						    OR g.nextturn="players" && p.type!="z" && p.turndone="0", "1", "0") = "1"',
						__FILE__, __LINE__, 'select players whose turn it is'); // turndone = ENUM(string)!
		$awaiting_turn = array();
		while ($p = $db->fetch($e)) {
			$awaiting_turn[] = $p['user'];
		}
		$smarty->assign("awaiting_turns", $awaiting_turn);

		$tix_query = $db->query('SELECT s.*, p.type playertype
								 FROM hz_games g
								 JOIN hz_stations s
								   ON g.map = s.map
								 JOIN hz_players p
								   ON p.game = g.id
								   AND p.station = s.id
								 LEFT JOIN hz_routes r
								   ON r.map = g.map
								   AND (r.end = s.id OR r.start = s.id)
								 LEFT JOIN hz_players other
								   ON other.game = g.id
								   AND (other.station = r.start OR other.station = r.end)
								   AND other.user!=p.user
								 WHERE g.id='.$gameid.'
								   '.($user->is_loggedin() ? 'AND p.user='.$user->id : null).'
								   AND (other.user is null OR other.type="z")',
							    __FILE__, __LINE__, 'Query available Tickets');

		$avail_tickets = array("taxi"=>0, "bus"=>0, "ubahn"=>0, "black"=>0);
		while ($tix = $db->fetch($tix_query)) {
			$avail_tickets['taxi'] = 1;
			if ($tix['bus']) $avail_tickets['bus'] = 1;
			if ($tix['ubahn']) $avail_tickets['ubahn'] = 1;
			if ($tix['playertype'] == 'z') $avail_tickets['black'] = 1;
		}
		$smarty->assign("avail_tickets", $avail_tickets);

		$pl_query = $db->query(
			"SELECT p.*, if(p.type='z', 'Mister z', 'Inspector') playertype,
				d.rank, d.score
			FROM hz_players p
			LEFT JOIN hz_dwz d
			  ON d.user = p.user
			WHERE p.game = '".$gameid."'
			ORDER BY p.type ASC",
			__FILE__, __LINE__
		);
		$players = array();
		while ($pl = $db->fetch($pl_query)) {
			$pl['img'] = IMGPATH.'player_'.$pl['type'].'.gif';
			$players[] = $pl;
		}
		$smarty->assign("players", $players);

		$tracks_query = $db->query("SELECT *
				 FROM hz_tracks
				 WHERE game = '".$gameid."'
				 ORDER BY nr ASC,
				   player ASC",
				 __FILE__, __LINE__);
		$tracks = array();
		while ($track = $db->fetch($tracks_query)) {
			if ($track['player'] == 'z') {
				$track['players'] = array();
				$tracks[] = $track;
			}else{
				$last = sizeof($tracks)-1;
				$tracks[$last]['players'][$track['player']] = $track;
			}
		}
		$smarty->assign("tracks", $tracks);
	}
}
