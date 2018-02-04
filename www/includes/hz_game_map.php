<?php
include_once( __DIR__ .'/usersystem.inc.php');
include_once( __DIR__ .'/hz_map.inc.php');
include_once( __DIR__ .'/strings.inc.php');
	
        if (!is_numeric($_GET['id'])) user_error("nuet isch", E_USER_ERROR);
        $gameid = $_GET['id'];
        $e = $db->query(
		"SELECT p.user from hz_players p where p.user='".$user->id."' and p.game = '".$gameid."'", __FILE__, __LINE__);
	$iplay = $db->fetch($e);
	$e = $db->query(
		"SELECT g.*, m.width, m.height, if(z.user='$user->id', '1', '0') iamz
		FROM hz_games g
		JOIN hz_players z
		  ON z.game=g.id
		JOIN hz_maps m
		  ON m.id=g.map
		WHERE g.id='".$gameid."' AND z.type='z'", 
		__FILE__, __LINE__);
	$d = $db->fetch($e);
	if ($d) {
		$im = draw_map_base($d['width'], $d['height']);
		
		$e = $db->query(
				"SELECT *, 
				CASE type 
				WHEN 'ubahn' THEN 1 
				WHEN 'bus' THEN 2
				WHEN 'taxi' THEN 3
				WHEN 'black' THEN 4
				END AS typesort
				FROM hz_routes 
				WHERE map='".$d[map]."'
				ORDER BY typesort ASC", __FILE__, __LINE__);
		while ($r = $db->fetch($e)) {
			draw_route(
				$im, 
				$r['type'], 
				station_pos($d['map'], $r['start']), 
				station_pos($d['map'], $r['end']), 
				transit_string2array($r['transit'], $d['map'])
			);
		}
		
		$e = $db->query(
			"SELECT a.*, s.x, s.y, if(t.station IS NULL, '1', '0') caught
			FROM hz_aims a
			JOIN hz_stations s
			  ON a.map=s.map
			  AND a.station=s.id
			LEFT JOIN hz_tracks t
			  ON t.game='".$gameid."'
			  AND t.station=a.station
			  AND t.player='z'
			WHERE a.map='".$d[map]."'", 
			__FILE__, __LINE__
		);
		while ($a = $db->fetch($e)) {
			draw_aim($im, $a['x'], $a['y'], $a['score'], $a['caught']);
		}
		
		
		$e = $db->query("SELECT * FROM hz_tracks t WHERE t.game=$d[id] AND t.player='z' ORDER BY nr DESC LIMIT 0,1", __FILE__, __LINE__);
		$last_track = $db->fetch($e);
		
		$e = $db->query(
			"SELECT p.*, s.x, s.y
			FROM hz_players p, hz_stations s
			WHERE p.game=$d[id] AND s.id=p.station AND s.map=$d[map]
			ORDER BY p.type ASC", 
			__FILE__, __LINE__
		);
		while ($p = $db->fetch($e)) {
			if ($p['type']=='z' && $d['iamz'] || $p['type']!='z' || $last_track['station']==$p['station']) {
				draw_player($im, $p['x'], $p['y'], $p['type']);
				if ($p['user'] == $user->id) draw_player_me($im, $p['x'], $p['y']);
			}
		}
		
		$e = $db->query(
			"SELECT s.x, s.y, t.station
			FROM hz_tracks t, hz_stations s
			WHERE t.game=$d[id] AND s.id=t.station AND s.map=$d[map] AND t.player='z'",
			__FILE__, __LINE__
		);
		while ($t = $db->fetch($e)) {
			draw_z_seen($im, $t['x'], $t['y'], 0);
		}
		
		if ($d['state'] == "finished") {
			$e = $db->query(
				"SELECT s.x, s.y
				FROM hz_players z, hz_stations s
				WHERE z.game=$d[id] AND z.type='z' AND s.id=z.station AND s.map=$d[map]",
				__FILE__, __LINE__
			);
			$f = $db->fetch($e);
			if ($d) draw_z_seen($im, $f['x'], $f['y'], 1);
		}		
		
		
		$e = $db->query(
			"SELECT sen.*, s.x, s.y, if(t.station IS NULL, '0', '1') disp
			FROM hz_sentinels sen, hz_stations s
			LEFT JOIN hz_tracks t ON t.game=$d[id] AND t.station=s.id AND t.player='z'
			WHERE sen.game=$d[id] AND sen.station=s.id AND s.map=$d[map]", __FILE__, __LINE__);
		while ($sen = $db->fetch($e)) {
		        if ((!$d['iamz'] && $iplay) || $sen['disp'] || $d['state']=="finished") draw_sentinel($im, $sen['x'], $sen['y']); 
		}
		
		$e = $db->query("SELECT * FROM hz_stations WHERE map=$d[map]", __FILE__, __LINE__);		
		while ($s = $db->fetch($e)) {
			draw_station($im, $s['id'], $s['x'], $s['y'], $s['bus'], $s['ubahn']);
		}

		header("Content-Type: image/gif");
		imagegif($im);
	}else{
		user_error(t('unknown-map', 'hz', $_GET['id']), E_USER_ERROR);
	}
