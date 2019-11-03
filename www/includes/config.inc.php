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
 * @const	DEVELOPMENT				Contains either 'true' or 'false' (boolean) - Default: false
 * @include	development.config.php	If DEVELOPMENT, load a corresponding config file containing DEV-specific settings. Was already checked to exist at define('DEVELOPMENT', true/false)
 */
define('DEVELOPMENT', ( (isset($_SERVER['environment']) && $_SERVER['environment'] === 'development') || file_exists( __DIR__ .'/development.config.php') ? true : false ), false);
if (DEVELOPMENT) include_once( __DIR__ . '/development.config.php');

/**
 * Define preferred Protocol that zorg.ch is running on
 * @const SITE_PROTOCOL https or http, required for building links like http(s)://... - Default: true
 * @link https://stackoverflow.com/questions/1175096/how-to-find-out-if-youre-using-https-without-serverhttps
 */
$isSecure = true;
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
    $isSecure = true;
}
elseif (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' || !empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on') {
    $isSecure = true;
}
if (!defined('SITE_PROTOCOL')) define('SITE_PROTOCOL', ($isSecure ? 'https' : 'http'), true);

/**
 * Define preferred Hostname where zorg.ch is accessible on
 * @const SITE_HOSTNAME e.g. zorg.ch WITHOUT trailing slash! (no ".../") - Default: zorg.ch
 */
if (empty($_SERVER['SERVER_NAME'])) $_SERVER['SERVER_NAME'] = 'zorg.ch';
if (!defined('SITE_HOSTNAME')) define('SITE_HOSTNAME', $_SERVER['SERVER_NAME'], true);

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
 * @const PAGETITLE_SUFFIX General suffix for <title>...[suffix]</title> on every page.
 */
if (!defined('PAGETITLE_SUFFIX')) define('PAGETITLE_SUFFIX', ' - ' . SITE_HOSTNAME, true);

/**
 * Define global Contact points, such as e-mail addresses (From:)
 * @const ZORG_EMAIL Sets valid sender e-mailadress such as info@zooomclan.org
 * @const ZORG_ADMIN_EMAIL Don't edit! This grabs the Admin E-Mail from the apache2 config
 * @const ZORG_VEREIN_EMAIL Zorg Verein E-Mail address
 * @const VORSTAND_USER User-ID of the Zorg Verein Vorstand-User
 * @const BARBARA_HARRIS User-ID of [z]Barbara Harris
 * @const ROSENVERKAEUFER User-ID des Rosenverkäufer's (für Peter-Spiele)
 * @const THE_ARCHITECT User-ID des [z]architect
 * @const TWITTER_NAME A Twitter-profile username which can be linked, e.g. ZorgCH (no "@")
 * @const FACEBOOK_APPID A Facebook App-ID which can be linked, see developers.facebook.com/apps/
 * @const FACEBOOK_PAGENAME Facebook page name (as in the group url) of the Zorg Facebook group
 * @const TELEGRAM_CHATLINK Telegram Messenger Group-Chat link to join the Zorg Community group
 * @const GIT_REPOSITORY zorg Code Git-Repository base URL
 */
