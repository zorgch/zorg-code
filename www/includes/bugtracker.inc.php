<?php
/**
 * Bugtracker Includes
 *
 * @author [z]milamber
 * @version 1.0
 * @package zorg\Bugtracker
 */

/**
 * File Includes
 */
require_once dirname(__FILE__).'/config.inc.php';
require_once INCLUDES_DIR.'usersystem.inc.php';
include_once INCLUDES_DIR.'util.inc.php';

/**
 * Bugtracker Klasse
 *
 * @author [z]milamber
 * @version 1.0
 * @package zorg\Bugtracker
 */
class Bugtracker
{
	/**
	 * Executing Bugtracker Actions
	 *
	 * Execute various Bugtracker GET- & POST-Actions such as:
	 * - adding new Bug
	 * - assigning Bug
	 * - Bug klauen
	 * - reopen a Bug
	 * - resolve a Bug
	 * - edit a Bug
	 * - deny a Bug
	 * - adding new Bug Category
	 *
	 * @author [z]milamber
	 * @author IneX
	 * @version 3.2
	 * @since 1.0 `[z]milamber` function added
	 * @since 2.0 `IneX` various code optimizations, I don't remember all of them
	 * @since 3.0 `26.11.2018` `IneX` updated to use new $notifcation Class & some code and query optimizations
	 * @since 3.1 `04.12.2019` `IneX` [GitHub-Issue #22] updated SQL-queries
	 * @since 3.2 `02.06.2023` `IneX` Fixes SQL Injection Risks (CWE-89), Open Redirect Risks (CWE-601)
	 *
	 * @global object $db Globales Class-Object mit allen MySQL-Methoden
	 * @global object $user Globales Class-Object mit den User-Methoden & Variablen
	 * @global object $notification Globales Class-Object mit allen Notification-Methoden
	 * @return void Header Location redirect
	 */
	static function execActions()
	{
		global $db, $user, $notification;

		$sanitizedReturnURL = parse_url(base64url_decode($_GET['url']), PHP_URL_PATH);

		/** Add new Bug */
		if (isset($_GET['action']) && $_GET['action'] === 'new')
		{
			/** Validate & escape fields */
			$bugCategory = ( isset($_GET['category_id']) && is_numeric($_GET['category_id']) && $_GET['category_id'] >= 0 ? $_GET['category_id'] : user_error('Bugtracker: invalid Category-ID "' . $_GET['category_id'] . '"', E_USER_WARNING) );
			$bugPriority = ( isset($_GET['priority']) && is_numeric($_GET['priority']) && $_GET['priority'] >= 0 ? $_GET['priority'] : user_error('Bugtracker: invalid Priority "' . $_GET['priority'] . '"', E_USER_WARNING) );
			$bugTitle = ( isset($_GET['title']) && !empty($_GET['title']) ? sanitize_userinput($_GET['title']) : user_error('Bugtracker: invalid Title "' . $_GET['title'] . '"', E_USER_WARNING) );
			$bugDescription = ( !empty($_GET['description']) ? escape_text($_GET['description'], '<br>') : '' );

			/** Add new Bug to DB */
			$newBugId = (int)$db->insert('bugtracker_bugs', [
											 'category_id' => $bugCategory
											,'reporter_id' => $user->id
											,'priority' => $bugPriority
											,'title' => $bugTitle
											,'description' => $bugDescription
											,'reported_date' => timestamp(true)
										  ], __FILE__, __LINE__, __METHOD__);

			/** Activity Eintrag auslösen */
			Activities::addActivity($user->id, 0, t('activity-newbug', 'bugtracker', [ SITE_URL, $newBugId, $bugTitle ]), 'bu');

			/** Benachrichtigungs-Message */
			if(count($_GET['msg_users']) > 0)
			{
				for ($i=0; $i < count($_GET['msg_users']); $i++)
				{
					$notification_subject = t('message-subject-newbug', 'bugtracker', [ $user->id2user($user->id, true), $newBugId ]);
					$notification_text = t('message-newbug', 'bugtracker', [ remove_html($bugDescription, '<a><br><i><b><code><pre>'), SITE_URL, $newBugId, $bugTitle]);
					$notification_status = $notification->send($_GET['msg_users'][$i], 'bugtracker', ['from_user_id'=>$user->id, 'subject'=>$notification_subject, 'text'=>$notification_text, 'message'=>$notification_text, 'to_users' => $_GET['msg_users']]);
					if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> $notification_status "%s" from user=%s to user=%s', __METHOD__, __LINE__, ($notification_status===true?'true':'false'), $user->id, $_GET['msg_users'][$i]));
				}
			}

			header('Location: /bug/'.urlencode($newBugId));
			exit;
		}

		/** Assign Bug */
		elseif (isset($_GET['action']) && $_GET['action'] === 'assign')
		{
			$bugId = ( isset($_GET['bug_id']) && is_numeric($_GET['bug_id']) && $_GET['bug_id'] >= 0 ? (int)$_GET['bug_id'] : user_error('Bugtracker: invalid Bug-ID "' . $_GET['bug_id'] . '"', E_USER_WARNING) );

			$rs = Bugtracker::getBugRS($bugId);
			if($rs['assignedto_id'] == 0) {
				$result = $db->update('bugtracker_bugs', $bugId, ['assignedto_id' => $user->id, 'assigned_date' => timestamp(true)], __FILE__, __LINE__, __METHOD__);
			}
			header('Location: '.(!empty($sanitizedReturnURL) ? $sanitizedReturnURL : '/bugtracker.php'));
			exit;
		}

		/** Bug klauen */
		elseif (isset($_GET['action']) &&  $_GET['action'] === 'klauen')
		{
			$bugId = ( isset($_GET['bug_id']) && is_numeric($_GET['bug_id']) && $_GET['bug_id'] >= 0 ? (int)$_GET['bug_id'] : user_error('Bugtracker: invalid Bug-ID "' . $_GET['bug_id'] . '"', E_USER_WARNING) );

			$rs = Bugtracker::getBugRS($bugId);
			if($rs['assignedto_id'] == 0 OR $rs['assignedto_id'] > 0) {
				$result = $db->update('bugtracker_bugs', $bugId, ['assignedto_id' => $user->id, 'assigned_date' => timestamp(true)], __FILE__, __LINE__, __METHOD__);
			}
			header('Location: '.(!empty($sanitizedReturnURL) ? $sanitizedReturnURL : '/bugtracker.php'));
			exit;
		}

		/** Bug erneut öffnen */
		elseif (isset($_GET['action']) && $_GET['action'] === 'reopen')
		{
			$bugId = ( isset($_GET['bug_id']) && is_numeric($_GET['bug_id']) && $_GET['bug_id'] >= 0 ? (int)$_GET['bug_id'] : user_error('Bugtracker: invalid Bug-ID "' . $_GET['bug_id'] . '"', E_USER_WARNING) );
			$result = $db->update('bugtracker_bugs', $bugId, ['resolved_date' => 'NULL', 'denied_date' => 'NULL'], __FILE__, __LINE__, __METHOD__);

			$rs = Bugtracker::getBugRS($bugId);
			if($user->id != $rs['reporter_id'])
			{
				$notification_subject = t('message-subject-reopenbug', 'bugtracker', [ $user->id2user($user->id, true), $bugId ]);
				$notification_text = t('message-newbug', 'bugtracker', [ remove_html($rs['description'],'<a><br><i><b><code><pre>'), SITE_URL, $bugId, $bugTitle]);
				$notification_status = $notification->send($rs['reporter_id'], 'bugtracker', ['from_user_id'=>$user->id, 'subject'=>$notification_subject, 'text'=>$notification_text, 'message'=>$notification_text]);
				if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> $notification_status "%s" from user=%s to user=%s', __METHOD__, __LINE__, ($notification_status===true?'true':'false'), $user->id, $rs['reporter_id']));
			}

			header('Location: '.(!empty($sanitizedReturnURL) ? $sanitizedReturnURL : '/bugtracker.php'));
			exit;
		}

		/** Bug wieder freigeben (unassign) */
		elseif (isset($_GET['action']) && $_GET['action'] === 'resign')
		{
			$bugId = ( isset($_GET['bug_id']) && is_numeric($_GET['bug_id']) && $_GET['bug_id'] >= 0 ? (int)$_GET['bug_id'] : user_error('Bugtracker: invalid Bug-ID "' . $_GET['bug_id'] . '"', E_USER_WARNING) );
			$result = $db->update('bugtracker_bugs', $bugId, ['assignedto_id' => 'NULL', 'assigned_date' => 'NULL'], __FILE__, __LINE__, __METHOD__);

			header('Location: '.(!empty($sanitizedReturnURL) ? $sanitizedReturnURL : '/bugtracker.php'));
			exit;
		}

		/** Bug als gelöst markieren */
		elseif (isset($_GET['action']) && $_GET['action'] === 'resolve')
		{
			$bugId = ( isset($_GET['bug_id']) && is_numeric($_GET['bug_id']) && $_GET['bug_id'] >= 0 ? (int)$_GET['bug_id'] : user_error('Bugtracker: invalid Bug-ID "' . $_GET['bug_id'] . '"', E_USER_WARNING) );
			$result = $db->update('bugtracker_bugs', $bugId, ['resolved_date' => timestamp(true)], __FILE__, __LINE__, __METHOD__);
			$rs = Bugtracker::getBugRS($bugId);

			/** Activity Eintrag auslösen */
			Activities::addActivity($user->id, 0, 'hat den Bug <a href="/bug/'.$bugId.'">'.$rs['title'].'</a> gelöst.<br/><br/>', 'bu');

			if($user->id != $rs['reporter_id'])
			{
				$notification_subject = t('message-subject-resolvedbug', 'bugtracker', [ $user->id2user($user->id, true), $bugId ]);
				$notification_text = t('message-newbug', 'bugtracker', [ remove_html($rs['description'],'<a><br><i><b><code><pre>'), SITE_URL, $bugId, $bugTitle]);
				$notification_status = $notification->send($rs['reporter_id'], 'bugtracker', ['from_user_id'=>$user->id, 'subject'=>$notification_subject, 'text'=>$notification_text, 'message'=>$notification_text]);
				if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> $notification_status "%s" from user=%s to user=%s', __METHOD__, __LINE__, ($notification_status===true?'true':'false'), $user->id, $rs['reporter_id']));
			}

			header('Location: '.(!empty($sanitizedReturnURL) ? $sanitizedReturnURL : '/bugtracker.php'));
			exit;
		}

		/** Bug bearbeiten */
		elseif (isset($_GET['action']) && $_GET['action'] == 'edit')
		{
			/** Validate & escape fields */
			$bugId = ( isset($_GET['bug_id']) && is_numeric($_GET['bug_id']) && $_GET['bug_id'] >= 0 ? (int)$_GET['bug_id'] : user_error('Bugtracker: invalid Bug-ID "' . $_GET['bug_id'] . '"', E_USER_WARNING) );
			$bugCategory = ( isset($_GET['category_id']) && is_numeric($_GET['category_id']) && $_GET['category_id'] >= 0 ? $_GET['category_id'] : user_error('Bugtracker: invalid Category-ID "' . $_GET['category_id'] . '"', E_USER_WARNING) );
			$bugPriority = ( isset($_GET['priority']) && is_numeric($_GET['priority']) && $_GET['priority'] >= 0 ? $_GET['priority'] : user_error('Bugtracker: invalid Priority "' . $_GET['priority'] . '"', E_USER_WARNING) );
			$bugTitle = ( isset($_GET['title']) && !empty($_GET['title']) ? sanitize_userinput($_GET['title']) : user_error('Bugtracker: invalid Title "' . $_GET['title'] . '"', E_USER_WARNING) );
			$bugDescription = ( !empty($_GET['description']) ? escape_text($_GET['description']) : '' );
			$bugCommit = ( !empty($_GET['code_commit']) ? sanitize_userinput($_GET['code_commit']) : '' );

			/** Update Bug in DB */
			$result = $db->update('bugtracker_bugs', $bugId, [
																 'title' => $bugTitle
																,'description' => $bugDescription
																,'category_id' => $bugCategory
																,'priority' => $bugPriority
																,'code_commit' => $bugCommit
															], __FILE__, __LINE__, __METHOD__);

			header('Location: '.(!empty($sanitizedReturnURL) ? $sanitizedReturnURL : '/bugtracker.php'));
			exit;
		}

		/** Bug ablehnen */
		elseif (isset($_GET['action']) && $_GET['action'] == 'deny')
		{
			$bugId = ( isset($_GET['bug_id']) && is_numeric($_GET['bug_id']) && $_GET['bug_id'] >= 0 ? (int)$_GET['bug_id'] : user_error('Bugtracker: invalid Bug-ID "' . $_GET['bug_id'] . '"', E_USER_WARNING) );
			$result = $db->update('bugtracker_bugs', $bugId, ['denied_date' => timestamp(true)], __FILE__, __LINE__, __METHOD__);
			$rs = Bugtracker::getBugRS($bugId);

			/** Activity Eintrag auslösen */
			Activities::addActivity($user->id, 0, 'hat den Bug <a href="/bug/'.$bugId.'">'.$rs['title'].'</a> abgelehnt.<br/><br/>', 'bu');

			if($user->id != $rs['reporter_id'])
			{
				$notification_subject = t('message-subject-deniedbug', 'bugtracker', [ $user->id2user($user->id, true), $bugId ]);
				$notification_text = t('message-newbug', 'bugtracker', [ remove_html($rs['description'],'<a><br><i><b><code><pre>'), SITE_URL, $bugId, $bugTitle]);
				$notification_status = $notification->send($rs['reporter_id'], 'bugtracker', ['from_user_id'=>$user->id, 'subject'=>$notification_subject, 'text'=>$notification_text, 'message'=>$notification_text]);
				if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> $notification_status "%s" from user=%s to user=%s', __METHOD__, __LINE__, ($notification_status===true?'true':'false'), $user->id, $rs['reporter_id']));
			}

			header('Location: '.(!empty($sanitizedReturnURL) ? $sanitizedReturnURL : '/bugtracker.php'));
			exit;
		}

		/** Add new Category */
		elseif (isset($_GET['action']) && $_GET['action'] == 'newcategory')
		{
			$title_sanitized = sanitize_userinput($_GET['title']);
			$categoryTitle = ( isset($title_sanitized) && !empty($title_sanitized) ? $title_sanitized : user_error('Bugtracker: invalid Category Title "' . $_GET['title'] . '"', E_USER_WARNING) );

			/** Add new Category to DB */
			$newBugId = $db->insert('bugtracker_categories', [ 'title' => $categoryTitle ], __FILE__, __LINE__, __METHOD__);

			header('Location: '.(!empty($sanitizedReturnURL) ? $sanitizedReturnURL : '/bugtracker.php'));
			exit;
		}
	}

