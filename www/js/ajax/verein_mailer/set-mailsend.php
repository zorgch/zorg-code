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
 * File includes
 */
require_once( __DIR__ .'/../../../includes/main.inc.php');

/**
 * Array with recipients
 * parse, cleanup & type conversion from string => int
 */
//error_log('[DEBUG] Recipients IDs (as passed): ' . $_POST['hidden_selected_recipients']);
$recipients = str_replace('"', '', $_POST['hidden_selected_recipients']);
$recipients = preg_split('/,/', $recipients, null, PREG_SPLIT_NO_EMPTY);
$recipients = array_unique($recipients); // Remove duplicates
sort($recipients);
//error_log('[DEBUG] Recipients IDs (cleaned up):' . print_r($recipients, TRUE));

/** Validate recipients */
if (!is_array($recipients) || count($recipients) <= 0)
{
	http_response_code(400); // Set response code 400 (bad request) and exit.
	die('Invalid or missing values');

/** Validate Template ID */
} elseif (!empty($_POST['template_id']) && is_numeric($_POST['template_id'])) {
	$leMailTemplate = 'email/verein/verein_htmlmail.tpl';

	try {
		foreach ($recipients as $recipient_id) {
			//error_log('[DEBUG] Processing $recipient_id: ' . $recipient_id);
			
			/** Get Recipient's E-Mail address */
			//$recipientEmail = $user->id2useremail($recipient_id); //--> fails when user has disabled 'email_notification'
			$recipientEmailQuery = 'SELECT email FROM user WHERE id = ' . $recipient_id;
			$recipientEmailResult = mysql_fetch_assoc($db->query($recipientEmailQuery, __FILE__, __LINE__, 'AJAX.POST(set-mailsend)'));
			$recipientEmail = $recipientEmailResult['email'];

			if (!empty($recipientEmail))
			{
				/** Compile the template */
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
						error_log('[INFO] OK - Successfully sent E-Mail «'.$mailMessage['subject_text'].'» to ' . $mailTo . ' (user id '.$recipient_id.')');
						//echo $recipient_id;
						$response[] = [ 'value' => $recipient_id ];
	
					} else {
						
						/** Failed */
						error_log('[ERROR] Failed to send E-Mail «'.$mailMessage['subject_text'].'» to ' . $mailTo);
						$response[] = [ 'value' => "Failed to send E-Mail to user id: $recipient_id" ]; // Don't die - would kill foreach{..}!
					}

				} else {
					error_log('[ERROR] Could not create new message for user '.$recipient_id);
					$response[] = [ 'value' => "Could not create new message for user id: $recipient_id" ]; // Don't die - would kill foreach{..}!
				}
			} else {
				error_log('[ERROR] Email not found, invalid or not allowed for user '.$recipient_id);
				$response[] = [ 'value' => "Email not found, invalid or not allowed for user id: $recipient_id" ]; // Don't die - would kill foreach{..}!
			}
		}
		
		/** Return results */
		//if (isset($successful) && count($successful) > 0)
		if (isset($response) && is_array($response))
		{
			//error_log('[DEBUG] Return $successful: ' . json_encode($response));
			http_response_code(200); // Set response code 200 (OK)
			header('Content-type: application/json');
			echo json_encode($response);
		} else {
			http_response_code(500); // Set response code 500 (internal server error)
			echo 'Oops... something went completely wrong :(';
		}
		
	} catch(Exception $e) {
		error_log($e->getMessage());
		http_response_code(500); // Set response code 500 (internal server error)
		echo $e->getMessage();
	}
	
} else {
	error_log('[ERROR] Referenced template id is invalid');
	http_response_code(500); // Set response code 500 (internal server error)
	die('Referenced template id is invalid');
}
