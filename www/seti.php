<?php
/**
 * SETI@Home Accounts
 * coded by [z]keep3r
 *
 * @author [z]keep3r
 * @package zorg\SETI
 */

/**
 * File includes
 * @include main.inc.php Includes the Main Zorg Configs and Methods
 * @include setiathome.inc.php Includes SETI@home setiathome() Class and Methods
 * @include core.model.php required
 */
require_once( __DIR__ .'/includes/main.inc.php');
require_once( __DIR__ .'/includes/setistats.inc.php');
require_once( __DIR__ .'/models/core.model.php');

/**
 * Initialise MVC Model
 */
$model = new MVC\Seti();

//echo head(40, "seti");
//$smarty->assign('tplroot', array('page_title' => 'seti'));

if ($user->is_loggedin() && $user->typ >= USER_MEMBER)
{
	$model->showAdminpage($smarty);
	$smarty->display('file:layout/head.tpl');

	/**
	 * Initialise SETI Stats Class-Object
	 */
	$seti = new SetiStats();
	
	/*
		$sql = "SELECT * 
				FROM user, setistats
				WHERE
				user.setimail <> '' AND
				setistats.user_id = user.id AND
				setistats.date <> '".date("d.m.y")."'";
	*/
	
		$sql = 'SELECT * FROM seti WHERE account IS NOT NULL AND account != ""';	   
	  	$result = $db->query($sql);
	  	while ($rs = $db->fetch($result))
	  	{
			 print $rs['account'].'<br>';
		}
	
	/*
	$seti->setEmail('keep3r@seti.zooomclan.org');
	$seti->Init();
	
	$seti->viewStats('Workunits');
	*/
}
/** Nicht eingeloggte User / keine Member */
else {
	$model->showOverview($smarty);
	$smarty->display('file:layout/head.tpl');
	echo 'Hier dürfen nur Member was machen. Tschau.';
}

//echo foot(52);
$smarty->display('file:layout/footer.tpl');
