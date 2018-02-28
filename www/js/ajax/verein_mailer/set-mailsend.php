<?php
/**
 * AJAX Request validation
 */
if(!isset($_GET['action']) || empty($_GET['action']) || $_GET['action'] != 'send')
{
	http_response_code(400); // Set response code 400 (bad request) and exit.
	die('Invalid or missing POST-Parameter');
}

/**
 * FILE INCLUDES
 */
require_once( __DIR__ .'/../../../includes/main.inc.php');

/**
 * Array with recipients
 * parse, cleanup & type conversion from string => int
 */
$recipients = str_replace('"', '', $_POST['hidden_selected_recipients']);
$recipients = preg_split('/,/', $recipients, null, PREG_SPLIT_NO_EMPTY);
$recipients = array_unique($recipients); // Remove duplicates
sort($recipients);

/** Validate recipients */
if (!is_array($recipients) || count($recipients) <= 0)
{
	http_response_code(400); // Set response code 400 (bad request) and exit.
	die('Invalid or missing values');

/** Validate Template ID */
} elseif (!empty($_POST['template_id']) && is_numeric($_POST['template_id'])) {
	$leMailTemplate = 'email/verein/verein_htmlmail.tpl';

	foreach ($recipients as $recipient_id) {
		try {
			/** Get Recipient's E-Mail address */
			$recipientEmail = $user->id2useremail($recipient_id);

			if (!empty($recipientEmail))
			{
				/**
				 * Compile the template
				 */
				$smarty->assign('mail_param', $_POST['template_id']);
				$smarty->assign('user_param', $recipient_id);
				$smarty->assign('hash_param', md5($_POST['template_id'] . $recipient_id) );
				$smarty->assign('user_email', $recipientEmail);
				$compiledMailTpl = $smarty->fetch('file:' . $leMailTemplate);	
	
				/** Cleanup Smarty-Tags from HTML-Markup */
				$compiledMailTpl = str_replace('{literal}', '', $compiledMailTpl);
				$compiledMailTpl = str_replace('{/literal}', '', $compiledMailTpl);
	
				/** Create new E-Mail message entry for recipient */
				error_log('[INFO] Creating a new E-Mail message for user ' . $recipient_id . ' based on template ' . $_POST['template_id']);
				$insertMailQuery = 'INSERT INTO verein_correspondence
										(communication_type, subject_text, preview_text, message_text, template_id, sender_id, recipient_id)
									SELECT
										communication_type,
										subject_text,
										preview_text,
										"'.escape_text($compiledMailTpl).'" as message_text,
										template_id,
										sender_id,
										'.$recipient_id.' as recipient_id
									FROM verein_correspondence
									WHERE template_id = '.$_POST['template_id'].' AND recipient_id = '.VORSTAND_USER;
				$messageId = $db->query($insertMailQuery, __FILE__, __LINE__, 'AJAX.POST(set-mailsend)');
	
				if ( isset($messageId) && $messageId > 0 )
				{
					error_log('[INFO] Sending E-Mail to user ' . $recipient_id);
	
					/** Query Message Parameters */
					$readParametersQuery = 	'SELECT
												subject_text, message_text
											 FROM verein_correspondence
											 WHERE id = ' . $messageId;
					$mailMessage = mysql_fetch_assoc($db->query($readParametersQuery, __FILE__, __LINE__, 'AJAX.POST(set-mailsend)'));
	
					/** E-Mail Headers */
					$senderEmail = ZORG_EMAIL;
					error_log('[INFO] Sending E-Mail from ' . $senderEmail);
					$mailTo = sprintf('%s <%s>', $user->id2user($recipient_id), $recipientEmail);
					$mailHeaders = "From: $senderEmail\r\n";
					$mailHeaders .= 'Reply-To: '.ZORG_VEREIN_EMAIL."\r\n";
					$mailHeaders .= 'MIME-Version: 1.0'."\r\n";
					$mailHeaders .= 'Content-Type: text/html; charset="utf-8"'."\r\n";
					$mailHeaders .= 'X-Mailer: PHP/'.phpversion()."\r\n";
	
					/** Send E-Mail */
					if ( mail($mailTo, $mailMessage['subject_text'], $mailMessage['message_text'], $mailHeaders) )
					{
						/** Success! */
						error_log('[OK] Successfully sent E-Mail «'.$mailMessage['subject_text'].'» to ' . $mailTo . ' (user id '.$recipient_id.')');
						http_response_code(200); // Set response code 200 (OK)
						echo $recipient_id;
	
					} else {
						
						/** Failed */
						error_log('[ERROR] Failed to send E-Mail «'.$mailMessage['subject_text'].'» to ' . $mailTo);
						http_response_code(500); // Set response code 500 (internal server error)
						die('Could not send e-mail message to user ' . $recipient_id);
					}

				} else {
					http_response_code(500); // Set response code 500 (internal server error)
					die('Could not create new message for user ' . $recipient_id);
				}
			} else {
				http_response_code(500); // Set response code 500 (internal server error)
				die('Email not found or invalid for user ' . $recipient_id);
			}
		} catch(Exception $e) {
			http_response_code(500); // Set response code 500 (internal server error)
			echo $e->getMessage();
		}
	}
} else {
	http_response_code(500); // Set response code 500 (internal server error)
	die('Referenced template id is invalid');
}
