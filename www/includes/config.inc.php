<?php
error_reporting(E_ALL & ~E_NOTICE);
/** @TODO: use error_reporting($WEBSITE_IS_LIVE ? 0 : E_STRICT); => with DEVELOPMENT constant */

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
 * If DEVELOPMENT, load a corresponding config file
 * @include	development.config.php File containing DEV-specific settings
 */
if (DEVELOPMENT) include_once( __DIR__ . '/development.config.php');

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
* @const ERRORLOG_DIR sets the directory for logging the custom user_errors as in
* @see errlog.inc.php, zorgErrorHandler(), user_error()
*/
if (!defined('ERRORLOG_DIR')) define('ERRORLOG_DIR', SITE_ROOT . '/../data/errlog/', true);
if (!defined('FILES_DIR')) define('FILES_DIR', SITE_ROOT . '/../data/files/', true);

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
if (!defined('INCLUDES_DIR')) define('INCLUDES_DIR', '/includes/', true);
if (!defined('IMAGES_DIR')) define('IMAGES_DIR', '/images/', true);
if (!defined('ACTIONS_DIR')) define('ACTIONS_DIR', '/actions/', true);
if (!defined('SCRIPTS_DIR')) define('SCRIPTS_DIR', '/scripts/', true);
if (!defined('UTIL_DIR')) define('UTIL_DIR', '/util/', true);
if (!defined('JS_DIR')) define('JS_DIR', '/js/', true);
if (!defined('CSS_DIR')) define('CSS_DIR', '/css/', true);
