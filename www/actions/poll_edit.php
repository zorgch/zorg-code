<?php
/**
 * Add a new zorg Poll.
 *
 * WICHTIG/16.05.2021: das ist keine "edit"-Funktion hier - Edit von Polls ist noch WIP...
 *
 * @packages zorg\Polls
 */
/**
 * File includes.
 * @include main.inc.php
 */
require_once dirname(__FILE__).'/../includes/main.inc.php';

/** User not logged in? Error in his face! */
if (!$user->is_loggedin()) {
	http_response_code(403); // Set response code 403 (Access denied)
	user_error('Access denied', E_USER_ERROR);
}

/** User ist eingeloggt... */
else {
	if (isset($_POST['frm'])) $frm = $_POST['frm'];
	else user_error('Do fehlt bizeli was a Date zum en Poll mache...');

	$types = [ 'standard' ];
	if ($user->typ >= USER_MEMBER) $types[] = 'member'; // Members können Member-only Polls machen

	$errors = '';

	if (empty(trim($frm['text']))) $errors .= 'Text / Frage fehlt.<br>';
	if (!in_array($frm['type'], $types)) $errors .= 'Ungültiger Typ '.$frm['type'].'<br>';
	if (empty(trim($frm['aw1']))) $errors .= 'Antwort fehlt (Antwort 1 muss gesetzt sein).<br>';
	if (!empty(trim($frm['aw1'])) && empty(($frm['aw2']))) $errors .= 'Nur eine Antwort bringts nicht (Antwort 2 muss gesetzt sein).';

	if (empty($errors))
	{
			$newPollId = $db->insert('polls', [
				 'text' => sanitize_userinput($frm['text'])
				,'user' => $user->id
				,'type' => sanitize_userinput($frm['type'])
				,'date' => 'NOW()'
			  ], __FILE__, __LINE__, 'INSERT INTO polls');
			if (false === $newPollId || $newPollId <= 0)
			{
				/** Error - Poll was not inserted */
				user_error('Error while creating Poll', E_USER_ERROR);
			}

			/** Poll added successfully - so add also the possible Answers */
			else {
				/** Min. 1 / Max. 10 Poll-Answers in $frm */
				$pollAnswers = array();
				for ($a=1;$a<=10;$a++)
				{
					/** Only add answer if it contains a value... */
					if (!empty(trim($frm['aw'.$a])))
					{
						$db->insert('poll_answers', [
							'text' => sanitize_userinput($frm['aw'.$a])
							,'poll' => $newPollId
						], __FILE__, __LINE__, 'INSERT aw1 INTO poll_answers');
						$pollAnswers[] = sanitize_userinput($frm['aw'.$a]); // required for $tgPollAnswers
					}
				}

				/** Activity Eintrag auslösen */
				Activities::addActivity($user->id, 0, t('activity-new-poll', 'poll', [ $newPollId ]), 'p');

				/** Telegram native Poll senden */
				$tgPollQuestion = text_width(sprintf('%s: %s', $user->id2user($user->id, TRUE), sanitize_userinput($frm['text'])), 300, '…');
				$tgPollAnswers = json_encode($pollAnswers, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
				if (DEVELOPMENT === true) error_log(sprintf('[DEBUG] <%s:%d> $tgPollAnswers JSON: %s', __FUNCTION__, __LINE__, $tgPollAnswers));
				$tgPollAnonymous = (sanitize_userinput($frm['type']) === 'member' ? false : true);
				$telegram->send->poll('group', $tgPollQuestion, $tgPollAnswers, $tgPollAnonymous, 'regular', false, null, ['disable_notification' => 'true']);
			}

		/** Redirect user back to Poll-Overview */
		$_GET['tpl'] = 109;
		header('Location: /?'.url_params());
		exit;
	}
	/** Load New Poll Form and display $errors */
	else {
		foreach ($frm as $key => $val) $frm[$key] = stripslashes($val);

		$_GET['tpl'] = 108;
		$smarty->assign('tplroot', ['id' => 108]);
		$smarty->assign('frm', $frm);
		$smarty->assign('poll_error', $errors);
		$smarty->display('file:layout/layout.tpl');//$smarty->display('tpl:108');
	}
}
