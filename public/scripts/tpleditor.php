<?php
/**
 * Load Template data for zorg Smarty Template-Editor
 *
 * @package zorg\Smarty\Tpleditor
 */

/**
 * File includes
 */
include_once __DIR__.'/../includes/tpleditor.inc.php';

/** Global Vars */
global $smarty, $db, $user;

/** Input validation */
$tpl_id = filter_input(INPUT_GET, 'tplupd', FILTER_DEFAULT, FILTER_REQUIRE_SCALAR) ?? 'new';
$smarty->assign('tpleditor_close_url', '/actions/tpleditor_close.php?'.url_params());
$username = $user->id2user($user->id, true);
$smarty->assign('rgroupids', array(0,1,2,3));
$smarty->assign('rgroupnames', array('Alle (auch nicht eingeloggte)', 'Normale User (eingeloggt)', 'Member und Sch&ouml;ne', 'Nur '.$username));
$smarty->assign('wgroupids', array(1,2,3));
$smarty->assign('wgroupnames', array('Normale User', 'Member und Sch&ouml;ne', 'Nur '.$username));
$smarty->assign('bordertypids', array(0,1,2));
$smarty->assign('bordertypnames', array('kein Rahmen', 'Rahmen mit Footer', 'Rahmen ohne Footer'));

$access_error = null;
$vars = $smarty->get_template_vars();

/**
 * Edit existing Template
 */
if ($tpl_id !== 'new' && intval($tpl_id) > 0)
{
	if (tpleditor_access_lock($tpl_id, $access_error))
	{
		/** Template content */
		$templatesQuerySql = 'SELECT *, UNIX_TIMESTAMP(created) created, UNIX_TIMESTAMP(last_update) last_update FROM templates WHERE id=?';
		$templatesQuery = $db->query($templatesQuerySql, __FILE__, __LINE__, 'SELECT FROM templates', [$tpl_id]);
		$templateData = $db->fetch($templatesQuery);

		if ($templateData && !$vars['tpleditor_frm'])
		{
			/** Template menus */
			$menusQuerySql = 'SELECT menu_id FROM tpl_menus WHERE tpl_id=?';
			$menusQuery = $db->query($menusQuerySql, __FILE__, __LINE__, 'SELECT FROM tpl_menus', [$tpl_id]);

			/** Template packages */
			$packagesQuerySql = 'SELECT package_id FROM tpl_packages WHERE tpl_id=?';
			$packagesQuery = $db->query($packagesQuerySql, __FILE__, __LINE__, 'SELECT FROM tpl_packages', [$tpl_id]);

			/** Assign Template Values to Tpleditor Frame */
			$templateData['title'] = stripslashes($templateData['title']);
			$templateData['tpl'] = stripslashes(htmlentities($templateData['tpl']));
			while ($menusData = $db->fetch($menusQuery)) {
				$templateData['menus'][] = $menusData['menu_id'];
			}
			while ($packagesData = $db->fetch($packagesQuery)) {
				$templateData['packages'][] = $packagesData['package_id'];
			}

			$smarty->assign('tpleditor_frm', $templateData);

		/** Template not found */
		} elseif (!$templateData) {
			$smarty->assign('tpleditor_strongerror', 'Template "'.$tpl_id.'" not found');
		}
	} else {
		$smarty->assign('tpleditor_strongerror', $access_error);
	}
}

/**
 * New Template
 */
elseif ($tpl_id === 'new' && !$vars['tpleditor_frm'])
{
	/** Set default values */
	$frm = [];
	$frm['read_rights'] = 0;
	$frm['write_rights'] = 3;
	$frm['border'] = 1;
	$frm['id'] = 'new';
	$frm['tpl'] = '';
	$frm['menus'] = 24;
	$smarty->assign('tpleditor_frm', $frm);
}
