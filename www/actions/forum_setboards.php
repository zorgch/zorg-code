<?php
//require_once($_SERVER['DOCUMENT_ROOT'].'/includes/main.inc.php');
require_once( __DIR__ .'/../includes/mysql.inc.php');
require_once( __DIR__ .'/../includes/usersystem.inc.php');

for($i = 0; $i < count($_POST['boards']); $i++) {
	$boards .= $_POST['boards'][$i].',';
}

$sql =	"UPDATE user SET forum_boards = '".$boards."' WHERE id = ".$user->id;
$db->query($sql, __FILE__, __LINE__);

header("Location: /forum.php");
die();
