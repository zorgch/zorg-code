<?php
/**
 * Hunting z Games Stats
 * @package zorg\Games\Hz
 */
global $db, $user, $smarty;

$usr_e = $db->query(
	"SELECT d.*, concat(u.clan_tag, u.username) username, count(g.id) games, count(if(p.type='z', '1', NULL)) zgames
	FROM hz_players p, hz_games g, user u, hz_dwz d
	WHERE g.id=p.game AND g.state='finished' AND u.id=p.user AND d.user=p.user
	GROUP BY p.user ORDER BY d.rank", __FILE__, __LINE__, 'SELECT Finished Games Stats'
);
$stats = array();
while ($usr = $db->fetch($usr_e))
{
	/** Wins + Loose by User */
	$e = $db->query(
		"SELECT CASE WHEN (g.z_score > sum(a.score)-g.z_score AND p.type='z') OR (g.z_score < sum(a.score)-g.z_score AND p.type!='z') THEN '1' ELSE '0' END win
		FROM hz_players p, hz_games g, hz_aims a WHERE g.id=p.game AND a.map=g.map AND g.state='finished' AND p.user=? GROUP BY g.id, p.type HAVING win='1'",
		__FILE__, __LINE__, 'SELECT User Wins+Looses', [intval($usr['user'])]
	);
	$usr['win'] = $db->num($e);
	$usr['loose'] = $usr['games'] - $usr['win'];


	if ($usr['zgames'] != 0) {
		$e = $db->query(
			"SELECT CASE WHEN (g.z_score > sum(a.score)-g.z_score AND p.type='z') THEN '1' ELSE '0' END win
			FROM hz_players p, hz_games g, hz_aims a
			WHERE g.id=p.game AND a.map=g.map AND g.state='finished' AND p.user=? GROUP BY g.id, p.type HAVING win='1'",
			__FILE__, __LINE__, 'SELECT User Wins as MrZ', [$usr['user']]
		);
		$usr['zwin'] = $db->num($e);
		$usr['zloose'] = $usr['zgames'] - $usr['zwin'];
	}else{
		$usr['zwin'] = 0;
		$usr['zloose'] = 0;
	}


	$usr['igames'] = $usr['games'] - $usr['zgames'];
	if ($usr['igames'] != 0) {
		$e = $db->query(
			"SELECT CASE WHEN (g.z_score < sum(a.score)-g.z_score AND p.type='z') THEN '1' ELSE '0' END win
			FROM hz_players p, hz_games g, hz_aims a
			WHERE g.id=p.game AND a.map=g.map AND g.state='finished' AND p.user=? GROUP BY g.id, p.type HAVING win='1'",
			__FILE__, __LINE__, 'SELECT User Total Wins', [$usr['user']]
		);
		$usr['iwin'] = $db->num($e);
		$usr['iloose'] = $usr['igames'] - $usr['iwin'];
	}else{
		$usr['iwin'] = 0;
		$usr['iloose'] = 0;
	}

	$stats[] = $usr;
}

for ($i=0; $i<sizeof($stats); $i++) {
	foreach ($stats[$i] as $key=>$val) {
		if (is_numeric($val) && $val == 0) $stats[$i][$key] = "-";
	}
}

$smarty->assign("stats", $stats);
