<?php
/**
 * Set locale to German, Switzerland & Timezone to Europe/Zurich
 */
setlocale(LC_TIME, 'de_CH');
date_default_timezone_set('Europe/Zurich');

/**
 * Start execution time measurement
 */
$parsetime_start = microtime(true);
$sqltracker_numqueries = 0;

/**
 * Environment-specific configurations: can be set in the Apache config using
 *    SetEnv environment 'development'
 *
 * @const DEVELOPMENT Contains either 'true' or 'false' (boolean) - default: false
 */
define('DEVELOPMENT', ( isset($_SERVER['environment']) && $_SERVER['environment'] == 'development' ? true : false ), false);

/**
* Define preferred Protocol that zorg.ch is running on
* @const SITE_PROTOCOL https or http, required for building links like http(s)://... - default: true
* @link https://stackoverflow.com/questions/1175096/how-to-find-out-if-youre-using-https-without-serverhttps
*/
$isSecure = false;
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
    $isSecure = true;
}
elseif (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' || !empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on') {
    $isSecure = true;
}
if (!defined('SITE_PROTOCOL')) define('SITE_PROTOCOL', ($isSecure ? 'https' : 'http'), true);

/**
* Define preferred Hostname where zorg.ch is accessible on
* @const SITE_HOSTNAME e.g. zorg.ch WITHOUT trailing slash! (no ".../")
*/
if (!defined('SITE_HOSTNAME') && isset($_SERVER['SERVER_NAME']))
{
	define('SITE_HOSTNAME', $_SERVER['SERVER_NAME'], true);
} else {
	$_SERVER['SERVER_NAME'] = 'zorg.ch';
}

/**
* Define preferred base URL where zorg.ch is accessible through
* @const SITE_URL Don't edit! Is generated using SITE_PROTOCOL and SITE_HOSTNAME
*/
if (!defined('SITE_URL')) define('SITE_URL', SITE_PROTOCOL . '://' . SITE_HOSTNAME, true);

/**
* Set a constant for the Site's Web Root
* @const SITE_ROOT Set the Site Root WITHOUT a trailing slash "/"
*/
if (!defined('SITE_ROOT')) define('SITE_ROOT', rtrim( __DIR__ ,'/\\').'/..', true);

/**
* Set a constant for the custom Error Log path
* @see errlog.inc.php, zorgErrorHandler(), user_error(), trigger_error()
* @const ERRORLOG_FILETYPE sets the file extension used for the error log file
* @const ERRORLOG_DIR sets the directory for logging the custom user_errors
* @const ERRORLOG_FILEPATH sets the directory & file path for logging the custom user_errors to
* @include errlog.inc.php 	Errorlogging Class
*/
if (!defined('ERRORLOG_FILETYPE')) define('ERRORLOG_FILETYPE', '.log', true);
if (!defined('ERRORLOG_DIR')) define('ERRORLOG_DIR', SITE_ROOT . '/../data/errlog/', true);
if (!defined('ERRORLOG_FILEPATH')) define('ERRORLOG_FILE', ERRORLOG_DIR . date('Y-m-d') . ERRORLOG_FILETYPE, true);
require_once( __DIR__ .'/errlog.inc.php');

/**
 * If DEVELOPMENT, load a corresponding config file
 * @include	development.config.php File containing DEV-specific settings
 */
if (DEVELOPMENT) include_once( __DIR__ . '/development.config.php');

/**
 * @const PAGETITLE_SUFFIX General suffix for <title>...[suffix]</title> on every page.
 */
if (!defined('PAGETITLE_SUFFIX')) define('PAGETITLE_SUFFIX', ' - ' . SITE_HOSTNAME, true);