	/**
	 * Display Bug / Bug edit form
	 *
	 * @author [z]milamber
	 * @author IneX
	 * @version 2.1
	 * @since 1.0 function added
	 * @since 2.0 `10.11.2018` `IneX` added Code Commit field
	 * @since 2.1 `04.12.2019` `IneX` [GitHub-Issue #22] updated SQL-queries & sanitized HTML-output from DB
	 *
	 * @see Thread::getNumPosts
	 * @return string HTML-Code for page output
	 */
	static function getBugHTML($bug_id, $edit=FALSE)
	{
		global $db, $user;

		$html = null;

		$sql = 'SELECT
					 bugtracker_bugs.*
					,categories.title as category
					,UNIX_TIMESTAMP(assigned_date) as assigned_date
					,UNIX_TIMESTAMP(denied_date) as denied_date
					,UNIX_TIMESTAMP(resolved_date) as resolved_date
					,UNIX_TIMESTAMP(reported_date) as reported_date
				 FROM bugtracker_bugs
					 LEFT JOIN bugtracker_categories as categories ON (bugtracker_bugs.category_id = categories.id)
				 WHERE bugtracker_bugs.id ='.$bug_id;
		$result = $db->query($sql, __FILE__, __LINE__, __METHOD__);
		$rs = $db->fetch($result);

		$reportedDate_iso8601 = date('c', $rs['reported_date']);
		if ($rs['resolved_date'] > 0) $resolvedDate_iso8601 = date('c', $rs['resolved_date']);
		if ($rs['denied_date'] > 0) $deniedDate_iso8601 = date('c', $rs['denied_date']);

		if($edit == TRUE)
		{
			$html .=
				'<form action="/bug/'.$rs['id'].'" method="get">'
				.'<input name="action" type="hidden" value="edit">'
				.'<input name="bug_id" type="hidden" value="'.$rs['id'].'">'
				.'<input name="url" type="hidden" value="'.base64url_encode(getChangedURL('action=')).'">'
			;
		}

		/** schema.org QAPage/Question "itemprop=answerCount" berechnen */
		$bugNumComments = Thread::getNumPosts('b', $rs['id']);
		if ($bugNumComments <= 0)
		{
			$schemaQuestionAnswerCount = ($rs['resolved_date'] > 0 || $rs['denied_date'] > 0 ? '1' : '1');
		} else {
			$schemaQuestionAnswerCount = $bugNumComments;
		}

		$html .=
			'<table class="border shadedcells" itemscope itemprop="mainEntity" itemtype="http://schema.org/Question">'
				.'<tr style="display:none;"><td itemprop="answerCount">'.$schemaQuestionAnswerCount.'</td></tr>'
				.'<tr>'
					.'<td align="left">Bug #</td>'
					.'<td align="left"><a itemprop="url" href="/bug/'.$rs['id'].'">'.$rs['id'].'</a></td>'
				.'</tr>'
				.'<tr>'
					.'<td align="left">Priorit&auml;t</td>'
					.'<td align="left">'
						.($user->typ >= USER_MEMBER && $edit == TRUE ? Bugtracker::getFormFieldPriority($rs['priority']) : $rs['priority'].' ('.Bugtracker::getPriorityDescription($rs['priority']).')' )
					.'</td>'
				.'</tr>'
				.'<tr>'
					.'<td align="left">Bereich</td>'
					.'<td align="left" class="strong info" itemprop="keywords">'
						.($user->typ >= USER_MEMBER && $edit == TRUE ? Bugtracker::getFormFieldCategory($rs['category_id']) : $rs['category'])
					.'</td>'
				.'</tr>'
				.'<tr>'
					.'<td align="left">Title</td>'
					.'<td align="left" style="font-weight: bold;" itemprop="name">'
						.($user->typ >= USER_MEMBER && $edit == TRUE ? Bugtracker::getFormFieldTitle($rs['title']) : $rs['title'])
					.'</td>'
				.'</tr>'
				.'<tr>'
					.'<td align="left" valign="top">Beschreibung</td>'
					.'<td align="left" colspan="5" itemprop="text">'
						.($user->typ >= USER_MEMBER && $edit == TRUE ? Bugtracker::getFormFieldDescription($rs['description']) : nl2br($rs['description']))
					.'</td>'
				.'</tr>'
				.'<tr>'
					.'<td align="left">Reported by</td>'
					.'<td align="left"><span itemprop="author" itemscope itemtype="http://schema.org/Person">'.$user->link_userpage($rs['reporter_id']).'</span> @ <time itemprop="dateCreated" datetime="'.$reportedDate_iso8601.'">'.datename($rs['reported_date']).'</time></td>'//|date_format:"%Y-%m-%d-T%H:00"}
				.'</tr>'
				.'<tr><td colspan="2">&nbsp;</td></tr>'
			.'<tbody'.(!empty($rs['assignedto_id']) ? ' itemtype="http://schema.org/Answer" itemscope itemprop="suggestedAnswer'.($rs['resolved_date'] > 0 || $rs['denied_date'] > 0 ? ' acceptedAnswer' : '').'"' : '').'>' // FIXME Either "acceptedAnswer" or "suggestedAnswer" should be specified
				.'<tr>'
					.'<td align="left">Git Commit</td>'
					.'<td align="left">'
						.($user->typ >= USER_MEMBER && $edit == TRUE ? Bugtracker::getFormFieldCommit($rs['code_commit']) : (!empty($rs['code_commit']) ? '<a href="'.GIT_REPOSITORY_URL.htmlentities($rs['code_commit'], ENT_QUOTES).'" target="_blank">'.htmlentities($rs['code_commit'], ENT_QUOTES).'</a>' : ''))
					.'</td>'
				.'</tr>'
				.'<tr>'
					.'<td align="left">Assigned to</td>'
					.'<td align="left">'.(!empty($rs['assignedto_id']) ? '<span itemprop="author" itemscope itemtype="http://schema.org/Person">'.$user->link_userpage($rs['assignedto_id']).'</span> @ '.datename($rs['assigned_date']) : '<span style="display:none;" itemprop="author" itemscope itemtype="http://schema.org/Person">'.$user->link_userpage($rs['reporter_id']).'</span>').'</td>' // suggestedAnswer:author
				.'</tr>'
				.'<tr>'
					.'<td align="left">Status</td>'
					.'<td align="left">'
						.(empty($rs['resolved_date']) && empty($rs['denied_date']) ? '<span itemprop="text" style="display:none;">Antwort ausstehend...</span>' : '')
						.($rs['resolved_date'] > 0 ? '<span class="strong success" itemprop="text">Resolved</span> @ <time itemprop="dateCreated" datetime="'.$resolvedDate_iso8601.'">'.datename($rs['resolved_date']).'</time>' : '')
						.($rs['denied_date'] > 0 ? '<span class="strong warn" itemprop="text">Denied</span> @ <time itemprop="dateCreated" datetime="'.$deniedDate_iso8601.'">'.datename($rs['denied_date']).'</time>' : '')
						.(empty($rs['resolved_date']) && empty($rs['denied_date']) ? '<time style="display:none;" itemprop="dateCreated" datetime="'.$reportedDate_iso8601.'">'.datename($rs['reported_date']).'</time>' : '') // suggestedAnswer:dateCreated (hidden)
						.'<a style="display:none;" itemprop="url" href="/bug/'.$rs['id'].'">'.$rs['id'].'</a>' // suggestedAnswer:url (hidden)
						.'<span style="display:none;" itemprop="upvoteCount">'.$schemaQuestionAnswerCount.'</span>' // suggestedAnswer:upvoteCount (hidden)
					.'</td>'
				.'</tr>'
			.'</tbody>'
			.'</table>';

			if ($user->typ >= USER_MEMBER && $edit == TRUE)
			{
				$html .= '<div style="margin-top: 10px; display: flex;white-space: nowrap;align-items: flex-start;align-content: flex-start;">'
							.'<input type="button" onclick="window.location.href=\'/bug/'.$rs['id'].'\';" value="cancel" class="align-to-text secondary" style="flex: 0.25;" />'
							.'<input type="submit" value="Updaten" class="align-to-text primary" style="flex: 0.75;">
						</div>';
			} else {
				$html .= Bugtracker::getFormActionsHTML($rs);
			}

		if ($edit == TRUE) $html .= '</form>';

		return $html;
	}

