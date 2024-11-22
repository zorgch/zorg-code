<?php
require_once __DIR__.'/../includes/config.inc.php';
require_once INCLUDES_DIR.'usersystem.inc.php';

global $smarty, $user;

if ($user->is_loggedin())
{
	$types = ['standard'];
	$types_n = ['Standard'];

	if ($user->typ >= USER_MEMBER) {
		$types[] = 'member';
		$types_n[] = 'Member';
	}

	$smarty->assign('poll_types_v', $types);
	$smarty->assign('poll_types_n', $types_n);
} else {
	$smarty->assign('error', ['type' => 'warn', 'title' => 'Nur eingeloggte User d&uuml;rfen Polls editieren!', 'dismissable' => false]);
	$smarty->display('file:layout/elements/block_error.tpl');
}
