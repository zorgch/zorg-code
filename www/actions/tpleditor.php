<?php
/**
 * zorg Smarty Template-Editor Save Actions
 *
 * @package zorg\Smarty\Tpleditor
 */

/**
 * File includes
 */
require_once( __DIR__ .'/../includes/tpleditor.inc.php');

/** Initialize Vars */
$error = null;
$state = null;
$access_error = null;
$frm = $_POST['frm'];

/**
 * Save Template
 */
if (tpleditor_access_lock($_GET['tplupd'], $access_error))
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

	// FIXME <biko> deaktiviert bis ein besserer syntax checker gebaut ist. 
	/* 
	$syntaxerr = html_syntax_check($frm['tpl']);
	if ($syntaxerr) $error .= "<br />HTML Syntax Error: $syntaxerr <br />";
	*/

	if (!$error)
	{
		$frm['id'] = htmlentities($frm['id'], ENT_QUOTES);
		$frm['tpl'] = mysql_real_escape_string($frm['tpl']);
		$frm['title'] = sanitize_userinput($frm['title']);
		$frm['sidebar_tpl'] = (empty($frm['sidebar_tpl']) ? 'NULL' : htmlentities($frm['sidebar_tpl'], ENT_QUOTES));
		$frm['page_title'] = htmlentities($frm['page_title'], ENT_NOQUOTES);
		$frm['menus'] = htmlentities($frm['menus'], ENT_QUOTES);
		$frm['packages'] = htmlentities($frm['packages'], ENT_QUOTES);

		/**
		 * NEW TEMPLATE
		 */
		if ($frm['id'] === 'new')
		{
			/*$sql = sprintf('INSERT INTO templates (tpl, title, word, border, owner, page_title, read_rights, write_rights, created, last_update, update_user, sidebar_tpl) VALUES ("%s", "%s", "%s", "%s", "%d", "%d", "%s", "%d", "%d", NOW(), NOW(), "%d")', $frm['tpl'], $frm['title'], $frm['word'], $frm['border'], $user->id, $frm['page_title'], $frm['read_rights'], $frm['write_rights'], $user->id, $frm['sidebar_tpl']);
			$frm['id'] = $db->query($sql, __FILE__, __LINE__, '');*/
			$frm['id'] = $db->insert('templates', [
											 'title' => $frm['title']
											,'page_title' => $frm['page_title']
											,'word' => $frm['word']
											,'tpl' => $frm['tpl']
											,'border' => $frm['border']
											,'read_rights' => $frm['read_rights']
											,'write_rights' => $frm['write_rights']
											,'sidebar_tpl' => $frm['sidebar_tpl']
											,'owner' => $user->id
											,'update_user' => $user->id
											,'created' => 'NOW()'
											,'last_update' => 'NOW()'
										  ], __FILE__, __LINE__, 'Add new Template');
			if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> New Template ID: %s', __FILE__, __LINE__, $frm['id']));

			/** Only on success... */
			if ($frm['id'] > 0 && $frm['id'] != false)
			{
				Thread::setRights('t', $frm['id'], $frm['read_rights']);
				$db->query('INSERT INTO templates_backup SELECT * FROM templates WHERE id='.$frm['id'], __FILE__, __LINE__, 'Copy Template to templates_backup');
	
				$_GET['tplupd'] = $frm['id'];
				$_GET['location'] = base64_encode('/?tpl='.$frm['id']);
				$smarty->assign('tplupdnew', 1);
				$state = t('created', 'tpl', $frm['id']);
	
				/** Activity Eintrag ausl�sen */
				Activities::addActivity($user->id, 0, t('activity-newpage', 'tpl', [ $frm['id'], $frm['title'] ]), 't');
			}
			/** Template has not been added */
			else {
				$error .= t('error-create', 'tpl');
			}

		/**
		 * UPDATE EXISTING TEMPLATE
		 */
		} elseif ($frm['id'] > 0) {
			/*if ($frm['word']) $set_word = ', word="'.$frm['word'].'"';
			$sql = sprintf('UPDATE templates SET tpl="%s", title="%s", page_title="%s", read_rights="%d", write_rights="%d", update_user=%d, border="%d", packages="%s", error="" %s, sidebar_tpl="", last_update=NOW() WHERE id=%d', $frm['tpl'], $frm['title'], $frm['page_title'], $frm['read_rights'], $frm['write_rights'], $user->id, $frm['border'], $frm['packages'], $set_word, $frm['sidebar_tpl'], $frm['id']);
			$db->query($sql, __FILE__, __LINE__, 'UPDATE templates');*/
			$templateUpdateParams = [
							 'title' => $frm['title']
							,'page_title' => $frm['page_title']
							,'tpl' => $frm['tpl']
							,'border' => $frm['border']
							,'read_rights' => $frm['read_rights']
							,'write_rights' => $frm['write_rights']
							,'sidebar_tpl' => $frm['sidebar_tpl']
							,'error' => ''
							,'owner' => $user->id
							,'update_user' => $user->id
							,'last_update' => 'NOW()'
						];
			if ($frm['word']) $templateUpdateParams = array_merge($templateUpdateParams, ['word' => $frm['word']]);
			if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> Update Template SQL-Params: %s', __FILE__, __LINE__, print_r($templateUpdateParams,true)));
			$result = $db->update('templates', ['id', $frm['id']], $templateUpdateParams, __FILE__, __LINE__, 'Update Template');
			Thread::setRights('t', $frm['id'], $frm['read_rights']);
			$db->query('REPLACE INTO templates_backup SELECT * FROM templates WHERE id='.$frm['id'].' AND unix_timestamp(NOW())-UNIX_TIMESTAMP(last_update) > (60*60*24*3)', __FILE__, __LINE__, 'REPLACE INTO templates_backup');
			if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> Template ID #%d updated', __FILE__, __LINE__, $frm['id']));
		}

		if (!$error && $frm['id'] > 0)
		{
			/** Menus: remove all links between Template & Menus, relink selected Menus */
			$db->query('DELETE FROM tpl_menus WHERE tpl_id ='.$frm['id']); // delete all
			$tplmenusInsertData = null;
			foreach ($_POST['frm']['menus'] as $menu_id) {
				/** Note: only works when getting Array directly from $_POST, not via $frm.
				Don't know why, cost me like 2 hours to figure this out WTF */
				if (!empty($menu_id)) $tplmenusInsertData[] = sprintf('(%d, %d)', $frm['id'], $menu_id);
			}
			$db->query('INSERT INTO tpl_menus (tpl_id, menu_id) VALUES '.implode(',',$tplmenusInsertData), __FILE__, __LINE__, 'Link Template to selected Menus'); // add new
			if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> Template ID #%d linked to Menus: %s', __FILE__, __LINE__, $frm['id'], print_r($tplmenusInsertData, true)));
	
			/** Packages: remove all links between Template & Packages, relink selected Packages */
			$db->query('DELETE FROM tpl_packages WHERE tpl_id ='.$frm['id']); // delete all
			$tplpackagesInsertData = null;
			foreach ($_POST['frm']['packages'] as $package_id) {
				/** Note: only works when getting Array directly from $_POST, not via $frm.
				Don't know why, cost me like 2 hours to figure this out WTF */
				if (!empty($package_id)) $tplpackagesInsertData[] = sprintf('(%d, %d)', $frm['id'], $package_id);
			}
			$db->query('INSERT INTO tpl_packages (tpl_id, package_id) VALUES '.implode(',',$tplpackagesInsertData), __FILE__, __LINE__, 'Link Template to selected Packages'); // add new
			if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> Template ID #%d linked to Packages: %s', __FILE__, __LINE__, $frm['id'], print_r($tplpackagesInsertData, true)));
		}
	}

	/**
	 * Force recompile a Smarty Template
	 *
	 * @author [z]biko
	 * @author IneX
	 * @version 2.0
	 * @since 1.0 <biko> procedure added intially
	 * @since 2.0 <inex> 03.01.2019 Fixed Bug #768: must also recompile template based on /page/word (not only /tpl/id )
	 */
	if (!$error)
	{
		/** Compile Templated - TPL-ID based */
		//if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> $smarty-compile() $frm: %s', __FILE__, __LINE__, print_r($frm,true)));
		if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> $smarty-compile(%s)', __FILE__, __LINE__, 'tpl:'.$frm['id']));
		if (!$smarty->compile('tpl:'.$frm['id'], $compile_err))
		{
			for ($i=0; $i<sizeof($compile_err); $i++) {
				$error .= "<br />".$compile_err[$i]."<br />";
			}
			if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> $smarty-compile(tpl): ERROR (%s)', __FILE__, __LINE__, $error));
		} else {
			if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> $smarty-compile(tpl): SUCCESS', __FILE__, __LINE__));
		}

		/** Compile Templated - TPL-Word based (if applicable) */
		if (!empty($frm['word']))
		{
			if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> $smarty-compile(%s)', __FILE__, __LINE__, 'word:'.$frm['word']));
			if (!$smarty->compile('word:'.$frm['word'], $compile_err))
			{
				for ($i=0; $i<sizeof($compile_err); $i++) {
					$error .= "<br />".$compile_err[$i]."<br />";
				}
				if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> $smarty-compile(word): ERROR (%s)', __FILE__, __LINE__, $error));
			} else {
				if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> $smarty-compile(word): SUCCESS', __FILE__, __LINE__));
			}
		}

		/** If compile-error, write it to the template in the DB */
		if (!empty($error))
		{
			try {
				$db->query('UPDATE templates SET error="'.addslashes($error).'" WHERE id='.$frm['id'], __FILE__, __LINE__, 'UPDATE templates (tplid)');
			}
			catch (Exception $e) {
				error_log($e->getMessage(), E_USER_ERROR);
			}
		}
	}

	/** Unlock Template for editing - only if no $error occurred */
	if (!$error)
	{
		tpleditor_unlock($_GET['tplupd']);
		if (!$_GET['location']) $_GET['location'] = base64_encode('/?tpl='.$_GET['tplupd']);

		unset($_GET['tplupd']);
		unset($_GET['tpleditor']);

		// @TODO: http://zorg.local/actions/tpleditor.php?tpl=17&tpleditor=1&tplupd=new => weisse seite
		header('Location: '.base64_decode($_GET['location']));
		die();

	/** Go back to TPL-Editor & display Errors */	
	} else {
		$frm['tpl'] = stripslashes(stripslashes($frm['tpl']));
		$frm['title'] = stripslashes(stripslashes($frm['title']));
		$frm['packages'] = stripslashes(stripslashes($frm['packages']));
		/** @FIXME <biko> aus irgend einem grund ist stripslashes() 2x n�tig. sonst wird nur ein teil der slashes entfernt. w�sste gern wieso. */

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
