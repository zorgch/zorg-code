<?php
/**
 * File includes
 * @include main.inc.php required
 */
require_once( __DIR__ .'/includes/main.inc.php');

//=============================================================================
// Layout & code
//=============================================================================

Messagesystem::execActions();

/** Pagetitle setzen */
if($_GET['regcode']) $pagetitle = 'Account bestätigen';
elseif($_GET['do'] === 'view') $pagetitle = 'Mein Userprofil bearbeiten';
elseif($user->is_loggedin()) {$pagetitle = $user->id2user($_GET['user_id'], TRUE);}

if (empty($pagetitle)) $pagetitle = 'Userlist';
$smarty->assign('tplroot', array('page_title' => $pagetitle, 'page_link' => $_SERVER['PHP_SELF']));

/**
 * Userlist anzeigen
 */
if ( (!$_GET['do'] && !$_GET['user_id'] && !$_GET['regcode']) || ($_GET['do'] && !$user->is_loggedin()) )
{
	$smarty->display('file:layout/head.tpl');
	echo menu('zorg');
	echo menu('user');
	echo "<br />";
	$smarty->display('tpl:219');
	$smarty->display('file:layout/footer.tpl');
	exit; // make sure only Userlist is processed / displayed
}

/**
 * Mein Profil ändern
 */
if ($user->is_loggedin())
{
	if($_GET['do'] === 'view' && !$_GET['user_id'])
	{
		/**
		 * Profil als anderen User anzeigen (DEV only!)
		 */
		if (!empty($_GET['viewas']) && DEVELOPMENT === true) {
			$smarty->assign('error', ['type' => 'info', 'dismissable' => 'false', 'title' => 'Userprofil wird angezeigt als <strong>'.$user->id2user($_GET['viewas'], TRUE).'</strong>']);

			/** Switch to "viewas"-User */
			$saveMyUserID = $user->id;
			$_SESSION['user_id'] = $_GET['viewas'];
			$user = new usersystem();
			$smarty->assign('user', $user);

			/** Display "viewas"-Userprofile */
			$smarty->assign('form_action', '?do=nothing');
			$smarty->display('file:layout/pages/profile_page.tpl');

			/** Switch back to current User */
			$_SESSION['user_id'] = $saveMyUserID;
			$user = new usersystem();
			$smarty->assign('user', $user);

		/**
		 * Mein Profil
		 */
		} elseif ($_GET['do'] === 'view' || empty($_GET['user_id'])) {

			/** Update Userprofile infos & settings */
			if($user->id && $_POST['do'] === 'update' && $_FILES['image']['error'] === 4)
			{
				/** Validate $_POST-request */
				if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> $_POST: %s', __FILE__, __LINE__, print_r($_POST,true)));
				if (count($_POST) > 1)
				{
					$changeprofile_result = $user->exec_changeprofile($user->id, $_POST);
				}
			}
			/** Upload and change new Userpic */
			if($user->id && $_POST['do'] === 'update' && $_FILES['image']['error'] === 0)
			{
				$uploadimage_result = $user->exec_uploadimage($user->id, $_FILES);
			}
			/** Change User Password */
			if($user->id && $_POST['do'] === 'change_password')
			{
				$newpassword_result = $user->exec_newpassword($user->id, $_POST['old_pass'], $_POST['new_pass'], $_POST['new_pass2']);
			}

			/**
			 * Error or Success message handling
			*/
			/* Userprofile change */
			if (isset($changeprofile_result[0])) {
				if ($changeprofile_result[0] === TRUE) {
					$smarty->assign('error', ['type' => 'warn', 'dismissable' => 'true', 'title' => $changeprofile_result[1]]);
				} else {
					$smarty->assign('error', ['type' => 'success', 'dismissable' => 'true', 'title' => t('userprofile-change-ok', 'user')]);
				}
			}
			/** Userpic change */
			if (isset($uploadimage_result[0])) {
				if ($uploadimage_result[0] === TRUE) {
					$smarty->assign('error', ['type' => 'warn', 'dismissable' => 'true', 'title' => $uploadimage_result[1]]);
				} else {
					$smarty->assign('error', ['type' => 'success', 'dismissable' => 'true', 'title' => t('userpic-change-ok', 'user')]);
				}
			}
			/** New Password */
			if (isset($newpassword_result[0])) {
				if ($newpassword_result[0] === TRUE) {
					$smarty->assign('error', ['type' => 'warn', 'dismissable' => 'true', 'title' => $newpassword_result[1]]);
				} else {
					$smarty->assign('error', ['type' => 'success', 'dismissable' => 'true', 'title' => t('new-userpw-confirmation', 'user')]);
				}
			}

			/** Instantiate a new, updated $user-Object (because new data...) */
			$user = new usersystem();
			$smarty->assign('user', $user);

			/** Display "Mein Profil ändern" */
			$smarty->assign('form_action', '?do=view');
			$smarty->display('file:layout/pages/profile_page.tpl');
		}
	}
}

