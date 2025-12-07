<?php
/**
 * zorg Messages
 *
 * @package zorg\Messagesystem
 */
/**
 * File includes
 */
require_once __DIR__.'/includes/messagesystem.inc.php';
require_once MODELS_DIR.'core.model.php';

/**
 * Initialise MVC Model
 */
$model = new MVC\Messagesystem();

$model->showOverview($smarty);
$html = '';

if ($user->is_loggedin())
{
	/** Validate passed GET-Parameters */
	$messageId = intval(filter_var($_GET['message_id'], FILTER_VALIDATE_INT)) ?? null; // $_GET['message_id']
	if (DEVELOPMENT === true) error_log(sprintf('[DEBUG] <%s:%d> $messageId: %d', __FILE__, __LINE__, $messageId));
	$postMessageAction = filter_input(INPUT_POST, 'action', FILTER_SANITIZE_SPECIAL_CHARS) ?? null; // $_POST['action']

	Messagesystem::execActions($postMessageAction);

	if (empty($messageId))
	{
		$model->showInvalidmessage($smarty, $messageId);
		http_response_code(404); // Set response code 404 (not found) and exit.
		$html = $smarty->fetch('file:layout/head.tpl');
		$smarty->assign('error', ['type' => 'warn', 'dismissable' => 'false', 'title' => 'Keine Nachricht angegeben!']);
		$html .= $smarty->fetch('file:layout/elements/block_error.tpl');

		//die($html);
	}
	/** Message-ID ist grundsätzlich valide */
	else {
		$messageDetails = Messagesystem::getMessageDetails($messageId);

		/** Nachricht NICHT gefunden */
		if ($messageDetails === false || empty($messageDetails))
		{
			http_response_code(400); // Set response code 400 (bad request) and exit.
			$model->showInvalidmessage($smarty, $messageId);
			$html = $smarty->fetch('file:layout/head.tpl');
			$smarty->assign('error', ['type' => 'warn', 'dismissable' => 'false', 'title' => 'Nachricht '.$messageId.' konnte nicht geladen werden.']);
			$html .= $smarty->fetch('file:layout/elements/block_error.tpl');
		}

		/** Nachricht gefunden & darf vom aktuellen User gelsen werden */
		elseif (intval($messageDetails['owner']) === $user->id)
		{
			$model->showMessage($smarty, $user, $messageId, $messageDetails['from_user_id'], $messageDetails['subject']);
			$html = $smarty->fetch('file:layout/head.tpl');

			$html .= Messagesystem::displayMessage($messageId);

			if(!is_int(strpos($messageDetails['subject'], 'Re:'))) {
				$subject = $messageDetails['subject'];
			} else {
				$subject = 'Re: '.$messageDetails['subject'];
			}

			$html .= '<br />';
			$preselect_to_users = [];
			if (intval($messageDetails['from_user_id']) === $user->id) $preselect_to_users = $messageDetails['to_users'];
			else $preselect_to_users[] = intval($messageDetails['from_user_id']);
			$html .= Messagesystem::getFormSend(
						 $preselect_to_users
						,$subject, '> '.str_replace("\n", "\n> "
						,$messageDetails['text'])
						,$messageId
					);
		}
		/** User darf diese Nachricht nicht lesen (weil es nicht seine ist, doh!) */
		else {
			http_response_code(403); // Set response code 403 (access denied) and exit.
			$model->showInvalidmessage($smarty, $messageId);
			$html = $smarty->fetch('file:layout/head.tpl');
			$smarty->assign('error', ['type' => 'warn', 'dismissable' => 'false', 'title' => 'Du darfst diese Message nicht lesen!']);
			$html .= $smarty->fetch('file:layout/elements/block_error.tpl');
		}
	}
}
else {
	/** Nicht eingeloggter User */
	$model->showOverview($smarty);
	$html = $smarty->fetch('file:layout/head.tpl');
	$smarty->assign('error', ['type' => 'warn', 'dismissable' => 'false', 'title' => 'Nur eingeloggte User können Messages schreiben und empfangen!']);
	$html .= $smarty->fetch('file:layout/elements/block_error.tpl');
}

echo $html;

$smarty->display('file:layout/footer.tpl');
