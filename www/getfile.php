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
 * @FIXME Refactore code according to gallery.readpic.php version 2.0
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
require_once( __DIR__ .'/includes/config.inc.php');
require_once( __DIR__ .'/includes/mysql.inc.php');

/** Look for & validate user-id and file-name in URL-Params */
if ($_GET['user'] && $_GET['file'])
{
	if (is_numeric($_GET['user']))
	{
		$e = $db->query('SELECT * FROM files WHERE user=' . $_GET['user'] . ' AND name="' . addslashes($_GET['file']) .'"', __FILE__, __LINE__);
		$d = $db->fetch($e);
	}

/** ...else check & validate for file-id in URL-Params */
} else {
	if (is_numeric($_GET['id']))
	{
		$e = $db->query('SELECT * FROM files WHERE id=' . $_GET['id'], __FILE__, __LINE__);
		$d = $db->fetch($e);
	}
}

/** Proceed only if the DB-Query returned a result... */
if ($d)
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
	header('HTTP/1.1 404 Not Found'); // set HTTP response header
	echo t('error-file-notfound');
	die; // Make sure to quit, due to error
}