/**
 * Userprofil anzeigen
 */
if($_GET['user_id']) {
	$smarty->display('file:layout/head.tpl');
	echo menu('zorg');
	echo menu('user');
	echo "<br />";

	/** Validate required $_GET parameters */
	if (!is_numeric($_GET['user_id']) || $_GET['user_id'] <= 0 || is_array($_GET['user_id']) || $user->id2user($_GET['user_id']) === false)
	{
		trigger_error(t('invalid-id', 'user'), E_USER_WARNING);
		$smarty->display('file:layout/footer.tpl');
		exit;
	}

	try {
		$sql = 'SELECT * FROM user WHERE id = '.$_GET['user_id'];
		$result = $db->query($sql, __FILE__, __LINE__, 'Userprofil anzeigen');
		$rs = $db->fetch($result);
	} catch(Exception $e) {
		trigger_error($e->getMessage(), E_trigger_error);
	}

	if (isset($_geaechtet[$_GET['user_id']])) {
		$smarty->assign('error', ['type' => 'warn', 'dismissable' => 'false', 'title' => t('user-wird-geaechtet', 'user', $user->id2user($_GET['user_id'], true))]);
		$smarty->display('file:layout/elements/block_error.tpl');
	}
	?>
		<table class="case" width="100%">
		<tr><td style="text-align: center" valign="top">
		<h1><?=$rs['clan_tag'].$rs['username']?></h1>
		<img src="<?=$user->userImage($_GET['user_id'], 1)?>">
		</td><td style="text-align: left" valign="top">
		<?php $smarty->display('tpl:211') /** User Events */ ?>
		</td></tr>
		<tr><td style="text-align: center" colspan="2">
	<?php

	if ($user->id > 0 && $_GET['user_id'] != $user->id && $rs['addle']) {
		?>
		<br />
		<form action="/addle.php?show=overview&do=new" method='post'>
			<INPUT type="hidden" name="id" value="<?=$_GET['user_id']?>">
			<input type='submit' class='button' value=" <?=$rs['username']?> zum Addle herausfordern ">
		</FORM>
		<br />
		<?
	}

	if($user->id > 0) {
		if($_GET['user_id'] == $user->id) { // User ist der eigene
			if(isset($_GET['newmsg'])) { // User will eine neue Message senden
				if(isset($_GET['msgusers']) && isset($_GET['msgsubject'])) {
					echo Messagesystem::getFormSend($_GET['msgusers'],$_GET['msgsubject'],'');
				} else {
					echo Messagesystem::getFormSend(0,'','');
				}
			} else { // User will Inbox sehen
				echo Messagesystem::getInboxHTML($_GET['box'], $pagesize=11, $_GET['page'], $_GET['sort'], $_GET['order']);
			}
		} else {
			echo Messagesystem::getFormSend(array($_GET['user_id']), '', '');
		}
	}

	echo "<br><img src='/images/stats.php?user_id=".$_GET['user_id']."'><br>"; // Post-Statistik ausgeben

	echo
		'<br />'
		.Forum::getLatestCommentsbyUser($_GET['user_id'])
		.'</td></tr>'
		.'</table>'
	;

	if ($user->typ != USER_NICHTEINGELOGGT) echo '<br />'.getUserPics($_GET['user_id'], 0);

	$smarty->display('file:layout/footer.tpl');
}

/**
 * User Login
 *
 * @TODO separate code & view by moving the HTML-parts to a Smarty-Template
 */
