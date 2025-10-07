<?php
/**
 * Show and Hide Favourite Templates
 *
 * //TODO This has NOT been implemented fully (e.g. Table Col missing!)
 * @package zorg\Templates
 */

/**
 * File includes
 */
require_once __DIR__.'/../includes/config.inc.php';

/** User not logged in? Error in his face! */
if (!$user->is_loggedin()) {
	http_response_code(403); // Set response code 403 (Forbidden)
	user_error('Access denied', E_USER_ERROR);
}

/** Validate passed Parameters */
$doShowFavourite = (string)filter_input(INPUT_GET, 'usershowfavourite', FILTER_VALIDATE_INT) ?? null; // $_GET['usershowfavourite'], 0=hide / 1=show

//TODO NOT IMPLEMENTED IN DATABASE!
// if (isset($doShowFavourite) && $doShowFavourite != $user->tpl_favourite_show)
// {
// 	$db->update('user', $user->id, ['tpl_favourite_show' => $doShowFavourite], __FILE__, __LINE__, 'UPDATE user SET tpl_favourite_show');
// 	$user->tpl_favourite_show = $doShowFavourite;

	unset($_GET['usershowfavourite']);
	header("Location: /?".url_params());
	exit;
// }
