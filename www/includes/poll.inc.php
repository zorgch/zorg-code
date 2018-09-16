<?php
/**
 * File includes
 * @include config.inc.php
 * @include usersystem.inc.php
 * @include colors.inc.php
 * @include strings.inc.php	Strings die im Zorg Code benutzt werden
 */
require_once( __DIR__ . '/config.inc.php');
require_once( __DIR__ . '/usersystem.inc.php');
include_once( __DIR__ . '/colors.inc.php');
include_once( __DIR__ . '/strings.inc.php');

/**
 * Poll anzeigen
 *
 * @author ?, IneX
 * @version 3.0
 * @since 1.0 function added
 * @since 1.5 04.02.2018 moved Strings used to Global Strings
 * @since 2.0 11.09.2018 fixed SQL-Query (Polls were broken for not-loggedin users)
 * @since 3.0 11.09.2018 fixed @TODO Extract HTML-View into Template-File and use $smarty->display()
 *
 * @see templates/layout/partials/polls/poll.tpl
 * @param $id Poll-ID to display
 * @global object $db Globales Class-Object mit allen MySQL-Methoden
 * @global object $user Globales Class-Object mit den User-Methoden & Variablen
 * @return string HTML-markup to display the Poll
 */
function getPoll ($id) {
	global $db, $user, $smarty;

	try {
		$sql = 'SELECT
					 p.*
					,UNIX_TIMESTAMP(p.date) date
					,(SELECT count(*) FROM poll_votes WHERE poll='.$id.') total_votes
					'.($user->islogged_in() ? ',(SELECT answer FROM poll_votes WHERE poll='.$id.' AND user='.$user->id.') myvote' : '').'
				FROM polls p
				WHERE id='.$id.'
				GROUP BY p.id';
		$poll = $db->fetch($db->query($sql, __FILE__, __LINE__, __FUNCTION__));
		if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> $poll: %s', __FUNCTION__, __LINE__, print_r($poll,true)));
	} catch (Exception $e) {
		error_log($e->getMessage());
		return false;
	}

	if (!empty($poll) && $poll !== false)
	{
		$smarty->assign('poll', $poll);

		/** Check current User's user_has_vote_permission() */
		$user_has_vote_permission = user_has_vote_permission($poll['type']);
		$smarty->assign('user_has_vote_permission', $user_has_vote_permission);
		if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> $user_has_vote_permission: %s', __FUNCTION__, __LINE__, ($user_has_vote_permission?'true':'false')));

		/** Query Poll answers and return each answer with votes count */
		$pollMaxvotes = 0;
		if($pollMaxvotes < $poll['total_votes']) $pollMaxvotes = $poll['total_votes'];
		$pollbarMaxwidth = 200;
		$pollbarSize = 0;
		try {
			//$e = $db->query('SELECT count(*) anz FROM poll_votes WHERE poll='.$id.' GROUP BY answer', __FILE__, __LINE__, __FUNCTION__);
			$sql = 'SELECT a.*, count(v.user) votes
					FROM poll_answers a
					LEFT JOIN poll_votes v ON v.answer=a.id
					WHERE a.poll='.$id.'
					GROUP BY a.id
					ORDER BY a.id';
			$pollAnswers = $db->query($sql, __FILE__, __LINE__, __FUNCTION__);
			while ($pollAnswer = $db->fetch($pollAnswers)) {
				$pollAnswersArray[$pollAnswer['id']] = $pollAnswer;

				/** Poll votes result-bar calculations */
				if ($pollAnswer['votes'] == 0) $pollbarSize = 1;
				else $pollbarSize = round($pollAnswer['votes'] / $pollMaxvotes * $pollbarMaxwidth);
				$pollAnswersArray[$pollAnswer['id']]['pollbar_size'] = $pollbarSize;
				$pollAnswersArray[$pollAnswer['id']]['pollbar_space'] = $pollbarMaxwidth - $pollbarSize;

				if ($poll['myvote'] == $pollAnswer['id']) {
					if ($poll['myvote'] && $poll['state']=='open' && $user_has_vote_permission) {
						//$old_url = base64_encode("$_SERVER[PHP_SELF]?".url_params());
						$pollAnswersArray[$pollAnswer['id']]['unvote_url'] = '/actions/poll_unvote.php?poll='.$poll['id'].'&redirect='.getURL();
					}
				}
			}
		} catch (Exception $e) {
			error_log($e->getMessage());
			return false;
		}
		if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> $pollAnswersArray: %s', __FUNCTION__, __LINE__, print_r($pollAnswersArray,true)));
		$smarty->assign('answers', $pollAnswersArray);

		/** Poll Voting: add Vote-Form if user_has_vote_permission() */
		if ($user_has_vote_permission && !$poll['myvote'] && $poll['state']=="open")
		{
			$redirect_url = base64_encode($_SERVER['PHP_SELF'].'?'.url_params());
			$action = '/actions/poll_vote.php?redirect='.$redirect_url;
			$smarty->assign('form_action', $action);
			$smarty->assign('form_url', $redirect_url);
		}

		/** Get Poll voters */
		if ($poll['type'] == 'member') {
			try {
				$sql = 'SELECT * FROM poll_votes WHERE poll='.$id.' ORDER BY answer ASC';
				$pollVoters = $db->query($sql, __FILE__, __LINE__, __FUNCTION__);
				while ($pollVoter = $db->fetch($pollVoters)) {
					$pollVotersArray[$pollVoter['answer']][] = $pollVoter;
				}
				if (DEVELOPMENT) error_log('[DEBUG] $pollVotersArray: '.print_r($pollVotersArray,true));
			} catch (Exception $e) {
				error_log($e->getMessage());
				return false;
			}
			$smarty->assign('voters', $pollVotersArray);
		}

		return $smarty->display('file:layout/partials/polls/poll.tpl');

	/** Poll not found - $id invalid */
	} else {
		user_error(t('invalid-poll_id', 'poll', $id), E_USER_WARNING);
		return false;
	}
}


function user_has_vote_permission ($poll_type) {
	global $user;
	return ($poll_type == 'standard' && $user->id || $poll_type == 'member' && $user->typ == USER_MEMBER ? true : false);
}
