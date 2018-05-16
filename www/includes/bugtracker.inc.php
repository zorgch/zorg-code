<?php
/**
 * Bugtracker Includes
 *
 * @author [z]milamber
 * @version 1.0
 * @package Zorg
 * @subpackage Bugtracker
 */
/**
 * File Includes
 */
include_once( __DIR__ .'/messagesystem.inc.php');
include_once( __DIR__ .'/usersystem.inc.php');
include_once( __DIR__ .'/util.inc.php');

/**
 * Bugtracker Klasse
 *
 * @author [z]milamber
 * @version 1.0
 * @package Zorg
 * @subpackage Bugtracker
 */
Class Bugtracker {

	function execActions() {

		global $db, $user;

		if($_GET['action'] == 'new') {
			// Validate & escape fields
			$bugCategory = ( isset($_GET['category_id']) && is_numeric($_GET['category_id']) && $_GET['category_id'] >= 0 ? $_GET['category_id'] : user_error('Bugtracker: invalid Category-ID "' . $_GET['category_id'] . '"', E_USER_WARNING) );
			$bugPriority = ( isset($_GET['priority']) && is_numeric($_GET['priority']) && $_GET['priority'] >= 0 ? $_GET['priority'] : user_error('Bugtracker: invalid Priority "' . $_GET['priority'] . '"', E_USER_WARNING) );
			$bugTitle = ( isset($_GET['title']) && !empty($_GET['title']) ? sanitize_userinput($_GET['title']) : user_error('Bugtracker: invalid Title "' . $_GET['title'] . '"', E_USER_WARNING) );
			$bugDescription = ( !empty($_GET['description']) ? sanitize_userinput($_GET['description']) : '' );
			
			$sql =
				"
				INSERT INTO
					bugtracker_bugs (category_id, reporter_id, priority, reported_date, title, description)
				VALUES
					(
						".$bugCategory."
						, ".$user->id."
						, ".$bugPriority."
						, now()
						, '".$bugTitle."'
						, '".$bugDescription."'
					)
				"
			;
			$db->query($sql, __FILE__, __LINE__);

			$sql = "SELECT MAX(id) AS id FROM bugtracker_bugs";
			$rs = $db->fetch($db->query($sql, __FILE__, __LINE__));

			// Activity Eintrag auslösen
			Activities::addActivity($user->id, 0, 'hat den Bug <a href="/bugtracker.php?bug_id='.$rs['id'].'">'.$bugTitle.'</a> gemeldet.<br/><br/>', 'bu');

			// Benachrichtigungs-Message
			if(count($_GET['msg_users']) > 0) {
				for ($i=0; $i < count($_GET['msg_users']); $i++) {
					Messagesystem::sendMessage(
						$user->id
						, $_GET['msg_users'][$i]
						, addslashes(
								stripslashes(
								'[Bugreport] von '.$user->id2user($user->id)
								)
							)
						, addslashes(
								stripslashes(
									'<a href="/bugtracker.php?bug_id="'.$rs['id'].'">'.$bugTitle.'</a>'
									.'<br /><i>'
									.$bugDescription
									.'</i>'
								)
							)
						, (is_array($_GET['msg_users']) ? implode(',', $_GET['msg_users']) : $_GET['msg_users'])
					);
				}
			}

			header("Location: /bugtracker.php?bug_id=".$rs['id']);
			exit;
		}

		else if($_GET['action'] == 'assign') {
			$bugId = ( isset($_GET['bug_id']) && is_numeric($_GET['bug_id']) && $_GET['bug_id'] >= 0 ? $_GET['bug_id'] : user_error('Bugtracker: invalid Bug-ID "' . $_GET['bug_id'] . '"', E_USER_WARNING) );
			
			$rs = Bugtracker::getBugRS($bugId);
			if($rs['assignedto_id'] == 0) {
				$sql =
					"UPDATE bugtracker_bugs"
					." SET assignedto_id=".$user->id
					." , assigned_date = now()"
					." WHERE id = ".$bugId
				;
				$db->query($sql, __FILE__, __LINE__);
			}
			header("Location: ".base64_decode($_GET['url']));
			exit;
		}

		else if($_GET['action'] == 'klauen') {
			$bugId = ( isset($_GET['bug_id']) && is_numeric($_GET['bug_id']) && $_GET['bug_id'] >= 0 ? $_GET['bug_id'] : user_error('Bugtracker: invalid Bug-ID "' . $_GET['bug_id'] . '"', E_USER_WARNING) );
			
			$rs = Bugtracker::getBugRS($bugId);
			if($rs['assignedto_id'] == 0 OR $rs['assignedto_id'] > 0) {
				$sql =
					"UPDATE bugtracker_bugs"
					." SET assignedto_id=".$user->id
					." , assigned_date = now()"
					." WHERE id = ".$bugId
				;
				$db->query($sql, __FILE__, __LINE__);
			}
			header("Location: ".base64_decode($_GET['url']));
			exit;
		}

		else if($_GET['action'] == 'reopen') {
			$bugId = ( isset($_GET['bug_id']) && is_numeric($_GET['bug_id']) && $_GET['bug_id'] >= 0 ? $_GET['bug_id'] : user_error('Bugtracker: invalid Bug-ID "' . $_GET['bug_id'] . '"', E_USER_WARNING) );
			
			$sql =
				"UPDATE bugtracker_bugs
				SET
					resolved_date = 0
					, denied_date = 0
				WHERE id = ".$bugId
			;
			$db->query($sql, __FILE__, __LINE__);

			// Activity Eintrag auslösen
			Activities::addActivity($user->id, 0, 'hat den Bug <a href="/bugtracker.php?bug_id='.$rs['id'].'">'.$rs['title'].'</a> wieder eröffnet.<br/><br/>', 'bu');

			if($user->id != $rs['reporter_id']) {
				Messagesystem::sendMessage(
					$user->id,
					$rs['reporter_id'],
					'[Bugtracker] Bug '.$_GET['bug_id'].' reopened',
					'<i><a href="/bugtracker.php?bug_id='.$rs['id'].'">'.$rs['title'].'</a>'
					.'<br />'
					.$rs['description']
					.'</i>',
					$rs['reporter_id']
				);
			}

			header("Location: ".base64_decode($_GET['url']));
			exit;
		}

		else if($_GET['action'] == 'resign') {
			$bugId = ( isset($_GET['bug_id']) && is_numeric($_GET['bug_id']) && $_GET['bug_id'] >= 0 ? $_GET['bug_id'] : user_error('Bugtracker: invalid Bug-ID "' . $_GET['bug_id'] . '"', E_USER_WARNING) );
			
			$sql =
				"UPDATE bugtracker_bugs
				SET assignedto_id = 0, assigned_date = 0
				WHERE id = ".$bugId
			;
			$db->query($sql, __FILE__, __LINE__);
			header("Location: ".base64_decode($_GET['url']));
			exit;
		}

		else if($_GET['action'] == 'resolve') {
			$bugId = ( isset($_GET['bug_id']) && is_numeric($_GET['bug_id']) && $_GET['bug_id'] >= 0 ? $_GET['bug_id'] : user_error('Bugtracker: invalid Bug-ID "' . $_GET['bug_id'] . '"', E_USER_WARNING) );
			
			$sql =
				"UPDATE bugtracker_bugs
				SET resolved_date = now()
				WHERE id = ".$bugId
			;
			$db->query($sql, __FILE__, __LINE__);
			$rs = Bugtracker::getBugRS($bugId);

			// Activity Eintrag auslösen
			Activities::addActivity($user->id, 0, 'hat den Bug <a href="/bugtracker.php?bug_id='.$bugId.'">'.$rs['title'].'</a> gelöst.<br/><br/>', 'bu');

			if($user->id != $rs['reporter_id']) {
				Messagesystem::sendMessage(
					$user->id,
					$rs['reporter_id'],
					'[Bugtracker] Bug '.$bugId.' resolved',
					'<i><a href="/bugtracker.php?bug_id='.$rs['id'].'">'.$rs['title'].'</a> '
					.'<br />'
					.$rs['description']
					.'</i>'
				);
			}

			header("Location: ".base64_decode($_GET['url']));
			exit;
		}

		else if($_GET['action'] == 'edit') {
			// Validate & escape fields
			$bugId = ( isset($_GET['bug_id']) && is_numeric($_GET['bug_id']) && $_GET['bug_id'] >= 0 ? $_GET['bug_id'] : user_error('Bugtracker: invalid Bug-ID "' . $_GET['bug_id'] . '"', E_USER_WARNING) );
			$bugCategory = ( isset($_GET['category_id']) && is_numeric($_GET['category_id']) && $_GET['category_id'] >= 0 ? $_GET['category_id'] : user_error('Bugtracker: invalid Category-ID "' . $_GET['category_id'] . '"', E_USER_WARNING) );
			$bugPriority = ( isset($_GET['priority']) && is_numeric($_GET['priority']) && $_GET['priority'] >= 0 ? $_GET['priority'] : user_error('Bugtracker: invalid Priority "' . $_GET['priority'] . '"', E_USER_WARNING) );
			$bugTitle = ( isset($_GET['title']) && !empty($_GET['title']) ? sanitize_userinput($_GET['title']) : user_error('Bugtracker: invalid Title "' . $_GET['title'] . '"', E_USER_WARNING) );
			$bugDescription = ( !empty($_GET['description']) ? sanitize_userinput($_GET['description']) : '' );
			
			$sql =
				"UPDATE bugtracker_bugs"
				." SET title = '".$bugTitle."'"
				." , description = '".$bugDescription."'"
				.",  category_id = ".$bugCategory.""
				.",  priority = ".$bugPriority.""
				." WHERE id = ".$bugId
			;
			$db->query($sql, __FILE__, __LINE__);
			header("Location: ".base64_decode($_GET['url']));
			exit;
		}

		else if($_GET['action'] == 'deny') {
			$sql =
				"UPDATE bugtracker_bugs"
				." SET denied_date = now()"
				." WHERE id = ".$bugId
			;
			$db->query($sql, __FILE__, __LINE__);
			$rs = Bugtracker::getBugRS($bugId);

			// Activity Eintrag auslösen
			Activities::addActivity($user->id, 0, 'hat den Bug <a href="/bugtracker.php?bug_id='.$bugId.'">'.$rs['title'].'</a> abgelehnt.<br/><br/>', 'bu');

			if($user->id != $rs['reporter_id']) {
				Messagesystem::sendMessage(
					$user->id,
					$rs['reporter_id'],
					'[Bugtracker] Bug '.$bugId.' denied',
					'<i><a href="/bugtracker.php?bug_id='.$rs['id'].'">'.$rs['title'].'</a>'
					.'<br />'
					.$rs['description']
					.'</i>'
				);
			}

			header("Location: ".base64_decode($_GET['url']));
			exit;
		}

		else if($_GET['action'] == 'newcategory') {
			$title_sanitized = sanitize_userinput($_GET['title']);
			$categoryTitle = ( isset($title_sanitized) && !empty($title_sanitized) ? $title_sanitized : user_error('Bugtracker: invalid Category Title "' . $_GET['title'] . '"', E_USER_WARNING) );
			
			$sql =
				"INSERT INTO bugtracker_categories (title)"
				." VALUES('".$title_sanitized."')"
			;
			$db->query($sql, __FILE__, __LINE__);
			header("Location: ".base64_decode($_GET['url']));
			exit;
		}
	}

	function getBugHTML($bug_id, $edit=FALSE) {

		global $db, $user;

		$sql =
			"SELECT"
			." bugtracker_bugs.*"
			.", categories.title as category"
			.", UNIX_TIMESTAMP(assigned_date) as assigned_date"
			.", UNIX_TIMESTAMP(denied_date) as denied_date"
			.", UNIX_TIMESTAMP(resolved_date) as resolved_date"
			.", UNIX_TIMESTAMP(reported_date) as reported_date"
			.", CONCAT(reporter.clan_tag, reporter.username) as reporter"
			.", CONCAT(assignedto.clan_tag, assignedto.username) as assignedto"
			." FROM bugtracker_bugs"
			." LEFT JOIN user as reporter ON (bugtracker_bugs.reporter_id = reporter.id)"
			." LEFT JOIN user as assignedto ON (bugtracker_bugs.assignedto_id = assignedto.id)"
			." LEFT JOIN bugtracker_categories as categories ON (bugtracker_bugs.category_id = categories.id)"
			." WHERE bugtracker_bugs.id =".$bug_id
		;

		$result = $db->query($sql, __FILE__, __LINE__);

		$rs = $db->fetch($result);

		if($edit == TRUE) {
			$html .=
				'<form action="'.$_SERVER['PHP_SELF'].'" method="get">'
				.'<input name="action" type="hidden" value="edit">'
				.'<input name="bug_id" type="hidden" value="'.$rs['id'].'">'
				.'<input name="url" type="hidden" value="'.base64_encode(getChangedURL('action=')).'">'
			;
		}

		$html .=
			'<table class="border shadedcells" width="100%">'

			.'<tr>'
			.'<td align="left" width="100">Nummer</td>'
			.'<td align="left">'.$rs['id'].'</td>'
			.'</tr>'

			.'<tr>'
			.'<td align="left">Priorit&auml;t:</td>'
			.'<td align="left">'
			.($user->typ == USER_MEMBER && $edit == TRUE ? Bugtracker::getFormFieldPriority($rs['priority']) : $rs['priority'].' ('.Bugtracker::getPriorityDescription($rs['priority']).')' )
			.'</td>'
			.'</tr>'

			.'<tr>'
			.'<td align="left">Bereich</td>'
			.'<td align="left" style="color: #FF0000; font-weight: bold;">'
			.($user->typ == USER_MEMBER && $edit == TRUE ? Bugtracker::getFormFieldCategory($rs['category_id']) : $rs['category'])
			.'</td>'
			.'</tr>'

			.'<tr>'
			.'<td align="left">Title</td>'
			.'<td align="left" style="font-weight: bold;">'
			.($user->typ == USER_MEMBER && $edit == TRUE ? Bugtracker::getFormFieldTitle($rs['title']) : $rs['title'])
			.'</td>'
			.'</tr>'

			.'<tr>'
			.'<td align="left">Reported by:</td>'
			.'<td align="left">'.$rs['reporter'].' @ '.datename($rs['reported_date']).'</td>'
			.'</tr>'

			.'<tr>'
			.'<td align="left" valign="top">Beschreibung</td>'
			.'<td align="left" colspan="5">'
			.($user->typ == USER_MEMBER && $edit == TRUE ? Bugtracker::getFormFieldDescription($rs['description']) : nl2br($rs['description']))
			.'<br />&nbsp;'
			.'</td></tr>'

			.'<tr>'
			.'<td align="left">Assigned to:</td>'
			.'<td align="left">'.$user->link_userpage($rs['assignedto_id']).' @ '.datename($rs['assigned_date']).'</td>'
			.'</tr>'

			.'<tr>'
			.'<td align="left">Status:</td>'
			.'<td align="left">'
			.($rs['resolved_date'] > 0 ? '<b>Resolved</b> @ '.datename($rs['resolved_date']) : '')
			.($rs['denied_date'] > 0 ? '<b>Denied</b> @ '.datename($rs['denied_date']) : '')
			.'</td>'
			.'</tr>'

			.'<tr>'
			.'<td align="center" colspan="6">'
			.($user->typ == USER_MEMBER && $edit == TRUE ? '<input class="button" type="submit" value="Updaten">' : Bugtracker::getFormActionsHTML($rs))
			.'</td></tr>'


			.'</table>'
		;

		if($edit == TRUE) {
			$html .= '</form>';
		}

		return $html;
	}

	function getBugRS($bug_id) {
		global $db;
		$sql =
			"SELECT *"
			." FROM bugtracker_bugs"
			." WHERE id =".$bug_id
		;
		$result = $db->query($sql, __FILE__, __LINE__);
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
	function getBugList($show, $order) {
		global $db, $user;

		if($order == '') $order = "priority ASC, category_id ASC, assignedto_id ASC";

		$sql =
			"
			SELECT DISTINCT
				cat.title AS category_title
				, CONCAT(user.clan_tag, user.username) AS assignedto
				, CONCAT(userrep.clan_tag, userrep.username) AS reportedby
				, bugs.*
				, UNIX_TIMESTAMP(resolved_date) AS resolved_date
				, UNIX_TIMESTAMP(denied_date) as denied_date
			FROM bugtracker_bugs AS bugs
			LEFT JOIN bugtracker_categories AS cat ON (bugs.category_id = cat.id)
			LEFT OUTER JOIN user ON (bugs.assignedto_id = user.id)
			LEFT OUTER JOIN user AS userrep ON (bugs.reporter_id = userrep.id)
			WHERE 1=1
			"
		;

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

			if($user->id > 0 && in_array('assigned', $show)) {
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

		if($user->id > 0 && (in_array('new', $show) || in_array('old', $show))) {
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
			'<table class="border" width="100%">'
			.'<tr class="title">'
			.'<td>'
			.'<a href="'.getChangedURL('order=priority ASC, category_id ASC, assignedto_id ASC').'">'
			.'Prio'
			.'</a>'
			.'</td>'
			.'<td>'
			.'<a href="'.getChangedURL('order=category_id ASC, priority ASC, assignedto_id ASC').'">'
			.'Category'
			.'</a>'
			.'</td>'

			.'<td>Title</td>'

			.'<td>Reported by</td>'

			.'<td>'
			.'<a href="'.getChangedURL('order=assignedto_id DESC, category_id ASC, priority ASC').'">'
			.'Assigned to'
			.'</a>'
			.'</td>'

			.'<td>Resolved @</td>'

			.'</tr>'
		;

		while($rs = $db->fetch($result)) {
			$html .=
				'<tr>'
				.'<td align="left" bgcolor="'.TABLEBACKGROUNDCOLOR.'">'.$rs['priority'].'</td>'
				.'<td align="left" bgcolor="'.TABLEBACKGROUNDCOLOR.'">'.$rs['category_title'].'</td>'
				.'<td align="left" bgcolor="'.TABLEBACKGROUNDCOLOR.'">'
				.'<a href="/bugtracker.php?bug_id='.$rs['id'].'">'.str_pad($rs['title'], 8, '.', STR_PAD_RIGHT).'</a>'
				.'</td>'
				.'<td align="left" bgcolor="'.TABLEBACKGROUNDCOLOR.'">'.$rs['reportedby'].'</td>'
				.'<td align="left" bgcolor="'.TABLEBACKGROUNDCOLOR.'">'.$rs['assignedto'].'</td>'
			;
			if ($rs['resolved_date'] > 0) { // wenn der Bug resolved wurde...
				$html .=
					'<td align="left" bgcolor="'.TABLEBACKGROUNDCOLOR.'">'.datename($rs['resolved_date']).'</td>'
				;
			} elseif ($rs['denied_date'] > 0) { // wenn der Bug denied wurde...
				$html .=
					'<td align="left" bgcolor="'.TABLEBACKGROUNDCOLOR.'">Denied @ '.datename($rs['denied_date']).'</td>'
				;
			} else { // wenn der Bug noch offen ist...
				$html .=
					'<td align="left" bgcolor="'.TABLEBACKGROUNDCOLOR.'"></td>'
				;
			}
			$html .=
				'</tr>'
			;
		}
		$html .= '</table>';

		return $html;
	}

	/**
	* Formular für neuen Bug generieren
	* @return string HTML-Code für das Formular "Neuen Bug eintragen"
	*/
	function getFormNewBugHTML() {
		global $user;

		/** Formular nur Ausgeben für eingeloggte User oder Member */
		if ($user->typ >= USER_USER)
		{
			$html =
				 t('newbug-headline', 'bugtracker')
				.'<form action="'.$_SERVER['PHP_SELF'].'" method="get">'
				.'<input name="action" type="hidden" value="new">'
				.'<input name="url" type="hidden" value="'.base64_encode($_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING']).'">'
				.'<table>'
					.'<tr>'
						.'<td><strong>Bereich:</strong></td>'
						.'<td>'.Bugtracker::getFormFieldCategory().'</td>'
						.'<td rowspan="5" style="text-align:left; vertical-align:top; padding-left:20px;">'
							.'<strong>Benachrichtigen:</strong><br />'
							.$user->getFormFieldUserlist('msg_users[]', 20)
						.'</td>'
					.'</tr><tr>'
						.'<td><strong>Titel:</strong></td>'
						.'<td>'.Bugtracker::getFormFieldTitle().'</td>'
					.'</tr><tr>'
						.'<td><strong>Priorit&auml;t:</strong></td>'
						.'<td>'.Bugtracker::getFormFieldPriority(3).'</td>'
					.'</tr><tr>'
						.'<td colspan="2">'.Bugtracker::getFormFieldDescription().'</td>'
					.'</tr><tr>'
						.'<td><input class="button" type="submit" value="Eintragen"></td>'
					.'</tr>'
				.'</table>'
				.'</form>'
			;

			return $html;
		} else {
			return false;
		}
	}

	/**
	* Formular für neue Kategorie generieren
	* @return string HTML-Code für das Formular "Neue Kategorie hinzufügen"
	*/
	function getFormNewCategoryHTML() {
		global $user;
		
		/** Formular nur Ausgeben für eingeloggte Member (normale User nicht) */
		if ($user->typ >= USER_MEMBER)
		{
			$html =
				 t('newcategory-headline', 'bugtracker')
				.'<form action="'.$_SERVER['PHP_SELF'].'" method="GET">'
				.'<input name="action" type="hidden" value="newcategory">'
				.'<input name="url" type="hidden" value="'.base64_encode($_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING']).'">'
				.'<table>'
					.'<tr>'
						.'<td><input class="text" name="title" maxlength="40" size="40" type="text" value="'.$rs['title'].'" placeholder="Kategorie-Bezeichnung"></td>'
						.'<td><input class="button" type="submit" value="Add"></td>'
					.'</tr>'
				.'</table>'
				.'</form>'
			;
	
			return $html;
		} else {
			return false;
		}
	}

	function getFormActionsHTML($rs) {
		global $user;
		
		$html = '';
		
		/** Formular nur Ausgeben für eingeloggte Member (normale User nicht) */
		if($user->typ >= USER_MEMBER) {
		
			$html .=
				'<table>'
				.'<form action="'.$_SERVER['PHP_SELF'].'" method="GET">'
				.'<input name="bug_id" type="hidden" value="'.$rs['id'].'">'
				.'<input name="url" type="hidden" value="'.base64_encode('http://'.$_SERVER['SERVER_NAME'].$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING']).'">'
				.'<tr>'
				.'<td>'
				.'Bug '
				.'<select name="action" size="1">'
			;
	
			if($rs['resolved_date'] == 0 && $rs['denied_date'] == 0) { // noch offen ------------------------------
	
				if($rs['assignedto_id'] == $user->id) { // mein bug ------------------------
					$html .= '<option value="resolve">resolven</option>';
					$html .= '<option value="deny">ablehnen</option>';
					$html .= '<option value="resign">wieder weggeben</option>';
				} else if($rs['assignedto_id'] == '0') { // noch niemandem zugewiesen
					$html .= '<option value="assign">zu mir nehmen</option>';
				} elseif ($rs['assignedto_id'] != $user->id) { // nicht mein bug --------------------
					$html .= '<option value="klauen">klauen</option>';
				}
	
			} else { // Bereits fertig --------------------
	
				if($rs['assignedto_id'] == $user->id) { // mein bug --------------------
					$html .= '<option value="reopen">neu eröffnen</option>';
				}
			}
	
			$html .= '<option value="editlayout">editieren</option>';
	
			$html .=
				'</select>'
				.'</td>'
				.'<td><input class="button" type="submit" value="Ok."></td>'
				.'</tr>'
				.'</form>'
				.'</table>'
			;
			
		}

		return $html;
	}

	static function getNumOwnBugs () {
		global $db, $user;

		if ($user->typ >= USER_MEMBER) {
			$sql =
					"SELECT count(*) as num FROM bugtracker_bugs"
					." WHERE assignedto_id = ".$user->id
					." AND resolved_date = 0 AND denied_date = 0"
			;
			$result = $db->query($sql, __FILE__, __LINE__);
		  $rs = $db->fetch($result);

			return $rs['num'];
		}
	}

	static function getNumOpenBugs () {
		global $db, $user;

		if ($user->typ >= USER_MEMBER) {
		$sql = "SELECT count(*) as num FROM bugtracker_bugs WHERE assignedto_id = 0";
			$result = $db->query($sql, __FILE__, __LINE__);
		  $rs = $db->fetch($result);

			return $rs['num'];
		}
	}

	static function getNumNewBugs () {
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

	function getFormFieldBuglist($name) {
		global $db;
		$sql =
			"SELECT * FROM bugtracker_bugs "
			." WHERE denied_date = 0 && resolved_date = 0";
		$result = $db->query($sql, __FILE__, __LINE__);

		$html .= '<select name="'.$name.'" size="1">';
		$html .= '<option value="0"> -- kein Bug --</option>';
		while ($rs = mysql_fetch_array($result)) {
		   $html .= '<option value="'.$rs['id'].'">#'.$rs['id'].' '.$rs['title'].'</option>';
		}
		$html .= '</select>';

		return $html;
	}

	function getFormFieldCategory($category_id='') {

		global $db;

		$html = '<select name="category_id">';

		$sql =
			"SELECT *"
			." FROM bugtracker_categories"
			." order by title asc"
		;
		$result = $db->query($sql, __FILE__, __LINE__);
		while($rs = $db->fetch($result)) {
			$html .=
				'<option value="'.$rs['id'].'" '.($rs['id'] == $category_id ? 'selected' : '').'>'
				.$rs['title'].'</option>'
			;
		}


		$html .= '</select>';

		return $html;
	}

	function getFormFieldFilterCategory($category_id='') {

		global $db;

		$html = '<select name="show[]">';

		$sql =
			"SELECT *"
			." FROM bugtracker_categories"
			." order by title asc"
		;
		$result = $db->query($sql, __FILE__, __LINE__);
		while($rs = $db->fetch($result)) {
			$html .=
				'<option value="'.$rs['id'].'" '.($rs['id'] == $category_id ? 'selected' : '').'>'
				.$rs['title'].'</option>'
			;
		}


		$html .= '</select>';

		return $html;
	}

	function getFormFieldDescription($description='') {
		return '<textarea name="description" placeholder="Beschreibung" cols="60" rows="15" style="width:100%;">'.$description.'</textarea>';
	}

	function getFormFieldPriority($priority=4) {
		return '<select name="priority" size="1">'
			.'<option value="1" '.($priority == 1 ? 'selected' : '').'>(1) '.Bugtracker::getPriorityDescription(1).'</option>'
			.'<option value="2" '.($priority == 2 ? 'selected' : '').'>(2) '.Bugtracker::getPriorityDescription(2).'</option>'
			.'<option value="3" '.($priority == 3 ? 'selected' : '').'>(3) '.Bugtracker::getPriorityDescription(3).'</option>'
			.'<option value="4" '.($priority == 4 ? 'selected' : '').'>(4) '.Bugtracker::getPriorityDescription(4).'</option>'
			.'</select>'
		;
	}

	function getFormFieldTitle($title='') {
		return '<input class="text" name="title" type="text" value="'.$title.'" style="width:100%;">';
	}

	function getPriorityDescription($priority_id) {
		switch($priority_id) {
			case 1: $descr = 'Sehr Hoch'; break;
			case 2: $descr = 'Hoch'; break;
			case 3: $descr = 'Normal'; break;
			case 4: $descr = 'Niedrig'; break;
		}
		return $descr;
	}
}
