<?
	include_once($_SERVER['DOCUMENT_ROOT'].'/includes/util.inc.php');
	include_once($_SERVER['DOCUMENT_ROOT'].'/includes/colors.inc.php');

	define("MAPIMGPATH", $_SERVER['DOCUMENT_ROOT'].'/images/hz/');
	
	
	function create_map ($mapfile, &$map_config, &$error, &$img_map) {
		global $user, $map_stations, $map_routes;
		$imgfile = $_SERVER['DOCUMENT_ROOT'].'/../data/hz_maps/'.$user->id.'.gif';
		
		$cfg = read_config($mapfile);
		if ($err = check_config ($cfg)) {
			$error = $err;
		}
		
		$im = draw_map_base($cfg['map']['width'], $cfg['map']['height']);
		
		foreach ($map_routes['ubahn'] as $it) 
			draw_route(
				$im, 
				"ubahn", 
				array($map_stations[$it['start']]['x'], $map_stations[$it['start']]['y']), 
				array($map_stations[$it['end']]['x'], $map_stations[$it['end']]['y']),
				$it['transit'],
				$_GET['station_checker']
			);
		foreach ($map_routes['bus'] as $it) 
			draw_route(
				$im, 
				"bus", 
				array($map_stations[$it['start']]['x'], $map_stations[$it['start']]['y']), 
				array($map_stations[$it['end']]['x'], $map_stations[$it['end']]['y']),
				$it['transit'],
				$_GET['station_checker']
			);
		foreach ($map_routes['taxi'] as $it) 
			draw_route(
				$im, 
				"taxi", 
				array($map_stations[$it['start']]['x'], $map_stations[$it['start']]['y']), 
				array($map_stations[$it['end']]['x'], $map_stations[$it['end']]['y']),
				$it['transit'],
				$_GET['station_checker']
			);
			
		foreach ($map_routes['black'] as $it)
			draw_route(
				$im, 
				"black", 
				array($map_stations[$it['start']]['x'], $map_stations[$it['start']]['y']), 
				array($map_stations[$it['end']]['x'], $map_stations[$it['end']]['y']),
				$it['transit'],
				$_GET['station_checker']
			);
		
		unset($_GET['station_checker']);
			
		foreach ($cfg['aims'] as $it) draw_aim($im, $map_stations[$it['station']]['x'], $map_stations[$it['station']]['y'], $it['score']);
		$img_map;
		
		foreach ($cfg['stations'] as $it) {
			draw_station($im, $it['id'], $it['x'], $it['y'], $it['bus'], $it['ubahn']);
			
			$img_map .= '<area shape="rect" coords="'.($it['x']-20).','
		      .($it['y']-15).','.($it['x']+20).','.($it['y']+15).'" '.
				'href="/?'.url_params().'&station_checker='.$it['id'].'" />'
			;
		}
		
		
		$map_config = $cfg['map'];
		
		imagegif($im, $imgfile);
		return $imgfile;
	}
	
	
	function save_map ($mapfile) {
		global $db, $user;
		
		$cfg = read_config($mapfile);
		if ($err = check_config($cfg)) {
			return $err;
		}else{
			$cfg['map']['user'] = $user->id;
			$mapid = $db->insert("hz_maps", $cfg['map'], __FILE__, __LINE__);
			foreach ($cfg['stations'] as $it) {
				$it['map'] = $mapid;
				print_array($it);
				$db->insert("hz_stations", $it, __FILE__, __LINE__);
			}
			foreach ($cfg['routes'] as $it) {
				$transit = "";
				foreach ($it['transit'] as $tr) {
					if (is_array($tr)) $transit .= "$tr[0]:$tr[1]-";
					else $transit .= "$tr-";
				}
				$it['transit'] = substr($transit, 0, -1);
				$it['map'] = $mapid;
				$db->insert("hz_routes", $it, __FILE__, __LINE__);
			}
			foreach ($cfg['aims'] as $it) {
				$it['map'] = $mapid;
				$db->insert("hz_aims", $it, __FILE__, __LINE__);
			}
		}
	}
	
	
	function change_map_state ($map, $state) {
		global $db, $user;
		
		if (!in_array($state, array("active", "inactive"))) user_error("Invalid map state '$state'", E_USER_ERROR);
		
		$e = $db->query("SELECT * FROM hz_maps WHERE id=$map", __FILE__, __LINE__);
		$d = $db->fetch($e);
		if ($d['user'] == $user->id) {
			$db->query("UPDATE hz_maps SET state='$state' WHERE id=$map", __FILE__, __LINE__);
		}else{
			user_error("Permission denied for change_map_state on map '$map'", E_USER_ERROR);
		}
	}
	
	
	
	
	function station_pos ($map, $id) {
		global $db;
		
		$e = $db->query("SELECT * FROM hz_stations WHERE map='$map' AND id='$id'", __FILE__, __LINE__);
		$d = $db->fetch($e);
		if ($d) {
			return array($d['x'], $d['y']);
		}else{
			user_error("Invalid station: map '$map', id '$id'", E_USER_ERROR);
		}
	}
	
	function transit_string2array ($transit, $map) {
		global $db;
	
		$ret = array();
		if ($transit) {
			$stations = explode("-", $transit);
			if (sizeof($stations)) {
				foreach ($stations as $it) {
					$t = explode(":", $it);
					if (sizeof($t) == 2) {
						$ret[] = $t;
					}else{
						$ret[] = station_pos($map, $t[0]);
					}
				}
			}
		}
		
		return $ret;
	}
	
	function read_config ($mapfile) {
		global $map_stations, $map_routes;
		
		if (!isset($map_stations)) $map_stations = array();
		if (!isset($map_routes)) $map_routes = array("taxi"=>array(), "bus"=>array(), "ubahn"=>array(), "black"=>array());
		
		$ret = array("map"=>array(), "stations"=>array(), "routes"=>array(), "aims"=>array());
		$section = "";
		$config = file($mapfile);
		for ($i=0; $i<sizeof($config); $i++) {
			if (preg_match("/\[MAP\]/", $config[$i])) {
				$section = "map";
			}elseif (preg_match("/\[STATIONS\]/", $config[$i])) {
				$section = "stations";
			}elseif (preg_match("/\[ROUTES\]/", $config[$i])) {
				$section = "routes";
			}elseif (preg_match("/\[AIMS\]/", $config[$i])) {
				$section = "aims";
			}else{
				if ($section == "map") {
					$entry = explode("=", $config[$i]);
					if (sizeof($entry) == 2) {
						$ret['map'][trim($entry[0])] = trim($entry[1]);
					}
				}elseif ($section == "stations") {
					$_entry = explode(",", $config[$i]);
					if (sizeof($_entry) == 5) {
						$entry = array();
						$entry['id'] = trim($_entry[0]);
						$entry['x'] = trim($_entry[1]);
						$entry['y'] = trim($_entry[2]);
						$entry['ubahn'] = trim($_entry[3]);
						$entry['bus'] = trim($_entry[4]);
						
						$ret['stations'][] = $entry;
						$map_stations[$entry['id']] = $entry;
					}
				}elseif ($section == "routes") {
					$entry = explode(",", $config[$i]);
					$ent = array();
					if (sizeof($entry) >= 3 && (trim($entry[0])=="bus" || trim($entry[0])=="taxi" || trim($entry[0])=="ubahn" || trim($entry[0])=="black")) {
						$ent['type'] = trim($entry[0]);
						$ent['start'] = trim($entry[1]);
						$ent['end'] = trim($entry[2]);
						$ent['transit'] = array();
						for ($j=3; $j<sizeof($entry); $j++) {
							$b = explode(":", $entry[$j]);
							if (sizeof($b) == 2) {
								$b[0] = trim($b[0]);
								$b[1] = trim($b[1]);
								$ent['transit'][] = $b;
							}elseif (sizeof($b) == 1) {
								$ent['transit'][] = trim($b[0]);
							}
						}
						
						$ret['routes'][] = $ent;
						$map_routes[$ent['type']][] = $ent;
						$map_stations[$ent['start']]["has_$ent[type]"] = 1;
						$map_stations[$ent['end']]["has_$ent[type]"] = 1;
					}
				}elseif ($section == "aims") {
					$entry = explode(",", $config[$i]);
					if (sizeof($entry) == 2) {
						$ent = array('station'=>trim($entry[0]), 'score'=>trim($entry[1]));
					}
					$ret['aims'][] = $ent;
				}
			}
		}
		return $ret;
	}
	
	function check_config ($cfg) {
		global $db, $map_stations;
		
		if (!$cfg['map']['name']) return "Missing 'name' in section [MAP]";
		$e = $db->query("SELECT * FROM hz_maps WHERE name='".$cfg['map']['name']."'", __FILE__, __LINE__);
		$d = $db->fetch($e);
		if ($d) return ("Es gibt schon eine Map mit diesem Namen. Bitte einen anderen Namen wählen.");
		
		if (!$cfg['map']['width']) return "Missing 'width' in section [MAP]";
		if (!is_numeric($cfg['map']['width']) || $cfg['map']['width']>5000 || $cfg['map']['width']<200) return "'width' in section [Map] must be between 200 and 5000.";
		if (!$cfg['map']['height']) return "Missing 'height' in section [MAP]";
		if (!is_numeric($cfg['map']['height']) || $cfg['map']['height']>5000 || $cfg['map']['height']<200) return "'players' in section [Map] must be between 200 and 5000.";
		if (!$cfg['map']['players']) return "Missing 'players' in section [MAP]";
		if (!is_numeric($cfg['map']['players']) || $cfg['map']['players']>8 || $cfg['map']['players']<1) return "'players' in section [Map] must be between 1 and 8.";
		$valid_map_keys = array("name", "width", "height", "players");
		foreach ($cfg['map'] as $key=>$val) {
			if (!in_array($key, $valid_map_keys)) return "Invalid option '$key' in section [MAP].";
		}
		
		$station_ids = array();
		
		foreach ($map_stations as $it) {
			if (!is_numeric($it['id']) || $it['id']<1 || $it['id']>999) return "Invalid Station id: '$it[id]' (must be between 1 and 999)";
			if (in_array($it['id'], $station_ids)) return "Dublicate Station id: $it[id]";
			array_push($station_ids, $it['id']);
			if (!is_numeric($it['x']) || $it['x']<1 || $it['x']>5000) return "Station $it[id]: Invalid x-position (2. Parameter, must be between 1 and 5000).";
			if (!is_numeric($it['y']) || $it['y']<1 || $it['y']>5000) return "Station $it[id]: Invalid y-position (3. Parameter, must be between 1 and 5000).";
			if ($it['ubahn']!=0 && $it['ubahn']!=1) return "Station $it[id]: Invalid U-Bahn flag (4. Parameter, 0 or 1 allowed).";
			if ($it['bus']!=0 && $it['bus']!=1) return "Station $it[id]: Invalid Bus flag (5. Parameter, 0 or 1 allowed).";
			if (!$it['has_taxi']) return "Station $it[id]: Taxi Route required.";
			if ($it['bus'] && !$it['has_bus']) return "Station $it[id]: Bus Route required.";
			if ($it['ubahn'] && !$it['has_ubahn']) return "Station $it[id]: Underground Route required.";
		}
		
		foreach ($cfg['routes'] as $it) {
			if (!$map_stations[$it['start']]['id']) return "$it[type]-Route $it[start] - $it[end]: Start Station doesn't exist.";
			if ($it['type'] != "taxi" && $it['type'] != "black" && !$map_stations[$it['start']][$it['type']]) 
				return "$it[type]-Route $it[start] - $it[end]: Start Station doesn't support $it[type]-Routes.";
			if (!$map_stations[$it['end']]['id']) return "$it[type]-Route $it[start] - $it[end]: End Station doesn't exist.";
			if ($it['type'] != "taxi" && $it['type'] != "black" && !$map_stations[$it['end']][$it['type']]) 
				return "$it[type]-Route $it[start] - $it[end]: End Station doesn't support $it[type]-Routes.";
			if ($it['start'] == $it['end']) return "$it[type]-Route $it[start] - $it[end]: Start and End Station must not be the same.";
			foreach ($it['transit'] as $tr) {				
				if (is_array($tr)) {
					if (!is_numeric($tr[0]) || $tr[0]<1 || $tr[0]>5000) return "$it[type]-Route $it[start] - $it[end]: Points (x) must be between 1 and 5000).";
					if (!is_numeric($tr[1]) || $tr[1]<1 || $tr[1]>5000) return "$it[type]-Route $it[start] - $it[end]: Points (y) must be between 1 and 5000).";
				}else{
					if (!$map_stations[$tr]['id']) return "$it[type]-Route $it[start] - $it[end]: Transit Station $tr doesn't exist.";
					if ($map_stations[$tr][$it['type']]) return "$it[type]-Route $it[start] - $it[end]: Transit Station $tr must not support $it[type]-Routes.";
				}
			}
		}
		
		$aim_ids = array();
		foreach ($cfg['aims'] as $it) {
			if (in_array($it['station'], $aim_ids)) return "Duplicate aims on station '$it[station]'.";
			if (!$map_stations[$it['station']]) return "Aim on station '$it[station]': Station doesn't exist.";
			if ($it['score'] < 1 || $it['score'] > 999) return "Aim on station '$it[station]': Invalid score (must be between 1 and 999).";
			$aim_ids[] = $it['station'];
		}
		
		return 0;
	}
	
	function draw_map_base ($x, $y) {
		$im = @ImageCreate ($x,$y);
		if (!$im) return array("error"=>__LINE__);
		
		$bg = htmlcolor2array(HZ_BG_COLOR);
		$background_color = ImageColorAllocate ($im, $bg['r'], $bg['g'], $bg['b']);		
		
		define("COLOR_TAXI", imagecolorallocate($im, 255,255,0));
		define("COLOR_UBAHN", imagecolorallocate($im, 255,0,0));
		define("COLOR_BUS", imagecolorallocate($im, 0,200,0));
		define("COLOR_BLACK", imagecolorallocate($im, 0,0,0));
		define("COLOR_BORDER", imagecolorallocate($im, 0,0,0));
		define("COLOR_TEXT", imagecolorallocate($im, 0,0,0));
		
		define("STATION_TAXI", imagecreatefromgif(MAPIMGPATH."station_taxi.gif"));
		define("STATION_TAXI_BUS", imagecreatefromgif(MAPIMGPATH."station_taxi_bus.gif"));
		define("STATION_UBAHN_TAXI", imagecreatefromgif(MAPIMGPATH."station_ubahn_taxi.gif"));
		define("STATION_UBAHN_TAXI_BUS", imagecreatefromgif(MAPIMGPATH."station_ubahn_taxi_bus.gif"));
		
		define("ROUTE_UBAHN", imagecreatefromgif(MAPIMGPATH."route_ubahn.gif"));
		define("ROUTE_BUS", imagecreatefromgif(MAPIMGPATH."route_bus.gif"));
		define("ROUTE_TAXI", imagecreatefromgif(MAPIMGPATH."route_taxi.gif"));
		define("ROUTE_BLACK", imagecreatefromgif(MAPIMGPATH."route_black.gif"));
		
		define("AIM", imagecreatefromgif(MAPIMGPATH."aim.gif"));
		define("AIM_CAUGHT", imagecreatefromgif(MAPIMGPATH."aim_caught.gif"));
		
		define("PLAYER_1", imagecreatefromgif(MAPIMGPATH."player_1.gif"));
		define("PLAYER_2", imagecreatefromgif(MAPIMGPATH."player_2.gif"));
		define("PLAYER_3", imagecreatefromgif(MAPIMGPATH."player_3.gif"));
		define("PLAYER_4", imagecreatefromgif(MAPIMGPATH."player_4.gif"));
		define("PLAYER_5", imagecreatefromgif(MAPIMGPATH."player_5.gif"));
		define("PLAYER_6", imagecreatefromgif(MAPIMGPATH."player_6.gif"));
		define("PLAYER_7", imagecreatefromgif(MAPIMGPATH."player_7.gif"));
		define("PLAYER_8", imagecreatefromgif(MAPIMGPATH."player_8.gif"));
		define("PLAYER_Z", imagecreatefromgif(MAPIMGPATH."player_z.gif"));
		define("PLAYER_Z_SEEN", imagecreatefromgif(MAPIMGPATH."player_z_seen.gif"));
		define("PLAYER_ME", imagecreatefromgif(MAPIMGPATH."player_me.gif"));
		
		define("SENTINEL", imagecreatefromgif(MAPIMGPATH."sentinel.gif"));
		
		imagerectangle($im, 0, 0, $x-1, $y-1, COLOR_BORDER);
		
		return $im;
	}
	
	function draw_station (&$im, $id, $x, $y, $bus, $ubahn) {		
		if ($bus && $ubahn) $type = STATION_UBAHN_TAXI_BUS;
		elseif ($bus) $type = STATION_TAXI_BUS;
		elseif ($ubahn) $type = STATION_UBAHN_TAXI;
		else $type = STATION_TAXI;
		
		if (floor($id / 100)) $str_x = $x-10;		// 3-stellig
		elseif (floor($id / 10)) $str_x = $x-6;	// 2-stellig
		else $str_x = $x-2;								// 1-stellig
		
		imagecopy($im, $type, $x-15,$y-10, 0,0, 30, 20);
		ImageString ($im, 3, $str_x,$y-7, $id, COLOR_TEXT);
	}
	
	function draw_aim (&$im, $x, $y, $score, $not_caught = true) {
		if ($not_caught) $pic = AIM;
		else $pic = AIM_CAUGHT;
		
		if (floor($score / 100)) $str_x = $x-8;		// 3-stellig
		elseif (floor($score / 10)) $str_x = $x-5;	// 2-stellig
		else $str_x = $x-2;								// 1-stellig
		
		imagecopy($im, $pic, $x-20, $y-25, 0,0, 40,40);
		imagestring($im, 2, $str_x, $y-23, $score, COLOR_TEXT);
	}
	
	function draw_player (&$im, $x, $y, $type) {
		switch ($type) {
			case 1: $pic = PLAYER_1; break;
			case 2: $pic = PLAYER_2; break;
			case 3: $pic = PLAYER_3; break;
			case 4: $pic = PLAYER_4; break;
			case 5: $pic = PLAYER_5; break;
			case 6: $pic = PLAYER_6; break;
			case 7: $pic = PLAYER_7; break;
			case 8: $pic = PLAYER_8; break;
			case 'z': $pic = PLAYER_Z; break;
			default: user_error("Invalid player type '$type'", E_USER_ERROR);
		}
		
		imagecopy($im, $pic, $x+5, $y-23, 0, 0, 30,40);
	}
	
	function draw_player_me (&$im, $x, $y) {
		imagecopy($im, PLAYER_ME, $x+14, $y-16, 0, 0, 12,12);
	}
	
	function draw_z_seen (&$im, $x, $y, $final=0) {
		if ($final) $pic = PLAYER_Z;
		else $pic = PLAYER_Z_SEEN;
		
		imagecopy($im, $pic, $x-35, $y-23, 0,0, 30,40);
	}
	
	function draw_sentinel (&$im, $x, $y) {
		imagecopy($im, SENTINEL, $x-21, $y+3, 0,0, 43,25);
	}
	
	
	function draw_route (&$im, $type, $start, $end, $transit = array(), $station_checker=0) {
		global $map_stations, $map_routes;
		
		if ($station_checker) {
			$s = $station_checker;
			$station_checker = array($map_stations[$s]['x'], $map_stations[$s]['y']);
		}
		
		if ($station_checker && ($start==$station_checker || $end==$station_checker) || !$station_checker) {
			switch ($type) {
				case "ubahn": 
					imagesetbrush($im, ROUTE_UBAHN);
					break;
				case "bus": 
					imagesetbrush($im, ROUTE_BUS);
					break;
				case "taxi": 
					imagesetbrush($im, ROUTE_TAXI);
					break;
				case "black":
					imagesetbrush($im, ROUTE_BLACK);
					break;
				default: user_error("Invalid route type", E_USER_ERROR);
			}
			
			for ($i=0; $i<sizeof($transit); $i++) {
				if (!is_array($transit[$i])) {
					$station = $map_stations[$transit[$i]];
					$transit[$i] = array();
					$transit[$i][0] = $station['x'];
					$transit[$i][1] = $station['y'];
				}
			}
			
			if (sizeof($transit)) {
				imageline($im, $start[0], $start[1], $transit[0][0], $transit[0][1], IMG_COLOR_BRUSHED);
				for ($i=0; $i<sizeof($transit)-1; $i++) {
					imageline($im, $transit[$i][0], $transit[$i][1], $transit[$i+1][0], $transit[$i+1][1],  IMG_COLOR_BRUSHED);
				}
				imageline($im, $transit[$i][0], $transit[$i][1], $end[0], $end[1], IMG_COLOR_BRUSHED);
			}else{
				imageline($im, $start[0], $start[1], $end[0], $end[1], IMG_COLOR_BRUSHED);
			}
		}
	}
?>
