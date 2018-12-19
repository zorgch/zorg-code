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
if ( (!$_GET['do'] && !$_GET['user_id'] && !$_GET['regcode']) )
{
	$smarty->display('file:layout/head.tpl');
	echo menu('zorg');
	echo menu('user');
	echo "<br>";
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
	echo "<br>";

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
		trigger_error($e->getMessage(), E_USER_ERROR);
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
		<br>
		<form action="/addle.php?show=overview&do=new" method='post'>
			<INPUT type="hidden" name="id" value="<?=$_GET['user_id']?>">
			<input type='submit' class='button' value=" <?=$rs['username']?> zum Addle herausfordern ">
		</FORM>
		<br>
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
		'<br>'
		.Forum::getLatestCommentsbyUser($_GET['user_id'])
		.'</td></tr>'
		.'</table>'
	;

	if ($user->typ != USER_NICHTEINGELOGGT) echo '<br>'.getUserPics($_GET['user_id'], 0);

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
	echo "<br>";

	if(!$_GET['regcode'] && !$_SESSION['user_id'] && $_GET['do'] == 'anmeldung')
	{
		/**
		 * reCAPTCHA v2 initialisieren (inkl. keys)
		 * @include includes/g-recaptcha-src/autoload.php Include Google reCaptcha PHP-Class and Methods
		 * @include googlerecaptchaapi_key.inc.php Include an Array containing valid Google reCaptcha API Keys
		 * @link https://www.google.com/recaptcha/
		 */
		if (fileExists(__DIR__ .'/includes/g-recaptcha-src/autoload.php'))
		{
			require_once( __DIR__ .'/includes/g-recaptcha-src/autoload.php');
			$reCaptchaApiKeysFile = require_once(__DIR__ .'/includes/googlerecaptchaapi_key.inc.php');
			$reCaptchaApiKeys = (DEVELOPMENT ? $reCaptchaApiKeysFile['DEVELOPMENT'] : $reCaptchaApiKeysFile['PRODUCTION']);
			$reCaptchaLang = 'de-CH'; // reCAPTCHA supported 40+ languages listed here: https://developers.google.com/recaptcha/docs/language
			try {
				$reCaptcha = new \ReCaptcha\ReCaptcha($reCaptchaApiKeys['secret']);
			} catch(Exception $e) {
				error_log(sprintf('[ERROR] Google reCAPTCHA: could not instantiate new ReCaptcha()-Class Object => %s', __FILE__, __LINE__, $e->getMessage()));
				$error = '<font color="red"><b>Google reCAPTCHA konnte nicht geladen werden. Melde uns dieses Problem bitte!</b></font>';
			}
	
			/** reCaptcha validieren */
			if (isset($_POST['g-recaptcha-response']))
			{
				//$reCaptcha = new \ReCaptcha\ReCaptcha($reCaptchaApiKeys['secret']);
				$resp = $reCaptcha->verify($_POST['g-recaptcha-response'], $_SERVER['REMOTE_ADDR']);
	
				/** reCaptcha VALID */
				if ($resp->isSuccess())
				{
					if (empty($_POST['new_username'])) $registerError = t('invalid-username', 'user');
					if (empty($_POST['new_email'])) $registerError = t('invalid-email', 'user');
					if (empty($_POST['new_password']) || empty($_POST['new_password2'])) $registerError = t('invalid-userpw-missing', 'user');
					if ($_POST['new_password'] != $_POST['new_password2']) $registerError = t('invalid-userpw-match', 'user');
					if (!check_email($_POST['new_email'])) $registerError = t('invalid-email', 'user');
	
					/** Userregistrierung schaut gut aus - User anlegen probieren */
					if (!isset($registerError) || empty($registerError))
					{
						$createUserResult = $user->create_newuser(htmlentities($_POST['new_username']), $_POST['new_password'], $_POST['new_password2'], $_POST['new_email']);
						if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> create_newuser() Result: %s', __FILE__, __LINE__, (is_bool($createUserResult)?'true':$createUserResult)));
						if (is_bool($createUserResult) && $createUserResult===true) {
							$error = t('account-confirmation', 'user');
							$smarty->assign('error', ['type' => 'success', 'dismissable' => 'true', 'title' => $error]);
						} else {
							$error = $createUserResult;
							$smarty->assign('error', ['type' => 'warn', 'dismissable' => 'true', 'title' => $error]);
						}
					} elseif (!empty($registerError)) {
						$error = $registerError;
						$smarty->assign('error', ['type' => 'warn', 'dismissable' => 'true', 'title' => $error]);
					}
				}

				/** reCaptcha UNGÜLTIG */
				else {
					foreach ($resp->getErrorCodes() as $code) error_log(sprintf('[ERROR] <%s:%d> Google reCAPTCHA: error code %s', __FILE__, __LINE__, $code));
	            	//$error = '<font color="red"><b>Das reCAPTCHA wurde FALSCH eingegeben oder leer gelassen.<br>Bitte versuch es nochmal!</b></font>';
	            	$error = 'Das reCAPTCHA wurde FALSCH eingegeben oder leer gelassen.';
	            	$smarty->assign('error', ['type' => 'warn', 'dismissable' => 'true', 'title' => $error, 'message' => 'Bitte versuch es nochmal!']);
				}
			}
			if (isset($error) && !empty($error) && $error !== true) $smarty->display('file:layout/elements/block_error.tpl');
			echo "<form action='$_SERVER[PHP_SELF]?do=anmeldung' method='post'>";
			echo "<table width='600' class='case' align='center'>
				<tr><td align='center' class='title' colspan='2'>
				Anmeldung
				</td></tr>";
			//gewuenschter username
			echo "<tr><td align='left'>
				<b>Gew&uuml;nschter Benutzername:</b><br>
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
				<b>E-Mail Adresse:</b><br>
				<b class='small'>(du bekommst einen Aktivierungscode per E-Mail zugeschickt)</b>
				</td><td align='left'>
				<input type='text' name='new_email' class='text' size='30' value='$_POST[new_email]'>
				</td></tr>";
			// reCAPTCHA v2 form
			echo '<tr><td align="left" colspan="2">
				<div style="display:inline-block;" class="g-recaptcha" data-sitekey="'.$reCaptchaApiKeys['key'].'"></div>
	            <script type="text/javascript" src="https://www.google.com/recaptcha/api.js?hl='.$reCaptchaLang.'"></script>
	            </td></tr>';
			//submit button
			echo "<tr><td align='left' colspan='2'>
				<input type='submit' name='newuser' class='button' value='Account registrieren'>
				</td></tr>
				</table>";
			echo "</form>";
			echo "<br><br>";
		}
		else {
			$smarty->assign('error', ['type' => 'warn', 'dismissable' => 'false', 'title' => 'Die Accountregistrierung steht zur zeit nicht zur Verfügung. Melde uns dieses Problem bitte, damit wir es schnellstmöglich beheben können.']);
			$smarty->display('file:layout/elements/block_error.tpl');
		}

		/** neues passwort zusenden */
		if (!empty($_POST['email'])) $email2check = sanitize_userinput($_POST['email']);
		$checkEmail = (!empty($email2check) ? check_email($email2check) : '');
		if ($checkEmail === false)
		{
			error_log(sprintf('[NOTICE] <%s:%d> Passwort reset for e-mail was requested, but e-mail is invalid: "%s"', __FILE__, __LINE__, $email2check)); // nur intern Fehler loggen - nicht nach aussen exponieren
		} elseif ($checkEmail === true) {
			$user->new_pass($email2check); // Send new Password to User
		}
		if (isset($email2check))
		{
			$smarty->assign('error', ['type' => 'info', 'dismissable' => 'true', 'title' => t('newpass-confirmation', 'user')]);
			$smarty->display('file:layout/elements/block_error.tpl');
		}

		echo "<form action='$_SERVER[PHP_SELF]?do=anmeldung' method='post'>
			<table width='600' class='case' align='center'>
			<tr><td align='center' class='title' colspan='2'>
			<b>Passwort vergessen ?</b>
			</td></tr><tr><td align='left'>
			<b>E-Mail Adresse:</b>
			</td><td align='left'>
			<input type='text' name='email' size='40' class='text'>
			</td></tr><tr><td align='left' colspan='2'>
			<b class='small'>Achtung!<br>Hiermit wird dir ein neues Passwort gesetzt und zugesendet, dieses kannst du sp&auml;ter wieder &auml;ndern!</b>
			<br><br>
			<input type='submit' name='send' value='neues Passwort zusenden' class='button'>
			</td></tr></table></form>";
	}

	/** user reaktivieren */
	elseif($_GET['regcode']) {
		$new_user = $user->activate_user($_GET['regcode']);
		$sql = "UPDATE user set active = 1 WHERE username='$new_user'";
		$db->query($sql,"profil.php",297);
		echo "<b>".$new_user."</b>";
	}
	$smarty->display('file:layout/footer.tpl');
}
