<?php
/**
 * Edit Comment Post-Action
 *
 * @package zorg\Forum
 */

/**
 * File includes
 * @include forum.inc.php
 */
require_once __DIR__.'/../includes/forum.inc.php';

/** Input validation */
$comment_id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?? null;
$thread_id = filter_input(INPUT_POST, 'thread_id', FILTER_VALIDATE_INT) ?? null;
$parent_id = filter_input(INPUT_POST, 'parent_id', FILTER_VALIDATE_INT) ?? null;
$board = filter_input(INPUT_POST, 'board', FILTER_DEFAULT, FILTER_REQUIRE_SCALAR) ?? null;
$msg_users = isset($_POST['msg_users']) ? explode(',', $_POST['msg_users'][0]) : null;
$commentText = htmlspecialchars_decode(filter_input(INPUT_POST, 'text', FILTER_SANITIZE_FULL_SPECIAL_CHARS), ENT_COMPAT | ENT_SUBSTITUTE) ?? null;
$returnUrl = base64url_decode(filter_input(INPUT_POST, 'url', FILTER_SANITIZE_FULL_SPECIAL_CHARS)) ?? '/forum.php'.$comment_id;

/** Board checken und validieren */
if(empty($board)) {
	http_response_code(400); // Set response code 400 (bad request) and exit.
	$returnUrl = changeUrl($returnUrl, 'error='.t('error-missing-board', 'commenting'));
	header('Location: '.$returnUrl); // Redirect user back to where he came from
	exit;
}
zorgDebugger::log()->debug('$_POST[board]: OK => %s', [$board]);

/** Parent id checken */
if(empty($parent_id) || $parent_id <= 0)
{
	http_response_code(400); // Set response code 400 (bad request) and exit.
	$returnUrl = changeUrl($returnUrl, 'error='.t('invalid-parent_id', 'commenting'));
	header('Location: '.$returnUrl); // Redirect user back to where he came from
	exit;
}
zorgDebugger::log()->debug('$_POST[parent_id]: OK => %d', [$parent_id]);

/** Thread id checken */
if(empty($thread_id) || $thread_id <= 0)
{
	http_response_code(400); // Set response code 400 (bad request) and exit.
	$returnUrl = changeUrl($returnUrl, 'error='.t('invalid-thread_id', 'commenting'));
	header('Location: '.$returnUrl); // Redirect user back to where he came from
	exit;
}
zorgDebugger::log()->debug('$_POST[thread_id]: OK => %d', [$thread_id]);

/** Text escapen */
if(empty($commentText) || $commentText === '')
{
	http_response_code(400); // Set response code 400 (bad request) and exit.
	$returnUrl = changeUrl($returnUrl, 'error='.t('invalid-comment-empty', 'commenting'));
	header('Location: '.$returnUrl); // Redirect user back to where he came from
	exit;
}
zorgDebugger::log()->debug('$_POST[text]: OK');

/** Existiert der Parent-Post? */
$comment_recordset = Comment::getRecordset($comment_id);
$comment_parentid = Comment::getParentid($comment_id, 1);

/** Keine Parent-ID gefunden */
if (!$comment_parentid || empty($comment_parentid) || $comment_parentid === 0)
{
	/** Comment ist im forum board */
	if ($comment_recordset['board'] === 'f')
	{
		//$rs = $db->fetch($db->query("SELECT * FROM comments WHERE id = ".$comment_id, __FILE__, __LINE__));
		if ($comment_recordset['parent_id'] != $parent_id)
		{
			zorgDebugger::log()->debug('parent_id does NOT match!');
			http_response_code(400); // Set response code 400 (bad request) and exit.
			$returnUrl = changeUrl($returnUrl, 'error='.t('invalid-comment-no-parentid', 'commenting'));
			header('Location: '.$returnUrl); // Redirect user back to where he came from
			exit;
		}
	}

	/** comment ist top level, da nicht im forum board */
	elseif ($parent_id != $thread_id) {
		zorgDebugger::log()->debug('comment ist top level, da nicht im forum board');
		http_response_code(400); // Set response code 400 (bad request) and exit.
		$returnUrl = changeUrl($returnUrl, 'error='.t('invalid-parent_id', 'commenting'));
		header('Location: '.$returnUrl); // Redirect user back to where he came from
		exit;
	}
}

/** Parent-ID vorhanden */
else {
	/** Besitzer checken */
	if($user->id != $comment_recordset['user_id'])
	{
		http_response_code(403.3); // Set response code 403.3 (Write access forbidden) and exit.
		$returnUrl = changeUrl($returnUrl, 'error='.t('invalid-comment-edit-permissions', 'commenting'));
		header('Location: '.$returnUrl); // Redirect user back to where he came from
		exit;
	}
}

/** Update Comment with new $_POST Data */
$updatedData = [
	 'board' => $board
	,'parent_it' => $parent_id
	,'thread_id' => $thread_id
	,'msg_users' => $msg_users
	,'text' => $commentText
];
$success = Comment::update($comment_id, $updatedData);
if (!$success) $returnUrl = changeUrl($returnUrl, 'error=updating%20comment%20failed');

/** User redirecten nach erfolgreichem Comment Update */
header('Location: '.$returnUrl);
exit;
