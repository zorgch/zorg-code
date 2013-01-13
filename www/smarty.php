<?
ini_set('display_errors',1);
error_reporting(E_ALL);	

//global $zorg, $zooomclan, $db;

require_once($_SERVER['DOCUMENT_ROOT'].'/includes/main.inc.php');


if (!isset($_GET['tpl']) && !isset($_GET['word'])) {
//	if ($zorg == true) { //print('<br />er isch bi dae tpl def...');
		//print('Er weiss immerno, dass es zorg.ch isch...<br />');
		$_GET['tpl'] = 23;
	
	/*
	} elseif ($zooomclan == true) {
		//print('Er weiss immerno, dass es zooomclan.ch isch...<br />');
		$_GET['tpl'] = 23;
	} else {
		//print('Er het vergesse wod bisch, und bringt di drum uf zorg.ch...<br />');
		$_GET['tpl'] = 671;
	}

	*/
}




include_once($_SERVER['DOCUMENT_ROOT'].'/includes/smarty.inc.php');


$smarty->display("file:main.html");
/*
if ($zorg == true) {
	//print("<br />Er wott s Zorg.ch HTML azeige...");
	//set_page_style($user->id, $zorg);
	$smarty->display("file:zorg_main.html");

} elseif ($zooomclan == true) {
	//set_page_style($user->id, FALSE, $zooomclan);
	$smarty->display("file:main.html");

} else {
	//set_page_style($user->id, $zorg);
	$smarty->display("file:zorg_main.html");
}

*/




?>