	/**
	 * Return a Bug's DB-Recordset
	 *
	 * @author [z]milamber
	 * @version 1.1
	 * @since 1.0 method added
	 * @since 1.1 `02.06.2023` `IneX` Fixes SQL Injection (CWE-89)
	 *
	 * @param int $bug_id Bug-ID to fetch as record from DB
	 * @return array
	 */
	static function getBugRS($bug_id)
	{
		global $db;

		$sql = 'SELECT * FROM bugtracker_bugs WHERE id = ?';
		$result = $db->query($sql, __FILE__, __LINE__, __METHOD__, [$bug_id]);
		return $db->fetch($result);
	}

	/**
	* Bug Liste
	*
	* Druckt eine Liste aller Bugs aus
	*
	* @return HTML
	* @param Array $show
	* @param String $order
	*/
	static function getBugList($show, $order)
	{
		global $db, $user;

		if($order == '') $order = 'priority ASC, category_id ASC, assignedto_id ASC';

		$sql = 'SELECT DISTINCT
					bugs.*
					, cat.title AS category_title
					, UNIX_TIMESTAMP(resolved_date) AS resolved_date
					, UNIX_TIMESTAMP(denied_date) as denied_date
				FROM bugtracker_bugs AS bugs
				LEFT JOIN bugtracker_categories AS cat ON (bugs.category_id = cat.id)
				LEFT OUTER JOIN user ON (bugs.assignedto_id = user.id)
				LEFT OUTER JOIN user AS userrep ON (bugs.reporter_id = userrep.id)
				WHERE 1=1';

		if(in_array('open', $show) || in_array('resolved', $show)) {
			$sql .=
				" AND ( 1=2"
				.(in_array('open', $show) ? " OR bugs.resolved_date = 0" : "")
				.(in_array('resolved', $show) ? " OR bugs.resolved_date <> 0" : "")
				.")"
			;
		}

		if(in_array('denied', $show) || in_array('notdenied', $show)) {
			$sql .=
				" AND (1=2"
				.(in_array('denied', $show)  ? " OR bugs.denied_date <> 0" :  " ")
				.(in_array('notdenied', $show)  ? " OR bugs.denied_date = 0" :  " ")
				.")"
			;
		}

		// Assigned -----------------------------------
		if(in_array('assigned', $show) || in_array('unassigned', $show)) {
			$sql .= " AND (1=2";

			if($user->is_loggedin() && in_array('assigned', $show)) {
				$sql .= " OR bugs.assignedto_id <> 0";
				if(in_array('own', $show) || in_array('notown', $show)) {
					$sql .=
						" AND (1=2"
						.(in_array('own', $show) ? " OR bugs.assignedto_id = ".$user->id : "" )
						.(in_array('notown', $show)  ? " OR bugs.assignedto_id <> ".$user->id : "")
						.")"
					;
				}
			}

			$sql .=
				(in_array('unassigned', $show) ? " OR bugs.assignedto_id = 0" : " ")
				.")"
			;
		}

		if($user->is_loggedin() && (in_array('new', $show) || in_array('old', $show))) {
			$sql .=
				" AND (1=2"
				.(in_array('new', $show)  ? " OR UNIX_TIMESTAMP(bugs.reported_date) > ".$user->lastlogin : "")
				.(in_array('old', $show)  ? " OR UNIX_TIMESTAMP(bugs.reported_date) <= ".$user->lastlogin : "")
				.")"
			;
		}

		$sql .= " ORDER BY ".$order;

		$result = $db->query($sql, __FILE__, __LINE__);

		$html =
			'<table class="border shadedcells" width="100%">'
				.'<thead><tr class="title">'
					.'<td><a href="'.getChangedURL('order=priority ASC, category_id ASC, assignedto_id ASC').'">Prio</a></td>'
					.'<td><a href="'.getChangedURL('order=category_id ASC, priority ASC, assignedto_id ASC').'">Category</a></td>'
					.'<td>Title</td>'
					.'<td class="hide-mobile">Reported by</td>'
					.'<td class="hide-mobile"><a href="'.getChangedURL('order=assignedto_id DESC, category_id ASC, priority ASC').'">Assignee</a></td>'
					.'<td>Status</td>'
				.'</tr></thead>'
				.'<tbody>';

		while($rs = $db->fetch($result))
		{
			$html .=
				'<tr>'
					.'<td align="left">'.($rs['priority'] === '1' ? '&#128314;' : ($rs['priority'] === '2' ? '&#128312;' : ($rs['priority'] === '3' ? '&#128313;' : ($rs['priority'] === '4' ? '&#9660;' : '?')))).'</td>'
					.'<td align="left">'.$rs['category_title'].'</td>'
					.'<td align="left"><a href="/bug/'.$rs['id'].'">'.str_pad($rs['title'], 8, '.', STR_PAD_RIGHT).'</a></td>'
					.'<td align="left" class="hide-mobile">'.$user->userprofile_link($rs['reporter_id'], ['link' => TRUE, 'username' => TRUE, 'clantag' => FALSE]).'</td>'
					.'<td align="left" class="hide-mobile">'.(!empty($rs['assignedto_id']) ? $user->userprofile_link($rs['assignedto_id'], ['link' => TRUE, 'username' => TRUE, 'clantag' => FALSE]) : '').'</td>';
			if (!empty($rs['resolved_date'])) { // wenn der Bug resolved wurde...
				$html .= '<td align="left" class="tiny"><span class="strong success">Resolved</span> <span class="hide-mobile">'.datename($rs['resolved_date']).'</span></td>';
			} elseif (!empty($rs['denied_date'])) { // wenn der Bug denied wurde...
				$html .= '<td align="left" class="tiny"><span class="strong warn">Denied</span> <span class="hide-mobile">'.datename($rs['denied_date']).'</span></td>';
			} else { // wenn der Bug noch offen ist...
				$html .= '<td align="left"></td>';
			}
			$html .= '</tr>';
		}
		$html .= '</tbody></table>';

		return $html;
	}

