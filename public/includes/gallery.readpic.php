<?php
/**
 * Gallery-Pic holen
 *
 * This script reads a gallery-pic (they aren't in a public directory).
 * It uses the standard session of the User.
 *
 * @package zorg\Gallery
 *
 * @version 3.5
 * @since 1.0 `[z]biko` File & functions added initially
 * @since 2.0 `IneX` Added check for valid GET-Parameters, refactored Caching & HTTP-Headers, added Movie-File output variations
 * @since 3.0 `14.11.2019` `IneX` GV Beschluss 2018: added check if User is logged-in & Vereinsmitglied
 * @since 3.5 `03.01.2024` `IneX` Code hardening and optimizations
 *
 * @param integer $_GET['id'] Passed integer > 0 of an existing Gallery Pic ID
 * @param string $_GET['token'] Passed token to validate for allowed anonymous access (required for Telegram Bot)
 * @param string $_GET['type'] Passed type value, to request either Full Pic or Thumbnail of the Pic
 * @return resource Media resource with correct MIME-Type and HTTP Headers
 */

/**
 * File includes
 * @include config.inc.php Include required global site configurations
 * @include mysql.inc.php MySQL-DB Connection and Functions
 * @include usersystem.inc.php Usersystem Functions and User definitions
 */
require_once __DIR__.'/config.inc.php';
//require_once INCLUDES_DIR.'mysql.inc.php'; --> included in config.inc.php
//include_once INCLUDES_DIR.'usersystem.inc.php'; --> included in config.inc.php

/** Check if passed $_GET['id'] is valid / integer & not empty */
$media_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?? 0; // $_GET['id']
if ($media_id <= 0) {
	// TODO instead of just exit(), output a default image showing "broken" or alike? */
	header('HTTP/1.1 400 Bad Request');
	exit( error_log(sprintf('<%s:%d> Invalid Media-ID was requested: %s', __FILE__, __LINE__, $media_id)) );
}

/** Query image metadata from database */
$query = $db->query('SELECT * FROM gallery_pics WHERE id=?', __FILE__, __LINE__, 'SELECT FROM gallery_pics', [$media_id]);
$media_data = $db->fetch($query);
if (!$media_data) {
	header('HTTP/1.1 400 Bad Request');
	exit( error_log(sprintf('<%s:%d> No Media data could be obtained', __FILE__, __LINE__)) );
}

/**
 * User & Vereinsmitglieder-Check: nur Vereinsmitglieder dürfen Pics sehen
 * - Ausnahme #1: APOD Pic
 * - Ausnahme #2: Telegram-Bot (Daily Pic)
 * @link https://github.com/zorgch/zorg-verein-docs/blob/master/GV/GV%202018/2018-12-23%20zorg%20GV%202018%20Protokoll.md
 */
