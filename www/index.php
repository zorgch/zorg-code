<?php
require_once(rtrim($_SERVER['DOCUMENT_ROOT'],'/\\').'/includes/main.inc.php');

if (!isset($_GET['tpl']) && !isset($_GET['word'])) {
	$_GET['tpl'] = 23;
}

include_once(SITE_ROOT.'/includes/smarty.inc.php');
$smarty->display("file:main.html");
