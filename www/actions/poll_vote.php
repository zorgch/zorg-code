<?php
/**
 * Poll Voting.
 * @packages zorg\Polls
 */
require_once dirname(__FILE__).'/../includes/poll.inc.php';

if (!$user->is_loggedin()) {
	http_response_code(403); // Set response code 403 (Access denied)
	user_error('Access denied', E_USER_ERROR);
}
if ((!isset($_POST['poll']) || !isset($_POST['vote'])) && (!isset($_GET['poll']) || !isset($_GET['vote']))) {
	http_response_code(403); // Set response code 403 (Access denied)
	user_error('Nice try', E_USER_ERROR);
}

$poll = (!empty($_POST['poll']) ? $_POST['poll'] : (!empty($_GET['poll']) ? $_GET['poll'] : null));
$vote = (!empty($_POST['vote']) ? $_POST['vote'] : (!empty($_GET['vote']) ? $_GET['vote'] : null));

if ($poll !== null && $vote !== null)
{
	$polls = new Polls();

	$e = $db->query('SELECT p.* FROM polls p, poll_answers a WHERE a.poll=p.id AND p.id='.$poll.' AND a.id='.$vote, __FILE__, __LINE__, __FILE__);
	$d = $db->fetch($e);

	if ($d && $d['state']=='open' && $polls->user_has_vote_permission($d['type'])) {
		$db->query('REPLACE INTO poll_votes (poll, user, answer) VALUES ('.$poll.', '.$user->id.', '.$vote.')', __FILE__, __LINE__, 'REPLACE INTO poll_votes');
	}else{
		user_error('Invalid Poll/Vote "'.$poll.' / '.$vote.'"', E_USER_ERROR);
	}

	header('Location: '.base64_decode($_GET['redirect']));
	exit;

} else {
	http_response_code(400); // Set response code 400 (Bad request)
	user_error('Invalid request', E_USER_ERROR);
}
