<?php
/**
 * zorg Smarty Template-Editor Save Actions
 *
 * @package zorg\Smarty\Tpleditor
 */

/**
 * File includes
 */
require_once __DIR__.'/../includes/tpleditor.inc.php';

/** Initialize Vars */
global $notification;
$error = null;
$state = null;
$access_error = null;
if (isset($_POST['frm']) && is_array($_POST['frm'])) {
	$frm['id'] = ($_POST['frm']['id'] === 'new' ? 'new' : (filter_var($_POST['frm']['id'], FILTER_SANITIZE_NUMBER_INT) ?? null));
	$frm['title'] = filter_var($_POST['frm']['title'], FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? null;
	$frm['page_title'] = filter_var($_POST['frm']['page_title'], FILTER_DEFAULT, FILTER_REQUIRE_SCALAR) ?? null;
	$frm['word'] = filter_var($_POST['frm']['word'], FILTER_DEFAULT, FILTER_REQUIRE_SCALAR) ?? null;
	$frm['border'] = filter_var($_POST['frm']['border'], FILTER_SANITIZE_NUMBER_INT) ?? 0;
	$frm['sidebar_tpl'] = filter_var($_POST['frm']['sidebar_tpl'], FILTER_SANITIZE_NUMBER_INT) ?? null;
	$frm['read_rights'] = filter_var($_POST['frm']['read_rights'], FILTER_SANITIZE_NUMBER_INT) ?? 0;
	$frm['write_rights'] = filter_var($_POST['frm']['write_rights'], FILTER_SANITIZE_NUMBER_INT) ?? 0;
	$frm['tpl'] = $_POST['frm']['tpl'];// FIXME better would be: htmlspecialchars($_POST['frm']['tpl'], ENT_QUOTES, 'UTF-8'); --> but this encodes too much within the Smarty Tpl / breaking them for compiling.
	$frm['menus'] = [];
	if (isset($_POST['frm']['menus']) && is_array($_POST['frm']['menus'])) {
		$i=0;
		for ($i; $i<count($_POST['frm']['menus']); $i++) {
			$menu_id = filter_var($_POST['frm']['menus'][$i], FILTER_SANITIZE_NUMBER_INT) ?? null;
			if (!empty($menu_id)) $frm['menus'][] = $menu_id;
		}
	}
	$frm['packages'] = [];
	if (isset($_POST['frm']['packages']) && is_array($_POST['frm']['packages'])) {
		$i=0;
		for ($i; $i<count($_POST['frm']['packages']); $i++) {
			$package_id = filter_var($_POST['frm']['packages'][$i], FILTER_SANITIZE_NUMBER_INT) ?? null;
			if (!empty($package_id)) $frm['packages'][] = $package_id;
		}
	}
}
$enable_tpleditor = filter_input(INPUT_GET, 'tpleditor', FILTER_VALIDATE_BOOL) ?? false; // $_GET['tpleditor']
$updated_tplid = ($_GET['tplupd'] === 'new' ? 'new' : (filter_input(INPUT_GET, 'tplupd', FILTER_VALIDATE_INT) ?? null)); // $_GET['tplupd']
$return_url = base64url_decode(filter_input(INPUT_GET, 'location', FILTER_SANITIZE_FULL_SPECIAL_CHARS)) ?? '/index.php?tpl='.$updated_tplid; // $_GET['location']

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
				$return_url = '/index.php?tpl='.$updated_tplid;
				$return_url .= '&created=1';
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
							//,'owner' => $user->id // ähhh nei, Owner söll nöd change?!
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
			if (!empty($frm['menus']))
			{
				$tplmenusInsertData = [];
				$params = [];
				foreach ($frm['menus'] as $menu_id) {
					if ($menu_id > 0) {
						$tplmenusInsertData[] = '(?, ?)';
						$params[] = $frm['id'];
						$params[] = $menu_id;
					}
				}
				$sql = 'INSERT INTO tpl_menus (tpl_id, menu_id) VALUES '.implode(',', $tplmenusInsertData);
				zorgDebugger::log()->debug('Template ID #%d linked to Menus: %s%s', [$frm['id'], $sql, print_r($params, true)]);
				$db->query($sql, __FILE__, __LINE__, 'Link Template to selected Menus', $params);
			}

			/** Packages: remove all links between Template & Packages, relink selected Packages */
			$db->query('DELETE FROM tpl_packages WHERE tpl_id=?', __FILE__, __LINE__, 'DELETE FROM tpl_packages', [$frm['id']]);
			if (!empty($frm['packages']))
			{
				$tplpackagesInsertData = [];
				$params = [];
				foreach ($frm['packages'] as $package_id) {
					if (!empty($package_id)) {
						$tplpackagesInsertData[] = '(?, ?)';
						$params[] = $frm['id'];
						$params[] = $package_id;
					}
				}
				$sql = 'INSERT INTO tpl_packages (tpl_id, package_id) VALUES ' . implode(',', $tplpackagesInsertData);
				zorgDebugger::log()->debug('Template ID #%d linked to Packages: %s%s', [$frm['id'], $sql, print_r($params, true)]);
				$db->query($sql, __FILE__, __LINE__, 'Link Template to selected Packages', $params);
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
			$db->query('UPDATE templates SET error=? WHERE id=?', __FILE__, __LINE__, 'UPDATE templates (tplid)', [$error, $frm['id']]);
		}
	}

	/** If everything worked well - ie. no Errors... */
	if (empty($error) || !$error)
	{
		/** Unlock Template for editing - only if no $error occurred */
		tpleditor_unlock($updated_tplid);
		if (!isset($return_url) || empty($return_url)) $return_url = '/index.php?tpl='.$updated_tplid;
		$return_url .= '&updated=1';

		$updated_tplid = null;
		$enable_tpleditor = null;

		/** Notify Template-Owner about change - if done by other User */
		$notifyOtherTplOwner = tpl_get_associated_user($frm['id']);
		zorgDebugger::log()->debug('Notify Template-Owner: owner %d <-- edit by %d', [$notifyOtherTplOwner, $user->id]);
		if ($notifyOtherTplOwner !== $user->id)
		{
			$username = $user->id2user($user->id);
			$notification_text = t('change-notification-owner', 'tpl', [ $username, $frm['id'], $frm['title'] ]);
			$notification_status = $notification->send($notifyOtherTplOwner, 'messagesystem', ['from_user_id'=>$user->id, 'subject'=>t('change-notification-owner-subject', 'tpl', [$username]), 'text'=>$notification_text, 'message'=>$notification_text]);
			zorgDebugger::log()->debug('$_TPLROOT[owner] Notification: %s', [$notification_status ? 'true' : 'false']);
		}

		unset($_GET['tpleditor']);
		unset($_GET['tplupd']);
		unset($_GET['location']);
		zorgDebugger::log()->debug('Location: %s', [$return_url]);
		header('Location: '.$return_url);
		exit();
	}

	/** Otherweise go back to TPL-Editor & display Errors */
	else {
		$frm['tpl'] = (is_string($frm['tpl']) ? stripslashes(stripslashes($frm['tpl'])) : strval($frm['tpl']));
		$frm['title'] = (is_string($frm['title']) ? stripslashes(stripslashes($frm['title'])) : strval($frm['title']));
		$frm['packages'] = (is_string($frm['packages']) ? stripslashes(stripslashes($frm['packages'])) : strval($frm['packages']));
		// FIXME aus irgend einem grund ist stripslashes() 2x nötig. sonst wird nur ein teil der slashes entfernt. wüsste gern wieso. ([z]biko)

		/** Pass $error to error-log */
		zorgDebugger::log()->warn('Smarty Template Error: %s', [strval($error)]);

		/** Pass $error to Smarty and display template */
		$smarty->assign('tpleditor_error', $error);
		$frm['tpl'] = htmlentities($frm['tpl']);
		$smarty->assign('tpleditor_state', $state);
		$smarty->assign('tpleditor_frm', $frm);

		$smarty->display('file:layout/layout.tpl');
	}

/** Access error (Template locked) */
} else {
	http_response_code(403); // Set response code 403 (access denied) and exit.
	user_error($access_error, E_USER_WARNING);
}
