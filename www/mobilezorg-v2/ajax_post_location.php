<?php
/**
 * FILE INCLUDES
 */
require_once 'config.php';
require_once PHP_INCLUDES_DIR.'mobilez/chat.inc.php';

$json_data = json_decode($_POST['locationData']);
$from_mobile = $json_data->{'from_mobile'};
$location = str_replace(' ', '', $json_data->{'location'});

if(!empty($location) && !empty($user->id) && $user->id > 0)
{
	$mobilezChat->postGoogleMapsLocation($user->id, $location, $from_mobile);
	error_log(sprintf('[INFO] <%s:%d> Location saved: %s', 'mobilezorg-v2/ajax_post_location', __LINE__, $location));

	/** Telegram Messenger Notification */
	if (DEVELOPMENT === true) define('TELEGRAM_BOT', 'zthearchitect_bot');
	require_once PHP_INCLUDES_DIR.'telegrambot.inc.php';
	$latlngInfo = explode(',', $location);
	if (count($latlngInfo) == 2)
	{
		$telegramMessageKeyboard = [ 'inline_keyboard' => [[
										['text'=>'Reply in [z]Chat','url'=>SITE_URL.'/mobilezorg-v2/']
									]] ];
		$telegram->send->message('group', sprintf('Location vom <b>%s</b> via [z]Chat:', $user->id2user($user->id, true)));
		$telegram->send->location('group', $latlngInfo[0], $latlngInfo[1], 0, ['reply_markup' => json_encode($telegramMessageKeyboard)]);
	}

	http_response_code(200); // Set response code 411 (Length Required) and exit.
	//header("Location: ".SITE_URL."/mobilezorg-v2/"); // Reload Chat -> solved in JS
} else {
	// In case the $_POST-values are empty or this Script was called directly...
	error_log(sprintf('[WARN] <%s:%d> Location is empty', 'mobilezorg-v2/ajax_post_location', __LINE__));
	http_response_code(411); // Set response code 411 (Length Required) and exit.
	exit('Location is empty');
}
