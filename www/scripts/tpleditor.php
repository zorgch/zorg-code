<?php
/**
 * Load Template data for zorg Smarty Template-Editor
 *
 * @package zorg\Smarty\Tpleditor
 */

/**
 * File includes
 */
include_once( __DIR__ .'/../includes/tpleditor.inc.php');

global $smarty, $db, $user;

$tpl_id = (!$_GET['tplupd'] ? 'new' : $_GET['tplupd']);
$smarty->assign('tpleditor_close_url', '/actions/tpleditor_close.php?'.url_params());
$username = $user->id2user($user->id, true);
$smarty->assign('rgroupids', array(0,1,2,3));
$smarty->assign('rgroupnames', array('Alle (auch nicht eingeloggte)', 'Normale User (eingeloggt)', 'Member und Sch&ouml;ne', 'Nur '.$username));
$smarty->assign('wgroupids', array(1,2,3));
$smarty->assign('wgroupnames', array('Normale User', 'Member und Sch&ouml;ne', 'Nur '.$username));
$smarty->assign('bordertypids', array(0,1,2));
$smarty->assign('bordertypnames', array('kein Rahmen', 'Rahmen mit Footer', 'Rahmen ohne Footer'));

$access_error = '';
$vars = $smarty->get_template_vars();

if (tpleditor_access_lock($tpl_id, $access_error))
{
	/**
	 * Edit existing Template
	 */
	if ($tpl_id != 'new')
	{
		/** Template content */
		$templatesQuerySql = 'SELECT *, unix_timestamp(created) created, unix_timestamp(last_update) last_update FROM templates WHERE id='.$tpl_id;
		$templatesQuery = $db->query($templatesQuerySql, __FILE__, __LINE__, 'SELECT FROM templates');
		$templateData = $db->fetch($templatesQuery);

		if ($templateData && !$vars['tpleditor_frm'])
		{
			/** Template menus */
			$menusQuerySql = 'SELECT menu_id FROM tpl_menus WHERE tpl_id='.$tpl_id;
			$menusQuery = $db->query($menusQuerySql, __FILE__, __LINE__, 'SELECT FROM tpl_menus');

			/** Template packages */
			$packagesQuerySql = 'SELECT package_id FROM tpl_packages WHERE tpl_id='.$tpl_id;
			$packagesQuery = $db->query($packagesQuerySql, __FILE__, __LINE__, 'SELECT FROM tpl_packages');

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
	}

	/**
	 * New Template
	 */
	elseif ($tpl_id == 'new' && !$vars['tpleditor_frm'])
	{
		/** Set default values */
		$frm = array();
		$frm['read_rights'] = 0;
		$frm['write_rights'] = 3;
		$frm['border'] = 1;
		$frm['id'] = 'new';
		$frm['tpl'] = '';
		$frm['menus'] = 24;
		$smarty->assign('tpleditor_frm', $frm);
	}
} else {	 
	$smarty->assign('tpleditor_strongerror', $access_error);
}
