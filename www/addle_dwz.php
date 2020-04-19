<?php
/**
 * Addle force DWZ update
 * DWZ Punkte aller Spieler über alle Addle Games force-updaten
 *
 * @author [z]biko
 * @version 1.0
 * @since 1.0 <biko> File added
 * @since 1.1 <inex> 11.09.2019 code updates
 *
 * @package zorg\Games\Addle
 * @see _update_dwz()
 */
/**
 * File Includes
 */
require_once dirname(__FILE__).'/includes/config.inc.php';
require_once INCLUDES_DIR.'usersystem.inc.php';
require_once INCLUDES_DIR.'addle.inc.php';

/** Nur wenn User [z]biko oder User mit Super-Admin Rechten */
if ($user->id == 7 || $user->typ >= USER_SPECIAL)
{
	echo '*** start processing ***<br/>';
	$db->query('TRUNCATE TABLE addle_dwz', __FILE__, __LINE__, 'TRUNCATE Query');
	$e = $db->query('SELECT * FROM addle WHERE finish="1" ORDER BY date ASC', __FILE__, __LINE__, 'SELECT Query');
	while ($d = $db->fetch($e))
	{
		_update_dwz($d['id']);
		echo '=';
		flush();
	}
	echo '<br>*** done ***';
}

/** Permission denied */
else {
	http_response_code(403); // Set response code 403 (access denied) and exit.
	echo "Access denied";
}
