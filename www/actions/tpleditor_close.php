<?php
/**
 * Template Editor close Template Action
 * @package zorg\Smarty\Tpleditor
 */
require_once dirname(__FILE__).'/../includes/main.inc.php';
require_once INCLUDES_DIR.'tpleditor.inc.php';

/** Unlock etc. only when Tpl ID not "new" */
if (isset($_GET['tplupd']) && is_numeric($_GET['tplupd']) && $_GET['tplupd'] !== 'new')
{
    tpleditor_unlock($_GET['tplupd']);
}

if (!isset($_GET['location']) || empty($_GET['location']))
{
	if ($_GET['tplupd'] == 'new') $_GET['location'] = base64_urlencode('/');
	else $_GET['location'] = base64_urlencode('/tpl/'.$_GET['tplupd']);
}

unset($_GET['tpleditor']);
unset($_GET['tplupd']);

header('Location: '.base64_urldecode($_GET['location']));
exit;