$token = filter_input(INPUT_GET, 'token', FILTER_DEFAULT, FILTER_REQUIRE_SCALAR) ?? null; // $_GET['token']
$token_validation = md5($_ENV['TELEGRAM_BOT_API']);
$auth_granted = (!empty($token) && $token === $token_validation ? true : null); // Validate Telegram-Bot Auth-Token
zorgDebugger::log()->debug('Auth-Token Validation: %s (required: %s)', [($auth_granted ? $_GET['token'] : 'false'), (!empty($_GET['token']) ? 'yes' : 'no')]);
if ((int)$media_data['album'] === APOD_GALLERY_ID || $auth_granted === true || ($user->is_loggedin() && !empty($user->vereinsmitglied) && $user->vereinsmitglied !== '0'))
{
	/** Check if passed $_GET['type'] is set to "tn" and valid - in all other cases fallback to "_pic" (default) */
	$type = filter_input(INPUT_GET, 'type', FILTER_DEFAULT, FILTER_REQUIRE_SCALAR) ?? null; // $_GET['type']
	$media_type = (empty($type) || $type != 'tn' ? 'pic_' : 'tn_' );

	/** Zensur-Check: zensurierte Pics können nur Members sehen */
	if (!$media_data['zensur'] || ($media_data['zensur'] && $user->typ == USER_MEMBER))
	{
		/** Set MIME-Type for HTTP response of resource */
		if ($media_data['extension'] === '.jpg' || $media_data['extension'] === '.jpeg' || $media_data['extension'] === '.jpe') { $media_mime = 'image/jpeg'; $media_extension = $media_data['extension']; $media_download = false; }
		if ($media_data['extension'] === '.gif') { $media_mime = 'image/gif'; $media_extension = $media_data['extension']; $media_download = false; }
		if ($media_data['extension'] === '.png') { $media_mime = 'image/png'; $media_extension = $media_data['extension']; $media_download = false; }
		if ($media_data['extension'] === '.mov') { $media_mime = 'video/quicktime'; $media_extension = $media_data['extension']; $media_download = false; }
		if ($media_data['extension'] === '.movie') { $media_mime = 'video/x-sgi-movie'; $media_extension = $media_data['extension']; $media_download = false; }
		if ($media_data['extension'] === '.m3u') { $media_mime = 'audio/x-mpegurl'; $media_extension = $media_data['extension']; $media_download = false; }
		if ($media_data['extension'] === '.mp3') { $media_mime = 'audio/mp3'; $media_extension = $media_data['extension']; $media_download = false; }
		if ($media_data['extension'] === '.mp4' || $media_data['extension'] === '.m4v' || $media_data['extension'] === '.m4a') { $media_mime = 'video/mp4'; $media_extension = $media_data['extension']; $media_download = false; }
		if ($media_data['extension'] === '.mpeg' || $media_data['extension'] === '.mpe' || $media_data['extension'] === '.mpg') { $media_mime = 'video/mpeg'; $media_extension = $media_data['extension']; $media_download = false; }
		if ($media_data['extension'] === 'youtube' || $media_data['extension'] === 'vimeo') { $media_mime = 'image/jpeg'; $media_extension = '.jpg'; $media_download = false; }
		if ($media_data['extension'] === 'website') { $media_mime = 'image/png'; $media_extension = '.png'; $media_download = false; }
		if (empty($media_extension)) { exit( error_log(sprintf('<%s:%d> Unknown Media Extension: %s', __FILE__, __LINE__, $media_data['extension'])) ); }

		/** Build path to the Media Item */
		$mediafile_name = $media_type.$media_data['id'].$media_extension;
		$mediafile = GALLERY_DIR . $media_data['album'] . DIRECTORY_SEPARATOR . $mediafile_name;

		/**
		 * Last file modification date HTTP-Headers, must be valid HTTP-Date (in GMT)
		 * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/ETag
		 * @link https://developer.mozilla.org/de/docs/Web/HTTP/Headers/Last-Modified
		 */
		$mediafile_lastmodified_gmt = gmdate('D, d M Y H:i:s T', filemtime($mediafile));
		$mediafile_hash = fileHash($mediafile, true);
		header('ETag: "'.$mediafile_hash.'"');
		header('Last-Modified: ' . $mediafile_lastmodified_gmt);

		/**
		 * Caching directives HTTP-Headers
		 * Falls die Last_Modified & ETag Werte vom Client mit dem vom Server übereinstimmen
		 * Bild nicht senden sondern HTTP 304 Not Modified zurückliefern (wird fuer caching benoetigt)
		 * @link https://developer.mozilla.org/de/docs/Web/HTTP/Headers/Cache-Control
		 * @link https://stackoverflow.com/questions/2000715/answering-http-if-modified-since-and-http-if-none-match-in-php
		 */
		header('Cache-Control: public, max-age=31536000'); // 31536000 seconds = 365 days
		if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) || isset($_SERVER['HTTP_IF_NONE_MATCH']))
		{
			if ($_SERVER['HTTP_IF_MODIFIED_SINCE'] == $mediafile_lastmodified_gmt || str_replace('"', '', stripslashes($_SERVER['HTTP_IF_NONE_MATCH'])) == $mediafile_hash)
			{
				/** File is chached & has not changed - so exit() */
				header('HTTP/1.1 304 Not Modified');
				exit();
			}
		}
		/* Das hier deaktiviert das Browsercaching des $mediafile
			header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
			header('Pragma: no-cache');
			header('Cache-Control: no-store, no-cache, max-age=0, must-revalidate');
		*/

		/** If not cached or changed: Media content HTTP-Headers */
		header('Content-Type: '.$media_mime);
		header('Content-Length: ' . filesize($mediafile)); ;

		/** Tell browser whether to display file (e.g. images) or download it (e.g. ZIP) */
		header('Content-Disposition: ' . ($media_download ? 'attachment; filename="'.basename($mediafile).'"' : 'inline' ) );

		/** If not cached or changed: return $mediafile */
		readfile($mediafile);

	/** Zensurmeldung für non-Members */
	} else {
		header('HTTP/1.1 451 Unavailable For Legal Reasons');
		exit( error_log(sprintf('<%s:%d> Not allowed access to censored Pic: %d', __FILE__, __LINE__, $media_id)) );
	}

/** Access denied */
} else {
	header('HTTP/1.1 403 Forbidden');
	exit( error_log(sprintf('<%s:%d> Access denied to Pic: %d', __FILE__, __LINE__, $media_id)) );
}
