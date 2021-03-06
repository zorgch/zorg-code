<?php
/**
 * Fix Comment Threads Rights.
 *
 * @package zorg\Forum\Utils
 */
require_once dirname(__FILE__).'/../includes/config.inc.php';
require_once INCLUDES_DIR.'mysql.inc.php';
require_once INCLUDES_DIR.'usersystem.inc.php';
require_once INCLUDES_DIR.'forum.inc.php';

if ($user->typ >= USER_SPECIAL || $user->id == 7)
{
	// gallery
	/*
	$e = $db->query("SELECT * FROM gallery_pics WHERE zensur='1'", __FILE__, __LINE__);
	echo "setting gallery rights <br />";
	$db->query("UPDATE comments_threads SET rights=0 WHERE board='i'", __FILE__, __LINE__);
	while ($d = $db->fetch($e)) {
		Thread::setRights('i', $d['id'], 2);
		echo "pic $d[id] <br>";
		flush();
	}
	
	// templates
	$e = $db->query("SELECT * FROM templates WHERE read_rights > 0", __FILE__, __LINE__);
	echo "setting tpl rights <br />";
	$db->query("UPDATE comments_threads SET rights=0 WHERE board='t'", __FILE__, __LINE__);
	while ($d = $db->fetch($e)) {
		Thread::setRights('t', $d['id'], $d['read_rights']);
		echo "tpl $d[id] <br />";
		flush();
	}
	*/
	
	
	// hunting z
	echo "setting hz rights <br />";
	$ge = $db->query("SELECT * FROM hz_games WHERE state='running'", __FILE__, __LINE__);
	while ($g = $db->fetch($ge)) {
		$pe = $db->query("SELECT * FROM hz_players WHERE game=$g[id] AND type!='z'", __FILE__, __LINE__);
		$pea = array();
		while ($p = $db->fetch($pe)) $pea[] = $p['user'];
		echo "hz $g[id] <br />";
		flush();
		Thread::setRights('h', $g['id'], $pea);
	}
	
}
