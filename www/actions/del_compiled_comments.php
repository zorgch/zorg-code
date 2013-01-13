<?
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/main.inc.php');
require_once($_SERVER['DOCUMENT_ROOT']."/includes/smarty.inc.php");
	require_once($_SERVER['DOCUMENT_ROOT']."/includes/usersystem.inc.php");
	
	$path = $_SERVER['DOCUMENT_ROOT']."/../data/smartylib/templates_c/";
	
	if ($user->typ == USER_MEMBER) {
		$handle = opendir($path);

		$ctr = array("found" => 0, "deleted" => 0, "not_deleted" => 0);
		while (false !== ($file = readdir ($handle))) {
			if (preg_match("/comments/", $file)) {
				if (@unlink($path.$file)) {
					$ctr['deleted']++;
				}else{
					$ctr['not_deleted']++;
				}
			}
		}
		closedir($handle);
		
		$ctr['found'] = $ctr['deleted'] + $ctr['not_deleted'];
		
		$smarty->assign("state_del_comments", $ctr);
		$smarty->display("file:main.html");
		
	}else{
		user_error("access denied", E_USER_ERROR);
	}
?>