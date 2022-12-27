<?php
if (!empty($_POST['url']))
{
	require_once dirname(__FILE__).'/../includes/main.inc.php';

	$from_mobile = ($_POST['from_mobile'] != '' || $_POST['from_mobile'] > 0) ? 1 : 0 ;
	$chat_text = sanitize_userinput($_POST['text']);
	$newBugId = $db->insert('chat', [ 'user_id' => $user->id, 'date' => 'NOW()', 'from_mobile' => $from_mobile, 'text' => $chat_text ], __FILE__, __LINE__, __METHOD__);

	header('Location: '.base64url_decode($_POST['url']));
	exit;
} else {
	http_response_code(403); // Set response code 403 (access denied) and exit.
	user_error('Du bist nicht eingeloggt.', E_USER_WARNING);
	exit;
}
