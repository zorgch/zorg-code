<?php
/**
 * Set locale to German, Switzerland & Timezone to Europe/Zurich
 */
setlocale(LC_TIME, 'de_CH');
date_default_timezone_set('Europe/Zurich');

/**
 * Environment-specific configurations: can be set in the Apache config using
 *    SetEnv environment 'development'
 *
 * @const DEVELOPMENT Contains either 'true' or 'false' (boolean) 
 */
define('DEVELOPMENT', ( isset($_SERVER['environment']) && $_SERVER['environment'] == 'development' ? true : false ), true);

/**
* Define preferred Protocol that zorg.ch is running on
* @const SITE_PROTOCOL https or http, required for building links like http(s)://...
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
 * @const INCLUDES_DIR File includes directory
 * @const IMAGES_DIR Images directory
 * @const FILES_DIR Files directory (local server path)
 * @const GALLERY_DIR Gallery directory (local server path)
 * @const ACTIONS_DIR Actions directory
 * @const SCRIPTS_DIR Scripts directory
 * @const UTIL_DIR Utilities directory
 * @const JS_DIR JavaScripts directory
 * @const CSS_DIR CSS directory
 */
if (!defined('INCLUDES_DIR')) define('INCLUDES_DIR', '/includes/', true);
if (!defined('IMAGES_DIR')) define('IMAGES_DIR', '/images/', true);
if (!defined('FILES_DIR')) define('FILES_DIR', SITE_ROOT . '/../data/files/', true);
if (!defined('GALLERY_DIR')) define('GALLERY_DIR', SITE_ROOT . '/../data/gallery/', true);
if (!defined('ACTIONS_DIR')) define('ACTIONS_DIR', '/actions/', true);
if (!defined('SCRIPTS_DIR')) define('SCRIPTS_DIR', '/scripts/', true);
if (!defined('UTIL_DIR')) define('UTIL_DIR', '/util/', true);
if (!defined('JS_DIR')) define('JS_DIR', '/js/', true);
if (!defined('CSS_DIR')) define('CSS_DIR', '/css/', true);

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
