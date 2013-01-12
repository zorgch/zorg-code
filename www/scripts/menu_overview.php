<?
	global $smarty, $db;
	
	$e = $db->query("SELECT * FROM menus GROUP BY name", __FILE__, __LINE__);
	$menus = array();
	while ($d = $db->fetch($e)) {
		if (tpl_permission($d['read_rights'], $d['owner'])) $menus[] = $d;
	}
	
	$smarty->assign("menus", $menus);
	
?>