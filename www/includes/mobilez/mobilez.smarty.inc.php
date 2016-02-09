<?
/**
 * This file handles the Smarty inclusion and configuration.
 * It is not part of the config.php, because smarty.inc.php
 * contains lots of sh*t that we don't need whenever working
 * or including the config.php ;)
 */
if (!defined('SMARTY_INC')) define('SMARTY_INC', PHP_INCLUDES_DIR.'/smarty.inc.php'); // Smarty Class file

/**
 * FILE INCLUDES
 */
if (!require_once SMARTY_INC) die('Including SMARTY_INC failed!');

/**
 * SMARTY
 * Special configurations
 */
$smarty->allow_constants = true;
$smarty->usesubdirs = false;
//$smarty->debugging = true;
$smarty->force_compile = true;