	/**
	* Formular für neuen Bug generieren
	* @return string HTML-Code für das Formular "Neuen Bug eintragen"
	*/
	static function getFormNewBugHTML()
	{
		global $user;

		/** Formular nur Ausgeben für eingeloggte User oder Member */
		if ($user->typ >= USER_USER)
		{
			$html = t('newbug-headline', 'bugtracker')
					.'<form action="'.getURL(false,false).'" method="get">'
						.'<input type="hidden" name="action" value="new">'
						.'<input type="hidden" name="url" value="'.getURL(true,true).'">'
						.'<fieldset>'
							.'<label style="display: flex;flex-direction: column;">Titel<br>'.Bugtracker::getFormFieldTitle().'</label>'
							.'<label style="display: flex;flex-direction: column;">Bereich<br>'.Bugtracker::getFormFieldCategory().'</label>'
							.'<label style="display: flex;flex-direction: column;">Priorit&auml;t<br>'.Bugtracker::getFormFieldPriority(3).'</label>'
							.'<label style="display: flex;flex-direction: column;">Beschreibung<br>'.Bugtracker::getFormFieldDescription().'</label>'
						.'</fieldset>
						<fieldset>'
							.'<label style="display: flex;flex-direction: column;">User informieren<br>'.$user->getFormFieldUserlist('msg_users[]', 10).'</label>'
							.'<input type="submit" value="Eintragen" class="secondary">'
						.'</fieldset>'
					.'</form>';

			return $html;
		} else {
			return false;
		}
	}

