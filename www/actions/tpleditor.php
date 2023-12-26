<?php
/**
 * zorg Smarty Template-Editor Save Actions
 *
 * @package zorg\Smarty\Tpleditor
 */

/**
 * File includes
 */
require_once dirname(__FILE__).'/../includes/tpleditor.inc.php';

/** Initialize Vars */
$error = null;
$state = null;
$access_error = null;
if (isset($_POST['frm']) && is_array($_POST['frm'])) $frm = (array)$_POST['frm'];
if (isset($_GET['tpleditor']) && (is_bool($_GET['tpleditor']) || is_numeric($_GET['tpleditor']))) $enable_tpleditor = (bool)$_GET['tpleditor'];
unset($_GET['tpleditor']);
if (isset($_GET['tplupd']) && is_numeric($_GET['tplupd']) && $_GET['tplupd'] > 0) $updated_tplid = (int)$_GET['tplupd'];
unset($_GET['tplupd']);
if (isset($_GET['location']) && is_string($_GET['location'])) $return_url = base64url_decode((string)$_GET['location']);
unset($_GET['location']);

/**
 * Save Template
 */
if (tpleditor_access_lock($updated_tplid, $access_error))
{
	/** check fields and put error msg. */
	if ($frm['read_rights']<0 || $frm['read_rights']>3) $error .= t('invalid-permissions-read', 'tpl');
	if ($frm['write_rights']<1 || $frm['write_rights']>3) $error .= t('invalid-permissions-write', 'tpl');
	if ($frm['border']<0 || $frm['border']>2) $error .= t('invalid-border', 'tpl');
	if (strlen(preg_replace("(\W*)", "", $frm['tpl'])) <= 0) $error .= t('error-empty', 'tpl');
	if (strlen($frm['word']) > 30) $error .= t('error-word-toolong', 'tpl', $frm['word']);
	if (preg_match("([^a-zA-Z0-9_-])", $frm['word']))
	{
		$error .= t('error-word-validation', 'tpl', $frm['word']);
		$frm['word'] = '';
	}

	if (!smarty_brackets_ok($frm['tpl'], $brack_err)) $error .= $brack_err;

	// FIXME deaktiviert bis ein besserer syntax checker gebaut ist. (biko)
	/*
	$syntaxerr = smarty_remove_invalid_html($frm['tpl']);
	if ($syntaxerr) $error .= "<br>HTML Syntax Error: $syntaxerr <br>";
	*/

	if (empty($error) || !$error)
	{
		$frm['id'] = htmlentities($frm['id'], ENT_QUOTES);
		$frm['tpl'] = $frm['tpl'];
		$frm['title'] = sanitize_userinput($frm['title']);
		$frm['sidebar_tpl'] = (empty($frm['sidebar_tpl']) ? 'NULL' : htmlentities($frm['sidebar_tpl'], ENT_QUOTES));
		$frm['page_title'] = htmlentities($frm['page_title'], ENT_NOQUOTES);
		$frm['border'] = (isset($frm['border']) && is_numeric($frm['border']) && $frm['border'] <= 2 ? $frm['border'] : '1');; // ENUM('0','1','2'), Default: '1'
		$frm['allow_comments'] = (isset($frm['allow_comments']) && !empty($frm['allow_comments']) ? '1' : '0'); // ENUM('0','1'), Default: '0'

		/**
		 * NEW TEMPLATE
		 */
		if ($frm['id'] === 'new')
		{
			if (DEVELOPMENT === true) error_log(sprintf('[DEBUG] <%s:%d> New Template Content: %s', __FILE__, __LINE__, print_r($frm, true)));
			$frm['id'] = $db->insert('templates', [
											 'title' => $frm['title']
											,'page_title' => $frm['page_title']
											,'word' => $frm['word']
											,'tpl' => $frm['tpl']
											,'border' => $frm['border']
											,'read_rights' => $frm['read_rights']
											,'write_rights' => $frm['write_rights']
											,'sidebar_tpl' => $frm['sidebar_tpl']
											,'allow_comments' => $frm['allow_comments']
											,'error' => null
											,'owner' => $user->id
											,'update_user' => $user->id
											,'created' => timestamp(true)
											,'last_update' => timestamp(true)
										  ], __FILE__, __LINE__, 'Add new Template');
			if (DEVELOPMENT === true) error_log(sprintf('[DEBUG] <%s:%d> New Template ID: %s', __FILE__, __LINE__, $frm['id']));

			/** Only on success... */
			if ($frm['id'] > 0 && $frm['id'] != false)
			{
				Thread::setRights('t', $frm['id'], $frm['read_rights']);
				$db->query('INSERT INTO templates_backup SELECT * FROM templates WHERE id=?', __FILE__, __LINE__, 'Copy Template to templates_backup', [$frm['id']]);

				$updated_tplid = $frm['id'];
				$return_url = '/?tpl='.$updated_tplid;
				$return_url .= '&created';
				$smarty->assign('tplupdnew', 1);
				$state = t('created', 'tpl', $frm['id']);

				/** Activity Eintrag auslösen */
				Activities::addActivity($user->id, 0, t('activity-newpage', 'tpl', [ $updated_tplid, $frm['title'] ]), 't');
			}
			/** Template has not been added */
			else {
				$error .= t('error-create', 'tpl');
			}

		/**
		 * UPDATE EXISTING TEMPLATE
		 */
		} elseif ($frm['id'] > 0) {
			/** Backup current version */
			$db->query('REPLACE INTO templates_backup SELECT * FROM templates WHERE id=? AND UNIX_TIMESTAMP(?)-UNIX_TIMESTAMP(last_update) > (60*60*24*3)', __FILE__, __LINE__, 'REPLACE INTO templates_backup', [$frm['id'], timestamp(true)]);

			/*if ($frm['word']) $set_word = ', word="'.$frm['word'].'"';*/
			$templateUpdateParams = [
							 'title' => $frm['title']
							,'page_title' => $frm['page_title']
							,'tpl' => $frm['tpl']
							,'border' => $frm['border']
							,'read_rights' => $frm['read_rights']
							,'write_rights' => $frm['write_rights']
							,'sidebar_tpl' => $frm['sidebar_tpl']
							,'allow_comments' => $frm['allow_comments']
							,'error' => null
							,'owner' => $user->id
							,'update_user' => $user->id
							,'last_update' => timestamp(true)
						];
			if ($frm['word']) $templateUpdateParams = array_merge($templateUpdateParams, ['word' => $frm['word']]);
			if (DEVELOPMENT === true) error_log(sprintf('[DEBUG] <%s:%d> Update Template SQL-Params: %s', __FILE__, __LINE__, print_r($templateUpdateParams,true)));
			$result = $db->update('templates', ['id', $frm['id']], $templateUpdateParams, __FILE__, __LINE__, 'Update Template');
			Thread::setRights('t', $frm['id'], $frm['read_rights']);
			if (DEVELOPMENT === true) error_log(sprintf('[DEBUG] <%s:%d> Template ID #%d updated', __FILE__, __LINE__, $frm['id']));
		}

		if ((empty($error) || !$error) && $frm['id'] > 0)
		{
			/** Menus: remove all links between Template & Menus, relink selected Menus */
			$db->query('DELETE FROM tpl_menus WHERE tpl_id=?', __FILE__, __LINE__, 'DELETE FROM tpl_menus', [$frm['id']]);
			if (!empty($_POST['frm']['menus']))
			{
				$tplmenusInsertData = null;
				foreach ($_POST['frm']['menus'] as $menu_id) {
					/** Note: only works when getting Array directly from $_POST, not via $frm.
					Don't know why, cost me like 2 hours to figure this out WTF */
					if (!empty($menu_id)) $tplmenusInsertData[] = sprintf('(%d, %d)', $frm['id'], $menu_id);
				}
				$db->query('INSERT INTO tpl_menus (tpl_id, menu_id) VALUES '.implode(',',$tplmenusInsertData), __FILE__, __LINE__, 'Link Template to selected Menus'); // add new
				if (DEVELOPMENT === true) error_log(sprintf('[DEBUG] <%s:%d> Template ID #%d linked to Menus: %s', __FILE__, __LINE__, $frm['id'], print_r($tplmenusInsertData, true)));
			}

			/** Packages: remove all links between Template & Packages, relink selected Packages */
			$db->query('DELETE FROM tpl_packages WHERE tpl_id=?', __FILE__, __LINE__, 'DELETE FROM tpl_packages', [$frm['id']]);
			if (!empty($_POST['frm']['packages']))
			{
				$tplpackagesInsertData = null;
				foreach ($_POST['frm']['packages'] as $package_id) {
					/** Note: only works when getting Array directly from $_POST, not via $frm.
					Don't know why, cost me like 2 hours to figure this out WTF */
					if (!empty($package_id)) $tplpackagesInsertData[] = sprintf('(%d, %d)', $frm['id'], $package_id);
				}
				$db->query('INSERT INTO tpl_packages (tpl_id, package_id) VALUES '.implode(',',$tplpackagesInsertData), __FILE__, __LINE__, 'Link Template to selected Packages'); // add new
				if (DEVELOPMENT === true) error_log(sprintf('[DEBUG] <%s:%d> Template ID #%d linked to Packages: %s', __FILE__, __LINE__, $frm['id'], print_r($tplpackagesInsertData, true)));
			}
		}
	}

	/**
	 * Force recompile a Smarty Template
	 *
	 * @author [z]biko
	 * @author IneX
	 * @version 2.0
	 * @since 1.0 `[z]biko` procedure added intially
	 * @since 2.0 `03.01.2019` `IneX` Fixed Bug #768: must also recompile template based on /page/word (not only /tpl/id )
	 */
	if (empty($error) || !$error)
	{
		/** Compile Templated - TPL-ID based */
		//if (DEVELOPMENT === true) error_log(sprintf('[DEBUG] <%s:%d> $smarty-compile() $frm: %s', __FILE__, __LINE__, print_r($frm,true)));
		if (DEVELOPMENT === true) error_log(sprintf('[DEBUG] <%s:%d> $smarty-compile(%s)', __FILE__, __LINE__, 'tpl:'.$frm['id']));
		try {
			if (!$smarty->compile('tpl:'.$frm['id'], $compile_err))
			{
				for ($i=0; $i<sizeof($compile_err); $i++) {
					$error .= "<br>".$compile_err[$i]."<br>";
				}
				if (DEVELOPMENT === true) error_log(sprintf('[DEBUG] <%s:%d> $smarty-compile(tpl): ERROR (%s)', __FILE__, __LINE__, $error));
			} else {
				if (DEVELOPMENT === true) error_log(sprintf('[DEBUG] <%s:%d> $smarty-compile(tpl): SUCCESS', __FILE__, __LINE__));
			}

			/** Compile Templated - TPL-Word based (if applicable) */
			if (!empty($frm['word']))
			{
				if (DEVELOPMENT === true) error_log(sprintf('[DEBUG] <%s:%d> $smarty-compile(%s)', __FILE__, __LINE__, 'word:'.$frm['word']));
				if (!$smarty->compile('word:'.$frm['word'], $compile_err))
				{
					for ($i=0; $i<sizeof($compile_err); $i++) {
						$error .= "<br>".$compile_err[$i]."<br>";
					}
					if (DEVELOPMENT === true) error_log(sprintf('[DEBUG] <%s:%d> $smarty-compile(word): ERROR (%s)', __FILE__, __LINE__, $error));
				} else {
					if (DEVELOPMENT === true) error_log(sprintf('[DEBUG] <%s:%d> $smarty-compile(word): SUCCESS', __FILE__, __LINE__));
				}
			}
		} catch (Exception $e) {
			$error .= '<br>Exception in $smarty->compile(): ' . $e->getMessage() . '<br>';
		}

		/** If compile-error, write it to the template in the DB */
		if (!empty($error))
		{
			$db->query('UPDATE templates SET error="'.addslashes($error).'" WHERE id='.$frm['id'], __FILE__, __LINE__, 'UPDATE templates (tplid)');
		}
	}

	/** If everything worked well - ie. no Errors... */
	if (empty($error) || !$error)
	{
		/** Unlock Template for editing - only if no $error occurred */
		tpleditor_unlock($updated_tplid);
		if (!isset($return_url) || empty($return_url)) $return_url = '/?tpl='.$updated_tplid;
		$return_url .= '&updated';

		$updated_tplid = null;
		$enable_tpleditor = null;

		if (DEVELOPMENT === true) error_log(sprintf('[DEBUG] <%s:%d> header(Location): %s', __FILE__, __LINE__, $return_url));
		header('Location: '.$return_url);
		exit();
	}

	/** Otherweise go back to TPL-Editor & display Errors */
	else {
		$frm['tpl'] = stripslashes(stripslashes($frm['tpl']));
		$frm['title'] = stripslashes(stripslashes($frm['title']));
		$frm['packages'] = stripslashes(stripslashes($frm['packages']));
		// FIXME aus irgend einem grund ist stripslashes() 2x nötig. sonst wird nur ein teil der slashes entfernt. wüsste gern wieso. ([z]biko)

		/** Pass $error to error-log */
		error_log($error);

		/** Pass $error to Smarty and display template */
		$smarty->assign('tpleditor_error', $error);
		$frm['tpl'] = htmlentities($frm['tpl']);
		$smarty->assign('tpleditor_frm', $frm);
		$smarty->assign('tpleditor_state', $state);

		$smarty->display('file:layout/layout.tpl');
	}

/** Access error (Template locked) */
} else {
	http_response_code(403); // Set response code 403 (access denied) and exit.
	user_error($access_error, E_USER_WARNING);
}
