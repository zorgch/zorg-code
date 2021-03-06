<?php
/**
 * Mobilezorg V2 Home
 *
 * @package zorg\Chat\Mobilezorg
 */

/**
 * File includes
 */
if (!require_once dirname(__FILE__).'/config.php') die('ERROR: Configurations could NOT be loaded!'); // Load the general configurations
if (!require_once MOBILEZ_INCLUDES_DIR.'mobilez.smarty.inc.php') die('ERROR: Smarty could NOT be loaded!'); // Load Smarty
if (!require_once MOBILEZ_INCLUDES_DIR.'chat.inc.php') die('ERROR: Chat could NOT be loaded!'); // The main Chat class and methods

/**
 * DO THE MAGIC STUFF
 */
/*if ($user->id >0) {*/
//mobilezChat::getChatMessages(); // Initially load Chat Messages
$smarty->assign('query_result', $mobilezChat->getChatMessages());

/**
 * LAYOUT
 */
$smarty->assign('errors', $errors);
$smarty->display('file:mobilez/main.tpl');
