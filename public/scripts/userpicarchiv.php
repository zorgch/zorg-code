<?php
/**
 * Userpics archive
 * @package zorg\Usersystem
 */
global $db, $smarty;

function extract_timestamp($string) {
	$startchr = '_';
	$endchr = '.';
	$startpos = strpos($string, $startchr);
	$endpos = strrpos($string, $endchr);
	$startpos_korr = $startpos++; // wert korrigieren, dass das suchzeichen nicht auch mitgenommen wird
	$endpos_korr = $endpos-3; // dito
	if($timestamp = substr($string, $startpos_korr, $endpos_korr-$startpos_korr)) {
		return $timestamp;
	}
}

$filelist = array();
$timelist = array();

$archiv_pfad = opendir(USER_IMGPATH."archiv/");
/*while (false !== ($file = readdir($archiv_pfad))) {
	if (strstr($file, '_tn') && preg_match("_tn", $file) && $file !== "." && $file !== "..") {

		array_push($filelist, array('userpic' => $file));
		array_push($timelist, array('userpictime' => extract_timestamp($file)));

	}
}
closedir($archiv_pfad);*/

while (false !== ($file = readdir($archiv_pfad))) {
	if (strstr($file, '_tn') && $file !== "." && $file !== "..") {
		$filelist[] = array('userpic' => $file, 'userpictime' => extract_timestamp($file));
	}
}



$smarty->assign('userpics', $userpics);


//$smarty->assign('userpics', array($filelist, $timelist));
//$smarty->assign(array("userpics" => $filelist, "userpicstime" => $timelist));
//$smarty->assign($filelist);
//$smarty->assign("userpics", $filelist);
//$smarty->assign("userpicstimes", $timelist);
