<?php
/**
 * Hunting z force DWZ update
 * DWZ Punkte aller Spieler über alle Hz Games force-updaten
 *
 * @author [z]biko
 * @version 1.1
 * @since 1.0 <biko> File added
 * @since 1.1 <inex> 11.09.2019 code updates
 *
 * @package zorg\Games\HuntingZ
 * @see _update_hz_dwz()
 */
/**
 * File includes
 */
require_once(__DIR__.'/includes/config.inc.php');
require_once(__DIR__.'/includes/usersystem.inc.php');
require_once(__DIR__.'/includes/hz_game.inc.php');

/** Nur wenn User [z]biko oder User mit Super-Admin Rechten */
if ($user->id == 7 || $user->typ >= USER_SPECIAL)
{
	echo '*** start processing ***<br/>';
	$db->query('TRUNCATE TABLE hz_dwz', __FILE__, __LINE__, 'TRUNCATE Query');
	$e = $db->query('SELECT * FROM hz_games WHERE state="finished" ORDER BY turndate ASC', __FILE__, __LINE__, 'SELECT Query');
	while ($d = $db->fetch($e))
	{
		echo '=';
		flush();
		_update_hz_dwz($d['id']);
	}
	echo '<br>*** done ***';
}

/** Permission denied */
else {
	echo 'access denied';
}
