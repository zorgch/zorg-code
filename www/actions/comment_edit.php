<?php
/**
 * File includes
 * @include main.inc.php DEPRECATED
 * @include forum.inc.php
 */
//require_once dirname(__FILE__) .'/../includes/main.inc.php';
require_once dirname(__FILE__).'/../includes/forum.inc.php';

/** Board checken und validieren */
if($_POST['board'] == '' || empty($_POST['board']) || strlen($_POST['board']) != 1) {
	http_response_code(400); // Set response code 400 (bad request) and exit.
	//user_error('Board nicht angegeben!', E_USER_WARNING);
	$url_querystring = changeQueryString(parse_url(base64_decode($_POST['url']))['query'], 'error='.t('error-missing-board', 'commenting'));
	header('Location: '.changeURL(base64_decode($_POST['url']), $url_querystring)); // Redirect user back to where he came from
	exit;
}
if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> $_POST[board]: OK => %s', __FILE__, __LINE__, $_POST['board']));

/** Parent id checken */
if($_POST['parent_id'] <= 0 || empty($_POST['parent_id']) || $_POST['parent_id'] === '0' || !is_numeric($_POST['parent_id']))
{
	http_response_code(400); // Set response code 400 (bad request) and exit.
	//user_error('Parent id leer oder ungültig: ' . $_POST['parent_id'], E_USER_WARNING);
	$url_querystring = changeQueryString(parse_url(base64_decode($_POST['url']))['query'], 'error='.t('invalid-parent_id', 'commenting'));
	header('Location: '.changeURL(base64_decode($_POST['url']), $url_querystring)); // Redirect user back to where he came from
	exit;
}
if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> $_POST[parent_id]: OK => %s', __FILE__, __LINE__, $_POST['parent_id']));

/** Thread id checken */
if($_POST['thread_id'] < 0 || empty($_POST['thread_id']) || $_POST['thread_id'] === '0' || !is_numeric($_POST['thread_id']))
{
	http_response_code(400); // Set response code 400 (bad request) and exit.
	//user_error('Thread id leer oder ungültig: ' . $_POST['thread_id'], E_USER_WARNING);
	$url_querystring = changeQueryString(parse_url(base64_decode($_POST['url']))['query'], 'error='.t('invalid-thread_id', 'commenting'));
	header('Location: '.changeURL(base64_decode($_POST['url']), $url_querystring)); // Redirect user back to where he came from
	exit;
}
if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> $_POST[thread_id]: OK => %s', __FILE__, __LINE__, $_POST['thread_id']));

/** Text escapen */
if(trim($_POST['text']) === '' || empty($_POST['text']) || !isset($_POST['text']))
{
	http_response_code(400); // Set response code 400 (bad request) and exit.
	//user_error('keine leeren Posts erlaubt.', E_USER_WARNING);
	$url_querystring = changeQueryString(parse_url(base64_decode($_POST['url']))['query'], 'error='.t('invalid-comment-empty', 'commenting'));
	header('Location: '.changeURL(base64_decode($_POST['url']), $url_querystring)); // Redirect user back to where he came from
	exit;
} else {
	$commentText = escape_text($_POST['text']);
	$_POST['text'] = $commentText; // required for passing to Comment::update() later...
}
if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> $_POST[text]: OK', __FILE__, __LINE__));

/** Existiert der Parent-Post? */
/**try {
	$sql = 
		"
		SELECT 
		* 
		FROM comments 
		WHERE id = ".$_POST['parent_id']." 
		AND board = '".$_POST['board']."'
		AND thread_id = '".$_POST['thread_id']."'
		"
	;
	$result = $db->query($sql, __FILE__, __LINE__);
	$rs = $db->fetch($result);
*/
	$comment_recordset = Comment::getRecordset($_POST['id']);
	if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> $comment_recordset: fetched => %s', __FILE__, __LINE__, print_r($comment_recordset,true)));
	$comment_parentid = Comment::getParentid($_POST['id'], 1);
	if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> $comment_parentid: fetched => %d', __FILE__, __LINE__, $comment_parentid));

	/** Keine Parent-ID gefunden */
	if ($comment_parentid == FALSE || $comment_parentid === 0 || empty($comment_parentid))
	{
		/** Comment ist im forum board */
		if ($comment_recordset['board'] === 'f')
		{
			//$rs = $db->fetch($db->query("SELECT * FROM comments WHERE id = ".$_POST['id'], __FILE__, __LINE__));
			if ($comment_recordset['parent_id'] != $_POST['parent_id'])
			{
				if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> parent_id does NOT match!', __FILE__, __LINE__));
				http_response_code(400); // Set response code 400 (bad request) and exit.
				//user_error(t('invalid-comment-no-parentid', 'commenting'), E_USER_WARNING);
				$url_querystring = changeQueryString(parse_url(base64_decode($_POST['url']))['query'], 'error='.t('invalid-comment-no-parentid', 'commenting'));
				header('Location: '.changeURL(base64_decode($_POST['url']), $url_querystring)); // Redirect user back to where he came from
				exit;
			}
		}

		/** comment ist top level, da nicht im forum board */
		elseif ($_POST['parent_id'] != $_POST['thread_id']) {
			if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> comment ist top level, da nicht im forum board', __FILE__, __LINE__));
			http_response_code(400); // Set response code 400 (bad request) and exit.
			//user_error(t('invalid-parent_id', 'commenting'), E_USER_WARNING);
			$url_querystring = changeQueryString(parse_url(base64_decode($_POST['url']))['query'], 'error='.t('invalid-parent_id', 'commenting'));
			header('Location: '.changeURL(base64_decode($_POST['url']), $url_querystring)); // Redirect user back to where he came from
			exit;
		}
	}

	/** Parent-ID vorhanden */
	else {
		/** Besitzer checken */
		//$rs = Comment::getRecordset($_POST['id']);
		if($user->id != $comment_recordset['user_id'])
		{
			http_response_code(403.3); // Set response code 403.3 (Write access forbidden) and exit.
			//user_error(t('invalid-comment-edit-permissions', 'commenting'), E_USER_WARNING);
			$url_querystring = changeQueryString(parse_url(base64_decode($_POST['url']))['query'], 'error='.t('invalid-comment-edit-permissions', 'commenting'));
			header('Location: '.changeURL(base64_decode($_POST['url']), $url_querystring)); // Redirect user back to where he came from
			exit;
		}
	}
/*} catch(Exception $e) {
	http_response_code(500); // Set response code 500 (internal server error)
	echo $e->getMessage();
}*/

/** Update Comment with new $_POST Data */
Comment::update($_POST['id'], $_POST);

/** User redirecten nach erfolgreichem Comment Update */
header('Location: '.base64_decode($_POST['url']));
exit;
