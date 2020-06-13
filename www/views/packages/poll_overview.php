<?php
global $db, $user, $smarty;

$e = $db->query('SELECT * FROM polls ORDER BY date DESC', __FILE__, __LINE__);
$polls = array();
while ($d = $db->fetch($e)) {
	$polls[] = $d['id'];
}
$smarty->assign('polls', $polls);
