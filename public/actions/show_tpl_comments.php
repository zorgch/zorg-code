<?php
/**
 * Show / Hide Comments
 *
 * @package zorg\Usersystem
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
$set_usershowcomments = (string)filter_input(INPUT_GET, 'usershowcomments', FILTER_VALIDATE_INT) ?? null; // $_GET['usershowcomments'], 0=hide / 1=show

/** Comments ein/ausblenden */
if (isset($set_usershowcomments) && $set_usershowcomments != $user->show_comments)
{
	$db->update('user', $user->id, ['show_comments' => $set_usershowcomments], __FILE__, __LINE__, 'UPDATE user SET show_comments');
	$user->show_comments = $set_usershowcomments;
}

/** Redirect to previous page */
unset($_GET['usershowcomments']);
header('Location: /?'.url_params());
exit;
