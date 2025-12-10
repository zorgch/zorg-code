<?php
/**
 * Template Editor close Template Action
 * @package zorg\Smarty\Tpleditor
 */
require_once __DIR__.'/../includes/config.inc.php';
require_once INCLUDES_DIR.'main.inc.php';
require_once INCLUDES_DIR.'tpleditor.inc.php';

/** Validate params */
$updated_tplid = ($_GET['tplupd'] === 'new' ? 'new' : (filter_input(INPUT_GET, 'tplupd', FILTER_VALIDATE_INT) ?? null)); // $_GET['tplupd']
unset($_GET['tplupd']);
$return_url = (isset($_GET['location']) ? base64url_decode(filter_input(INPUT_GET, 'location', FILTER_SANITIZE_FULL_SPECIAL_CHARS)) : '/index.php?tpl='.$updated_tplid); // $_GET['location']
unset($_GET['tpleditor']);

/** Unlock etc. only when Tpl ID not "new" */
if (is_numeric($updated_tplid) && $updated_tplid>0)
{
    tpleditor_unlock($updated_tplid);
}

if (empty($return_url))
{
	if ($updated_tplid === 'new') $return_url = '/';
	else $return_url = '/tpl/'.$updated_tplid;
}

header('Location: '.$return_url);
exit;
