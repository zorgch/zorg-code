<?php
/**
 * Returns a user-file
 *
 * Injects into the requests to /data/files/[user-id]/ and returns the file
 * ONLY IF it is also present in the DB user table "files"!
 *
 * @author ?
 * @author IneX
 * @since 1.0 file & functions added
 * @since 1.2 Validate $_GET parameters, changed file include
 * @since 1.3 Minor code improvements, added FIXME, updated comments
 * @version 1.3
 *
 * @FIXME Refactor code according to gallery.readpic.php version 2.0
 *
 * @link http://zorg.ch/files/1/Bild1.png
 * @param integer	$_GET['user']	user-id from db
 * @param string	$_GET['file']	filename
 * @param integer	$_GET['id']		file-id from db
 * @return file
 */
/**
 * File Includes
 * @include config.inc.php Include required global site configurations
 * @include mysql.inc.php MySQL-DB Connection and Functions
 */
require_once dirname(__FILE__).'/includes/config.inc.php';
require_once INCLUDES_DIR.'mysql.inc.php';

/** Look for & validate user-id and file-name in URL-Params */
if ((isset($_GET['user']) && !empty($_GET['user']) && is_numeric($_GET['user']) && $_GET['user'] > 0) &&
	(isset($_GET['file']) && !empty($_GET['file'])))
{
	$e = $db->query('SELECT * FROM files WHERE user=? AND name=?',
					__FILE__, __LINE__, 'SELECT files by user', [(int)$_GET['user'], addslashes($_GET['file'])]);
	$d = $db->fetch($e);
}
/** ...else check & validate for file-id in URL-Params */
elseif (isset($_GET['id']) && is_numeric($_GET['id']) && $_GET['id'] > 0)
{
	$e = $db->query('SELECT * FROM files WHERE id=?',
					__FILE__, __LINE__, 'SELECT files by id', [(int)$_GET['id']]);
	$d = $db->fetch($e);
}
/** ...finally: it's an invalid requests, it seems */
else {
	http_response_code(400); // Set response code 400 (bad request) and exit.
	exit('Invalid or missing GET-Parameter');
}

/** Proceed only if the DB-Query returned a result... */
if ($d !== false && $db->num($e) > 0)
{
	$lastmod = filemtime( FILES_DIR . $d['user'] . DIRECTORY_SEPARATOR . $d['name']);

	header('Content-Type: ' . $d['mime']);
	header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $lastmod) . ' GMT');

	/*
	   header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");

	   header("Pragma: no-cache");
	   header("Cache-Control: no-store, no-cache, max-age=0, must-revalidate");
	   */

	readfile( FILES_DIR . $d['user'] . DIRECTORY_SEPARATOR . $d['name']);

/** Otherwise return a HTTP 404 "Not found" error & exit */
} else {
	http_response_code(404); // set HTTP response code 404 (Not Found)
	exit((string)t('error-file-notfound')); // Make sure to quit, due to error
}
