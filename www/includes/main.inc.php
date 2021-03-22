<?php
/**
 * @include config.inc.php Include required global site configurations
 */
require_once dirname(__FILE__).'/config.inc.php';

/**
 * RSS Feeds
 * @const RSS_URL Basic URL for RSS-Feeds
 */
if (!defined('RSS_URL')) define('RSS_URL', SITE_URL . '/?layout=rss');

/**
 * @deprecated
 * @const BODYSETTINGS (DEPRECATED) bodysettings wird verwendet, um den div nach den menüs wieder zu öffnen.
 */
if (!defined('BODYSETTINGS')) define("BODYSETTINGS", 'align="center" valign="top" style="margin: 0px 40px;"');

/**
 * Require important scripts
 * (PHP execution cannot be continued without these files)
 *
 * @include mysql.inc.php 	MySQL-DB Connection and Functions
 * @include smarty.inc.php 	Smarty Template-Engine
 * @include sunrise.inc.php Sunrise information and current Sun, Day & Night state
 * @include usersystem.inc.php Usersystem Functions and User definitions
 * @include util.inc.php 	Various Helper Functions
 */
//require_once INCLUDES_DIR.'strings.inc.php';
require_once INCLUDES_DIR.'mysql.inc.php';
require_once INCLUDES_DIR.'usersystem.inc.php';
require_once INCLUDES_DIR.'util.inc.php';
require_once INCLUDES_DIR.'smarty.inc.php';
require_once INCLUDES_DIR.'sunrise.inc.php';

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
 * @include messagesystem.inc.php Messagesystem Functions -> included via config.inc.php
 * @include peter.inc.php 		Peter Game functions
 * @include poll.inc.php 		Poll functions
 * @include quotes.inc.php 		Quotes functions
 * @include rezepte.inc.php 	Rezepte Datenbank functions
 * @include schach.inc.php  	Schach Game functions (DEPRECATED)
 * @include spaceweather.inc.php Spaceweather functions
 * @include telegrambot.inc.php Telegram Messenger Bot functions -> included via config.inc.php
 */
include_once INCLUDES_DIR.'activities.inc.php';
include_once INCLUDES_DIR.'addle.inc.php';
include_once INCLUDES_DIR.'forum.inc.php';
include_once INCLUDES_DIR.'gallery.inc.php';
include_once INCLUDES_DIR.'go_game.inc.php';
include_once INCLUDES_DIR.'graph.inc.php';
//include_once INCLUDES_DIR.'messagesystem.inc.php';
include_once INCLUDES_DIR.'peter.inc.php';
include_once INCLUDES_DIR.'poll.inc.php';
include_once INCLUDES_DIR.'quotes.inc.php';
include_once INCLUDES_DIR.'rezepte.inc.php'; // "Call to undefined function getOpenChessGames()" ["file"]=> string(48) "/Users/or/Sites/zooomclan/www/scripts/header.php" ["line"]=> int(18)
include_once INCLUDES_DIR.'schach.inc.php';
include_once INCLUDES_DIR.'spaceweather.inc.php';
//include_once INCLUDES_DIR.'telegrambot.inc.php';

/**
 * Smarty assign variables
 * Variables can be accessed in Smarty-Templates using {$variable}
 * @const SMARTY_DEFAULT_TPL Default (fallback) Smarty-Template tpl:- or file:-ID/Name
 * @const SMARTY_404PAGE_TPL 404 "Page not found" Smarty-Template reference
 */
if (!defined('SMARTY_DEFAULT_TPL')) define('SMARTY_DEFAULT_TPL', 23);
if (!defined('SMARTY_404PAGE_TPL')) define('SMARTY_404PAGE_TPL', 'file:layout/pages/404_page.tpl');
$smarty->register_function('sqltracker', 'dbcon::sqltracker');
$smarty->register_modifier('rendertime', 'smarty_modifier_rendertime');
$smarty->assign('spaceweather', spaceweather_ticker());
//$smarty->assign('parsetime', round((microtime(true)-$parsetime_start), 2)); // PHP-Script Parsetime
$smarty->assign('parsetime_start', $parsetime_start); // PHP-Script Parsetime
smarty_modifier_rendertime('begin'); // Start Smarty-Template Rendering-Timer
