<?php
/**
 * @const DEVELOPMENT Contains either 'true' or 'false' (boolean)
 *
 * Can be set in the Apache config using
 *    SetEnv environment 'development'
 */
define('DEVELOPMENT', ( isset($_SERVER['environment']) && $_SERVER['environment'] == 'development' ? true : false ), true);

/**
 * If DEVELOPMENT, load a corresponding config file
 * @include	development.config.php File containing DEV-specific settings
 */
if (DEVELOPMENT) include_once( __DIR__ . '/development.config.php');
