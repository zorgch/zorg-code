<?php
/**
 * @FIXME NOT IMPLEMENTED IN DATABASE!
 *
 * Show and Hide Favourite Templates
 * @package zorg\Templates
 */
require_once dirname(__FILE__).'/../includes/main.inc.php';	

if (isset($_GET['usershowfavourite']) && !empty($_GET['usershowfavourite'])) $doShowFavourite = sanitize_userinput($_GET['usershowfavourite']);

if (isset($doShowFavourite))
{
	$db->query('UPDATE user SET tpl_favourite_show='.$doShowFavourite.' WHERE id='.$user->id, __FILE__, __LINE__, 'UPDATE user');
	$user->tpl_favourite_show = $doShowFavourite;

	unset($_GET['usershowfavourite']);
	header("Location: /?".url_params());
	die();
}
