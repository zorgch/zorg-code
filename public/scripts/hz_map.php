<?php
/**
 * Hunting z Maps
 * @package zorg\Games\Hz
 */
global $db, $smarty;

/** File includes */
require_once __DIR__.'/../includes/hz_map.inc.php';


/** Validate & sanitize GET/POST Parameters */
$map_activate = (isset($_GET['map_activate']) && is_numeric($_GET['map_activate']) && $_GET['map_activate'] > 0 ? (int)$_GET['map_activate'] : null);
$map_deactivate = (isset($_GET['map_deactivate']) && is_numeric($_GET['map_deactivate']) && $_GET['map_deactivate'] > 0 ? (int)$_GET['map_deactivate'] : null);

if ($map_activate !== null) {
	change_map_state($map_activate, "active");
} elseif ($map_deactivate !== null) {
	change_map_state($map_deactivate, "inactive");
}

$map_id = (isset($_GET['map']) && is_numeric($_GET['map']) ? (int)$_GET['map'] : null);

if ($map_id !== null)
{
	$maps = array();
	$e = $db->query('SELECT * FROM hz_maps ORDER BY name ASC', __FILE__, __LINE__);
	while ($d = $db->fetch($e)) {
		$map_id = (int)$d['id'];
		$d['linkparam'] = 'map='.$map_id;
		$d['activate'] = 'map_activate='.$map_id;
		$d['deactivate'] = 'map_deactivate='.$map_id;

		$win_e = $db->query('SELECT if(g.z_score >= sum(a.score)-g.z_score, "z", "i") winner FROM hz_games g
								JOIN hz_aims a ON a.map=g.map
								WHERE g.map=? AND g.state=? GROUP BY g.id',
							__FILE__, __LINE__, 'empty($map_id)', [$map_id, 'finished']);
		$wins = ['z'=>0, 'i'=>0];
		while ($win = $db->fetch($win_e)) {
			$wins[$win['winner']]++;
		}
		$d['winners'] = $wins;

		$maps[] = $d;
	}
	$smarty->assign('hz_maps', $maps);
}
else {
	$e = $db->query('SELECT * FROM hz_maps WHERE id=?', __FILE__, __LINE__, 'isset($map_id)', [$map_id]);
	$d = $db->fetch($e);
	$smarty->assign('hz_map', $d);
}
