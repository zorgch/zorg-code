<?php
/**
 * Poll Unvote.
 * @packages zorg\Polls
 */
require_once dirname(__FILE__).'/../includes/poll.inc.php';

if(!$user->is_loggedin()) {
	http_response_code(403); // Set response code 403 (Access denied)
	user_error('Du bist nicht eingeloggt', E_USER_ERROR);
}
if(!isset($_GET['poll']) || !is_numeric($_GET['poll']) || (int)$_GET['poll'] <= 0) {
	http_response_code(404); // Set response code 404 (Not found)
	user_error('Invalid poll-id: '.$_GET['poll'], E_USER_ERROR);
}

$polls = new Polls();

$e = $db->query('SELECT * FROM polls WHERE id='.$_GET['poll'], __FILE__, __LINE__, 'SELECT');
$d = $db->fetch($e);
if ($d['state'] === 'closed' || !$polls->user_has_vote_permission($d['type']))
{
	user_error('Poll "'.$_GET['poll'].'" is closed', E_USER_ERROR);
} else {
	$db->query('DELETE FROM poll_votes WHERE poll='.$_GET['poll'].' AND user='.$user->id, __FILE__, __LINE__, 'DELETE');
	unset($_GET['poll']);
}

header('Location: '.base64_urldecode($_GET['redirect']));
exit;
