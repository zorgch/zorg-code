<?php
global $db, $user, $smarty;

$polls = [];
$e = $db->query('SELECT * FROM polls ORDER BY date DESC', __FILE__, __LINE__, 'SELECT * FROM polls');
while ($d = $db->fetch($e)) {
	$polls[] = $d['id'];
}
$smarty->assign('polls', $polls);
