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
 * // TODO Refactor code according to gallery.readpic.php version 2.0
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
require_once __DIR__.'/includes/config.inc.php';
require_once INCLUDES_DIR.'mysql.inc.php';

/** Look for & validate user-id and file-name in URL-Params */
$user = filter_input(INPUT_GET, 'user', FILTER_VALIDATE_INT) ?? null;
$fileid = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?? null;
$filename = filter_input(INPUT_GET, 'file', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? null;

if ((!empty($user) && $user > 0) &&	(!empty($filename)))
{
	$e = $db->query('SELECT * FROM files WHERE user=? AND name=?', __FILE__, __LINE__, 'SELECT files by user', [$user, $filename]);
	$d = $db->fetch($e);
}
/** ...else check & validate for file-id in URL-Params */
elseif (!empty($fileid) && $fileid > 0)
{
	$e = $db->query('SELECT * FROM files WHERE id=?', __FILE__, __LINE__, 'SELECT files by id', [$fileid]);
	$d = $db->fetch($e);
}
/** ...finally: it's an invalid requests, it seems */
else {
	http_response_code(400); // Set response code 400 (bad request) and exit.
	exit('Invalid or missing GET-Parameter');
}

/** Proceed only if the DB-Query returned a result... */
if ($d !== false && $db->num($e) > 0 && !empty($d['name']))
{
	$lastmod = filemtime( FILES_DIR . $d['user'] . DIRECTORY_SEPARATOR . $d['name']);

	if (isset($d['mime'])) header('Content-Type: ' . $d['mime']);
	if (!empty($lastmod)) header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $lastmod) . ' GMT');
	/*
	   header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");

	   header("Pragma: no-cache");
	   header("Cache-Control: no-store, no-cache, max-age=0, must-revalidate");
	*/

	readfile( FILES_DIR . $d['user'] . DIRECTORY_SEPARATOR . $d['name']);

/** Otherwise return a HTTP 404 "Not found" error & exit */
} else {
	http_response_code(404); // set HTTP response code 404 (Not Found)
	exit(t('error-file-notfound')); // Make sure to quit, due to error
}
