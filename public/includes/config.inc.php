<?php
/**
 * Set a constant for the Site's App and Web Root.
 * @const SITE_ROOT Set the Site Root WITHOUT a trailing slash "/". IMPORTANT: relative to the config.inc.php File!
 * @const APP_ROOT Sets the general Application Root WITHOUT a trailing slash "/".
 */
if (!defined('SITE_ROOT')) define('SITE_ROOT', rtrim(__DIR__, '/\\').'/..');
if (!defined('APP_ROOT')) define('APP_ROOT', SITE_ROOT.'/..');

/**
 * Load Environment-specific configurations.
 * Set environment configs inside a ".env"-file in the APP_ROOT level
 * (Optional) Env Vars can also be set in the Apache config using:
 *	 SetEnv environment 'development'
 *
 * @const COMPOSER_AUTOLOAD Composer Autoloader for third-party Vendor libraries
 */
if (!defined('COMPOSER_AUTOLOAD')) define('COMPOSER_AUTOLOAD', APP_ROOT.'/vendor/autoload.php');
if (file_exists(COMPOSER_AUTOLOAD))
{
	require_once COMPOSER_AUTOLOAD;

	/**
	 * Load PHP dotENV library.
	 * Depends on a ".env" file in APP_ROOT configured for the corresponding environment!
	 * Some Environment Variables are REQUIRED - see below list in $dotenv->required().
	 * @link https://github.com/vlucas/phpdotenv
	 */
	try {
		$dotenv = Dotenv\Dotenv::createImmutable(APP_ROOT);
		$dotenv->load();
        /** Variable validations: required variables */
		$dotenv->required(['MYSQL_HOST', 'MYSQL_DATABASE', 'MYSQL_USER'])->notEmpty();
		$dotenv->required(['LOCALE'])->allowedRegexValues('([[:lower:]]{2}_[[:upper:]]{2})');
		$dotenv->required(['TIMEZONE'])->allowedRegexValues('([a-zA-Z0-9]+([\/|+-][a-zA-Z0-9_]+)?(\/[a-zA-Z0-9_]+)?)');
        /** Variable validations: Integers */
        $dotenv->ifPresent('USER_TIMEOUT')->isInteger();
        $dotenv->ifPresent('USER_OLD_AFTER')->isInteger();
        $dotenv->ifPresent('USERIMAGE_SIZE_LARGE')->isInteger();
        $dotenv->ifPresent('USERIMAGE_SIZE_SMALL')->isInteger();
        $dotenv->ifPresent('ADDLE_MAX_GAMES')->isInteger();
        $dotenv->ifPresent('ADDLE_BASE_POINTS')->isInteger();
        $dotenv->ifPresent('ADDLE_MAX_POINTS_TRANSFERABLE')->isInteger();
        $dotenv->ifPresent('APOD_GALLERY_ID')->isInteger();
        $dotenv->ifPresent('CHESS_DWZ_BASE_POINTS')->isInteger();
        $dotenv->ifPresent('CHESS_DWZ_MAX_POINTS_TRANSFERABLE')->isInteger();
        $dotenv->ifPresent('FORUM_DEFAULT_MAXDEPTH')->isInteger();
        $dotenv->ifPresent('FORUM_THREAD_CLEARCACHE_AFTER')->isInteger();
        $dotenv->ifPresent('GO_OFFSET_PIC')->isInteger();
        $dotenv->ifPresent('GO_LINKRADIUS')->isInteger();
        $dotenv->ifPresent('GO_FIELDSIZE')->isInteger();
        $dotenv->ifPresent('GO_LINEWIDTH')->isInteger();
        $dotenv->ifPresent('GO_STARDOTWIDTH')->isInteger();
        $dotenv->ifPresent('GO_STONEBIGWIDTH')->isInteger();
        $dotenv->ifPresent('GO_LASTSTONEWIDTH')->isInteger();
        $dotenv->ifPresent('HZ_MAX_GAMES')->isInteger();
        $dotenv->ifPresent('HZ_TURN_TIME')->isInteger();
        $dotenv->ifPresent('HZ_TURN_COUNT')->isInteger();
        $dotenv->ifPresent('HZ_TURN_ADD_MONEY')->isInteger();
        $dotenv->ifPresent('SESSION_LIFETIME')->isInteger();
        $dotenv->ifPresent('COOKIE_EXPIRATION')->isInteger();
        $dotenv->ifPresent('SMARTY_DEFAULT_TPL_ID')->isInteger();
        $dotenv->ifPresent('VORSTAND_USER')->isInteger();
        $dotenv->ifPresent('BARBARA_HARRIS')->isInteger();
        $dotenv->ifPresent('ROSENVERKAEUFER')->isInteger();
        $dotenv->ifPresent('THE_ARCHITECT')->isInteger();
        $dotenv->ifPresent('ANFICKER_USER_ID')->isInteger();
        $dotenv->ifPresent('ZORG_VEREIN_PLZ')->isInteger();
        /** Variable validations: Booleans */
        $dotenv->ifPresent('TELEGRAM_DISABLE_WEBPAGE_PREVIEW')->isBoolean();
        $dotenv->ifPresent('TELEGRAM_DISABLE_NOTIFICATION')->isBoolean();
        $dotenv->ifPresent('USER_USE_CURRENT_LOGIN')->isBoolean();
        $dotenv->ifPresent('USER_USE_REGISTRATION_CODE')->isBoolean();
        $dotenv->ifPresent('USER_USE_ONLINE_LIST')->isBoolean();
        $dotenv->ifPresent('USERIMAGE_ENABLED')->isBoolean();
        $dotenv->ifPresent('ENABLE_COOKIES')->isBoolean();
        $dotenv->ifPresent('COOKIE_HTTPONLY')->isBoolean();
	} catch (Exception $e) {
		exit(sprintf('[ERROR] <%s:%d> %s', __FILE__, __LINE__, $e->getMessage()));
	}
}

