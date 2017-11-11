<?
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
* Define a global SENDER e-mail addresses (From:)
* @const ZORG_EMAIL A valid e-mailadress such as info@zooomclan.org
* @const SERVER_EMAIL Don't edit! This grabs the Admin E-Mail from the apache2 config
*/
define('ZORG_EMAIL', 'info@zorg.ch', true);
define('ZORG_ADMIN_EMAIL', $_SERVER['SERVER_ADMIN'], true);

/**
 * Include important scripts
 */
require_once(SITE_ROOT.'/includes/errlog.inc.php');
require_once(SITE_ROOT.'/includes/mysql.inc.php');
require_once(SITE_ROOT.'/includes/usersystem.inc.php');
?>