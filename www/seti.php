<?
//coded by [z]keep3r
	require_once($_SERVER['DOCUMENT_ROOT'].'/includes/main.inc.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/includes/layout.inc.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/includes/main.inc.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/includes/setistats.inc.php');

echo head(40, "seti");

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

echo foot(52);
?>