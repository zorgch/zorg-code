<?php
/**
 * Poll Voting.
 * @packages zorg\Polls
 */
require_once __DIR__.'/../includes/poll.inc.php';

/** Input validation and sanitization */
$poll = (filter_input(INPUT_POST, 'poll', FILTER_VALIDATE_INT) ?? (filter_input(INPUT_GET, 'poll', FILTER_VALIDATE_INT) ?? null)); // $_POST['poll'] / $_GET['poll']
$vote = (filter_input(INPUT_POST, 'vote', FILTER_VALIDATE_INT) ?? (filter_input(INPUT_GET, 'vote', FILTER_VALIDATE_INT) ?? null)); // $_POST['vote'] / $_GET['vote']
$redirect = base64url_decode(filter_input(INPUT_GET, 'redirect', FILTER_DEFAULT, FILTER_REQUIRE_SCALAR)) ?? null; // $_GET['redirect']

if (!$user->is_loggedin()) {
	http_response_code(403); // Set response code 403 (Access denied)
	user_error('Access denied', E_USER_ERROR);
}
if (empty($poll) || $poll <= 0 || empty($vote) || $vote <= 0) {
	http_response_code(403); // Set response code 403 (Access denied)
	user_error('Nice try', E_USER_ERROR);
}

if ($poll !== null && $vote !== null)
{
	$polls = new Polls();

	$e = $db->query('SELECT p.* FROM polls p, poll_answers a WHERE a.poll=p.id AND p.id=? AND a.id=?', __FILE__, __LINE__, __FILE__, [$poll, $vote]);
	$d = $db->fetch($e);

	if ($d && $d['state']=='open' && $polls->user_has_vote_permission($d['type'])) {
		$db->query('REPLACE INTO poll_votes (poll, user, answer) VALUES (?, ?, ?)',
					__FILE__, __LINE__, 'REPLACE INTO poll_votes', [$poll, $user->id, $vote]);
	}else{
		user_error('Invalid Poll/Vote "'.$poll.' / '.$vote.'"', E_USER_ERROR);
	}

	header('Location: '.$redirect);
	exit;

} else {
	http_response_code(400); // Set response code 400 (Bad request)
	user_error('Invalid request', E_USER_ERROR);
}
