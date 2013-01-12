<?
	require_once($_SERVER['DOCUMENT_ROOT']."/includes/smarty.inc.php");
	require_once($_SERVER['DOCUMENT_ROOT']."/includes/usersystem.inc.php");
	
	$path = $_SERVER['DOCUMENT_ROOT']."/smartylib/templates_c/";
	
	$handle = opendir($path);

	$ctr = array("found" => 0, "deleted" => 0, "not_deleted" => 0);
	while (false !== ($file = readdir ($handle))) {
		if (@unlink($path.$file)) {
			$ctr['deleted']++;
		}else{
			$ctr['not_deleted']++;
		}
	}
	closedir($handle);
	
	$ctr['found'] = $ctr['deleted'] + $ctr['not_deleted'];
	
	echo $ctr['deleted'];
	//$smarty->assign("state_del_comments", $ctr);
	//$smarty->display("file:main.html");
		
?>