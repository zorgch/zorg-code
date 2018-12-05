<?php
/**
 * @include config.inc.php Include required global site configurations
 */
require_once( __DIR__ . '/config.inc.php');

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
require_once( __DIR__ .'/strings.inc.php');
require_once( __DIR__ .'/mysql.inc.php');
require_once( __DIR__ .'/usersystem.inc.php');
require_once( __DIR__ .'/errlog.inc.php');
require_once( __DIR__ .'/util.inc.php');
require_once( __DIR__ .'/smarty.inc.php');
require_once( __DIR__ .'/colors.inc.php');
require_once( __DIR__ .'/sunrise.inc.php');

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
 * @const SMARTY_DEFAULT_TPL Default (fallback) Smarty-Template tpl:- or file:-ID/Name
 */
if (!defined('SMARTY_DEFAULT_TPL')) define('SMARTY_DEFAULT_TPL', 23, true);
$smarty->register_function('sqltracker', 'dbcon::sqltracker');
$smarty->register_modifier('rendertime', 'smarty_modifier_rendertime');
$smarty->assign('spaceweather', spaceweather_ticker());
//$smarty->assign('parsetime', round((microtime(true)-$parsetime_start), 2)); // PHP-Script Parsetime
$smarty->assign('parsetime_start', $parsetime_start); // PHP-Script Parsetime
smarty_modifier_rendertime('begin'); // Start Smarty-Template Rendering-Timer
