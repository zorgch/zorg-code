<?php
/**
 * @include main.inc.php
 */
require_once( __DIR__ . '/includes/main.inc.php');

/**
 * Smarty variables
 */
$smarty->assign('tplroot', array('page_title' => 'Zorg Verein Mailer'));

/**
 * Show a mail message's webview
 */
if((!empty($_GET['mail']) && is_numeric($_GET['mail'])) && (!empty($_GET['user']) && is_numeric($_GET['user']) ) && !empty($_GET['hash']))
{
	/**
	 * Match Hash from URL against Database
	 */
	try {
		$matchingAgainst = md5($_GET['mail'] . $_GET['user']);
		/** ORDER BY id DESC = ensures, if same message was sent multiple times, that the NEWEST is displayed (and not the oldest) */
		$checkHashQuery = 'SELECT template_id, recipient_id, message_text, MD5(CONCAT(template_id, recipient_id)) as hash
				FROM verein_correspondence
				WHERE MD5(CONCAT(template_id, recipient_id)) = "' . $_GET['hash'] . '"
				ORDER BY id DESC';
		$matchedResult = mysql_fetch_assoc($db->query($checkHashQuery, __FILE__, __LINE__, 'verein_mailer.php'));

		/** if Hash returned a result... */
		if ($matchedResult && $_GET['hash'] == $matchedResult['hash'])
		{
			if ($matchedResult['template_id'] == $_GET['mail'] && $matchedResult['recipient_id'] == $_GET['user'])
			{
				/**
				 * Update EMAIL read status
				 */
				error_log('[DEBUG] Updating Read State for template ' . $matchedResult['template_id'] . ' and user ' . $matchedResult['recipient_id']);
				$updateReadStateQuery = 'UPDATE verein_correspondence
										 SET
										 	recipient_confirmation = TRUE,
										 	recipient_confirmationdate = NOW()
										 WHERE
										 	recipient_confirmation != TRUE
										 	AND template_id = '.$matchedResult['template_id'].'
										 	AND recipient_id = '.$matchedResult['recipient_id'];
				$updateReadState = mysql_fetch_assoc($db->query($checkHashQuery, __FILE__, __LINE__, 'verein_mailer.php'));
				
				/**
				 * Show web-view of the E-Mail Message
				 */
				http_response_code(200); // Set response code 200 (OK)
				echo $matchedResult['message_text'];
			
			/** Template and/or User IDs do NOT match... */
			} else {
				http_response_code(403); // Set response code 403 (forbidden)
				$smarty->display('file:layout/head.tpl');
				user_error('Nice try - aber Du dörfsch die Message nöd aluege. Yarak!', E_USER_NOTICE);
				$smarty->display('file:layout/footer.tpl');
			}

		/** Hash did NOT match... */
		} else {
			http_response_code(403); // Set response code 403 (forbidden)
			$smarty->display('file:layout/head.tpl');
			user_error('Nope - do stimmt was nöd. Tschau.', E_USER_NOTICE);
			$smarty->display('file:layout/footer.tpl');
		}

	}
	catch(Exception $e) {
		http_response_code(500); // Set response code 500 (internal server error)
		echo $e->getMessage();
	}
	/** mail, user & hash are are NOT set... 
	} else {
		http_response_code(403); // Set response code 403 (forbidden)
		$smarty->display('file:layout/head.tpl');
		user_error('Irgendwas isch schief glaufe - oder fehlt.', E_USER_NOTICE);
		$smarty->display('file:layout/footer.tpl');
	}*/

/**
 * Show Verein Mailer application
 */
} else {
	$smarty->display('file:layout/partials/verein_mailer.tpl');
}
