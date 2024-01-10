<?php
/**
 * Poll Unvote.
 * @packages zorg\Polls
 */
require_once dirname(__FILE__).'/../includes/poll.inc.php';

/** Input validation and sanitization */
$pollId = filter_input(INPUT_GET, 'poll', FILTER_VALIDATE_INT) ?? null; // $_GET['poll']

if(!$user->is_loggedin()) {
	http_response_code(403); // Set response code 403 (Access denied)
	user_error('Du bist nicht eingeloggt', E_USER_ERROR);
}
if(empty($pollId) || $pollId <= 0) {
	http_response_code(404); // Set response code 404 (Not found)
	user_error('Invalid poll-id: '.$pollId, E_USER_ERROR);
}

$polls = new Polls();

$e = $db->query('SELECT * FROM polls WHERE id=?', __FILE__, __LINE__, 'SELECT', [$pollId]);
$d = $db->fetch($e);
if ($d['state'] === 'closed' || !$polls->user_has_vote_permission($d['type']))
{
	user_error('Poll "'.$pollId.'" is closed', E_USER_ERROR);
} else {
	$db->query('DELETE FROM poll_votes WHERE poll=? AND user=?', __FILE__, __LINE__, 'DELETE', [$pollId, $user->id]);
	unset($_GET['poll']);
}

header('Location: '.base64url_decode($_GET['redirect']));
exit;