/** Set locale to German, Switzerland & Timezone to Europe/Zurich */
setlocale(LC_TIME, $_ENV['LOCALE']);
setlocale(LC_MONETARY, $_ENV['LOCALE']);
date_default_timezone_set($_ENV['TIMEZONE']);

/** Start execution time measurement */
$parsetime_start = microtime(true);
$sqltracker_numqueries = 0;


/**
 * Development Environment: early inject special development.config.php - if applicable.
 * @const DEVELOPMENT Contains either 'true' or 'false' (boolean). Default: false
 * @include	development.config.php If DEVELOPMENT===true, load a corresponding config file containing DEV-specific settings.
 */
$isDevelopmentEnv = false; // Generally it's assumed we're running Production
if ((isset($_ENV['ENVIRONMENT']) && $_ENV['ENVIRONMENT'] === 'development') ||
	(isset($_SERVER['environment']) && $_SERVER['environment'] === 'development'))
{
	$isDevelopmentEnv = true; // $_ENV has priority, $_SERVER for backwards-compatibility before Dotenv
}
if (!defined('DEVELOPMENT')) define('DEVELOPMENT', (true === $isDevelopmentEnv ? true : false)); // Kept for backwards-compatibility (before Dotenv)
if (true === $isDevelopmentEnv && true === file_exists(__DIR__.'/development.config.php')) include_once __DIR__.'/development.config.php';

/**
 * Define preferred Hostname where website is accessible on.
 * @link https://stackoverflow.com/questions/1175096/how-to-find-out-if-youre-using-https-without-serverhttps
 *
 * @const SITE_PROTOCOL HTTP Protocol: https or http, required for building links like http(s)://... Default: false
 * @const SITE_HOSTNAME e.g. zorg.ch WITHOUT trailing slash! (no ".../"). Default: zorg.ch
 * @const SITE_URL Preferred base URL where zorg.ch is accessible through. Generated using SITE_PROTOCOL and SITE_HOSTNAME.
 * @const PAGETITLE_SUFFIX General suffix for <title>...[suffix]</title> on every page. Default: (empty)
 */