	/**
	* Formular für neue Kategorie generieren
	* @return string HTML-Code für das Formular "Neue Kategorie hinzufügen"
	*/
	static function getFormNewCategoryHTML()
	{
		global $user;

		/** Formular nur Ausgeben für eingeloggte Member (normale User nicht) */
		if ($user->typ >= USER_MEMBER)
		{
			$html =
				 t('newcategory-headline', 'bugtracker')
				.'<form action="'.getURL(true,false).'" method="GET">'
					.'<input name="action" type="hidden" value="newcategory">'
					.'<input name="url" type="hidden" value="'.getURL(true,true).'">'
					.'<fieldset style="display: flex;white-space: nowrap;align-items: center;">'
						.'<input type="text" name="title" maxlength="100" placeholder="Kategorie-Bezeichnung" style="flex: 1.5;">'
						.'<input class="button" type="submit" value="Add" style="flex: 0.5;">'
					.'</fieldset>'
				.'</form>';

			return $html;
		} else {
			return false;
		}
	}

	/**
	 * Bug Actions-Form
	 *
	 * @param array $rs Fetched Bug DB-record
	 * @return string
	 */
	static function getFormActionsHTML($rs)
	{
		global $user;

		$html = null;

		/** Formular nur Ausgeben für eingeloggte Member (normale User nicht) */
		if($user->typ >= USER_MEMBER)
		{
			$html .= '<form action="/bug/'.$rs['id'].'" method="GET">'
						.'<input name="bug_id" type="hidden" value="'.$rs['id'].'">'
						.'<input name="url" type="hidden" value="'.base64url_encode('/bug/'.$rs['id']).'">'
						.'<fieldset style="display: flex;align-items: center;">'
							.'<label style="white-space: nowrap;">Bug&nbsp;</label>'
								.'<select name="action" size="1">';
								if(empty($rs['resolved_date']) && empty($rs['denied_date']))
								{ // Bug ist noch offen
									if ($rs['assignedto_id'] == $user->id)
									{ // Bug ist mir zugewiesen
										$html .= '<option value="resolve">resolven</option>';
										$html .= '<option value="deny">ablehnen</option>';
										$html .= '<option value="resign">wieder weggeben</option>';
									} elseif (empty($rs['assignedto_id'])) { // Bug ist noch niemandem zugewiesen
										$html .= '<option value="assign">zu mir nehmen</option>';
									} elseif ($rs['assignedto_id'] != $user->id) { // nicht mein Bug
										$html .= '<option value="klauen">klauen</option>';
									}
								} else { // Bug ist bereits gelöst
									if($rs['assignedto_id'] == $user->id) { // Bug ist mir assigned
										$html .= '<option value="reopen">neu eröffnen</option>';
									}
								}
								$html .= '<option value="editlayout">editieren</option>'
								.'</select>';

					$html .= '<input type="submit" value="maaachs" class="secondary">'
						.'</fieldset>'
					.'</form>';
		}

		return $html;
	}

