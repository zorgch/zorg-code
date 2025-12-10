<?php
global $smarty, $db;

$e = $db->query('SELECT id, name FROM packages', __FILE__, __LINE__, 'SELECT-Query');
$packages = array();
while ($d = $db->fetch($e)) {
	$packages[] = $d;
}

$smarty->assign('packages', $packages);
