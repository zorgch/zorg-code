<?php
//coded by [z]keep3r
require_once( __DIR__ .'/includes/main.inc.php');
require_once( __DIR__ .'/includes/setistats.inc.php');

//echo head(40, "seti");
$smarty->assign('tplroot', array('page_title' => 'seti'));
$smarty->display('file:layout/head.tpl');

$seti = New SetiStats();

/*
	$sql = "SELECT * 
			FROM user, setistats
			WHERE
			user.setimail <> '' AND
			setistats.user_id = user.id AND
			setistats.date <> '".date("d.m.y")."'";
*/

	$sql = "SELECT * FROM user WHERE setimail <> ''";	   
  	$result = $db->query($sql);

  	while ($rs = $db->fetch($result)) {
		 print "$rs[setimail]<br>";
	}


/*
$seti->setEmail('keep3r@seti.zooomclan.org');
$seti->Init();

$seti->viewStats('Workunits');
*/

//echo foot(52);
$smarty->display('file:layout/footer.tpl');
