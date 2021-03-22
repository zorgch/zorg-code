<?php
/**
 * Poll Actions
 * @package zorg\Polls
 */
require_once dirname(__FILE__).'/../includes/poll.inc.php';

if (!$user->id) {
	http_response_code(403); // Set response code 403 (Access denied)
	user_error('Access denied', E_USER_ERROR);
}
if (!in_array($_GET['state'], array("open", "closed"))) {
	http_response_code(400); // Set response code 400 (Bad request)
	user_error('Invalid state "'.$_GET['state'].'"', E_USER_ERROR);
}

$polls = new Polls();

$e = $db->query("SELECT * FROM polls WHERE user=$user->id AND id=$_GET[poll]", __FILE__, __LINE__);
$d = $db->fetch($e);

if ($d && $polls->user_has_vote_permission($d['type']))
{
	$db->query("UPDATE polls SET state='$_GET[state]' WHERE id=$_GET[poll]", __FILE__, __LINE__);
}else{
	user_error("Invalid poll_change_state (poll=$_GET[poll] & state=$_GET[state])", E_USER_ERROR);
}

unset($_GET['poll']);
unset($_GET['state']);

header('Location: /?'.url_params());
exit;
