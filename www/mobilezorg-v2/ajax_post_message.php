<?php
/**
 * FILE INCLUDES
 */
require_once 'config.php';
require_once PHP_INCLUDES_DIR.'mobilez/chat.inc.php';
require_once PHP_INCLUDES_DIR.'util.inc.php';

if(isset($_POST['message']) && !empty($user->id) && $user->id > 0)
{
	$from_mobile = (!isset($_POST['from_mobile']) ? 0 : $_POST['from_mobile']);
	
	/**
	 * AJAX POST-Parameter validation and sanitization
	 */
	if (isset($_POST['message']) && !empty($_POST['message']))
	{
		$messageText = sanitize_userinput($_POST['message']);
		if (is_array($messageText) || is_numeric($messageText))
		{
			http_response_code(400); // Set response code 400 (bad request) and exit.
			header('Location: '.SITE_URL.'/mobilezorg-v2/?error_msg=Invalid%20POST-Parameter');
			exit;
		}
		if (strlen($messageText) <= 5)
		{
			http_response_code(411); // Set response code 411 (Length Required) and exit.
			header('Location: '.SITE_URL.'/mobilezorg-v2/?error_msg=Minimum%20length%20missed%20of%20POST-Parameter');
			exit;
		}
	} else {
		http_response_code(400); // Set response code 400 (bad request) and exit.
		header('Location: '.SITE_URL.'/mobilezorg-v2/?error_msg=Missing%20POST-Parameter');
		exit;
	}

	/**
	 * MESSAGE IS CHAT COMMAND
	 */
	if ($_POST['message'][0] == '/')
	{
		$command = substr(stristr($_POST['message'], ' ', true), 1);
		$parameters = ltrim(substr($_POST['message'], stripos($_POST['message'], ' ')));
		if (!empty($command)) {
			$mobilezChat->execChatMessageCommand($user->id, $command, $parameters);
		} else {
			header('Location: '.SITE_URL.'/mobilezorg-v2/?error_msg=Command%20is%20invalid%21');
			exit;
		}

	/**
	 * MESSAGE IS CHAT MESSAGE
	 */
	} else {
		$debugMode = FALSE;
		$fake_user_id_arr = array(1,2,3,7,8,9,10,11,13,14,15,16,17,18,22,26,30,37,51,52,59,117);
		$user_id = (($debugMode) ? array_rand($fake_user_id_arr, 1) : $user->id);
		$mobilezChat->postChatMessage($user_id, $_POST['message'], $from_mobile);

		/** Telegram Messenger Notification */
		if (DEVELOPMENT === true) define('TELEGRAM_BOT', 'zthearchitect_bot', true);
		require_once PHP_INCLUDES_DIR.'telegrambot.inc.php';
		$telegramMessage = sprintf('[z]Chat message by <b>%s</b>: <i>%s</i>', $user->id2user($user->id, true), $messageText);
		$telegramMessageKeyboard = json_encode([ 'inline_keyboard' => [[['text'=>'Reply in [z]Chat','url'=>SITE_URL.'/mobilezorg-v2/']]] ]);
		$telegram->send->message('group', $telegramMessage, ['reply_markup' => $telegramMessageKeyboard]);

		http_response_code(200);
	}
} else {
	http_response_code(411); // Set response code 411 (Length Required) and exit.
	header('Location: '.SITE_URL.'/mobilezorg-v2/?error_msg=Message%20is%20empty%21');
	exit;
}

// In case this Script was called directly...
header('Location: '.SITE_URL.'/mobilezorg-v2/');
exit;
