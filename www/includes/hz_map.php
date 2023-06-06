<?php
/**
 * Hunting z Map Queries
 * @package zorg\Games\HuntingZ
 */
require_once dirname(__FILE__).'/config.inc.php';
require_once INCLUDES_DIR.'mysql.inc.php';
require_once INCLUDES_DIR.'hz_map.inc.php';

/** Check Map ID */
$mapid = (isset($_GET['id']) && is_numeric($_GET['id']) && $_GET['id'] > 0 ? (int)$_GET['id'] : null);

if (!empty($mapid))
{
	$e = $db->query('SELECT * FROM hz_maps WHERE id='.$mapid, __FILE__, __LINE__, 'SELECT FROM hz_maps');
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
				WHERE map=".$d['id']."
				ORDER BY typesort ASC", __FILE__, __LINE__);
		while ($r = $db->fetch($e)) {
			draw_route(
				$im,
				$r['type'],
				station_pos($d['id'], $r['start']),
				station_pos($d['id'], $r['end']),
				transit_string2array($r['transit'], $d['id'])
			);
		}

		$e = $db->query("SELECT a.*, s.x, s.y FROM hz_aims a, hz_stations s WHERE a.map=".$d['id']." AND a.map=s.map AND s.id=a.station", __FILE__, __LINE__);
		while ($a = $db->fetch($e)) {
			draw_aim($im, $a['x'], $a['y'], $a['score']);
		}

		$e = $db->query('SELECT * FROM hz_stations WHERE map='.$d['id'], __FILE__, __LINE__);
		while ($s = $db->fetch($e)) {
			draw_station($im, $s['id'], $s['x'], $s['y'], $s['bus'], $s['ubahn']);
		}

		header("Content-Type: image/gif");
		imagegif($im);
	} else {
		user_error(t('unknown-map', 'hz', $mapid), E_USER_ERROR);
	}
} else {
	http_response_code(404); // Set response code 404 (not found) and exit.
	user_error(t('unknown-map', 'hz', $mapid), E_USER_ERROR);
}
