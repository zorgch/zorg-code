<?php
/**
 * Profile pages
 *
 * @package zorg\Usersystem
 *
 */
/**
 * File includes
 * @include main.inc.php required
 * @include core.model.php required
 */
require_once __DIR__.'/includes/config.inc.php';
require_once MODELS_DIR.'core.model.php';

/**
 * Validate GET-Parameters
 */
$doAction = (isset($doAction) ? $doAction : (filter_input(INPUT_GET, 'do', FILTER_DEFAULT, FILTER_REQUIRE_SCALAR) ?? null)); // $_GET['do']
$postDoAction = filter_input(INPUT_POST, 'do', FILTER_DEFAULT, FILTER_REQUIRE_SCALAR) ?? null; // $_POST['do']
$userRegcode = filter_input(INPUT_GET, 'regcode', FILTER_DEFAULT, FILTER_REQUIRE_SCALAR) ?? null; // $_GET['regcode']
$user_id = (isset($getUserId) ? intval($getUserId) : (filter_input(INPUT_GET, 'user_id', FILTER_VALIDATE_INT) ?? null)); // $_GET['user_id']
$view_as_user = filter_input(INPUT_GET, 'viewas', FILTER_VALIDATE_INT) ?? null; // $_GET['viewas']
$messageToUsers = filter_input(INPUT_GET, 'msgusers', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? null; // $_GET['msgusers']
$messageSubject = filter_input(INPUT_GET, 'msgsubject', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? null; // $_GET['msgsubject']

//=============================================================================
// Layout & code
//=============================================================================
/**
 * Initialise MVC Model
 */
$model = new MVC\Profile();
$model->showOverview($smarty);

/** Messaging update */
Messagesystem::execActions($postDoAction);

/**
 * Userlist anzeigen
 */
if (empty($doAction) && empty($user_id) && empty($userRegcode))
{
	$smarty->display('file:layout/head.tpl');
	$smarty->display('tpl:219');
	$smarty->display('file:layout/footer.tpl');
	exit; // make sure only Userlist is processed / displayed
}

/**
 * Mein Profil
 */
if ($doAction === 'view')
{
	if (empty($user_id) && $user->is_loggedin())
	{
		/**
		 * Profil als anderen User anzeigen (DEV only!)
		 */
		if (zorgDebugger::log()->isDevelopmentEnvironment && $view_as_user > 0)
		{
			$model->showOtherprofile($smarty, $user, $_GET['viewas']);
			$smarty->assign('error', ['type' => 'info', 'dismissable' => 'false', 'title' => 'Userprofil wird angezeigt als <strong>'.$user->id2user((int)$_GET['viewas'], TRUE).'</strong>']);

			/** Switch to "viewas"-User */
			$saveMyUserID = $user->id;
			$_SESSION['user_id'] = (int)$_GET['viewas'];
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
		 * Mein Profil anzeigen + updaten
		 */
		} else {//if ($doAction === 'view' || empty($user_id)) {
			$model->showProfileupdate($smarty);

			/** Update Userprofile infos & settings */
			if (!empty($postDoAction))
			{
				if ($user->id > 0)
				{
					if($postDoAction === 'update' && $_FILES['image']['error'] === 4)
					{
						/** Validate $_POST-request */
						zorgDebugger::log()->debug('$_POST: %s', [print_r($_POST,true)]);
						if (count($_POST) > 1)
						{
							$changeprofile_result = $user->exec_changeprofile($user->id, $_POST);
						}
					}
					/** Upload and change new Userpic */
					if($postDoAction === 'update' && $_FILES['image']['error'] === 0)
					{
						$uploadimage_result = $user->exec_uploadimage($user->id, $_FILES);
					}
					/** Change User Password */
					if($postDoAction === 'change_password')
					{
						$newpassword_result = $user->exec_newpassword($user->id, $_POST['old_pass'], $_POST['new_pass'], $_POST['new_pass2']);
					}
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
			}

			/** Display "Mein Profil ändern" */
			$smarty->assign('form_action', '?do=view');
			$smarty->display('file:layout/pages/profile_page.tpl');

			exit; // make sure only personal Profile page is processed / displayed
		}
	} else {
		http_response_code(403); // Set response code 403 (Forbidden)
		$model->showOverview($smarty);
		$smarty->assign('error', ['type' => 'warn', 'dismissable' => 'false', 'title' => t('invalid-username', 'user')]);
		$smarty->display('file:layout/head.tpl');
		$smarty->display('file:layout/footer.tpl');
		exit;
	}
}

/**
 * Userprofil anzeigen
 */
if (!empty($user_id) && $user_id>0)
{
	$htmlOutput = null;
	$sidebarHtml = null;
	$username = $user->id2user($user_id, true);

	/** Check for invalid User ID */
	if ($username === false)
	{
		http_response_code(404); // Set response code 404 (not found)
		$model->showUnknownuser($smarty, $user_id);
		$smarty->assign('error', ['type' => 'warn', 'dismissable' => 'false', 'title' => t('invalid-id', 'user')]);
		$smarty->display('file:layout/head.tpl');
		$smarty->display('file:layout/footer.tpl');
		exit;
	}

	// $sql = 'SELECT * FROM user WHERE id=?';
	// $result = $db->query($sql, __FILE__, __LINE__, 'Userprofil anzeigen', [$user_id]);
	// $rs = $db->fetch($result);
	if (isset($_geaechtet[$user_id])) $smarty->assign('error', ['type' => 'info', 'dismissable' => 'true', 'title' => t('user-wird-geaechtet', 'user', $username)]);

	$htmlOutput .= '<h1>'.html_entity_decode($username).'</h1>';
	$htmlOutput .= '<img src="'.$user->userImage($user_id, 1).'">';//style="width: 100%;max-width: 100%;"

	/** User Addle (nur wenn Viewer selber eingeloggt ist) */
	if ($user->is_loggedin() && $user_id !== $user->id && $user->userPlays($user_id, 'addle'))
	{
		$sidebarHtml .= '<h3>Addle</h3>
		<form action="/addle.php?show=overview&do=new" method="post">
			<input type="hidden" name="id" value="'.$user_id.'">
			<input type="submit" class="button" value="'.$_users['username'].' zum Addle herausfordern">
		</form>';
	}

	if($user->is_loggedin())
	{
		/** User Messaging: der User das bin ich */
		if($user_id === $user->id)
		{
			/** User will eine neue Message senden */
			if($doAction === 'newmsg')
			{
				if(!empty($messageToUsers) && !empty($messageSubject)) {
					$htmlOutput .= Messagesystem::getFormSend($messageToUsers, $messageSubject,'');
				} else {
					$htmlOutput .= Messagesystem::getFormSend(0,'','');
				}
			/** User will Inbox sehen */
			} else {
				$box   = isset( $_GET['box'] ) ? $_GET['box'] : null;
				$page  = isset( $_GET['page'] ) ? $_GET['page'] : null;
				$sort  = isset( $_GET['sort'] ) ? $_GET['sort'] : null;
				$order = isset( $_GET['order'] ) ? $_GET['order'] : null;
				$htmlOutput .= Messagesystem::getInboxHTML($box, 11, $page, $sort, $order);
			}

		/** Der User ist jemand anderes */
		} else {
			$htmlOutput .= Messagesystem::getFormSend(array($user_id), '', '');
		}

		/** User markierte Gallery-Pics */
		if (!empty($user->vereinsmitglied)) $htmlOutput .= getUserPics($user_id, 0);
	}

	/** User Events */
	$sidebarHtml .= $smarty->fetch('tpl:211');

	/** User Post-Statistik */
	$sidebarHtml .= '<h3>Forum Stats</h3><img src="/images/stats.php?user_id='.$user_id.'&amp;w=490&amp;h=350" style="width: 100%;max-width: 100%;"><br>';

	/** User last Posts */
	$sidebarHtml .= Forum::getLatestCommentsbyUser($user_id);

	/** Layout */
	$model->showUserprofile($smarty, $user, $user_id);
	$smarty->assign('sidebarHtml', $sidebarHtml);
	$smarty->display('file:layout/head.tpl');
	echo $htmlOutput;
	$smarty->display('file:layout/footer.tpl');

	exit; // make sure only Userprofile page is processed / displayed
}
/** Malformatted User ID */
elseif (!empty($user_id)) {
	http_response_code(404); // Set response code 404 (not found)
	$model->showUnknownuser($smarty, $user_id);
	$smarty->assign('error', ['type' => 'warn', 'dismissable' => 'false', 'title' => t('invalid-id', 'user')]);
	$smarty->display('file:layout/head.tpl');
	$smarty->display('file:layout/footer.tpl');
	exit;
}

/**
 * Registrationsformular / User activation
 *
 * @TODO separate code & view by moving the HTML-parts to a Smarty-Template
 */
if (!$user->is_loggedin() && $doAction === 'anmeldung' || !empty($userRegcode))
{
	/**
	 * Registrationsformular anzeigen
	 */
	if(empty($userRegcode) && !isset($_SESSION['user_id']))
	{
		$model->showLogin($smarty);
		$smarty->display('file:layout/head.tpl');

		/**
		 * reCAPTCHA v2 initialisieren (inkl. keys)
		 * @include includes/g-recaptcha-src/autoload.php Include Google reCaptcha PHP-Class and Methods
		 * @include googlerecaptchaapi_key.inc.php Include an Array containing valid Google reCaptcha API Keys
		 * @link https://www.google.com/recaptcha/
		 */
		if (fileExists(INCLUDES_DIR.'g-recaptcha-src/autoload.php'))
		{
			require_once INCLUDES_DIR.'g-recaptcha-src/autoload.php';
			$reCaptchaApiKeys = ['key' => $_ENV['GOOGLE_RECAPTCHA_KEY'],'secret' => $_ENV['GOOGLE_RECAPTCHA_SECRET']];
			$reCaptchaLang = $_ENV['GOOGLE_RECAPTCHA_LOCALE'];
			$reCaptchaVerification = filter_input(INPUT_POST, 'g-recaptcha-response', FILTER_DEFAULT, FILTER_REQUIRE_SCALAR) ?? null; // $_POST['g-recaptcha-response']
			try {
				$reCaptcha = new \ReCaptcha\ReCaptcha($reCaptchaApiKeys['secret']);
			} catch(Exception $e) {
				error_log(sprintf('[ERROR] Google reCAPTCHA: could not instantiate new ReCaptcha()-Class Object => %s', __FILE__, __LINE__, $e->getMessage()));
				$error = '<font color="red"><b>Google reCAPTCHA konnte nicht geladen werden. Melde uns dieses Problem bitte!</b></font>';
			}

			/** reCaptcha validieren */
			if (!empty($reCaptchaVerification))
			{
				//$reCaptcha = new \ReCaptcha\ReCaptcha($reCaptchaApiKeys['secret']);
				$resp = $reCaptcha->verify($reCaptchaVerification, $_SERVER['REMOTE_ADDR']);

				/** reCaptcha VALID */
				if ($resp->isSuccess())
				{
					$registerError = null;
					$newUsername = htmlentities(sanitize_userinput(filter_input(INPUT_POST, 'new_username', FILTER_DEFAULT, FILTER_REQUIRE_SCALAR))) ?? null; // $_POST['new_username']
					$newEmail = filter_input(INPUT_POST, 'new_email', FILTER_VALIDATE_EMAIL) ?? null; // $_POST['new_email']
					$newPassword = $user->crypt_pw($_POST['new_password2']);
					$comparePassword = password_verify($_POST['new_password'], $newPassword);
					if (empty($newUsername)) $registerError = t('invalid-username', 'user');
					if (empty($newEmail)) $registerError = t('invalid-email', 'user');
					if (!check_email($newEmail)) $registerError = t('invalid-email', 'user');
					if (empty($newPassword)) $registerError = t('invalid-userpw-missing', 'user');
					if (!$comparePassword) $registerError = t('invalid-userpw-match', 'user');

					/** Userregistrierung schaut gut aus - User anlegen probieren */
					if (empty($registerError))
					{
						$createUserResult = $user->create_newuser($newUsername, $newPassword, $newEmail);
						zorgDebugger::log()->debug('Result: %s', [(is_bool($createUserResult)?'true':$createUserResult)]);
						if ($createUserResult===true) {
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
	            	$smarty->assign('error', ['type' => 'warn', 'dismissable' => 'true', 'title' => 'Bitte versuch es nochmal!', 'message' => $error]);
				}
			}

			/**
			 * Anmeldeform anzeigen
			 * (default, oder bi Errors)
			 */
			echo '<form action="?do=anmeldung#newuser" method="post" style="font-size: 0.65rem">';
			echo '<h1 id="newuser">Neuen User erstellen</h1>';
			if ($smarty->get_template_vars('error') != null) $smarty->display('file:layout/elements/block_error.tpl');
			//if ($smarty->getTemplateVars('error') != null) $smarty->display('file:layout/elements/block_error.tpl'); // Smarty 3.x
			/** username eingeben */
			echo '<fieldset>';
			echo '<label>Gew&uuml;nschter Benutzername
					<br><input type="text" class="text" name="new_username" value="'.(isset($error) && !empty($error) ? htmlspecialchars($newUsername) : '').'">
					</label>
					<br><span class="tiny info">Clan Tag kannst du sp&auml;ter separat angeben</span>
				</fieldset>';
			/** passwort setzen */
			echo '<fieldset>';
			echo '<label>Passwort<br>
					<input type="password" class="text" name="new_password">
					</label>
				<br><label>Passwort wiederholen<br>
					<input type="password" class="text" name="new_password2">
					</label>
				</fieldset>';
			/** email adresse eingeben */
			echo '<fieldset>';
			echo '<label>E-Mail Adresse
					<br><input type="text" name="new_email" class="text" value="'.(isset($error) && !empty($error) ? htmlspecialchars($newEmail) : '').'">
					</label>
					<br><span class="tiny info">Du bekommst einen Aktivierungscode per E-Mail zugeschickt
				</fieldset>';
			/** reCAPTCHA v2 form */
			echo '<fieldset>';
			echo '<div style="display:inline-block;" class="g-recaptcha" data-sitekey="'.$reCaptchaApiKeys['key'].'" data-theme="'.($zorgLayout->layouttype === 'day' ? 'light' : 'dark').'"></div>
	            	<script type="text/javascript" src="https://www.google.com/recaptcha/api.js?hl='.$reCaptchaLang.'"></script>
	            </fieldset>';
			/** abschickäää */
			echo '<fieldset>
					<input type="submit" name="newuser" class="button primary" value="Account erstellen">
				</fieldset>';
			echo '</form>';
		}
		/** reCAPTCHA not found / not loaded */
		else {
			error_log(sprintf('[ERROR] <%s:%d> g-recaptcha-src/autoload.php could not be loaded!', __FILE__, __LINE__));
			$smarty->assign('error', ['type' => 'warn', 'dismissable' => 'false', 'title' => 'Die Accountregistrierung steht zur zeit nicht zur Verfügung. Melde uns dieses Problem bitte, damit wir es schnellstmöglich beheben können.']);
			$smarty->display('file:layout/elements/block_error.tpl');
		}

		/**
		 * Passwort vergessen Formular
		 */
		$email2check = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL) ?? null; // $_POST['email']
		if (!empty($email2check))
		{
			$checkEmail = (!empty($email2check) ? check_email($email2check) : null);
			if ($checkEmail === false)
			{
				/** Fehler nur INTERN loggen - nicht nach aussen exponieren! */
				error_log(sprintf('[NOTICE] <%s:%d> Passwort reset was requested, but e-mail is invalid: "%s"', __FILE__, __LINE__, $email2check));
			} elseif ($checkEmail === true) {
				/** Passwort reset triggern */
				$pwreset_error = $user->new_pass($email2check); // Send new Password to User
			}
			if (isset($email2check))
			{
				$smarty->assign('error', ['type' => 'info', 'dismissable' => 'true', 'title' => t('newpass-confirmation', 'user'), 'message' => t('newpass-confirmation-text', 'user')]);
			}
			if ($smarty->get_template_vars('error') != null) $smarty->display('file:layout/elements/block_error.tpl');
			//if ($smarty->getTemplateVars('foo') != null) $smarty->display('file:layout/elements/block_error.tpl'); // Smarty 3.x
		}
		echo '<form action="?do=anmeldung#pwreset" method="post" style="font-size: 0.65rem;margin-top: 60px;">
			<h2 id="pwreset">Passwort vergessen?</h2>
			<b class="small">Achtung!<br>Hiermit wird dir ein neues Passwort gesetzt und zugesendet, dieses kannst du sp&auml;ter wieder &auml;ndern!</b>
			<fieldset>
				<label>E-Mail Adresse<br>
					<input type="text" class="text" name="email">
				</label>
			<fieldset>
				<input type="submit" class="button secondary" name="send" value="neues Passwort zusenden">
			</fieldset>
			</form>';
	}

	/**
	 * Neuen User mittels Regcode aktivieren
	 */
	elseif (!empty($userRegcode))
	{
		zorgDebugger::log()->debug('$userRegcode: %s', [$userRegcode]);
		$user_activation_result = $user->activate_user($userRegcode);
		$model->showActivation($smarty, $user->error_message);
		if ($user_activation_result === true) $smarty->assign('error', ['type' => 'success', 'dismissable' => 'false', 'title' => t('account-activated', 'user'), 'message' => t('account-activated-text', 'user')]);
		else $smarty->assign('error', ['type' => 'warn', 'dismissable' => 'false', 'title' => $user->error_message]);
		$smarty->display('file:layout/head.tpl');
		//if ($user_activation_result === true) $smarty->display('file:layout/partials/loginform.tpl'); => Form Redirect-Error
	}

	$smarty->display('file:layout/footer.tpl');
}
