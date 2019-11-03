<?php
/**
 * zorg Messages
 *
 * @package zorg\Messagesystem
 */

/**
 * File includes
 */
require_once( __DIR__ .'/includes/messagesystem.inc.php');
require_once( __DIR__ .'/models/core.model.php');

/**
 * Initialise MVC Model
 */
$model = new MVC\Messagesystem();

/**
 * Validate passed GET-Parameters
 */
$messageId = (int)$_GET['message_id'];

Messagesystem::execActions();

//echo head(24).menu("zorg").'<br />';
//$smarty->assign('tplroot', array('page_title' => 'Messagesystem'));
$model->showOverview($smarty);
//$html .= menu("zorg");
//$html .= menu("user");

if ($user->is_loggedin())
{
	$model->showInvalidmessage($smarty);
	if(empty($messageId) || $messageId == '0' || $messageId <= 0)
	{
		http_response_code(404); // Set response code 404 (not found) and exit.
		$html = $smarty->fetch('file:layout/head.tpl');
		$smarty->assign('error', ['type' => 'warn', 'dismissable' => 'false', 'title' => 'Keine Nachricht angegeben!']);
		$html .= $smarty->fetch('file:layout/elements/block_error.tpl');
		//user_error('Keine Nachricht angegeben!', E_USER_WARNING);

		die($html);
	}

	try {
		$sql = 'SELECT *, UNIX_TIMESTAMP(date) AS date FROM messages WHERE id = '.$messageId;
		$rs = $db->fetch($db->query($sql, __FILE__, __LINE__));

		if ($rs == false)
		{
			http_response_code(400); // Set response code 400 (bad request) and exit.
			$html = $smarty->fetch('file:layout/head.tpl');
			$smarty->assign('error', ['type' => 'warn', 'dismissable' => 'false', 'title' => 'Nachricht '.$messageId.' konnte nicht geladen werden.']);
			$html .= $smarty->fetch('file:layout/elements/block_error.tpl');
			//user_error('Nachricht '.$messageId.' konnte nicht geladen werden.', E_USER_WARNING);

		}
		elseif ($rs['owner'] == $user->id)
		{
			$model->showMessage($smarty, $user, $messageId, $rs['from_user_id'], $rs['subject']);
			$html = $smarty->fetch('file:layout/head.tpl');

			$html .= Messagesystem::getMessage($messageId);

			if(!is_int(strpos($rs['subject'], "Re:"))) { 
				$subject = $rs['subject'];
			} else {
				$subject = 'Re: '.$rs['subject'];
			}

			$html .= '<br />';
			$html .= Messagesystem::getFormSend(
						array($rs['from_user_id'])
						, $subject, '> '.str_replace("\n", "\n> "
						, $rs['text'])
						, $messageId
					);
		} else {
			http_response_code(403); // Set response code 403 (access denied) and exit.
			$smarty->assign('error', ['type' => 'warn', 'dismissable' => 'false', 'title' => 'Du darfst diese Message nicht lesen!']);
			$html .= $smarty->fetch('file:layout/elements/block_error.tpl');
			//user_error('<b>Du darfst diese Message nicht lesen!</b>', E_USER_NOTICE);
		}
	}
	catch(Exception $e) {
		http_response_code(500); // Set response code 500 (internal server error)
		$smarty->assign('error', ['type' => 'warn', 'dismissable' => 'false', 'title' => $e->getMessage()]);
		$html .= $smarty->fetch('file:layout/elements/block_error.tpl');
		//user_error($e->getMessage(), E_USER_WARNING);
	}
}
// Nicht eingeloggte User
else {
	$html = $smarty->fetch('file:layout/head.tpl');
	$smarty->assign('error', ['type' => 'warn', 'dismissable' => 'false', 'title' => 'Nur eingeloggte User können Messages schreiben und empfangen!']);
	$html .= $smarty->fetch('file:layout/elements/block_error.tpl');
}

echo $html;

//echo foot();
$smarty->display('file:layout/footer.tpl');
