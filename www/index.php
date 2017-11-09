<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/main.inc.php');

if (!isset($_GET['tpl']) && !isset($_GET['word'])) {
	$_GET['tpl'] = 23;
}

include_once($_SERVER['DOCUMENT_ROOT'].'/includes/smarty.inc.php');
$smarty->display("file:main.html");
