<?php
/**
 * Returns a user-file
 *
 * Injects into the requests to /data/files/[user-id]/ and returns the file
 * ONLY IF it is also present in the DB user table "files"!
 *
 * @author ?
 * @author IneX
 * @since ?
 * @version 1.2
 * 
 * @link http://zorg.ch/files/1/Bild1.png
 * @param integer	$_GET['user']	user-id from db
 * @param string	$_GET['file']	filename
 * @param integer	$_GET['id']		file-id from db
 * @return file
 */
/**
 * File includes
 */
require_once( __DIR__ .'/includes/main.inc.php');

// Check for user-id and file-name in URL-Params
if ($_GET['user'] && $_GET['file']) {
	if (is_numeric($_GET['user'])) {
		$e = $db->query('SELECT * FROM files WHERE user=' . $_GET['user'] . ' AND name="' . addslashes($_GET['file']) .'"', __FILE__, __LINE__);
		$d = $db->fetch($e);
	}
}else{	// Else check for file-id in URL-Params
	if (is_numeric($_GET['id'])) {
		$e = $db->query('SELECT * FROM files WHERE id=' . $_GET['id'], __FILE__, __LINE__);
		$d = $db->fetch($e);
	}
}

// Only if the DB-Query returned a result...
if ($d) {
	$lastmod = filemtime( FILES_DIR . $d['user'] . DIRECTORY_SEPARATOR . $d['name']);

	header('Content-Type: ' . $d['mime']);
	header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $lastmod) . ' GMT');


	/*
	   header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");

	   header("Pragma: no-cache");
	   header("Cache-Control: no-store, no-cache, max-age=0, must-revalidate");
	   */

	readfile( FILES_DIR . $d['user'] . DIRECTORY_SEPARATOR . $d['name']);
}else{ // Else return a 404-not found error
	header('HTTP/1.0 404 Not Found'); // set HTTP response header
	echo t('error-file-notfound');
	die; // Make sure to quit, due to error
}