if (!defined('ZORG_EMAIL')) define('ZORG_EMAIL', 'info@'.SITE_HOSTNAME, true);
if (!defined('ZORG_ADMIN_EMAIL')) define('ZORG_ADMIN_EMAIL', $_SERVER['SERVER_ADMIN'], true);
if (!defined('ZORG_VEREIN_EMAIL')) define('ZORG_VEREIN_EMAIL', 'zorg-vorstand@googlegroups.com', true);
if (!defined('VORSTAND_USER')) define('VORSTAND_USER', 451);
if (!defined('BARBARA_HARRIS')) define('BARBARA_HARRIS', 59);
if (!defined('ROSENVERKAEUFER')) define('ROSENVERKAEUFER', 439);
if (!defined('THE_ARCHITECT')) define('THE_ARCHITECT', 582);
if (!defined('TWITTER_NAME')) define('TWITTER_NAME', 'ZorgCH', true);
if (!defined('FACEBOOK_APPID')) define('FACEBOOK_APPID', '110932998937967', true);
if (!defined('FACEBOOK_PAGENAME')) define('FACEBOOK_PAGENAME', 'zorgch', true);
if (!defined('TELEGRAM_CHATLINK')) define('TELEGRAM_CHATLINK', 'https://t.me/joinchat/AbPXbRIhBf3PSG0ujGzY4g', true);
if (!defined('GIT_REPOSITORY')) define('GIT_REPOSITORY', 'https://github.com/zorgch/zorg-code/commit/');

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
if (!defined('ZORG_SESSION_ID')) define('ZORG_SESSION_ID', 'z');
if (!defined('ZORG_COOKIE_SESSION')) define('ZORG_COOKIE_SESSION', ZORG_SESSION_ID);
if (!defined('ZORG_COOKIE_USERID')) define('ZORG_COOKIE_USERID', 'autologin_id');
if (!defined('ZORG_COOKIE_USERPW')) define('ZORG_COOKIE_USERPW', 'autologin_pw');
if (!defined('USER_ALLE')) define('USER_ALLE', 0);
if (!defined('USER_USER')) define('USER_USER', 1);
if (!defined('USER_MEMBER')) define('USER_MEMBER', 2);
if (!defined('USER_SPECIAL')) define('USER_SPECIAL', 3);
//define('USER_EINGELOGGT', 0);
//define('USER_NICHTEINGELOGGT', 2);
define('USER_NICHTEINGELOGGT', false);
//define('USER_ALLE', 3);
if (!defined('USER_IMGEXTENSION')) define('USER_IMGEXTENSION',  '.jpg');
if (!defined('USER_IMGPATH')) define('USER_IMGPATH',  SITE_ROOT.'/../data/userimages/');
if (!defined('USER_IMGPATH_PUBLIC')) define('USER_IMGPATH_PUBLIC', '/data/userimages/');
if (!defined('USER_IMGPATH_ARCHIVE')) define('USER_IMGPATH_ARCHIVE',  SITE_ROOT.'/../data/userimages/archiv/');
if (!defined('USER_IMGSIZE_LARGE')) define('USER_IMGSIZE_LARGE', 500);
if (!defined('USER_IMGSIZE_SMALL')) define('USER_IMGSIZE_SMALL', 150);
if (!defined('USER_IMGPATH_DEFAULT')) define('USER_IMGPATH_DEFAULT', 'none.jpg');
if (!defined('USER_TIMEOUT')) define('USER_TIMEOUT', 200);
if (!defined('USER_OLD_AFTER')) define('USER_OLD_AFTER', 60*60*24*30*12*3); // 3 Jahre | 3 Monate: 60*60*24*30*3
if (!defined('DEFAULT_MAXDEPTH')) define('DEFAULT_MAXDEPTH', 10);
//if (!defined('FILES_DIR')) define('FILES_DIR', rtrim($_SERVER['DOCUMENT_ROOT'],'/\\').'/../data/files/'); // /data/files/ directory outside the WWW-Root

/**
 * Define Smarty constants
 */
if (!defined('SMARTY_DIR')) define('SMARTY_DIR', SITE_ROOT.'/smartylib/');
if (!defined('SMARTY_TRUSTED_DIRS')) define('SMARTY_TRUSTED_DIRS', SITE_ROOT.'/scripts/'); // TODO PHP7.x: make this an array
if (!defined('SMARTY_TEMPLATES_HTML')) define('SMARTY_TEMPLATES_HTML',  SITE_ROOT.'/templates/');
if (!defined('SMARTY_CACHE')) define('SMARTY_CACHE',  SITE_ROOT.'/../data/smartylib/cache/');
if (!defined('SMARTY_COMPILE')) define('SMARTY_COMPILE', SITE_ROOT.'/../data/smartylib/templates_c/');
if (!defined('SMARTY_PACKAGES_DIR')) define('SMARTY_PACKAGES_DIR', SITE_ROOT.'/packages/');
if (!defined('SMARTY_PACKAGES_EXTENSION')) define('SMARTY_PACKAGES_EXTENSION', '.php');

/**
 * Define and include various Placeholder-Strings related constants and files
 * @include strings.inc.php
 */
include_once( __DIR__ .'/strings.inc.php');

/**
 * Define and include various Notification System-related constants and files
 * @include notifications.inc.php
 */
include_once( __DIR__ .'/notifications.inc.php');

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

/**
 * Define and include various Telegram-Bot/Telegram-Messaging related constants and files
 * @include telegrambot.inc.php Required to send Telegram-Notifications
 */
include_once( __DIR__ .'/telegrambot.inc.php');

/**
 * Define various Addle related constants
 * @const MAX_ADDLE_GAMES	Anzahl der erlaubten gleichzeitig offenen Addle-Spiele eines Users
 * @const MAX_ADDLE_GAMES	Anzahl der erlaubten gleichzeitig offenen Addle-Spiele eines Users
 * @const MAX_ADDLE_GAMES	Anzahl der erlaubten gleichzeitig offenen Addle-Spiele eines Users
 */
define('MAX_ADDLE_GAMES', 1);
define('ADDLE_BASE_POINTS', 1600);
define('ADDLE_MAX_POINTS_TRANSFERABLE', 32);

/**
 * Define and include various Layout related constants and files
 * @include colors.inc.php Required to have various color vars accessible
 */
include_once( __DIR__ .'/colors.inc.php');
