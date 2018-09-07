<?
/* Ultra Debug:
function shutdown(){
  var_dump(error_get_last());
} register_shutdown_function('shutdown');*/

/**
 * FILE INCLUDES
 */
if (!require_once 'config.php') die('ERROR: Configurations could NOT be loaded!'); // Load the general configurations
if (!require_once __DIR__ .'/../includes/mobilez/mobilez.smarty.inc.php') die('ERROR: Smarty could NOT be loaded!'); // Load Smarty
if (!require_once __DIR__ .'/../includes/mobilez/chat.inc.php') die('ERROR: Chat could NOT be loaded!'); // The main Chat class and methods

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
