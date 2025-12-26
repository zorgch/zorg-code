<?php
/**
 * User Profile actions
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
$sperrMiUse = filter_input(INPUT_GET, 'do', FILTER_SANITIZE_SPECIAL_CHARS) ?? null; // $_GET['do'] ("aussperren")
$sperrStunde = filter_input(INPUT_POST, 'aussperrenHour', FILTER_VALIDATE_INT) ?? null; // $_POST['aussperrenHour']
$sperrTag = filter_input(INPUT_POST, 'aussperrenDay', FILTER_VALIDATE_INT) ?? null; // $_POST['aussperrenDay']
$sperrMonat = filter_input(INPUT_POST, 'aussperrenMonth', FILTER_VALIDATE_INT) ?? null; // $_POST['aussperrenMonth']
$sperrJahr = filter_input(INPUT_POST, 'aussperrenYear', FILTER_VALIDATE_INT) ?? null; // $_POST['aussperrenYear']

if ($sperrMiUse === 'aussperren')
{
	/** User aussperren */
	$ausgesperrt = $user->exec_aussperren($user->id, ['hour'=>$sperrStunde, 'day'=>$sperrTag, 'month'=>$sperrMonat, 'year'=>$sperrJahr]);
	zorgDebugger::log()->debug('$ausgesperrt bis %d.%d.%d um %d Uhr DONE: %s', [$sperrTag, $sperrMonat, $sperrJahr, $sperrStunde, ($ausgesperrt?'true':'false')]);

	if ($ausgesperrt)
	{
		/** User force-ausloggen */
		$user->logout();

		/** Instantiate a new, updated $user-Object (weil User ist jetzt nur noch Gast...) */
		$user = new usersystem();
		$smarty->assign('user', $user);

		header('Location: /user/'.$user->id2user($user->id));
		exit;
	}
	else {
		header('Location: /profil.php?do=view');
		trigger_error(t('error-lockout-status', 'user'), E_USER_NOTICE);
		exit;
	}
}
