<?php
global $db, $user, $smarty;

$usr_e = $db->query(
	"SELECT d.*, concat(u.clan_tag, u.username) username, count(g.id) games, count(if(p.type='z', '1', NULL)) zgames
	FROM hz_players p, hz_games g, user u, hz_dwz d
	WHERE g.id=p.game AND g.state='finished' AND u.id=p.user AND d.user=p.user
	GROUP BY p.user
	ORDER BY d.rank",
	__FILE__, __LINE__
);
$stats = array();
while ($usr = $db->fetch($usr_e)) {
	$usr['userparam'] = "user=$usr[user]";
	
	$e = $db->query(
		"SELECT if(g.z_score > sum(a.score)-g.z_score && p.type='z' || g.z_score < sum(a.score)-g.z_score && p.type!='z', '1', '0') win
		FROM hz_players p, hz_games g, hz_aims a
		WHERE g.id=p.game AND a.map=g.map AND g.state='finished' AND p.user=$usr[user] 
		GROUP BY g.id
		HAVING win='1'",
		__FILE__, __LINE__
	);
	$usr['win'] = mysql_num_rows($e);
	$usr['loose'] = $usr['games'] - $usr['win'];
	
	
	if ($usr['zgames'] != 0) {
		$e = $db->query(
			"SELECT if(g.z_score > sum(a.score)-g.z_score && p.type='z', '1', '0') win
			FROM hz_players p, hz_games g, hz_aims a
			WHERE g.id=p.game AND a.map=g.map AND g.state='finished' AND p.user=$usr[user] 
			GROUP BY g.id
			HAVING win='1'",
			__FILE__, __LINE__
		);
		$usr['zwin'] = mysql_num_rows($e);
		$usr['zloose'] = $usr['zgames'] - $usr['zwin'];
	}else{
		$usr['zwin'] = 0;
		$usr['zloose'] = 0;
	}
	
	
	$usr['igames'] = $usr['games'] - $usr['zgames'];
	if ($usr['igames'] != 0) {
		$e = $db->query(
			"SELECT if(g.z_score < sum(a.score)-g.z_score && p.type!='z', '1', '0') win
			FROM hz_players p, hz_games g, hz_aims a
			WHERE g.id=p.game AND a.map=g.map AND g.state='finished' AND p.user=$usr[user] 
			GROUP BY g.id
			HAVING win='1'",
			__FILE__, __LINE__
		);
		$usr['iwin'] = mysql_num_rows($e);
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