	static function getNumOwnBugs()
	{
		global $db, $user;

		if ($user->typ >= USER_MEMBER)
		{
			$sql = 'SELECT count(*) as num FROM bugtracker_bugs
					 WHERE assignedto_id = ?
					 AND resolved_date = 0 AND denied_date = 0';
			$result = $db->query($sql, __FILE__, __LINE__, __METHOD__, [$user->id]);
			$rs = $db->fetch($result);

			return $rs['num'];
		}
	}

	static function getNumOpenBugs()
	{
		global $db, $user;

		if ($user->typ >= USER_MEMBER) {
		$sql = 'SELECT count(*) as num FROM bugtracker_bugs WHERE assignedto_id = 0';
			$result = $db->query($sql, __FILE__, __LINE__);
		  $rs = $db->fetch($result);

			return $rs['num'];
		}
	}

	static function getNumNewBugs()
	{
		global $db, $user;

		if ($user->typ >= USER_MEMBER) {
			$sql =
				"
				SELECT count(*) as num FROM bugtracker_bugs
				WHERE UNIX_TIMESTAMP(reported_date) > ".$user->lastlogin."
				AND UNIX_TIMESTAMP(resolved_date) = 0
				AND UNIX_TIMESTAMP(denied_date) = 0
				"
			;

		  $rs = $db->fetch($db->query($sql, __FILE__, __LINE__));

		 	return $rs['num'];
		}
	}

