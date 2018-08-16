<?php
require_once( __DIR__ .'/../includes/main.inc.php');

//$path = $_SERVER['DOCUMENT_ROOT']."/../data/smartylib/templates_c/";

if ($user->typ == USER_MEMBER) {
	$handle = opendir(SMARTY_COMPILE);

	$ctr = array("found" => 0, "deleted" => 0, "not_deleted" => 0);
	while (false !== ($file = readdir ($handle))) {
		if (preg_match("%comments%", $file)) { // Filename scheme: %%FF^FFF^FFFD1F46%%comments%3A6855.php
			if (@unlink(SMARTY_COMPILE.$file)) {
				$ctr['deleted']++;
			}else{
				$ctr['not_deleted']++;
			}
		}
	}
	closedir($handle);

	$ctr['found'] = $ctr['deleted'] + $ctr['not_deleted'];

	$smarty->assign('state_del_comments', $ctr);
	$_TPLROOT['id'] = 93;
	$smarty->assign('tplroot', $_TPLROOT);
	$smarty->display('file:layout/layout.tpl');

}else{
	user_error("access denied", E_USER_ERROR);
}
