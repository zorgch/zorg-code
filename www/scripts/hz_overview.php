<?
	require_once($_SERVER['DOCUMENT_ROOT'].'/includes/hz_game.inc.php');

	hz_turn_passing();

	global $db, $user, $smarty;


	// running games
	$e = $db->query(
		"SELECT hzg.*, unix_timestamp(hzg.turndate) AS turndate, z.user AS mrz, m.name AS mapname,
		  if(me.type='z' && hzg.nextturn='z' || me.type!='z' && hzg.nextturn='players' &&
		     me.turndone='0', '1', '0') AS myturn
		FROM hz_games hzg
		JOIN hz_players z
		  ON (z.game=hzg.id && z.type='z')
		JOIN hz_maps m
		  ON hzg.map=m.id
		LEFT JOIN hz_players me
		  ON (hzg.id=me.game && me.user='".$user->id."')
		WHERE hzg.state='running'
		ORDER BY hzg.turndate DESC",
		__FILE__, __LINE__);
	
        $running_games = array();
	while ($d = $db->fetch($e)) {
		$d['maplink'] = "map=$d[map]";
		$d['gamelink'] = "game=$d[id]";
	        $d['z'] = $d['mrz'];
		$e2 = $db->query("SELECT * FROM hz_players WHERE type!='z' AND game='".$d[id]."'", __FILE__, __LINE__);
		$d['players'] = array();
		if ($d['nextturn'] == 'z') $d['awaiting'] = array($d['z']);
		else $d['awaiting'] = array();
		while ($d2 = $db->fetch($e2)) {
			$d['players'][] = $d2;
			if ($d2['turndone']==0 && $d['nextturn']=='players') $d['awaiting'][] = $d2['user'];
		}
		$running_games[] = $d;
	}
	$smarty->assign("running_games", $running_games);

	$own_games = $db->fetch($db->query(
		"SELECT count(me.user) anz
		FROM hz_games hzg
		JOIN hz_players me
		  ON me.game = hzg.id
		WHERE hzg.state!='finished'
		  AND me.type='z'
		  AND me.user='".$user->id."'",
		__FILE__, __LINE__
	));
	$smarty->assign("new_game_possible", $own_games['anz']<=MAX_HZ_GAMES ? 1 : 0);

	$e = $db->query("SELECT * FROM hz_maps 
			  WHERE state='active'
			  ORDER BY name ASC",
			__FILE__, __LINE__);
	$map_ids = array();
	$map_names = array();
	while ($d = $db->fetch($e)) {
		$map_ids[] = $d['id'];
		$map_names[] = "$d[name] ($d[players] Inspectors)";
	}
	$smarty->assign("map_ids", $map_ids);
	$smarty->assign("map_names", $map_names);


	// open games
	$e = $db->query(
		"SELECT hzg.*, z.user mrz, m.name mapname, m.players total,
		  (m.players-count(numpl.user)+1) missing,
		  IF(p.user IS NULL, '0', '1') joined
		FROM hz_games hzg
		LEFT JOIN hz_players p
		  ON p.game=hzg.id
		  AND p.user='".$user->id."'
		LEFT JOIN hz_maps m
		  ON hzg.map=m.id
		LEFT JOIN hz_players z
		  ON z.game=hzg.id
		  AND z.type='z'
		LEFT JOIN hz_players numpl
		  ON numpl.game = hzg.id
		WHERE hzg.state='open'
		GROUP BY hzg.id",
		__FILE__, __LINE__);
	$open_games = array();
	while ($d = $db->fetch($e)) {
		$d['maplink'] = "map=$d[map]";
		$d['joinlink'] = "join=$d[id]";
	        $d['z'] = $d['mrz'];
	        $e2 = $db->query("SELECT * FROM hz_players
				   WHERE type!='z'
				   AND game='".$d[id]."'",
				 __FILE__, __LINE__);
		$d['players'] = array();
		while ($d2 = $db->fetch($e2)) {
			$d['players'][] = $d2;
		}
		$open_games[] = $d;
	}

	$smarty->assign("open_games", $open_games);
?>
