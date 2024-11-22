<?php
/**
 * Hunting z Maps
 * @package zorg\Games\Hz
 */
global $db, $smarty;

/** File includes */
require_once dirname(__FILE__).'/../includes/hz_map.inc.php';

if (isset($_GET['map_activate']) && is_numeric($_GET['map_activate'])) {
	change_map_state($_GET['map_activate'], "active");
}elseif (isset($_GET['map_deactivate']) && is_numeric($_GET['map_deactivate'])) {
	change_map_state($_GET['map_deactivate'], "inactive");
}

$map_id = (isset($_GET['map']) && is_numeric($_GET['map']) ? (int)$_GET['map'] : null);

if (empty($map_id))
{
	$maps = array();
	$e = $db->query('SELECT * FROM hz_maps ORDER BY name ASC', __FILE__, __LINE__);
	while ($d = $db->fetch($e)) {
		$d['linkparam'] = 'map='.$d['id'];
		$d['activate'] = 'map_activate='.$d['id'];
		$d['deactivate'] = 'map_deactivate='.$d['id'];

		$win_e = $db->query(
			"SELECT if(g.z_score >= sum(a.score)-g.z_score, 'z', 'i') winner
			FROM hz_games g
			JOIN hz_aims a
			  ON a.map=g.map
			WHERE g.map=".$d['id']."
			  AND g.state='finished'
			GROUP BY g.id",
			__FILE__, __LINE__
		);
		$wins = array('z'=>0, 'i'=>0);
		while ($win = $db->fetch($win_e)) {
			$wins[$win['winner']]++;
		}
		$d['winners'] = $wins;

		$maps[] = $d;
	}
	$smarty->assign('hz_maps', $maps);

} else {	
	$e = $db->query('SELECT * FROM hz_maps WHERE id='.$map_id, __FILE__, __LINE__);
	$d = $db->fetch($e);
	$smarty->assign('hz_map', $d);
}
