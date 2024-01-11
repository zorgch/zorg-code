<?php
/**
 * Hunting z Stats Output als XMP-File
 * @package zorg\Games\HuntingZ
 */

/**
 * File includes
 */
require_once __DIR__.'/../includes/config.inc.php';
require_once INCLUDES_DIR.'mysql.inc.php';
require_once INCLUDES_DIR.'util.inc.php';

$ge = $db->query(
	"SELECT g.id, if(sum(a.score)-g.z_score > g.z_score, 'i', 'z') winner, u.username mrz FROM hz_players p, hz_games g, hz_aims a, user u
	WHERE g.id=p.game AND p.type='z' AND g.state='finished' AND a.map=g.map AND u.id=p.user GROUP BY g.id",
	__FILE__, __LINE__, 'SELECT User Score'
);
$user = array();
while ($g = $db->fetch($ge)) {
	$t = $db->fetch($db->query("SELECT * FROM hz_tracks WHERE player='z' AND game=? ORDER BY nr DESC LIMIT 1", __FILE__, __LINE__, 'SELECT User Wins', [$g['id']]));
	if ($g['winner']=='z') $user['wins'][$g['mrz']]++;
	$user['games'][$g['mrz']]++;
	$user['turns'][$g['mrz']] += $t['nr'];
	$user['mrz'][$g['mrz']] = $g['mrz'];
}


foreach ($user['games'] as $key=>$val) {
	$user['wins'][$key] = $user['wins'][$key] / $user['games'][$key] * 100;
	$user['turns'][$key] /= $user['games'][$key];

}

array_multisort($user['turns'], SORT_DESC, SORT_NUMERIC);

print_array($user);


echo '<xmp>';
echo text_width("User", 11).text_width("Siege", 7)."Durchschnittliche Anz. Züge pro Game\n";
echo "==========================================================\n";
foreach ($user['turns'] as $key=>$val) {
	echo text_width($user['mrz'][$key], 11).text_width(round($user['wins'][$key]).'%', 7).round($user['turns'][$key], 2)."\n";
}
echo '</xmp>';
