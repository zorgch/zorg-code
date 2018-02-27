<?php
error_reporting(E_ALL & ~E_NOTICE);

/**
 * Set locale to German, Switzerland
 */
setlocale(LC_TIME,"de_CH");

/**
 * Start execution time measurement
 */
$parsetime_start = microtime(true);
$sqltracker_numqueries = 0;

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
define('SITE_PROTOCOL', ($isSecure ? 'https' : 'http'), true);

/**
* Define preferred Hostname where zorg.ch is accessible on
* @const SITE_HOSTNAME e.g. zorg.ch WITHOUT trailing slash! (no ".../")
*/
define('SITE_HOSTNAME', $_SERVER['SERVER_NAME'], true);

/**
* Define preferred base URL where zorg.ch is accessible through
* @const SITE_URL Don't edit! Is generated using SITE_PROTOCOL and SITE_HOSTNAME
*/
define('SITE_URL', SITE_PROTOCOL . '://' . SITE_HOSTNAME, true);

/**
* Set a constant for the Site's Web Root
* @const SITE_ROOT Set the Site Root WITHOUT a trailing slash "/"
*/
define('SITE_ROOT', rtrim( __DIR__ ,'/\\').'/..', true);

/**
* Set a constant for the custom Error Log path
* @const ERRORLOG_DIR sets the directory for logging the custom user_errors as in
* @see errlog.inc.php zorgErrorHandler()
*/
define('ERRORLOG_DIR', SITE_ROOT . '/../data/errlog/', true);
define('FILES_DIR', SITE_ROOT . '/../data/files/', true);

/**
* Define global Contact points, such as e-mail addresses (From:)
* @const ZORG_EMAIL A valid e-mailadress such as info@zooomclan.org
* @const SERVER_EMAIL Don't edit! This grabs the Admin E-Mail from the apache2 config
* @const TWITTER_NAME A Twitter-profile username which can be linked, e.g. ZorgCH (no "@")
* @const FACEBOOK_APPID A Facebook App-ID which can be linked, see developers.facebook.com/apps/
* @const BARBARA_HARRIS User-ID of [z]Barbara Harris
* @const VORSTAND_USER User-ID of the Zorg Verein Vorstand-User
*/
define('ZORG_EMAIL', 'info@' . SITE_HOSTNAME, true);
define('ZORG_ADMIN_EMAIL', $_SERVER['SERVER_ADMIN'], true);
define('ZORG_VEREIN_EMAIL', 'zorg-vorstand@googlegroups.com', true);
define('TWITTER_NAME', 'ZorgCH', true);
define('FACEBOOK_APPID', '110932998937967', true);
define('BARBARA_HARRIS', 59);
define('VORSTAND_USER', 451);

/**
 * @const SITE_HOSTNAME General suffix for <title>...[suffix]</title> on every page.
 */
if (!defined('PAGETITLE_SUFFIX')) define('PAGETITLE_SUFFIX', ' - '.SITE_HOSTNAME);

/**
 * RSS Feeds
 * @const RSS_URL Basic URL for RSS-Feeds
 */
if (!defined('RSS_URL')) define('RSS_URL', SITE_URL . '/?layout=rss');

/**
 * Define paths to directories where HTML web resources will be referenced from
 * @const INCLUDES_DIR File includes directory
 * @const IMAGES_DIR Images directory
 * @const ACTIONS_DIR Actions directory
 * @const SCRIPTS_DIR Scripts directory
 * @const UTIL_DIR Utilities directory
 * @const JS_DIR JavaScripts directory
 * @const CSS_DIR CSS directory
 */
if (!defined('INCLUDES_DIR')) define('INCLUDES_DIR', '/includes/');
if (!defined('IMAGES_DIR')) define('IMAGES_DIR', '/images/');
if (!defined('ACTIONS_DIR')) define('ACTIONS_DIR', '/actions/');
if (!defined('SCRIPTS_DIR')) define('SCRIPTS_DIR', '/scripts/');
if (!defined('UTIL_DIR')) define('UTIL_DIR', '/util/');
if (!defined('JS_DIR')) define('JS_DIR', '/js/');
if (!defined('CSS_DIR')) define('CSS_DIR', '/css/');

