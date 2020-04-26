<?php
/**
 * Mobilezorg V2 - Reload Chat Messages
 *
 * @package zorg\Chat\Mobilezorg
 */

/**
 * FILE INCLUDES
 */
require_once dirname(__FILE__).'/config.php';
require_once MOBILEZ_INCLUDES_DIR.'chat.inc.php';

if (!require_once MOBILEZ_INCLUDES_DIR.'mobilez.smarty.inc.php') exit('ERROR: Smarty could NOT be loaded!'); // Load Smarty
echo $smarty->fetch('file:mobilez/messages.tpl');

// In case this Script was called directly...
//header("Location: ".SITE_URL."/mobilezorg-v2/?error_msg=No%20Message%20ID%20provided%21");
