<?php
/**
 * Hunting z Games Archive
 * @package zorg\Games\Hz
 */
global $db, $user, $smarty;

if (isset($_GET['user']) && is_numeric($_GET['user'])) $usr = $_GET['user'];
elseif ($user->is_loggedin()) $usr = $user->id;

if (isset($usr) && !empty($usr))
{
	$smarty->assign('usr', $usr);

	$e = $db->query(
			"SELECT g.*, UNIX_TIMESTAMP(g.turndate) turndate, z.user z,
			  m.name mapname, sum(a.score) - g.z_score player_score,
			  me.type mytype, catcher.user catcher
			FROM hz_games g
			JOIN hz_aims a
			  ON a.map = g.map
			JOIN hz_players z
			  ON z.game = g.id
			  AND z.type = 'z'
			JOIN hz_maps m
			  ON m.id = g.map
			LEFT JOIN hz_players me
			  ON me.game = g.id
			LEFT JOIN hz_players catcher
			  ON catcher.game = g.id
			  AND z.station = catcher.station
			  AND catcher.type != 'z'
			WHERE g.state = 'finished'
			  AND me.user =".$usr."
			GROUP BY g.id, z.user, me.type, catcher.user
			ORDER BY g.turndate DESC",
		__FILE__, __LINE__
	);
	$games = array();
	$stats = array(
		"games"=>0,
		"win"=>0,
		"loose"=>0,
		"z"=>0,
		"player"=>0,
		"avgturns"=>0,
		"i_catch"=>0,
		"other_catch"=>0,
		"zwin"=>0,
		"playerwin"=>0,
		"zloose"=>0,
		"playerloose"=>0
	);

	    while ($game = $db->fetch($e)) {
		$e2 = $db->query('SELECT count(*) numturns FROM hz_tracks WHERE player="z" AND game='.$game['id'],
						__FILE__, __LINE__, 'SELECT numturns');
		$numturns = $db->fetch($e2);
		$game['numturns'] = $numturns['numturns'];
		$stats['avgturns'] += $numturns['numturns'];

		$e2 = $db->query('SELECT * FROM hz_players WHERE type!="z" AND game='.$game['id'].' ORDER BY type',
						__FILE__, __LINE__, 'SELECT FROM hz_players');
		$game['players'] = array();
		while ($pl = $db->fetch($e2)) {
			$game['players'][] = $pl;
		}

		if ($game['mytype'] != 'z' && $game['catcher'] == $usr) $stats['i_catch']++;
		elseif ($game['mytype']=='z') $stats['other_catch']++;

		if (
			$game['z_score'] > $game['player_score'] && $game['mytype']=="z"
			|| $game['player_score'] > $game['z_score'] && $game['mytype']!='z'
		) {
			$game['ausgang'] = '<b>gewonnen</b>';
			if ($game['mytype'] == 'z') $stats['zwin']++;
			else $stats['playerwin']++;
			$stats['win']++;
		}else{
			$game['ausgang'] = 'verloren';
			if ($game['mytype'] == 'z') $stats['zloose']++;
			else $stats['playerloose']++;
			$stats['loose']++;
		}

		if ($game['mytype'] == "z") $stats['z']++;
		else $stats['player']++;

		$game['link_map'] = "map=$game[map]";
		$game['link_game'] = "game=$game[id]";

		$games[] = $game;

		$stats['games']++;

	}

	$stats['avgturns'] /= $stats['games'];


	foreach ($stats as $key => $value) {
		if (!$value) $stats[$key] = '-';
	}


	$smarty->assign('games', $games);
	$smarty->assign('stats', $stats);
}
