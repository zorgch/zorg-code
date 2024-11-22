<?php
global $db, $user, $smarty;

if ($user->is_loggedin())
{
	$polls = [];
	$e = $db->query('SELECT * FROM polls WHERE user=? ORDER BY date DESC', __FILE__, __LINE__, 'SELECT polls of User', [$user->id]);
	while ($d = $db->fetch($e)) {
		$polls[] = $d['id'];
	}
	$smarty->assign('polls', $polls);
} else {
	$smarty->assign('error', ['type' => 'info', 'title' => 'Du kannst nur Deine Polls anzeigen, wenn Du eingeloggt bist', 'dismissable' => false]);
	$smarty->display('file:layout/elements/block_error.tpl');
}
