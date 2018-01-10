<?
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/main.inc.php');
require_once($_SERVER['DOCUMENT_ROOT']."/includes/tpleditor.inc.php");

	
	tpleditor_unlock($_GET['tplupd']);
	
	if (!$_GET['location']) {
		if ($_GET['tplupd'] == 'new') {
			$_GET['location'] = base64_encode('/?');
		}else{
			$_GET['location'] = base64_encode("/?tpl=$_GET[tplupd]");
		}
	}
	
	unset($_GET['tpleditor']);
	unset($_GET['tplupd']);
	 
	header('Location: '.base64_decode($_GET['location']));
	die();
