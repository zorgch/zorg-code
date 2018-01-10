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
* Set a constant for the Site's Document Root
* @const SITE_ROOT Automatically extract's the configured Document Root WITHOUT a trailing slash /
*/
define('SITE_ROOT', rtrim($_SERVER['DOCUMENT_ROOT'],'/\\').'/', true);

/**
* Set a constant for the custom Error Log path
* @const ERRORLOG_DIR sets the directory for logging the custom user_errors as in
* @see errlog.inc.php zorgErrorHandler()
*/
define('ERRORLOG_DIR', SITE_ROOT . '../data/errlog/', true);

/**
* Define a global SENDER e-mail addresses (From:)
* @const ZORG_EMAIL A valid e-mailadress such as info@zooomclan.org
* @const SERVER_EMAIL Don't edit! This grabs the Admin E-Mail from the apache2 config
*/
define('ZORG_EMAIL', 'info@zorg.ch', true);
define('ZORG_ADMIN_EMAIL', $_SERVER['SERVER_ADMIN'], true);

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
 * @include	css.inc.php 	CSS
 * @include errlog.inc.php 	Errorlogging
 * @include mysql.inc.php 	MySQL-DB Connection and Functions
 * @include smarty.inc.php 	Smarty Template-Engine
 * @include sunrise.inc.php Sunrise information and current Sun, Day & Night state
 * @include usersystem.inc.php Usersystem Functions and User definitions
 * @include util.inc.php 	Various Helper Functions
 */
require_once(SITE_ROOT.'/includes/colors.inc.php');
require_once(SITE_ROOT.'/includes/css.inc.php');
require_once(SITE_ROOT.'/includes/errlog.inc.php');
require_once(SITE_ROOT.'/includes/mysql.inc.php');
require_once(SITE_ROOT.'/includes/smarty.inc.php');
require_once(SITE_ROOT.'/includes/sunrise.inc.php');
require_once(SITE_ROOT.'/includes/usersystem.inc.php');
require_once(SITE_ROOT.'/includes/util.inc.php');

/**
 * Include other scripts
 * (PHP execution can go on, show users the output, even if the file is accidentally missing or unreadable)
 *
 * @include	activities.inc.php 	Activities Functions and Stream
 * @include	addle.inc.php 		Addle Functions
 * @include	forum.inc.php 		Forum and Commenting Functions
 * @include imap.inc.php 		IMAP functions (DEPRECATED)
 * @include messagesystem.inc.php 	Messagesystem Functions
 * @include peter.inc.php 		Peter Game functions
 * @include rezepte.inc.php 	Rezepte Datenbank functions
 * @include schach.inc.php  	Schach Game functions
 */
include_once(SITE_ROOT.'/includes/activities.inc.php');
include_once(SITE_ROOT.'/includes/addle.inc.php');
include_once(SITE_ROOT.'/includes/forum.inc.php');
include_once(SITE_ROOT.'/includes/gallery.inc.php');
include_once(SITE_ROOT.'/includes/go_game.inc.php');
include_once(SITE_ROOT.'/includes/graph.inc.php');
//include_once(SITE_ROOT.'/includes/imap.inc.php');
include_once(SITE_ROOT.'/includes/messagesystem.inc.php');
include_once(SITE_ROOT.'/includes/peter.inc.php');
include_once(SITE_ROOT.'/includes/quotes.inc.php');
include_once(SITE_ROOT.'/includes/rezepte.inc.php');
include_once(SITE_ROOT.'/includes/schach.inc.php');
include_once(SITE_ROOT.'/includes/spaceweather.inc.php');

/**
 * Globals
 * Assign defined variables to global scope
 */
global $smarty, $db, $user, $_TPLROOT, $parsetime_start, $sun, $country, $layouttype;

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