/**
 * @const BODYSETTINGS bodysettings wird verwendet, um den div nach den menüs wieder zu öffnen.
 */
if (!defined('BODYSETTINGS')) define("BODYSETTINGS", 'align="center" valign="top" style="margin: 0px 40px;"');

/**
 * Require important scripts
 * (PHP execution cannot be continued without these files)
 *
 * @include	colors.inc.php 	Colors
 * @include errlog.inc.php 	Errorlogging
 * @include mysql.inc.php 	MySQL-DB Connection and Functions
 * @include smarty.inc.php 	Smarty Template-Engine
 * @include strings.inc.php Text strings to be replaced within code functions etc.
 * @include sunrise.inc.php Sunrise information and current Sun, Day & Night state
 * @include usersystem.inc.php Usersystem Functions and User definitions
 * @include util.inc.php 	Various Helper Functions
 */
require_once( __DIR__ .'/colors.inc.php');
require_once( __DIR__ .'/errlog.inc.php');
require_once( __DIR__ .'/mysql.inc.php');
require_once( __DIR__ .'/smarty.inc.php');
require_once( __DIR__ .'/strings.inc.php');
require_once( __DIR__ .'/sunrise.inc.php');
require_once( __DIR__ .'/usersystem.inc.php');
require_once( __DIR__ .'/util.inc.php');

/**
 * Include other scripts
 * (PHP execution can go on, show users the output, even if the file is accidentally missing or unreadable)
 *
 * @include	activities.inc.php 	Activities Functions and Stream
 * @include	addle.inc.php 		Addle Functions
 * @include	forum.inc.php 		Forum and Commenting Functions
 * @include gallery.inc.php 	Gallery and Pic functions
 * @include go_game.inc.php 	Go Game functions
 * @include graph.inc.php 		Image Graph Stats functions
 * @include imap.inc.php 		IMAP functions (DEPRECATED)
 * @include messagesystem.inc.php Messagesystem Functions
 * @include peter.inc.php 		Peter Game functions
 * @include poll.inc.php 		Poll functions
 * @include quotes.inc.php 		Quotes functions
 * @include rezepte.inc.php 	Rezepte Datenbank functions
 * @include schach.inc.php  	Schach Game functions (DEPRECATED)
 * @include spaceweather.inc.php Spaceweather functions
 * @include telegrambot.inc.php Telegram Messenger Bot functions
 */
include_once( __DIR__ .'/activities.inc.php');
include_once( __DIR__ .'/addle.inc.php');
include_once( __DIR__ .'/forum.inc.php');
include_once( __DIR__ .'/gallery.inc.php');
include_once( __DIR__ .'/go_game.inc.php');
include_once( __DIR__ .'/graph.inc.php');
//include_once( __DIR__ .'/imap.inc.php');
include_once( __DIR__ .'/messagesystem.inc.php');
include_once( __DIR__ .'/peter.inc.php');
include_once( __DIR__ .'/poll.inc.php');
include_once( __DIR__ .'/quotes.inc.php');
include_once( __DIR__ .'/rezepte.inc.php'); // "Call to undefined function getOpenChessGames()" ["file"]=> string(48) "/Users/or/Sites/zooomclan/www/scripts/header.php" ["line"]=> int(18)
include_once( __DIR__ .'/schach.inc.php');
include_once( __DIR__ .'/spaceweather.inc.php');
include_once( __DIR__ .'/telegrambot.inc.php');

/**
 * Smarty assign variables
 * Variables can be accessed in Smarty-Templates using {$variable}
 */
$smarty->register_function('sqltracker', 'dbcon::sqltracker');
$smarty->register_modifier('rendertime', 'smarty_modifier_rendertime');
$smarty->assign('spaceweather', spaceweather_ticker());
//$smarty->assign('parsetime', round((microtime(true)-$parsetime_start), 2)); // PHP-Script Parsetime
$smarty->assign('parsetime_start', $parsetime_start); // PHP-Script Parsetime
smarty_modifier_rendertime('begin'); // Start Smarty-Template Rendering-Timer
