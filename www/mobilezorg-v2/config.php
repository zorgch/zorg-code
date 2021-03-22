<?php
require_once dirname(__FILE__).'/../includes/config.inc.php';
/**
 * DEFINE CONSTANTS
 */
//setlocale(LC_TIME,"de_CH"); // Set locale to German, Switzerland
//date_default_timezone_set('Europe/Zurich');

/**
 * Environment-specific configurations
 *
 * can be set in the Apache config using
 *    SetEnv environment 'development'
 *
 * @const	DEVELOPMENT				Contains either 'true' or 'false' (boolean) - Default: false
 * @include	development.config.php	If DEVELOPMENT, load a corresponding config file containing DEV-specific settings. Was already checked to exist at define('DEVELOPMENT', true/false)
 */
//if (!defined('DEVELOPMENT')) define('DEVELOPMENT', ( (isset($_SERVER['environment']) && $_SERVER['environment'] === 'development') || file_exists( dirname(__FILE__).'/../includes/development.config.php') ? true : false ));
//if (DEVELOPMENT === true) include_once dirname(__FILE__).'/../includes/development.config.php';

// PHP Files and Folder Paths
//if (!defined('SITE_ROOT')) define('SITE_ROOT', rtrim(dirname(__FILE__), '/\\').'/..'); // Document Root to /www/ directory
//if (!defined('PHP_INCLUDES_DIR')) define('PHP_INCLUDES_DIR', SITE_ROOT.'/includes/'); // /includes/ directory
if (!defined('MOBILEZ_INCLUDES_DIR')) define('MOBILEZ_INCLUDES_DIR', INCLUDES_DIR.'mobilez/'); // /includes/ directory
if (!defined('ERROR_HANDLER_INC')) define('ERROR_HANDLER_INC', MOBILEZ_INCLUDES_DIR.'error_handler.inc.php'); // MySQL DB Connection Class file
if (!defined('MYSQL_DB_INC')) define('MYSQL_DB_INC', MOBILEZ_INCLUDES_DIR.'pdo.inc.php'); // MySQL DB Connection Class file
//if (!defined('USER_FILES_DIR')) define('USER_FILES_DIR', SITE_ROOT.'/../data/files/'); // /data/files/ directory outside the WWW-Root

// MySQL Settings
if (!defined('MYSQL_CHARSET')) define('MYSQL_CHARSET', 'utf8mb4');			// Charset for the PDO MySQL-Connection
if (!defined('DB_CHAT_TABLE')) define('DB_CHAT_TABLE', 'chat'); // Database-Table for the Chat

// File and Image Settings
if (!defined('IMAGE_FORMAT')) define('IMAGE_FORMAT', 'jpg');				// Preferred image format for saving image files from users
if (!defined('IMAGE_FORMAT_MIME')) define('IMAGE_FORMAT_MIME', 'image/jpeg'); // MIME-Type for the preferred image format: http://sitepoint.com/web-foundations/mime-types-complete-list/
if (!defined('IMG_THUMB_W')) define('IMG_THUMB_W', 320); 					// Image Thumbnails WIDTH (maximum)
if (!defined('IMG_THUMB_H')) define('IMG_THUMB_H', 180); 					// Image Thumbnails HEIGHT (maximum)
if (!defined('IMG_THUMB_SUFFIX')) define('IMG_THUMB_SUFFIX', '_s'); 		// Filename suffix for thumbnail images (small)
if (!defined('IMG_PREV_SUFFIX')) define('IMG_PREV_SUFFIX', '_m'); 			// Filename suffix for preview images (medium)
if (!defined('IMG_FULL_SUFFIX')) define('IMG_FULL_SUFFIX', '_l'); 			// Filename suffix for full size images (large)

// Site Settings
//$isSecure = false;
//if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
//    $isSecure = true;
//}
//elseif (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' || !empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on') {
//    $isSecure = true;
//}
//if (!defined('SITE_PROTOCOL')) define('SITE_PROTOCOL', ($isSecure ? 'https' : 'http'));				// TCP/IP Protocol used: HTTP or HTTPS
//if (!defined('SITE_HOSTNAME')) define('SITE_HOSTNAME', $_SERVER['SERVER_NAME']); 		// Extract the Top Level Domain
//if (!defined('SITE_URL')) define('SITE_URL', SITE_PROTOCOL.'://'.SITE_HOSTNAME); 		// Complete HTTP-URL to the website
//if (!defined('PAGETITLE_SUFFIX')) define('PAGETITLE_SUFFIX', ' - '.SITE_HOSTNAME); 		// General suffix for <title>...[suffix]</title> on every page
//if (!defined('BARBARA')) define('BARBARA', 59); 							// [z]Barbara Harris User-ID
if (!defined('BUG_CATEGORY_ID')) define('BUG_CATEGORY_ID', 23); 			// General Bug Category ID to tag new Bugs with (23 = Chat)
if (!defined('BUG_PRIORITY')) define('BUG_PRIORITY', 3); 					// General Bug Priority to tag new Bugs with (4 = Niedrig, 3 = Normal, 2 = Hoch, 1 = Sehr hoch)

// Site Paths (ending with a / slash!)
//if (!defined('INCLUDES_DIR')) define('INCLUDES_DIR', '/includes/'); 		// File-Includes directory
//if (!defined('IMAGES_DIR')) define('IMAGES_DIR', '/images/'); 				// Images directory
//if (!defined('ACTIONS_DIR')) define('ACTIONS_DIR', '/actions/'); 			// Actions directory
//if (!defined('SCRIPTS_DIR')) define('SCRIPTS_DIR', '/scripts/'); 			// Scripts directory
//if (!defined('UTIL_DIR')) define('UTIL_DIR', '/util/'); 					// Utilities directory
//if (!defined('FILES_DIR')) define('FILES_DIR', '/files/'); 					// Files directory

/**
 * FILE INCLUDES
 */
if (!require_once ERROR_HANDLER_INC) die('Requiring ERROR_HANDLER_INC failed!');
if (!require_once MYSQL_DB_INC) die('Including MYSQL_DB_INC failed!');
