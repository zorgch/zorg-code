<?php
global $smarty, $db, $user;

/** First get all menus with the corresponding tpl_id */
$menusQuery = $db->query('SELECT id, name, tpl_id FROM menus', __FILE__, __LINE__, 'SELECT-Query "menus"');
while ($menusQueryResult = $db->fetch($menusQuery)) {
	$menus[$menusQueryResult['tpl_id']] = $menusQueryResult; // set key=tpl_id so we can delete specific entris later
	$menusTplids[] = $menusQueryResult['tpl_id']; // build separate Array containing menus tpl_id to validate
}
if (DEVELOPMENT === true) error_log(sprintf('[DEBUG] <%s:%d> array($menus): %s', __FILE__, __LINE__, print_r($menus,true)));

/** Create a comma speared list from only the Array with tpl_id, to check permissions */
$tplidsList = implode(',', $menusTplids);
if (DEVELOPMENT === true) error_log(sprintf('[DEBUG] <%s:%d> implode($menus): %s', __FILE__, __LINE__, $tplidsList));

/** Check permissions of the associated tpl_id of each menu */
$tplQuery = $db->query('SELECT id, read_rights, owner FROM templates WHERE id IN ('.$tplidsList.')', __FILE__, __LINE__, 'SELECT-Query "templates"');
while ($tplQueryResult = $db->fetch($tplQuery)) {
	/** Remove any menu from the Array, if permissions are denied for the user */
	if (!tpl_permission($tplQueryResult['read_rights'], $tplQueryResult['owner'])) unset($menus[$tplQueryResult['id']]);
	/*if ($tplQueryResult['read_rights'] === 1 && !$user->is_loggedin()) unset($menus[$tplQueryResult['id']]);
	if ($tplQueryResult['read_rights'] === 2 && $user->typ < USER_MEMBER) unset($menus[$tplQueryResult['id']]);
	if ($tplQueryResult['read_rights'] === 3  && $tplQueryResult['owner'] != $user->id) unset($menus[$tplQueryResult['id']]);*/
}

if (DEVELOPMENT === true) error_log(sprintf('[DEBUG] <%s:%d> array($menus) - cleaned: %s', __FILE__, __LINE__, print_r($menus,true)));
$smarty->assign('menus', $menus);
