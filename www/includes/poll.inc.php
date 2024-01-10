<?php
/**
 * Poll Klasse und Funktionen.
 * @package zorg\Polls
 */

/**
 * File includes
 * @include config.inc.php
 * @include mysql.inc.php
 * @include usersystem.inc.php
 * @include smarty.inc.php
 */
require_once __DIR__.'/config.inc.php';
require_once INCLUDES_DIR.'mysql.inc.php';
require_once INCLUDES_DIR.'usersystem.inc.php';
require_once INCLUDES_DIR.'smarty.inc.php';

/**
 * zorg Polls
 */
class Polls
{
	/**
	 * Poll anzeigen
	 *
	 * @author ?
	 * @author IneX
	 * @version 3.0
	 * @since 1.0 function added
	 * @since 1.5 `04.02.2018` moved Strings used to Global Strings
	 * @since 2.0 `11.09.2018` fixed SQL-Query (Polls were broken for not-loggedin users)
	 * @since 3.0 `11.09.2018` fixed @TODO Extract HTML-View into Template-File and use $smarty->display()
	 * @since 3.1 `19.02.2019` code optimizations, moved function inside class
	 * @since 3.2 `03.01.2024` changed SQL queries to mysqli prepared statements
	 *
	 * @uses self::user_has_vote_permission()
	 * @param $id Poll-ID to display
	 * @global object $db Globales Class-Object mit allen MySQL-Methoden
	 * @global object $user Globales Class-Object mit den User-Methoden & Variablen
	 * @return string HTML-markup to display the Poll
	 */
	function show($id)
	{
		global $db, $user, $smarty;

		$params = [];
		$sql = 'SELECT p.* ,UNIX_TIMESTAMP(p.date) date ,(SELECT count(*) FROM poll_votes WHERE poll=?) total_votes
					'.($user->is_loggedin() ? ',(SELECT answer FROM poll_votes WHERE poll=? AND user=?) myvote' : '').'
				FROM polls p WHERE id=? GROUP BY p.id';
		$params[] = $id;
		if ($user->is_loggedin()) {
			$params[] = $id;
			$params[] = $user->id;
		}
		$params = $id;
		$poll = $db->fetch($db->query($sql, __FILE__, __LINE__, __FUNCTION__, $params));
		//if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> $poll: %s', __FUNCTION__, __LINE__, print_r($poll,true)));

		if (!empty($poll) && $poll !== false)
		{
			$smarty->assign('poll', $poll);

			/** Check current User's user_has_vote_permission() */
			$user_has_vote_permission = $this->user_has_vote_permission($poll['type']);
			$smarty->assign('user_has_vote_permission', $user_has_vote_permission);
			//if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> $user_has_vote_permission: %s', __FUNCTION__, __LINE__, ($user_has_vote_permission?'true':'false')));

			/** Query Poll answers and return each answer with votes count */
			$pollMaxvotes = ($poll['total_votes'] > 0 ? $poll['total_votes'] : 0);
			$pollbarMaxwidth = 200;
			$pollbarSize = 0;

			//$e = $db->query('SELECT count(*) anz FROM poll_votes WHERE poll='.$id.' GROUP BY answer', __FILE__, __LINE__, __FUNCTION__);
			$sql = 'SELECT a.*, count(v.user) votes FROM poll_answers a
						LEFT JOIN poll_votes v ON v.answer=a.id
					WHERE a.poll=? GROUP BY a.id ORDER BY a.id';
			$pollAnswers = $db->query($sql, __FILE__, __LINE__, __FUNCTION__, [$id]);
			while ($pollAnswer = $db->fetch($pollAnswers))
			{
				$pollAnswersArray[$pollAnswer['id']] = $pollAnswer;

				/** Poll votes result-bar calculations */
				if ($pollAnswer['votes'] == 0) $pollbarSize = 1;
				else $pollbarSize = round($pollAnswer['votes'] / $pollMaxvotes * $pollbarMaxwidth);
				$pollAnswersArray[$pollAnswer['id']]['pollbar_size'] = $pollbarSize;
				$pollAnswersArray[$pollAnswer['id']]['pollbar_space'] = $pollbarMaxwidth - $pollbarSize;

				if ($poll['myvote'] == $pollAnswer['id']) {
					if ($poll['myvote'] && $poll['state']=='open' && $user_has_vote_permission) {
						//$old_url = base64url_encode("$_SERVER[PHP_SELF]?".url_params());
						$pollAnswersArray[$pollAnswer['id']]['unvote_url'] = '/actions/poll_unvote.php?poll='.$poll['id'].'&redirect='.getURL();
					}
				}
			}
			//if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> $pollAnswersArray: %s', __FUNCTION__, __LINE__, print_r($pollAnswersArray,true)));
			$smarty->assign('answers', $pollAnswersArray);

			/** Poll Voting: add Vote-Form if user_has_vote_permission() */
			if ($user_has_vote_permission && !$poll['myvote'] && $poll['state']=="open")
			{
				$redirect_url = base64url_encode($_SERVER['PHP_SELF'].'?'.url_params());
				$action = '/actions/poll_vote.php?redirect='.$redirect_url;
				$smarty->assign('form_action', $action);
				$smarty->assign('form_url', $redirect_url);
			}

			/** Get Poll voters */
			if ($poll['type'] == 'member')
			{
				$sql = 'SELECT * FROM poll_votes WHERE poll=? ORDER BY answer ASC';
				$pollVoters = $db->query($sql, __FILE__, __LINE__, __FUNCTION__, [$id]);
				while ($pollVoter = $db->fetch($pollVoters)) {
					$pollVotersArray[$pollVoter['answer']][] = $pollVoter;
				}
				//if (DEVELOPMENT) error_log('[DEBUG] $pollVotersArray: '.print_r($pollVotersArray,true));
				$smarty->assign('voters', $pollVotersArray);
			}

			$smarty->display('file:layout/partials/polls/poll.tpl');

		/** Poll not found - $id invalid */
		} else {
			$smarty->assign('error', ['type' => 'warn', 'title' => t('invalid-poll_id', 'poll', [$id]), 'dismissable' => false]);
			$smarty->display('file:layout/elements/block_error.tpl');
		}
	}