if(!$user->id) {
	$smarty->display('file:layout/head.tpl');
	echo menu('zorg');
	echo menu('user');
	echo "<br />";

	if(!$_GET['regcode'] && !$_SESSION['user_id'] && $_GET['do'] == "anmeldung")  {
		
		// reCAPTCHA v2 initialisieren (inkl. keys)
		require('includes/g-recaptcha-src/autoload.php');
		$siteKey = '6Ld_MgYAAAAAAMiFFL65_-QSB8P2e4Zz2FbSHfUv';
		$secret = '6Ld_MgYAAAAAADDFApargrMpDLE0g1m4X-q0oOL-';
		$lang = 'de-CH'; // reCAPTCHA supported 40+ languages listed here: https://developers.google.com/recaptcha/docs/language
		$recaptcha = new \ReCaptcha\ReCaptcha($secret);
		
		//captcha validieren
		if (isset($_POST['g-recaptcha-response']))
		{
			$recaptcha = new \ReCaptcha\ReCaptcha($secret);
			$resp = $recaptcha->verify($_POST['g-recaptcha-response'], $_SERVER['REMOTE_ADDR']);
			
			//captcha VALID
			if ($resp->isSuccess()) {
				$error = "<b class='small'>".$user->create_newuser(htmlentities($_POST['new_username']), $_POST['new_password'],$_POST['new_password2'],$_POST['new_email'])."</b>";
			
			//captcha UNGÜLTIG
			} else {
				//foreach ($resp->getErrorCodes() as $code) {
                	$error = "<font color='red'><b>Das reCAPTCHA wurde FALSCH eingegeben oder leer gelassen.<br />Bitte versuch es nochmal!</b></font>";//."<br />(reCAPTCHA error codes: " . $code . ")";
            	//}
			}
		}
		echo "<form action='$_SERVER[PHP_SELF]?do=anmeldung' method='post'>";
		echo "<table width='600' class='case' align='center'>
			<tr><td align='center' class='title' colspan='2'>
			Anmeldung
			</td></tr>";
		//gewuenschter username
		echo "<tr><td align='left'>
			<b>Gew&uuml;nschter Benutzername:</b><br />
			<b class='small'>(Clan Zeichen kann seperat angegeben werden)</b>
			</td><td align='left'>
			<input type='text' class='text' name='new_username' size='30' value='$_POST[new_username]'>
			</td></tr>";
		//gewuenschtes passwort
		echo "<tr><td align='left'>
			<b>Passwort:</b>
			</td><td align='left'>
			<input type='password' class='text' name='new_password' size='30'>
			</td></tr><tr><td align='left'>
			<b>Passwort wiederholen:</b>
			</td><td align='left'>
			<input type='password' class='text' name='new_password2' size='30'>
			</td></tr>";
		//email adresse:
		echo "<tr><td align='left'>
			<b>E-Mail Adresse:</b><br />
			<b class='small'>(du bekommst einen Aktivierungscode per E-Mail zugeschickt)</b>
			</td><td align='left'>
			<input type='text' name='new_email' class='text' size='30' value='$_POST[new_email]'>
			</td></tr>";
		// reCAPTCHA v2 form
		echo '<tr><td align="left" colspan="2">
			<div style="display:inline-block;" class="g-recaptcha" data-sitekey="'.$siteKey.'"></div>
            <script type="text/javascript"
                    src="https://www.google.com/recaptcha/api.js?hl='.$lang.'">
            </script>
            </td></tr>';
		//submit button
		echo "<tr><td align='left' colspan='2'>
			<input type='submit' name='newuser' class='button' value='absenden'>
			<br /><br />$error</b>
			</td></tr>
			</table>";
		echo "</form>";
		echo "<br /><br />";

		
		//neues passwort zusenden
		$error = $user->new_pass($_POST['email']);


		echo "<form action='$_SERVER[PHP_SELF]?do=anmeldung' method='post'>";
		echo "<table width='600' class='case' align='center'>
			<tr><td align='center' class='title' colspan='2'>
			<b>Passwort vergessen ?</b>
			</td></tr><tr><td align='left'>
			<b>E-Mail Adresse:</b>
			</td><td align='left'>
			<input type='text' name='email' size='40' class='text'>
			</td></tr><tr><td align='left' colspan='2'>
			<b class='small'>Achtung!<br />Hiermit wird dir ein neues Passwort gesetzt und zugesendet, dieses kannst du sp&auml;ter wieder &auml;ndern!</b>
			<br /><br />
			<font color='red'><b>$error</b></font><br />
			<input type='submit' name='send' value='neues Passwort zusenden' class='button'>
			</td></tr></table>";

		echo "</form>";
	//user reaktivieren
	} elseif($_GET['regcode']) {
		$new_user = $user->activate_user($_GET['regcode']);
		$sql = "UPDATE user set active = 1 WHERE username='$new_user'";
		$db->query($sql,"profil.php",297);
		echo "<b>".$new_user."</b>";
	}
	$smarty->display('file:layout/footer.tpl');
}