/**
* Define global Contact points, such as e-mail addresses (From:)
* @const ZORG_EMAIL Sets valid sender e-mailadress such as info@zooomclan.org
* @const ZORG_ADMIN_EMAIL Don't edit! This grabs the Admin E-Mail from the apache2 config
* @const ZORG_VEREIN_EMAIL Zorg Verein E-Mail address
* @const BARBARA_HARRIS User-ID of [z]Barbara Harris
* @const VORSTAND_USER User-ID of the Zorg Verein Vorstand-User
* @const TWITTER_NAME A Twitter-profile username which can be linked, e.g. ZorgCH (no "@")
* @const FACEBOOK_APPID A Facebook App-ID which can be linked, see developers.facebook.com/apps/
* @const FACEBOOK_PAGENAME Facebook page name (as in the group url) of the Zorg Facebook group
* @const TELEGRAM_CHATLINK Telegram Messenger Group-Chat link to join the Zorg Community group
*/
if (!defined('ZORG_EMAIL')) define('ZORG_EMAIL', 'info@'.SITE_HOSTNAME, true);
if (!defined('ZORG_ADMIN_EMAIL')) define('ZORG_ADMIN_EMAIL', $_SERVER['SERVER_ADMIN'], true);
if (!defined('ZORG_VEREIN_EMAIL')) define('ZORG_VEREIN_EMAIL', 'zorg-vorstand@googlegroups.com', true);
if (!defined('BARBARA_HARRIS')) define('BARBARA_HARRIS', 59);
if (!defined('VORSTAND_USER')) define('VORSTAND_USER', 451);
if (!defined('TWITTER_NAME')) define('TWITTER_NAME', 'ZorgCH', true);
if (!defined('FACEBOOK_APPID')) define('FACEBOOK_APPID', '110932998937967', true);
if (!defined('FACEBOOK_PAGENAME')) define('FACEBOOK_PAGENAME', 'zorgch', true);
if (!defined('TELEGRAM_CHATLINK')) define('TELEGRAM_CHATLINK', 'https://t.me/joinchat/AbPXbRIhBf3PSG0ujGzY4g', true);

/**
 * Define paths to directories where HTML web resources will be referenced from
 * @const INCLUDES_DIR PHP-Script includes directory for using in PHP-Scripts
 * @const IMAGES_DIR Images directory for Frontend-Resources (don't use in PHP Scripts! Refer to PHP_IMAGES_DIR)
 * @const PHP_IMAGES_DIR Images directory for including Images in PHP-Scripts
 * @const FILES_DIR Files directory (local server path)
 * @const GALLERY_DIR Gallery directory (local server path)
 * @const ACTIONS_DIR Actions directory for Frontend-Resources 
 * @const SCRIPTS_DIR Scripts directory for Frontend-Resources 
 * @const UTIL_DIR Utilities directory for Frontend-Resources 
 * @const JS_DIR JavaScripts directory for Frontend-Resources 
 * @const CSS_DIR CSS directory for Frontend-Resources 
 */
if (!defined('INCLUDES_DIR')) define('INCLUDES_DIR', SITE_ROOT . '/includes/', true);
if (!defined('IMAGES_DIR')) define('IMAGES_DIR', '/images/', true);
if (!defined('PHP_IMAGES_DIR')) define('PHP_IMAGES_DIR', SITE_ROOT . '/images/', true);
if (!defined('FILES_DIR')) define('FILES_DIR', SITE_ROOT . '/../data/files/', true);
if (!defined('GALLERY_DIR')) define('GALLERY_DIR', SITE_ROOT . '/../data/gallery/', true);
if (!defined('ACTIONS_DIR')) define('ACTIONS_DIR', '/actions/', true);
if (!defined('SCRIPTS_DIR')) define('SCRIPTS_DIR', '/scripts/', true);
if (!defined('UTIL_DIR')) define('UTIL_DIR', '/util/', true);
if (!defined('JS_DIR')) define('JS_DIR', '/js/', true);
if (!defined('CSS_DIR')) define('CSS_DIR', '/css/', true);

