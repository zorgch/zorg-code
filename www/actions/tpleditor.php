<?php
/**
 * File Includes
 */
require_once( __DIR__ .'/../includes/tpleditor.inc.php');

$error = "";
$state = "";
$access_error = "";

$frm = $_POST['frm'];

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
		$frm['word'] = "";
	}

	if (!smarty_brackets_ok($frm['tpl'], $brack_err)) $error .= $brack_err;

	/** Read packages */
	$frm['packages'] = addslashes($frm['packages']);
	$frm['packages'] = strip_tags($frm['packages']);
	$packs = preg_replace("(\s)", "", $frm['packages']);
	$packs = explode(";", $packs);
	$frm['packages'] = '';
	foreach ($packs as $p) {
		if ($p) {
			if (!file_exists(package_path($p))) $error .= t('error-package-missing', 'tpl', $p);
			$frm['packages'] .= "$p; ";
		}
	}

	/* 
	* deaktiviert bis ein besserer syntax checker gebaut ist. 
	* 
	$syntaxerr = html_syntax_check($frm['tpl']);
	if ($syntaxerr) $error .= "<br />HTML Syntax Error: $syntaxerr <br />";
	*/

	if (!$error)
	{
		$frm['tpl'] = addslashes($frm['tpl']);
		$frm['title'] = addslashes($frm['title']);
		$frm['title'] = strip_tags($frm['title']);
		$frm['page_title'] = htmlentities($frm['page_title'], ENT_NOQUOTES);
		$frm['menus'] = htmlentities($frm['menus'], ENT_QUOTES);

		/**
		 * NEW TEMPLATE
		 */
		if (!$error && $frm['id'] == "new")
		{
			try {
				$sql = sprintf("INSERT INTO templates (tpl, title, word, packages, border, owner, page_title, read_rights, write_rights, created, last_update, update_user) VALUES ('%s', '%s', '%s', '%s', '%d', '%d', '%s', '%d', '%d', NOW(), NOW(), '%d')", $frm['tpl'], $frm['title'], $frm['word'], $frm['packages'], $frm['border'], $user->id, $frm['page_title'], $frm['read_rights'], $frm['write_rights'], $user->id);
				$frm['id'] = $db->query($sql, __FILE__, __LINE__, '');
				Thread::setRights('t', $frm['id'], $frm['read_rights']);
				$db->query("INSERT INTO templates_backup SELECT * FROM templates WHERE id='".$frm['id']."'", __FILE__, __LINE__, 'INSERT INTO');
			}
			catch (Exception $e) {
				error_log($e->getMessage(), E_USER_ERROR);
			}

			$_GET['tplupd'] = $frm['id'];
			$_GET['location'] = base64_encode("/?tpl=".$frm['id']);
			$smarty->assign("tplupdnew", 1);
			$state = t('created', 'tpl', $frm['id']);

			/** Activity Eintrag auslösen */
			Activities::addActivity($user->id, 0, t('activity-newpage', 'tpl', [ $frm['id'], $frm['title'] ]), 't');

		/**
		 * UPDATE EXISTING TEMPLATE
		 */
		} elseif (!$error) {
			try {
				if ($frm['word']) $set_word = ", word='".$frm['word']."'";
				$sql = sprintf("UPDATE templates SET tpl='%s', title='%s', page_title='%s', read_rights='%d', write_rights='%d', last_update=NOW(), update_user='%d', border='%d', 
								packages='%s' %s, error='' WHERE id='%d'", $frm['tpl'], $frm['title'], $frm['page_title'], $frm['read_rights'], $frm['write_rights'], $user->id, $frm['border'], $frm['packages'], $set_word, $frm['id']);
				$db->query($sql, __FILE__, __LINE__, 'UPDATE');
				Thread::setRights('t', $frm['id'], $frm['read_rights']);
				$db->query("REPLACE INTO templates_backup SELECT * FROM templates WHERE id=".$frm['id']." AND unix_timestamp(NOW())-UNIX_TIMESTAMP(last_update) > (60*60*24*3)", __FILE__, __LINE__, 'REPLACE INTO');
			}
			catch (Exception $e) {
				error_log($e->getMessage(), E_USER_ERROR);
			}
		}
	}

	/** Compile Template */
	if (!$error) {
		if (!$smarty->compile('tpl:'.$frm['id'], $compile_err))
		{
			for ($i=0; $i<sizeof($compile_err); $i++) {
				$error .= "<br />".$compile_err[$i]."<br />";
			}
			try {
				$db->query("UPDATE templates SET error='".addslashes($error)."' WHERE id=".$frm['id'], __FILE__, __LINE__, 'UPDATE');
			}
			catch (Exception $e) {
				error_log($e->getMessage(), E_USER_ERROR);
			}
		}
	}

	/** Unlock Template for editing */
	if (!$error)
	{
		tpleditor_unlock($_GET['tplupd']);
		if (!$_GET['location']) $_GET['location'] = base64_encode('/?tpl='.$_GET['tplupd']);

		unset($_GET['tplupd']);
		unset($_GET['tpleditor']);

		// @TODO: http://zorg.local/actions/tpleditor.php?tpl=17&tpleditor=1&tplupd=new => weisse seite
		header('Location: '.base64_decode($_GET['location']));
		die();

	/** Error Output */	
	} else {
		$frm['tpl'] = stripslashes(stripslashes($frm['tpl']));
		$frm['title'] = stripslashes(stripslashes($frm['title']));
		$frm['packages'] = stripslashes(stripslashes($frm['packages']));
		// aus irgend einem grund ist das 2x nötig. sonst wird nur ein teil der slashes entfernt. wüsste gern wieso. (biko)

		/** Pass $error to error-log */
		error_log($error);

		/** Pass $error to Smarty and display template */
		$smarty->assign("tpleditor_error", $error);
		$frm['tpl'] = htmlentities($frm['tpl']);
		$smarty->assign("tpleditor_frm", $frm);
		$smarty->assign("tpleditor_state", $state);

		$smarty->display("file:layout/layout.tpl");
	}

/** Access error (Template locked) */
} else {
	http_response_code(403); // Set response code 403 (access denied) and exit.
	user_error($access_error, E_USER_WARNING);
}