	function getFormFieldBuglist($name)
	{
		global $db;

		$sql = 'SELECT * FROM bugtracker_bugs WHERE (denied_date = 0 OR denied_date IS NULL) && (resolved_date = 0 OR resolved_date IS NULL)';
		$result = $db->query($sql, __FILE__, __LINE__);

		$html .= '<select name="'.$name.'" size="1">';
		$html .= '<option value="0"> -- kein Bug --</option>';
		while ($rs = $db->fetch($result)) {
		   $html .= '<option value="'.$rs['id'].'">#'.$rs['id'].' '.htmlentities($rs['title'], ENT_QUOTES).'</option>';
		}
		$html .= '</select>';

		return $html;
	}

	static function getFormFieldCategory($category_id='')
	{
		global $db;

		$sql = 'SELECT * FROM bugtracker_categories ORDER BY title ASC';
		$result = $db->query($sql, __FILE__, __LINE__);

		$html = '<select name="category_id"><option label="--- Kategorie wählen ---" selected disabled>--- Kategorie wählen ---</option>';
		while($rs = $db->fetch($result))
		{
			$html .=
				'<option value="'.$rs['id'].'" '.($rs['id'] == $category_id ? 'selected' : '').'>'
				.htmlentities($rs['title'], ENT_QUOTES).'</option>'
			;
		}

		$html .= '</select>';

		return $html;
	}

