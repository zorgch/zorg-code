<?php
/**
 * User Profile actions
 *
 * @package zorg\Usersystem
 * @include mysql.inc.php required
 * @include usersystem.inc.php required
 */
require_once dirname(__FILE__).'/../includes/mysql.inc.php';
require_once dirname(__FILE__).'/../includes/usersystem.inc.php';

if(isset($_GET['do']) && $_GET['do'] === 'aussperren')
{
	/** User aussperren */
	$ausgesperrt = $user->exec_aussperren($user->id, ['hour'=>$_POST['aussperrenHour'], 'day'=>$_POST['aussperrenDay'], 'month'=>$_POST['aussperrenMonth'], 'year'=>$_POST['aussperrenYear']]);
	if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> $ausgesperrt done: %s', __FUNCTION__, __LINE__, ($ausgesperrt?'true':'false')));
	if ($ausgesperrt === true)
	{
		/** User force-ausloggen */
		$user->logout();

		/** Instantiate a new, updated $user-Object (weil User ist jetzt nur noch Gast...) */
		$user = new usersystem();
		$smarty->assignByRef('user', $user);

		header('Location: /user/'.$user->id2user($user->id));
		exit;
	} else {
		header('Location: /profil.php?do=view');
		trigger_error(t('error-lockout-status', 'user'), E_USER_NOTICE);
		exit;
	}
}