$isSecure = false;
switch(true)
{
	case !empty($_SERVER['HTTP_X_FORWARDED_PROTO']):
		$isSecure = ($_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https'); break;
	case !empty($_SERVER['HTTP_X_FORWARDED_SSL']):
		$isSecure = ($_SERVER['HTTP_X_FORWARDED_SSL'] === 'on'); break;
	case !empty($_SERVER['HTTPS']):
		$isSecure = ($_SERVER['HTTPS'] === 'on'); break;
}
if (!defined('SITE_PROTOCOL')) define('SITE_PROTOCOL', (false === $isSecure ? 'http' : 'https'));

$httpHost = null;
if (isset($_SERVER['HTTP_X_FORWARDED_HOST'])) {
	$httpHost = $_SERVER['HTTP_X_FORWARDED_HOST'];
} elseif (isset($_SERVER['HTTP_HOST'])) {
	$httpHost = $_SERVER['HTTP_HOST'];
} elseif (isset($_ENV['HOSTNAME'])) {
	$httpHost = $_ENV['HOSTNAME'];
}
if (!defined('SITE_HOSTNAME')) define('SITE_HOSTNAME', $httpHost);
if (!defined('SITE_URL')) define('SITE_URL', SITE_PROTOCOL . '://' . SITE_HOSTNAME);
if (!defined('PAGETITLE_SUFFIX')) define('PAGETITLE_SUFFIX', (isset($_ENV['PAGETITLE_SUFFIX']) && !empty($_ENV['PAGETITLE_SUFFIX']) ? $_ENV['PAGETITLE_SUFFIX'] : ''));

/**
 * Define global Contact points, such as e-mail addresses (From:)
 *
 * @const SENDMAIL_EMAIL Sets valid sender e-mailadress such as info@zooomclan.org
 * @const ADMIN_EMAIL E-mailaddress to send stuff like application alerts to, may be used from the apache2 config (Fallback: SENDMAIL_EMAIL)
 * @const GIT_REPOSITORY_ROOT zorg Code Git-Repository on the server (for code version info). For dev adjust in development.config.php
 * @const GIT_REPOSITORY_URL zorg Code Git-Repository public URL
 * @const FACEBOOK_APPID A Facebook App-ID which can be linked, see developers.facebook.com/apps/
 * @const FACEBOOK_PAGENAME Facebook page name (as in the group url) of the Zorg Facebook group
 * @const TELEGRAM_CHATLINK Telegram Messenger Group-Chat link to join the Zorg Community group
 * @const TWITTER_NAME A Twitter-profile username which can be linked, e.g. ZorgCH (no "@")
 */
if (!defined('SENDMAIL_EMAIL') && isset($_ENV['EMAILS_FROM'])) define('SENDMAIL_EMAIL', $_ENV['EMAILS_FROM']);
if (!defined('ADMIN_EMAIL') && (isset($_SERVER['SERVER_ADMIN']) || isset($_ENV['ADMIN_EMAIL']))) define('ADMIN_EMAIL', (isset($_ENV['ADMIN_EMAIL']) && !empty($_ENV['ADMIN_EMAIL']) ? $_ENV['ADMIN_EMAIL'] : (isset($_SERVER['SERVER_ADMIN']) || !empty($_SERVER['SERVER_ADMIN']) ? $_SERVER['SERVER_ADMIN'] : SENDMAIL_EMAIL)));
if (!defined('GIT_REPOSITORY_ROOT')) define('GIT_REPOSITORY_ROOT', (isset($_ENV['GIT_REPOSITORY_ROOT']) ? $_ENV['GIT_REPOSITORY_ROOT'] : null));
if (!defined('GIT_REPOSITORY_URL')) define('GIT_REPOSITORY_URL', (isset($_ENV['GIT_REPOSITORY_URL']) ? $_ENV['GIT_REPOSITORY_URL'] : null));
if (!defined('FACEBOOK_APPID')) define('FACEBOOK_APPID', (isset($_ENV['FACEBOOK_APPID']) ? $_ENV['FACEBOOK_APPID'] : null));
if (!defined('FACEBOOK_PAGENAME')) define('FACEBOOK_PAGENAME', (isset($_ENV['FACEBOOK_PAGENAME']) ? $_ENV['FACEBOOK_PAGENAME'] : null));
if (!defined('TELEGRAM_CHATLINK')) define('TELEGRAM_CHATLINK', (isset($_ENV['TELEGRAM_CHATLINK']) ? $_ENV['TELEGRAM_CHATLINK'] : null));
if (!defined('TWITTER_NAME')) define('TWITTER_NAME', (isset($_ENV['TWITTER_NAME']) ? $_ENV['TWITTER_NAME'] : null));

/**
 * Define internal Application paths to where scripts and resources will be loaded from
 *
 * @const INCLUDES_DIR PHP-Script includes directory for using in PHP-Scripts
 * @const MODELS_DIR MVC-Models directory
 * @const VIEWS_DIR MVC-Views directory (Smarty templates, see also: SMARTY_TEMPLATES_HTML)
 * @const CONTROLLERS_DIR MVC-Controllers directory
 * @const FILES_DIR Files directory (local server path)
 * @const GALLERY_DIR Gallery directory (local server path)
 * @const GALLERY_UPLOAD_DIR Path to the Upload directory for new Galleries / Gallery Pics on the Server
 * @const HZ_MAPS_DIR Hunting z Maps-directory (local server path)
 * @const UPLOAD_DIR Temporary upload storage for uploads of files & images from PHP-Scripts
 * @const PHP_IMAGES_DIR Images directory for including Images in PHP-Scripts
 * @const TAUSCHARTIKEL_IMGPATH Path to store uploaded images for Tauschbörse-Angebote
 * @const TAUSCHARTIKEL_IMGPATH_UPLOAD Path to temporarily store image uploads for Tauschbörse-Angebote
 * @const USER_IMGPATH	Interner PHP-Pfad zum Userpics Ordner
 * @const USER_IMGPATH_ARCHIVE	Interner PHP-Pfad zum Userpics Archiv-Ordner
 * @const USER_IMGPATH_UPLOAD	Interner PHP-Pfad zum temporären Upload-Ordner für neue Userpics
 */
if (!defined('INCLUDES_DIR')) define('INCLUDES_DIR', (isset($_ENV['INCLUDES_DIR']) ? $_ENV['INCLUDES_DIR'] : null));
if (!defined('MODELS_DIR')) define('MODELS_DIR', (isset($_ENV['MODELS_DIR']) ? $_ENV['MODELS_DIR'] : null));
if (!defined('VIEWS_DIR')) define('VIEWS_DIR', (isset($_ENV['VIEWS_DIR']) ? $_ENV['VIEWS_DIR'] : null));
if (!defined('CONTROLLERS_DIR')) define('CONTROLLERS_DIR', (isset($_ENV['CONTROLLERS_DIR']) ? $_ENV['CONTROLLERS_DIR'] : null));
if (!defined('FILES_DIR')) define('FILES_DIR', (isset($_ENV['FILES_DIR']) ? $_ENV['FILES_DIR'] : null));
if (!defined('GALLERY_DIR')) define('GALLERY_DIR', (isset($_ENV['GALLERY_DIR']) ? $_ENV['GALLERY_DIR'] : null));
if (!defined('GALLERY_UPLOAD_DIR')) define('GALLERY_UPLOAD_DIR', (isset($_ENV['GALLERY_UPLOAD_DIR']) ? $_ENV['GALLERY_UPLOAD_DIR'] : null));
if (!defined('HZ_MAPS_DIR')) define('HZ_MAPS_DIR', (isset($_ENV['HZ_MAPS_DIR']) ? $_ENV['HZ_MAPS_DIR'] : null));
if (!defined('UPLOAD_DIR')) define('UPLOAD_DIR', (isset($_ENV['DATA_UPLOAD_DIR']) ? $_ENV['DATA_UPLOAD_DIR'] : null));
if (!defined('PHP_IMAGES_DIR')) define('PHP_IMAGES_DIR', (isset($_ENV['IMAGES_DIR']) ? $_ENV['IMAGES_DIR'] : null));
if (!defined('TAUSCHARTIKEL_IMGPATH')) define('TAUSCHARTIKEL_IMGPATH', (isset($_ENV['TAUSCHARTIKEL_IMGAGES_DIR']) ? $_ENV['TAUSCHARTIKEL_IMGAGES_DIR'] : null));
if (!defined('TAUSCHARTIKEL_IMGPATH_UPLOAD')) define('TAUSCHARTIKEL_IMGPATH_UPLOAD', (isset($_ENV['TAUSCHARTIKEL_UPLOAD_DIR']) ? $_ENV['TAUSCHARTIKEL_UPLOAD_DIR'] : null));
if (!defined('USER_IMGPATH')) define('USER_IMGPATH', (isset($_ENV['USERIMAGES_DIR']) ? $_ENV['USERIMAGES_DIR'] : null));
if (!defined('USER_IMGPATH_ARCHIVE')) define('USER_IMGPATH_ARCHIVE', (isset($_ENV['USERIMAGES_ARCHIVE_DIR']) ? $_ENV['USERIMAGES_ARCHIVE_DIR'] : null));
if (!defined('USER_IMGPATH_UPLOAD')) define('USER_IMGPATH_UPLOAD', (isset($_ENV['USERIMAGES_UPLOAD_DIR']) ? $_ENV['USERIMAGES_UPLOAD_DIR'] : null));

/**
 * Define external frontend paths to where certain resources are retrieved from by webbrowsers
 *
 * @const ACTIONS_DIR Actions directory for Frontend-Resources
 * @const CSS_DIR CSS directory for Frontend-Resources
 * @const IMAGES_DIR Images directory for Frontend-Resources (don't use in PHP Scripts! Refer to PHP_IMAGES_DIR)
 * @const JS_DIR JavaScripts directory for Frontend-Resources
 * @const SCRIPTS_DIR Scripts directory for Frontend-Resources
 * @const UTIL_DIR Utilities directory for Frontend-Resources
 * @const USER_IMGPATH_PUBLIC Externer Pfad zu den Userpics
 * @const TAUSCHBOERSE_IMGPATH_PUBLIC Externer Pfad zu den Tauschbörse Artikel-Bilder
 * @const RSS_URL External Base URL from where RSS-Feeds are being served from
 */
if (!defined('ACTIONS_DIR')) define('ACTIONS_DIR', (isset($_ENV['URLPATH_ACTIONS']) ? $_ENV['URLPATH_ACTIONS'] : null));
if (!defined('CSS_DIR')) define('CSS_DIR', (isset($_ENV['URLPATH_CSS']) ? $_ENV['URLPATH_CSS'] : null));
if (!defined('IMAGES_DIR')) define('IMAGES_DIR', (isset($_ENV['URLPATH_IMAGES']) ? $_ENV['URLPATH_IMAGES'] : null));
if (!defined('JS_DIR')) define('JS_DIR', (isset($_ENV['URLPATH_JS']) ? $_ENV['URLPATH_JS'] : null));
if (!defined('SCRIPTS_DIR')) define('SCRIPTS_DIR', (isset($_ENV['URLPATH_SCRIPTS']) ? $_ENV['URLPATH_SCRIPTS'] : null));
if (!defined('UTIL_DIR')) define('UTIL_DIR', (isset($_ENV['URLPATH_UTILS']) ? $_ENV['URLPATH_UTILS'] : null));
if (!defined('USER_IMGPATH_PUBLIC')) define('USER_IMGPATH_PUBLIC', (isset($_ENV['URLPATH_USERIMAGES']) ? $_ENV['URLPATH_USERIMAGES'] : null));
if (!defined('TAUSCHBOERSE_IMGPATH_PUBLIC')) define('TAUSCHBOERSE_IMGPATH_PUBLIC', (defined('IMAGES_DIR') ? IMAGES_DIR.'tauschboerse/' : null));
if (!defined('RSS_URL')) define('RSS_URL', SITE_URL . (isset($_ENV['URLPATH_RSS']) ? $_ENV['URLPATH_RSS'] : '/?layout=rss'));

/**
 * Define User & Usersystem constants
 *
 * @const ZORG_SESSION_ID			Session name
 * @const ZORG_SESSION_LIFETIME		Session duration time = 12 hours
 * @const ZORG_COOKIE_SESSION		Session Cookie name
 * @const ZORG_COOKIE_USERID		User-ID Cookie name
 * @const ZORG_COOKIE_USERPW		User Password Cookie name
 * @const ZORG_COOKIE_SECURE		Cookie is secure (true=https) or not (false=http), dont use boolean values! Use 0 for false / 1 for true.
 * @const ZORG_COOKIE_EXPIRATION	Cookie Lifetime = 1 week
 * @const ZORG_COOKIE_DOMAIN		Domain where Cookie is valid. Add leading dot '.zorg.ch' for better compatibility
 * @const ZORG_COOKIE_PATH			Site Path where the Cookie is valid. '/' = all pages
 * @const ZORG_COOKIE_SAMESITE		Cookie strictness. Valid is only "Lax" or "Strict". Strict is problematic in Cross-Site Requests.
 * @const COOKIE_HTTPONLY		    Cookie allowed only in HTTP requests. Valid values: 'true' or 'false' ('true' is strongly recommended)
 * @const USER_ALLE		Wert für nicht eingeloggte User
 * @const USER_USER		Wert für normale eingeloggte User
 * @const USER_MEMBER 	Wert für [z]member & schöne
 * @const USER_SPECIAL	Wert für Admins & Coder
 * @const USER_IMGEXTENSION	File-Extension/Format von Userpics
 * @const USER_IMGSIZE_LARGE	Grösse in Pixel der normalen Userpics
 * @const USER_IMGSIZE_SMALL	Grösse in Pixel der Userpic-Thumbnails
 * @const USER_IMGPATH_DEFAULT	Filename des Standard-Userpic im USER_IMGPATH
 * @const USER_TIMEOUT	Session Timeout für eingeloggte User, in Sekunden. Default: 60 (1 Minute)
 * @const USER_OLD_AFTER	Zeit bis ein User als "alt" gilt -> 3 Monate
 * @const DEFAULT_MAXDEPTH	Standard Setting für die Anzeigetiefe von Comments in Forum-Threads
 */
if (!defined('ZORG_SESSION_ID')) define('ZORG_SESSION_ID', (isset($_ENV['SESSION_ID']) ? $_ENV['SESSION_ID'] : 'sess'));
if (!defined('ZORG_SESSION_LIFETIME')) define('ZORG_SESSION_LIFETIME', (isset($_ENV['SESSION_LIFETIME']) ? (int)$_ENV['SESSION_LIFETIME'] : 0));
if (!defined('ZORG_COOKIE_SESSION')) define('ZORG_COOKIE_SESSION', (isset($_ENV['COOKIE_SESSION']) ? $_ENV['COOKIE_SESSION'] : ZORG_SESSION_ID));
if (!defined('ZORG_COOKIE_USERID')) define('ZORG_COOKIE_USERID', (isset($_ENV['COOKIE_USERID']) ? $_ENV['COOKIE_USERID'] : null));
if (!defined('ZORG_COOKIE_USERPW')) define('ZORG_COOKIE_USERPW', (isset($_ENV['COOKIE_USERPW']) ? $_ENV['COOKIE_USERPW'] : null));
if (!defined('ZORG_COOKIE_SECURE')) define('ZORG_COOKIE_SECURE', (false === $isSecure ? null : true));
if (!defined('ZORG_COOKIE_DOMAIN')) define('ZORG_COOKIE_DOMAIN', (isset($_ENV['COOKIE_DOMAIN']) ? $_ENV['COOKIE_DOMAIN'] : SITE_HOSTNAME));
if (!defined('ZORG_COOKIE_EXPIRATION')) define('ZORG_COOKIE_EXPIRATION', (isset($_ENV['COOKIE_EXPIRATION']) ? time()+(int)$_ENV['COOKIE_EXPIRATION'] : time()));
if (!defined('ZORG_COOKIE_PATH')) define('ZORG_COOKIE_PATH', (isset($_ENV['COOKIE_PATH']) ? $_ENV['COOKIE_PATH'] : '/'));
if (!defined('ZORG_COOKIE_SAMESITE')) define('ZORG_COOKIE_SAMESITE', (isset($_ENV['COOKIE_SAMESITE']) ? $_ENV['COOKIE_SAMESITE'] : 'Lax'));
if (!defined('COOKIE_HTTPONLY')) define('COOKIE_HTTPONLY', (isset($_ENV['COOKIE_HTTPONLY']) ? (bool)$_ENV['COOKIE_HTTPONLY'] : true));
if (!defined('USER_ALLE')) define('USER_ALLE', (isset($_ENV['USERLEVEL_ALLE']) ? (int)$_ENV['USERLEVEL_ALLE'] : 0));
if (!defined('USER_USER')) define('USER_USER', (isset($_ENV['USERLEVEL_USER']) ? (int)$_ENV['USERLEVEL_USER'] : 1));
if (!defined('USER_MEMBER')) define('USER_MEMBER', (isset($_ENV['USERLEVEL_MEMBER']) ? (int)$_ENV['USERLEVEL_MEMBER'] : 2));
if (!defined('USER_SPECIAL')) define('USER_SPECIAL', (isset($_ENV['USERLEVEL_ADMIN']) ? (int)$_ENV['USERLEVEL_ADMIN'] : 3));
if (!defined('USER_IMGEXTENSION')) define('USER_IMGEXTENSION', (isset($_ENV['USERIMAGE_EXTENSION']) ? $_ENV['USERIMAGE_EXTENSION'] : '.jpg'));
if (!defined('USER_IMGSIZE_LARGE')) define('USER_IMGSIZE_LARGE', (isset($_ENV['USERIMAGE_SIZE_LARGE']) ? (int)$_ENV['USERIMAGE_SIZE_LARGE'] : null));
if (!defined('USER_IMGSIZE_SMALL')) define('USER_IMGSIZE_SMALL', (isset($_ENV['USERIMAGE_SIZE_SMALL']) ? (int)$_ENV['USERIMAGE_SIZE_SMALL'] : null));
if (!defined('USER_IMGPATH_DEFAULT')) define('USER_IMGPATH_DEFAULT', (isset($_ENV['USERIMAGE_DEFAULT']) ? $_ENV['USERIMAGE_DEFAULT'] : null));
if (!defined('USER_TIMEOUT')) define('USER_TIMEOUT', (isset($_ENV['USER_TIMEOUT']) ? (int)$_ENV['USER_TIMEOUT'] : 60));
if (!defined('USER_OLD_AFTER')) define('USER_OLD_AFTER', (isset($_ENV['USER_OLD_AFTER']) ? (int)$_ENV['USER_OLD_AFTER'] : null));
if (!defined('DEFAULT_MAXDEPTH')) define('DEFAULT_MAXDEPTH', (isset($_ENV['FORUM_DEFAULT_MAXDEPTH']) ? (int)$_ENV['FORUM_DEFAULT_MAXDEPTH'] : 7));

/**
 * Define Smarty constants
 * @const SMARTY_DEFAULT_TPL Default (fallback) Smarty-Template tpl:- or file:-ID/Name
 * @const SMARTY_404PAGE_TPL 404 "Page not found" Smarty-Template reference
 */
if (!defined('SMARTY_DIR')) define('SMARTY_DIR', (isset($_ENV['SMARTY_DIR']) ? $_ENV['SMARTY_DIR'] : null));
if (!defined('SMARTY_TRUSTED_DIRS')) {
    define('SMARTY_TRUSTED_DIRS', isset($_ENV['SMARTY_TRUSTED_DIRS']) ? explode(',', $_ENV['SMARTY_TRUSTED_DIRS']) : []);
}
if (!defined('SMARTY_TEMPLATES_HTML')) {
    define('SMARTY_TEMPLATES_HTML', isset($_ENV['SMARTY_TEMPLATES_HTML']) ? explode(',', $_ENV['SMARTY_TEMPLATES_HTML']) : []);
}
if (!defined('SMARTY_CACHE')) define('SMARTY_CACHE', (isset($_ENV['SMARTY_CACHE']) ? $_ENV['SMARTY_CACHE'] : null));
if (!defined('SMARTY_COMPILE')) define('SMARTY_COMPILE', (isset($_ENV['SMARTY_COMPILE']) ? $_ENV['SMARTY_COMPILE'] : null));
if (!defined('SMARTY_PACKAGES_DIR')) define('SMARTY_PACKAGES_DIR', (isset($_ENV['SMARTY_PACKAGES_DIR']) ? $_ENV['SMARTY_PACKAGES_DIR'] : null));
if (!defined('SMARTY_PACKAGES_EXTENSION')) define('SMARTY_PACKAGES_EXTENSION', (isset($_ENV['SMARTY_PACKAGES_EXTENSION']) ? $_ENV['SMARTY_PACKAGES_EXTENSION'] : '.php'));
if (!defined('SMARTY_DEFAULT_TPL')) define('SMARTY_DEFAULT_TPL', (isset($_ENV['SMARTY_DEFAULT_TPL_ID']) ? (int)$_ENV['SMARTY_DEFAULT_TPL_ID'] : null));
if (!defined('SMARTY_404PAGE_TPL')) define('SMARTY_404PAGE_TPL', (isset($_ENV['SMARTY_404PAGE_TPL_FILE']) ? $_ENV['SMARTY_404PAGE_TPL_FILE'] : null));

/**
 * Define various Gallery related constants.
 * @const MAX_PIC_SIZE The maximum width & height for pictures
 * @const MAX_THUMBNAIL_SIZE The maximum width & height for pic thumbnails
 */
if (!defined('MAX_PIC_SIZE')) {
	define('MAX_PIC_SIZE', (isset($_ENV['GALLERY_MAX_PIC_WIDTH']) && isset($_ENV['GALLERY_MAX_PIC_HEIGHT']) ? ['width' => $_ENV['GALLERY_MAX_PIC_WIDTH'], 'height' => $_ENV['GALLERY_MAX_PIC_HEIGHT']] : ['width' => 800, 'height' => 600]));
}
if (!defined('MAX_THUMBNAIL_SIZE')) {
	define('MAX_THUMBNAIL_SIZE', (isset($_ENV['GALLERY_MAX_THUMB_WIDTH']) && isset($_ENV['GALLERY_MAX_THUMB_HEIGHT']) ? ['width' => $_ENV['GALLERY_MAX_THUMB_WIDTH'], 'height' => $_ENV['GALLERY_MAX_THUMB_HEIGHT']] : ['width' => 150, 'height' => 150]));
}

/**
 * Define various NASA API and APOD related constants.
 * @const NASA_API_KEY A constant holding the NASA API Key, can be used optionally (!) for requests to NASA's APIs such as the APOD
 * @const APOD_GALLERY_ID ID der APOD-Gallery in der Datenbank
 * @const APOD_SOURCE Source-URL für die APOD-Bilder Archiv-Links
 * @const APOD_API NASA APOD API-URL von wo das tägliche APOD-Bild mit dem NASA_API_KEY geholt werden kann, mittels ?apod_date=yyyy-mm-dd kann ein spezifisches APOD geholt werden
 */
if (!defined('NASA_API_KEY') && isset($_ENV['NASA_API_KEY'])) define('NASA_API_KEY', $_ENV['NASA_API_KEY']);
if (!defined('APOD_GALLERY_ID')) define('APOD_GALLERY_ID', (isset($_ENV['APOD_GALLERY_ID']) ? (int)$_ENV['APOD_GALLERY_ID'] : null));
if (!defined('APOD_SOURCE')) define('APOD_SOURCE', (isset($_ENV['NASA_APOD_SOURCE']) ? $_ENV['NASA_APOD_SOURCE'] : null));
if (!defined('APOD_API')) define('APOD_API', (isset($_ENV['NASA_APOD_API']) ? $_ENV['NASA_APOD_API'] : null));

/**
 * Define various Addle related constants.
 * @const MAX_ADDLE_GAMES	Anzahl der erlaubten gleichzeitig offenen Addle-Spiele eines Users
 * @const MAX_ADDLE_GAMES	Anzahl der erlaubten gleichzeitig offenen Addle-Spiele eines Users
 * @const MAX_ADDLE_GAMES	Anzahl der erlaubten gleichzeitig offenen Addle-Spiele eines Users
 */
define('MAX_ADDLE_GAMES', (isset($_ENV['ADDLE_MAX_GAMES']) ? (int)$_ENV['ADDLE_MAX_GAMES'] : 0));
define('ADDLE_BASE_POINTS', (isset($_ENV['ADDLE_BASE_POINTS']) ? (int)$_ENV['ADDLE_BASE_POINTS'] : 0));
define('ADDLE_MAX_POINTS_TRANSFERABLE', (isset($_ENV['ADDLE_MAX_POINTS_TRANSFERABLE']) ? (int)$_ENV['ADDLE_MAX_POINTS_TRANSFERABLE'] : 0));

/**
 * Define zorg-specific global constants
 *
 * @const VORSTAND_USER User-ID of the Zorg Verein Vorstand-User
 * @const BARBARA_HARRIS User-ID of [z]Barbara Harris
 * @const ROSENVERKAEUFER User-ID des Rosenverkäufer's (für Peter-Spiele)
 * @const THE_ARCHITECT User-ID des [z]architect
 * @const ZORG_VEREIN_NAME Bezeichnung des Vereins (z.B. für Swiss QR Bill)
 * @const ZORG_VEREIN_EMAIL Zorg Verein E-Mail address
 * @const ZORG_VEREIN_STRASSE Strasse der Adresse des Vereins (z.B. für Swiss QR Bill)
 * @const ZORG_VEREIN_PLZ PLZ der Adresse des Vereins (z.B. für Swiss QR Bill)
 * @const ZORG_VEREIN_ORT Ort der Adresse des Vereins (z.B. für Swiss QR Bill)
 * @const ZORG_VEREIN_LAND Zorg Land der Adresse des Vereins (z.B. für Swiss QR Bill)
 * @const ZORG_VEREIN_LAND_ISO2 2-stelliger ISO-Code des Land des Vereins (z.B. für Swiss QR Bill)
 * @const ZORG_VEREIN_KONTO_BANK Bankname des Vereinskontos (z.B. für Swiss QR Bill)
 * @const ZORG_VEREIN_KONTO_SWIFT SWIFT-Identifikation des Vereinskontos (z.B. für Swiss QR Bill)
 * @const ZORG_VEREIN_KONTO_IBAN IBAN-Nummer des Vereinskontos (z.B. für Swiss QR Bill)
 * @const ZORG_VEREIN_KONTO_IBAN_QRBILL Swiss QR-Bill IBAN-Nummer des Vereinskontos (z.B. für Swiss QR Bill)
 * @const ZORG_VEREIN_KONTO_CURRENCY Akzeptierte Währung des Vereinskontos (z.B. für Swiss QR Bill)
 * @const ZORG_VEREIN_KONTO_BESRID Diese Identifikationsnummer (BESR-ID) wird von der Bank vergeben (nicht bei Post Finance). Benötigt für Referenznummern auf Rechnungen.
 */
if (!defined('VORSTAND_USER')) define('VORSTAND_USER', (isset($_ENV['VORSTAND_USER']) ? (int)$_ENV['VORSTAND_USER'] : null));
if (!defined('BARBARA_HARRIS')) define('BARBARA_HARRIS', (isset($_ENV['BARBARA_HARRIS']) ? (int)$_ENV['BARBARA_HARRIS'] : null));
if (!defined('ROSENVERKAEUFER')) define('ROSENVERKAEUFER', (isset($_ENV['ROSENVERKAEUFER']) ? (int)$_ENV['ROSENVERKAEUFER'] : null));
if (!defined('THE_ARCHITECT')) define('THE_ARCHITECT', (isset($_ENV['THE_ARCHITECT']) ? (int)$_ENV['THE_ARCHITECT'] : null));
if (!defined('ZORG_VEREIN_NAME')) define('ZORG_VEREIN_NAME', (isset($_ENV['ZORG_VEREIN_NAME']) ? $_ENV['ZORG_VEREIN_NAME'] : null));
if (!defined('ZORG_VEREIN_EMAIL')) define('ZORG_VEREIN_EMAIL', (isset($_ENV['ZORG_VEREIN_EMAIL']) ? $_ENV['ZORG_VEREIN_EMAIL'] : null));
if (!defined('ZORG_VEREIN_STRASSE')) define('ZORG_VEREIN_STRASSE', (isset($_ENV['ZORG_VEREIN_STRASSE']) ? $_ENV['ZORG_VEREIN_STRASSE'] : null));
if (!defined('ZORG_VEREIN_PLZ')) define('ZORG_VEREIN_PLZ', (isset($_ENV['ZORG_VEREIN_PLZ']) ? (int)$_ENV['ZORG_VEREIN_PLZ'] : null));
if (!defined('ZORG_VEREIN_ORT')) define('ZORG_VEREIN_ORT', (isset($_ENV['ZORG_VEREIN_ORT']) ? $_ENV['ZORG_VEREIN_ORT'] : null));
if (!defined('ZORG_VEREIN_LAND')) define('ZORG_VEREIN_LAND', (isset($_ENV['ZORG_VEREIN_LAND']) ? $_ENV['ZORG_VEREIN_LAND'] : null));
if (!defined('ZORG_VEREIN_LAND_ISO2')) define('ZORG_VEREIN_LAND_ISO2', (isset($_ENV['ZORG_VEREIN_LAND_ISO2']) ? $_ENV['ZORG_VEREIN_LAND_ISO2'] : null));
if (!defined('ZORG_VEREIN_KONTO_BANK')) define('ZORG_VEREIN_KONTO_BANK', (isset($_ENV['ZORG_VEREIN_KONTO_BANK']) ? $_ENV['ZORG_VEREIN_KONTO_BANK'] : null));
if (!defined('ZORG_VEREIN_KONTO_SWIFT')) define('ZORG_VEREIN_KONTO_SWIFT', (isset($_ENV['ZORG_VEREIN_KONTO_SWIFT']) ? $_ENV['ZORG_VEREIN_KONTO_SWIFT'] : null));
if (!defined('ZORG_VEREIN_KONTO_IBAN')) define('ZORG_VEREIN_KONTO_IBAN', (isset($_ENV['ZORG_VEREIN_KONTO_IBAN']) ? $_ENV['ZORG_VEREIN_KONTO_IBAN'] : null));
if (!defined('ZORG_VEREIN_KONTO_IBAN_QRBILL')) define('ZORG_VEREIN_KONTO_IBAN_QRBILL', (isset($_ENV['ZORG_VEREIN_KONTO_IBAN_QRBILL']) ? $_ENV['ZORG_VEREIN_KONTO_IBAN_QRBILL'] : null));
if (!defined('ZORG_VEREIN_KONTO_CURRENCY')) define('ZORG_VEREIN_KONTO_CURRENCY', (isset($_ENV['ZORG_VEREIN_KONTO_CURRENCY']) ? $_ENV['ZORG_VEREIN_KONTO_CURRENCY'] : 'CHF'));
if (!defined('ZORG_VEREIN_KONTO_BESRID')) define('ZORG_VEREIN_KONTO_BESRID', (isset($_ENV['ZORG_VEREIN_KONTO_BESRID']) ? $_ENV['ZORG_VEREIN_KONTO_BESRID'] : null));

/**
 * Set a constant for the custom Error Log path.
 * @see zorgErrorHandler(), user_error(), trigger_error()
 * @link https://github.com/zorgch/zorg-code/blob/master/www/includes/errlog.inc.php errlog.inc.php
 *
 * @const ERRORLOG_FILETYPE sets the file extension used for the error log file
 * @const ERRORLOG_DIR sets the directory for logging the custom user_errors
 * @const ERRORLOG_FILEPATH sets the directory & file path for logging the custom user_errors to
 * @const ERRORLOG_LEVEL sets the verbosity of logging errors, warnings, and notices caused by the application. See: https://stackoverflow.com/q/3758418
 * @const ERRORLOG_DEBUG_SCOPE (Optional) sets a focused scope for DEBUG log entries
 * @include errlog.inc.php Errorlogging Class: Load the zorg Error and Debug Handling
 */
if (!defined('ERRORLOG_FILETYPE')) define('ERRORLOG_FILETYPE', (isset($_ENV['ERRORLOG_FILETYPE']) ? $_ENV['ERRORLOG_FILETYPE'] : '.log'));
if (!defined('ERRORLOG_DIR')) define('ERRORLOG_DIR', (isset($_ENV['ERRORLOG_DIR']) ? $_ENV['ERRORLOG_DIR'] : null));
if (!defined('ERRORLOG_FILE')) define('ERRORLOG_FILE', ERRORLOG_DIR.date('Y-m-d').ERRORLOG_FILETYPE);
if (!defined('ERRORLOG_LEVEL')) define('ERRORLOG_LEVEL', (isset($_ENV['ERROR_REPORTING_LEVELS']) && is_numeric($_ENV['ERROR_REPORTING_LEVELS']) ? $_ENV['ERROR_REPORTING_LEVELS'] : E_ERROR));
if (!defined('ERRORLOG_DEBUG_SCOPE')) {
    define('ERRORLOG_DEBUG_SCOPE', isset($_ENV['DEBUG_SCOPE']) && !empty($_ENV['DEBUG_SCOPE']) ? explode(',', $_ENV['DEBUG_SCOPE']) : []);
}
error_reporting(ERRORLOG_LEVEL);
require_once INCLUDES_DIR.'errlog.inc.php';
//set_error_handler('zorgErrorHandler');

/**
 * Include some generic files and functions to make them globally available by default.
 * (keep this at the end of the config.inc.php!)
 *
 * @include strings.inc.php Various Placeholder-Strings related constants and files.
 * @include util.inc.php Various Helper Functions and Code Utilities.
 * @include mysql.inc.php MySQL-DB Connection and Functions
 * @include usersystem.inc.php Usersystem Functions and User definitions
 * @include notifications.inc.php Various Notification System-related constants and files
 * @include telegrambot.inc.php Required to send Telegram-Notifications
 */
require_once INCLUDES_DIR.'strings.inc.php';
require_once INCLUDES_DIR.'util.inc.php';
require_once INCLUDES_DIR.'mysql.inc.php';
require_once INCLUDES_DIR.'usersystem.inc.php';
include_once INCLUDES_DIR.'notifications.inc.php';
include_once INCLUDES_DIR.'telegrambot.inc.php';