/**
 * Define User & Usersystem constants
 * User Typen:
 *	1 = Normaler User ##################### 0 isch nöd so cool wil wenns nöd gsetzt isch chunt jo au 0
 *	2 = [z]member und schöne
 *	0 = nicht eingeloggt ##################### Aber Weber: wenn typ = 2, gits $user jo gar nöd?! -> doch s'usersystem isch jo immer verfügbar
 *	=> verfügbar über $user->typ
 *
 * @const USER_ALLE		Wert für nicht eingeloggte User
 * @const USER_USER		Wert für normale eingeloggte User
 * @const USER_MEMBER 	Wert für [z]member & schöne
 * @const USER_SPECIAL	Wert für Admins & Coder
 * @const USER_IMGEXTENSION	File-Extension/Format von Userpics
 * @const USER_IMGPATH	Interner PHP-Pfad zum Userpics Ordner
 * @const USER_IMGPATH_PUBLIC	Externer Pfad zu den Userpics
 * @const USER_IMGSIZE_LARGE	Grösse in Pixel der normalen Userpics
 * @const USER_IMGSIZE_SMALL	Grösse in Pixel der Userpic-Thumbnails
 * @const USER_IMGPATH_DEFAULT	Externer Pfad zum Standard-Userpic
 * @const USER_TIMEOUT	Session Timeout für eingeloggte User
 * @const USER_OLD_AFTER	Zeit bis ein User als "alt" gilt -> 3 Monate
 * @const DEFAULT_MAXDEPTH	Standard Setting für die Anzeigetiefe von Comments in Forum-Threads
 */
define('USER_ALLE', 0);
define('USER_USER', 1);
define('USER_MEMBER', 2);
define('USER_SPECIAL', 3);
//define('USER_EINGELOGGT', 0);
//define('USER_MEMBER', 1);
//define('USER_NICHTEINGELOGGT', 2);
//define('USER_ALLE', 3);
define('USER_IMGEXTENSION',  '.jpg');
define('USER_IMGPATH',  __DIR__ .'/../../data/userimages/');
define('USER_IMGPATH_PUBLIC', '/data/userimages/');
define('USER_IMGSIZE_LARGE', 427);
define('USER_IMGSIZE_SMALL', 150);
define('USER_IMGPATH_DEFAULT', 'none.jpg');
define('USER_TIMEOUT', 200);
define('USER_OLD_AFTER', 60*60*24*30*3); // 3 Monate
define('DEFAULT_MAXDEPTH', 10);
//define('AUSGESPERRT_BIS', 'ausgesperrt_bis');
//if (!defined('FILES_DIR')) define('FILES_DIR', rtrim($_SERVER['DOCUMENT_ROOT'],'/\\').'/../data/files/'); // /data/files/ directory outside the WWW-Root

/**
 * Define Smarty constants
 */
define('SMARTY_DIR', SITE_ROOT.'/smartylib/');
define('SMARTY_TEMPLATES_HTML',  SITE_ROOT.'/templates/');
define('SMARTY_CACHE',  SITE_ROOT.'/../data/smartylib/cache/');
define('SMARTY_COMPILE', SITE_ROOT.'/../data/smartylib/templates_c/');

/**
* Grab the NASA API Key
* @include nasaapis_key.inc.php Include a String containing a valid NASA API Key
* @const NASA_API_KEY A constant holding the NASA API Key, can be used optionally (!) for requests to NASA's APIs such as the APOD
*/
if (!defined('NASA_API_KEY')) define('NASA_API_KEY', include_once( (file_exists( __DIR__ .'/nasaapis_key.inc.local.php') ? 'nasaapis_key.inc.local.php' : 'nasaapis_key.inc.php') ), true);
if (DEVELOPMENT && !empty(NASA_API_KEY)) error_log(sprintf('[DEBUG] <%s:%d> NASA_API_KEY: found', __FILE__, __LINE__));

/**
 * Define various APOD related constants
 * @const APOD_GALLERY_ID ID der APOD-Gallery in der Datenbank
 * @const APOD_TEMP_IMGPATH Pfad zum initialen Download des aktuellen APOD-Bildes
 * @const APOD_SOURCE Source-URL für die APOD-Bilder Archiv-Links
 * @const APOD_API NASA APOD API-URL von wo das tägliche APOD-Bild mit dem NASA_API_KEY geholt werden kann, mittels ?apod_date=yyyy-mm-dd kann ein spezifisches APOD geholt werden
 */
if (!defined('APOD_GALLERY_ID')) define('APOD_GALLERY_ID', 41);
if (!defined('APOD_TEMP_IMGPATH')) define('APOD_TEMP_IMGPATH', __DIR__.'/../../data/temp/');
if (!defined('APOD_SOURCE')) define('APOD_SOURCE', 'https://apod.nasa.gov/apod/');
if (!defined('APOD_API')) define('APOD_API', 'https://api.nasa.gov/planetary/apod?api_key=' . NASA_API_KEY);