	/**
	 * Check User Vote-Permissions on Poll
	 *
	 * @version 1.0
	 * @since 1.0 function added
	 *
	 * @param string $poll_type Poll-Type is "standard" or "member"
	 * @global object $user Globales Class-Object mit den User-Methoden & Variablen
	 * @return bool
	 */
	function user_has_vote_permission($poll_type)
	{
		global $user;
		return (($poll_type == 'standard' && $user->id > 0) || ($poll_type == 'member' && $user->typ >= USER_MEMBER) ? true : false);
	}

	/**
	 * Updates the title and options of a poll.
	 * @link https://zorg.ch/bug/765 [Bug #765] Edit-Link bei bestehenden My Polls fehlt
	 *
	 * @version 1.0
	 * @since 1.0 `03.03.2023` `IneX` Generated using ChatGPT. Fixes #765
	 *
	 * @param int $poll_id The ID of the poll to update.
	 * @param string $title The new title of the poll.
	 * @param array $options An associative array of option IDs and their corresponding new text.
	 * @global object $db Globales Class-Object mit allen MySQL-Methoden
	 * @return bool Returns true if the update was successful, or false otherwise.
	 */
	public function update($poll_id, $title, $type, $answers) {
		global $db;

		$updateTitle = $db->update('polls', $poll_id, ['title' => $title], __FILE__, __LINE__, __METHOD__);
		if (!$updateTitle) {
			return false;
		}

		// foreach ($options as $option_id => $option_text) {
		// 	$updateOption = $db->update('poll_options', $poll_id, ['title' => $title], __FILE__, __LINE__, __METHOD__);

		// 	if (!$updateOption) {
		// 		return false;
		// 	}
		// }

		return true;
	}
}
