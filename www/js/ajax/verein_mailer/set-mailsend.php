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
require_once( __DIR__ .'/../../../includes/config.inc.php');
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
}

/** Validate Template ID */
elseif (!empty($_POST['template_id']) && is_numeric($_POST['template_id']))
{
	$leMailTemplate = 'email/verein/verein_htmlmail.tpl';

	foreach ($recipients as $recipient_id)
	{
		/** Get Recipient's E-Mail address */
		//error_log('[DEBUG] Processing $recipient_id: ' . $recipient_id);
		try {
			//$recipientEmail = $user->id2useremail($recipient_id); //--> fails when user has disabled 'email_notification'
			$recipientEmailQuery = 'SELECT email FROM user WHERE id = ' . $recipient_id;
			$recipientEmailResult = $db->fetch($db->query($recipientEmailQuery, __FILE__, __LINE__, 'AJAX.POST(set-mailsend)'));
			$recipientEmail = $recipientEmailResult['email'];
		} catch(Exception $e) {
			error_log($e->getMessage());
			http_response_code(500); // Set response code 500 (internal server error)
			echo $e->getMessage();
		}

		if (!empty($recipientEmail) && check_email($recipientEmail))
		{
			/** Compile the template */
			$mailRecipientHash = md5($_POST['template_id'] . $recipient_id);
			$smarty->assign('mail_param', $_POST['template_id']);
			$smarty->assign('user_param', $recipient_id);
			$smarty->assign('hash_param', $mailRecipientHash );
			$smarty->assign('user_email', $recipientEmail);
			$compiledMailTpl = $smarty->fetch('file:' . $leMailTemplate);	

			/** Cleanup Smarty-Tags from HTML-Markup */
			$compiledMailTpl = str_replace('{literal}', '', $compiledMailTpl);
			$compiledMailTpl = str_replace('{/literal}', '', $compiledMailTpl);

			/**
			 * Create new E-Mail message entry for recipient
			 * @TODO To be discussed: make this work with with "ON DUPLICATE KEY UPDATE..."?
			 */
			try {
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
			} catch(Exception $e) {
				error_log($e->getMessage());
				http_response_code(500); // Set response code 500 (internal server error)
				echo $e->getMessage();
			}

			if ( isset($messageId) && $messageId > 0 )
			{
				error_log('[INFO] Sending E-Mail to user ' . $recipient_id);

				/** Query Message Parameters */
				try {
					$readParametersQuery = 	'SELECT
												subject_text, message_text
											 FROM verein_correspondence
											 WHERE id = ' . $messageId;
					$mailMessage = $db->fetch($db->query($readParametersQuery, __FILE__, __LINE__, 'AJAX.POST(set-mailsend)'));
				} catch(Exception $e) {
					error_log($e->getMessage());
					http_response_code(500); // Set response code 500 (internal server error)
					echo $e->getMessage();
				}
				$formatNewline  = "\r\n"; // Line breaks
				/**
				 * Define different Mail Boundaries
				 * @link https://stackoverflow.com/a/1880524/5750030
				 */
				$mailBoundaryHeader = 'mailcontenttype-boundary'; // Boundary-Divider instructions for the Mail-Head
				$mailBoundary = '--'.$mailBoundaryHeader; // Boundary-Divider for the Mail-Body
				$mailBoundaryLast = $mailBoundary.'--'; // Boundary-Divider indicating the Mail-End

				/**
				 * base64 encode & character-split $mailMessage
				 *
				 * Fixes: https://stackoverflow.com/questions/12216228/html-email-annoying-line-breaking
				 * E-Mails shouldn't have more than 76 chars per line, for comapitibility reasons
				 * - base64_encode() method encodes the HTML message with base64
				 * - chunk_split() splits the encoded messages into smaller chunks
				 * - the $mailBoundary is used to indicate where the encoded message part starts
				 * - "multipart/alternative" ensures, that only 1 body-part of the e-mail is being displaye
				 *
				 * @link https://ctrlq.org/code/19840-base64-encoded-email
				 * @link https://www.drweb.de/aufbau-von-mime-mails-2/
				 * @link https://www.webdeveloper.com/forum/d/185299-sending-a-html-email-in-base64-help
				 */
				$message_text_b64 = chunk_split(base64_encode($mailMessage['message_text']));

				/**
				 * From:-Address Format "From: Präsident|Aktuar|Kassier <ZORG_EMAIL>\r\n"
				 * @link https://stackoverflow.com/a/10381429/5750030
				 */
				if ($_POST['topic'] === 'president') $senderEmail = 'Präsident <'.ZORG_EMAIL.'>';
				elseif ($_POST['topic'] === 'actuary') $senderEmail = 'Aktuar <'.ZORG_EMAIL.'>';
				elseif ($_POST['topic'] === 'treasurer') $senderEmail = 'Kassier <'.ZORG_EMAIL.'>';
				else $senderEmail = ZORG_EMAIL;

				/**
				 * Build E-Mail
				 */
				$mailTo = sprintf('%s <%s>', $user->id2user($recipient_id), $recipientEmail);
				//$mailHeaders  = 'Subject: '.$mailMessage['subject_text'].$formatNewline;
				$mailHeaders  = 'From: '.$senderEmail.$formatNewline;
				$mailHeaders .= 'Reply-to: '.ZORG_VEREIN_EMAIL.$formatNewline;
				//$mailHeaders  = 'To: '.$mailTo.$formatNewline;
				$mailHeaders .= 'MIME-version: 1.0'.$formatNewline;
				$mailHeaders .= 'X-Mailer: PHP/'.phpversion().$formatNewline;
				$mailHeaders .= 'Content-type: multipart/alternative; boundary="'.$mailBoundaryHeader.'"; charset=utf-8';

					/** Plain-Text E-Mail Part (= lower Prio)
					 * chunk_split() alternative supporting unicode strings
					 * @link https://php.net/manual/en/function.chunk-split.php#118887
					 */
					$message_text_plain_teaser = t('webview-link', 'verein_mailer', [ SITE_URL, $_POST['template_id'], $recipient_id, $mailRecipientHash]).$formatNewline;
					$message_text_plain = trim(mb_ereg_replace('\s{2,}', ' ', mb_ereg_replace('^[\s\S]*?VereinVorstandGVStatutenProtokolleKonto', '', remove_html($compiledMailTpl))));
					$message_text_plain_pattern = '~.{1,76}~u';
					$message_text_plain = rtrim(preg_replace($message_text_plain_pattern, '$0'.$formatNewline, $message_text_plain), $formatNewline);

					$message_text  = $message_text_plain_teaser;
					$message_text .= $formatNewline.$mailBoundary.$formatNewline;
					$message_text .= 'Content-type: text/plain; charset=utf-8'.$formatNewline;
					$message_text .= 'Content-transfer-encoding: quoted-printable'.$formatNewline;
					$message_text .= $formatNewline;
					$message_text .= $message_text_plain_teaser;
					$message_text .= $message_text_plain.$formatNewline;

					/** HTML-E-Mail (base64 encoded) Part (= higher Prio, because last) */
					$message_text .= $mailBoundary.$formatNewline;
					$message_text .= 'Content-type: text/html'.$formatNewline;
					$message_text .= 'Content-transfer-encoding: base64'.$formatNewline;
					$message_text .= $formatNewline;
					$message_text .= $message_text_b64.$formatNewline;
					$message_text .= $mailBoundaryLast;

				/** Send E-Mail */
				if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> %s', __FILE__, __LINE__, $formatNewline.$mailMessage['subject_text'].$formatNewline.$mailHeaders.$formatNewline.$message_text));
				error_log('[INFO] Sending E-Mail from ' . $senderEmail);
				if ( mail($mailTo, $mailMessage['subject_text'], $message_text, $mailHeaders) )
				{
					/** mail(): Success! */
					error_log('[INFO] OK - Successfully sent E-Mail «'.$mailMessage['subject_text'].'» to ' . $mailTo . ' (user id '.$recipient_id.')');
					//echo $recipient_id;
					$response[] = [ 'value' => $recipient_id ];
				} else {
					/**
					 * mail(): Failed
					 * @link http://php.net/manual/de/function.mail.php#121163
					 */
					error_log('[ERROR] Failed to send E-Mail «'.$mailMessage['subject_text'].'» to ' . $mailTo . ': ' . error_get_last()['message']);
					$response[] = [ 'value' => "Failed to send E-Mail to user id: $recipient_id with error: ".error_get_last()['message'] ]; // Don't die - would kill foreach{..}!
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

} else {
	error_log('[ERROR] Referenced template id is invalid');
	http_response_code(500); // Set response code 500 (internal server error)
	die('Referenced template id is invalid');
}