	function getFormFieldFilterCategory($category_id='')
	{
		global $db;

		$sql = 'SELECT * FROM bugtracker_categories ORDER BY title ASC';
		$result = $db->query($sql, __FILE__, __LINE__);

		$html = '<select name="show[]">';
		while($rs = $db->fetch($result))
		{
			$html .=
				'<option value="'.$rs['id'].'" '.($rs['id'] == $category_id ? 'selected' : '').'>'
				.htmlentities($rs['title'], ENT_QUOTES).'</option>'
			;
		}

		$html .= '</select>';

		return $html;
	}

	static function getFormFieldDescription($description='') {
		return '<textarea name="description" placeholder="Klare Beschreibung, Herleitung und URL bei Problemen..." style="min-height: 200px;">'.htmlentities($description, ENT_QUOTES).'</textarea>';
	}

	static function getFormFieldPriority($priority=4) {
		return '<select name="priority" size="1">'
					.'<option value="1" '.($priority == 1 ? 'selected' : '').'>'.Bugtracker::getPriorityDescription(1).'</option>'
					.'<option value="2" '.($priority == 2 ? 'selected' : '').'>'.Bugtracker::getPriorityDescription(2).'</option>'
					.'<option value="3" '.($priority == 3 ? 'selected' : '').'>'.Bugtracker::getPriorityDescription(3).'</option>'
					.'<option value="4" '.($priority == 4 ? 'selected' : '').'>'.Bugtracker::getPriorityDescription(4).'</option>'
				.'</select>';
	}

	static function getFormFieldTitle($title='') {
		return '<input type="text" name="title" value="'.htmlentities($title, ENT_QUOTES).'" placeholder="Feature/Problem Titel">';
	}

	static function getPriorityDescription($priority_id) {
		switch($priority_id) {
			case 1: $descr = '&#128314; Sehr Hoch'; break;
			case 2: $descr = '&#128312; Hoch'; break;
			case 3: $descr = '&#128313; Normal'; break;
			case 4: $descr = '&#9660; Niedrig'; break;
		}
		return $descr;
	}

	function getFormFieldCommit($commit='') {
		return '<input class="text" name="code_commit" type="text" value="'.htmlentities($commit, ENT_QUOTES).'" maxlength="7"> <label for="code_commit" class="info tiny">7 Zeichen Hash ID, z.B. «<code>7cb0118</code>»</label>';
	}
}
