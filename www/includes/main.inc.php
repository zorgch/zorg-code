<?php
/**
 * Require important scripts
 * (PHP execution cannot be continued without these files)
 *
 * @include config.inc.php Include required global site configurations
 * @include mysql.inc.php 	MySQL-DB Connection and Functions
 * @include smarty.inc.php 	Smarty Template-Engine
 * @include sunrise.inc.php Sunrise information and current Sun, Day & Night state
 * @include usersystem.inc.php Usersystem Functions and User definitions
 * @include util.inc.php 	Various Helper Functions
 * @include core.model.php MCV Models -> FIXME requires namespace & use cleanup first
 */
require_once __DIR__.'/config.inc.php';
require_once INCLUDES_DIR.'mysql.inc.php';
require_once INCLUDES_DIR.'usersystem.inc.php';
require_once INCLUDES_DIR.'util.inc.php';
require_once INCLUDES_DIR.'smarty.inc.php';

/**
 * Define and include the MCV Controllers and initialise Layout related settings.
 * @include layout.controller.php MVC Controller for Layout
 */
//require_once MODELS_DIR.'core.model.php'; // FIXME requires namespace & use cleanup first
require_once CONTROLLERS_DIR.'layout.controller.php';
if (DEVELOPMENT === true) error_log(sprintf('[DEBUG] <%s:%d> new MVC\Controller\Layout()', __FILE__, __LINE__));
$zorgLayout = new MVC\Controller\Layout();

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
 */
$smarty->register_function('sqltracker', 'dbcon::sqltracker');
$smarty->register_modifier('rendertime', 'smarty_modifier_rendertime');
$smarty->assign('spaceweather', spaceweather_ticker());
//$smarty->assign('parsetime', round((microtime(true)-$parsetime_start), 2)); // PHP-Script Parsetime
$smarty->assign('parsetime_start', $parsetime_start); // PHP-Script Parsetime
smarty_modifier_rendertime('begin'); // Start Smarty-Template Rendering-Timer
