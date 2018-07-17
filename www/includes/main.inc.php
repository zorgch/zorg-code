<?php
/**
 * Start execution time measurement
 */
$parsetime_start = microtime(true);
$sqltracker_numqueries = 0;

/**
 * @include config.inc.php Include required global site configurations
 */
require_once( __DIR__ . '/config.inc.php');

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
 * @const PAGETITLE_SUFFIX General suffix for <title>...[suffix]</title> on every page.
 */
if (!defined('PAGETITLE_SUFFIX')) define('PAGETITLE_SUFFIX', ' - ' . SITE_HOSTNAME, true);

/**
 * RSS Feeds
 * @const RSS_URL Basic URL for RSS-Feeds
 */
if (!defined('RSS_URL')) define('RSS_URL', SITE_URL . '/?layout=rss', true);

/**
 * @const BODYSETTINGS bodysettings wird verwendet, um den div nach den menüs wieder zu öffnen.
 */
if (!defined('BODYSETTINGS')) define("BODYSETTINGS", 'align="center" valign="top" style="margin: 0px 40px;"', true);

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
