<?php
/**
 * Poll State change (close/open).
 * @package zorg\Polls
 */
require_once __DIR__.'/../includes/poll.inc.php';

/** Input validation and sanitization */
$pollId = filter_input(INPUT_GET, 'poll', FILTER_VALIDATE_INT) ?? null; // $_GET['poll']
$pollState = filter_input(INPUT_GET, 'state', FILTER_DEFAULT, FILTER_REQUIRE_SCALAR) ?? null; // $_GET['state']

if (!$user->is_loggedin()) {
	http_response_code(403); // Set response code 403 (Access denied)
	user_error('Access denied', E_USER_ERROR);
}
if (!in_array($pollState, array('open', 'closed'))) {
	http_response_code(400); // Set response code 400 (Bad request)
	user_error('Invalid state "'.$pollState.'"', E_USER_ERROR);
}
if(empty($pollId) || $pollId <= 0) {
	http_response_code(404); // Set response code 404 (Not found)
	user_error('Invalid poll-id: '.$pollId, E_USER_ERROR);
}

$polls = new Polls();

$e = $db->query('SELECT * FROM polls WHERE user=? AND id=?', __FILE__, __LINE__, 'SELECT', [$user->id, $pollId]);
$d = $db->fetch($e);

if ($d && $polls->user_has_vote_permission($d['type']))
{
	$db->query('UPDATE polls SET state=? WHERE id=?', __FILE__, __LINE__, 'UPDATE', [$pollState, $pollId]);
	// @TODO Stop Telegram-Poll on close via chat_id using https://core.telegram.org/bots/api#stoppoll
}else{
	user_error('Invalid poll_change_state (poll='.$pollId.' & state='.$pollState.')', E_USER_ERROR);
}

unset($_GET['poll']);
unset($_GET['state']);

header('Location: /?'.url_params());
exit;
