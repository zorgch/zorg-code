<?php
/**
 * AJAX Request validation
 */
header('Content-type:application/json;charset=utf-8');
$_POST = json_decode(file_get_contents('php://input'), true);
if(!isset($_GET['action']) || empty($_GET['action']) || $_GET['action'] != 'post')
{
	http_response_code(400); // Set response code 400 (bad request) and exit.
	die(json_encode('Invalid or missing GET-Parameter'));
}

/**
 * FILE INCLUDES
 */
require_once dirname(__FILE__).'/../../includes/config.inc.php';
require_once INCLUDES_DIR.'util.inc.php';

/**
 * AJAX POST-Parameter validation and sanitization
 */
if (isset($_POST['message']) && !empty($_POST['message']))
{
	$messageText = sanitize_userinput($_POST['message']);
	if (is_array($messageText) || is_numeric($messageText))
	{
		http_response_code(400); // Set response code 400 (bad request) and exit.
		die(json_encode('Invalid POST-Parameter'));
	}
	if (strlen($messageText) <= 5)
	{
		http_response_code(411); // Set response code 411 (Length Required) and exit.
		die(json_encode('Minimum length missed of POST-Parameter'));
	}
} else {
	http_response_code(400); // Set response code 400 (bad request) and exit.
	die(json_encode('Missing POST-Parameter'));	
}
if (isset($_POST['contact']) && !empty($_POST['contact']))
{
	$contactName = sanitize_userinput($_POST['contact']);
	if (is_array($contactName) || is_numeric($contactName))
	{
		http_response_code(400); // Set response code 400 (bad request) and exit.
		die(json_encode('Invalid POST-Parameter'));
	}
	if (strlen($contactName) <= 5)
	{
		http_response_code(411); // Set response code 411 (Length Required) and exit.
		die(json_encode('Minimum length missed of POST-Parameter'));
	}
} else {
	http_response_code(400); // Set response code 400 (bad request) and exit.
	die(json_encode('Missing POST-Parameter'));	
}

/**
 * Send Telegram-Message
 */
$telegramMessage = sprintf("Contact message by <b>%s</b>:\n\n<i>%s</i>", $contactName, $messageText);
$telegram->send->message('group', $telegramMessage);
http_response_code(200);
