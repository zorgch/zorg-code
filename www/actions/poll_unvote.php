<?php
require_once dirname(__FILE__).'/../includes/poll.inc.php';

if(!is_numeric($user->id)) {
	http_response_code(403); // Set response code 403 (Access denied)
	user_error('Du bist nicht eingeloggt', E_USER_ERROR);
}
if(!is_numeric($_GET['poll'])) {
	http_response_code(404); // Set response code 404 (Not found)
	user_error('Invalid poll-id: '.$_GET['poll'], E_USER_ERROR);
}

$polls = new Polls();

$e = $db->query("SELECT * FROM polls WHERE id='$_GET[poll]'", __FILE__, __LINE__);
$d = $db->fetch($e);
if ($d['state'] == "closed" || !$polls->user_has_vote_permission($d['type'])) user_error("Poll '$_GET[poll]' is closed", E_USER_ERROR);


$db->query("DELETE FROM poll_votes WHERE poll=$_GET[poll] AND user=$user->id", __FILE__, __LINE__);
unset($_GET['poll']);


header('Location: '.base64_decode($_GET['redirect']));
exit;
