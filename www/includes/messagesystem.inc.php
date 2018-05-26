<?php
/**
 * Messagesystem
 * 
 * Das Messagesystem erlaubt es einerseits, dass sich User
 * gegenseitig persönliche Nachrichten zuschicken können.
 * Andererseits wird es verwendet für Benachrichtigungen
 * aus diversen Bereichen der Webseite, wie z.B. Spiele,
 * Comment-Benachrichtigungen, etc.
 * Wenn ein User die entsprechende Option in seinen Ein-
 * stellungen aktiviert hat, wird zudem auch eine E-Mail
 * Nachricht verschickt.
 *
 * Diese Klasee benutzt folgende Tabellen aus der DB:
 *		messages
 *
 * @version		2.0
 * @package		Zorg
 * @subpackage	Messagesystem
 */ 

/**
 * File Includes
 * @include util.inc.php
 * @include strings.inc.php 	Strings die im Zorg Code benutzt werden
 */
//require_once( __DIR__ . '/main.inc.php');
require_once( __DIR__ . '/util.inc.php');
include_once( __DIR__ . '/strings.inc.php');

/**
 * Messagesystem Class
 * 
 * In dieser Klasse befinden sich alle Funktionen zum Senden & Verwalten der Nachrichten
 *
 * @author		[z]milamber
 * @author		IneX
 * @date		25.05.2018
 * @version		3.0
 * @package		Zorg
 * @subpackage	Messagesystem
 */
class Messagesystem {

	/**
	 * Nachrichten löschen
	 * 
	 * Löscht ausgewählte Nachrichten von der Inbox/Outbox
	 * 
	 * @author [z]milamber
	 * @date 
	 * @version 2.0
	 *
	 * @see BARBARA_HARRIS, Messagesystem::sendMessage()
	 * @param integer $messageid ID der ausgewählten Nachricht(en)
	 * @param integer $deleter_userid User-ID welcher die Nachricht(en) löscht
	 * @global $db Globales Class-Object mit allen MySQL-Methoden
	 */
	function execActions()
	{
		global $db, $user;

		if($_POST['action'] == 'sendmessage') {

			$to_users = ( empty($_POST['to_users']) ? $user->id : $_POST['to_users'] );
			
			for ($i=0; $i < count($to_users); $i++) {
				
				/** Wenn ich mir selber was schicke, dann nimm die Bärbe als Absender */
				if ($to_users[$i] == $user->id) {
					Messagesystem::sendMessage(
						BARBARA_HARRIS,
						$to_users[$i],
						$_POST['subject'],
						$_POST['text'],
						implode(',', $to_users)
					);
				
				/** Nachricht an andere Leute */
				} else {
					Messagesystem::sendMessage(
						$user->id,
						$to_users[$i],
						$_POST['subject'],
						$_POST['text'],
						implode(',', $to_users)
					);
				}
				
			}

			/** Eigene Message für den 'Sent'-Ordner */
			Messagesystem::sendMessage(
				$user->id,
				$user->id,
				$_POST['subject'],
				$_POST['text'],
				$to_users=implode(',', $to_users),
				1
			);
			
			/** Wieso wird hier die deleteMessage-Funktion aufgerufen in der "sendmessage"-Aktion? Inex/28.10.2013 */
			if($_POST['delete_message_id'] > 0) {
				Messagesystem::deleteMessage($_POST['delete_message_id'], $user->id);
			}

			//header("Location: profil.php?user_id=".$user->id."&box=outbox&sent=successful".session_name()."=".session_id());
			$headerLocation = sprintf('Location: %s/profil.php?user_id=%d&box=outbox&sent=successful%s%s', SITE_URL, $user->id, session_name(), session_id());
			header($headerLocation);

			//exit;
		}


		if($_POST['do'] == 'delete_messages') {

			for ($i=0; $i < count($_POST['message_id']); $i++) {
				Messagesystem::deleteMessage($_POST['message_id'][$i], $user->id);
			}

			if(count($_POST['message_id']) == 1) {
				$msgid = Messagesystem::getPrevMessageid($_POST['message_id'][0]);
				if($msgid > 0) {
					header("Location: messagesystem.php?message_id=".$msgid."&".session_name()."=".session_id());
					//exit;
				} else {
					header("Location: profil.php?user_id=".$user->id."&".session_name()."=".session_id());
					//exit;
				}
			}

			header("Location: ".base64_decode($_POST['url']));
			//exit;
		}
		
		
		if($_POST['do'] == 'messages_as_unread') {
			
			/** Change Message Status to UNREAD */
			for ($i=0; $i < count($_POST['message_id']); $i++) {
				Messagesystem::doMessagesUnread($_POST['message_id'][$i], $user->id);
			}

			if(count($_POST['message_id']) == 1) {
				$msgid = Messagesystem::getPrevMessageid($_POST['message_id'][0]);
				if($msgid > 0) {
					header("Location: messagesystem.php?message_id=".$msgid."&".session_name()."=".session_id());
					//exit;
				} else {
					header("Location: profil.php?user_id=".$user->id."&".session_name()."=".session_id());
					//exit;
				}
			}

			header("Location: ".base64_decode($_POST['url']));
			//exit;
		}
		
		
		if($_POST['do'] == 'mark_all_as_read') {
			
			/** Mark all Messages as read */
			Messagesystem::doMarkAllAsRead($user->id);

			if(count($_POST['message_id']) == 1) {
				$msgid = Messagesystem::getPrevMessageid($_POST['message_id'][0]);
				if($msgid > 0) {
					header("Location: messagesystem.php?message_id=".$msgid."&".session_name()."=".session_id());
					//exit;
				} else {
					header("Location: profil.php?user_id=".$user->id."&".session_name()."=".session_id());
					//exit;
				}
			}

			header("Location: ".base64_decode($_POST['url']));
			//exit;
		}
	}
	
	
	/**
	 * Nachrichten löschen
	 * 
	 * Löscht ausgewählte Nachrichten von der Inbox/Outbox
	 * 
	 * @author [z]milamber
	 * @version 1.0
	 *
	 * @param integer $messageid ID der ausgewählten Nachricht(en)
	 * @param integer $deleter_userid User-ID welcher die Nachricht(en) löscht
	 * @global $db Globales Class-Object mit allen MySQL-Methoden
	 */
	function deleteMessage($messageid, $deleter_userid)
	{
		global $db;

		$sql = "SELECT id, owner FROM messages where id = ".$messageid;
		$rs = $db->fetch($db->query($sql, __FILE__, __LINE__, __METHOD__));

		if($rs['owner'] == $deleter_userid) {
		$sql =
			"DELETE FROM messages WHERE id = ".$messageid
		;
		$db->query($sql, __FILE__, __LINE__, __METHOD__);
		}
	}


	/**
	 * Nachrichten als ungelesen ändern
	 * 
	 * @author IneX
	 * @date 28.10.2013
	 * @since 1.0
	 * @version 1.0
	 *
	 * @param integer $messageid ID der ausgewählten Nachricht(en)
	 * @global $db Globales Class-Object mit allen MySQL-Methoden
	 */
	function doMessagesUnread($messageid, $userid)
	{
		global $db;
		
		if ($messageid > 0 && $messageid != '' && $userid > 0 && $userid != '') // ok man könnte auch noch auf $user->id checken
		{
			$sql =
				"UPDATE messages SET isread='0' WHERE isread='1' AND id=$messageid AND owner=$userid";
			$db->query($sql, __FILE__, __LINE__, __METHOD__);
		}
	}
	
	
	/**
	 * Alle Nachrichten als gelesen markieren
	 * 
	 * @author IneX
	 * @date 28.10.2013
	 * @since 1.0
	 * @version 1.0
	 *
	 * @param integer $userid User-ID welcher alle Nachricht(en) als gelesen markieren möchte
	 * @global $db Globales Class-Object mit allen MySQL-Methoden
	 */
	function doMarkAllAsRead($userid)
	{
		global $db;
		
		if ($userid > 0 && $userid != '') // man könnte auch noch auf $user->id checken
		{
			$sql =
				"UPDATE messages SET isread='1' WHERE isread='0' AND owner=$userid";
				$db->query($sql, __FILE__, __LINE__, __METHOD__);
		}
	}
	
	
	/**
	 * Nachrichten-Löschfomular
	 * 
	 * Baut das HTML-Formular um Nachrichten zu löschen
	 * 
	 * @author [z]milamber
	 * @date 
	 * @version 1.0
	 *
	 * @param integer $id ID der ausgewählten Nachricht
	 * @return string
	 */
	function getFormDelete($id)
	{
		global $user;

	  $html =

	    '<table>'
	    .'<form name="deleteform" action="'.$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'].'" method="post">'
	    .'<input type="hidden" name="do" value="delete_messages">'
	    .'<input type="hidden" name="url" value="'.base64_encode("/profil.php?user_id=".$user->id).'">'
	    .'<input type="hidden" name="message_id[]" value="'.$id.'">'
			.'<tr>'
			.'<td>'
			.'<input class="button" name="submit" type="submit" value="Nachricht l&ouml;schen">'
			.'</td>'
			.'</tr></table>'
			.'</form>'
	  ;
	  return $html;
	}
	
	
	/**
	 * Nachrichten-Formular
	 * 
	 * Baut das HTML-Formular um eine neue Nachrichten zu versenden
	 * 
	 * @author [z]milamber
	 * @date 
	 * @version 1.0
	 *
	 * @param string $to_users Alle Empfänger der Nachricht
	 * @param string $subject Titel der Nachricht
	 * @param string $text Nachrichten-Text
	 * @param integer $delete_message_id Löschstatus der Nachricht (Default: ungelöscht)
	 * @return string
	 */
	function getFormSend($to_users, $subject, $text, $delete_message_id=0)
	{
	  global $user;

	  $html =
	    '<form name="sendform" action="/profil.php?'.$_SERVER['QUERY_STRING'].'" method="post">'
	    .'<input type="hidden" name="action" value="sendmessage">'
		.'<input type="hidden" name="url" value="'.base64_encode($_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING']).'">'
	    .'<table width="'.FORUMWIDTH.'" class="border" align="center">'
	  ;

	  if($_GET['sent'] == 'successful') {
		$html .= '<tr><td colspan="2" style="text-align: center;"><br /><font size="6"><b>Nachricht gesendet!</b></font><br />&nbsp;</td></tr>';
	  }

	  $html .=
			'<tr bgcolor="'.TABLEBACKGROUNDCOLOR.'"><td colspan="3"><b>Nachricht senden</b></td></tr>'
			.'<tr bgcolor="'.TABLEBACKGROUNDCOLOR.'">'
			.'<td width="70"><b>An:</b></td>'
			.'<td><b>Betreff:</b></td>'
			.'<td width="80%">'
			.'<input class="text" maxlength="40" name="subject" size="35" tabindex="1" type="text" value="'.$subject.'"></td>'
			.'</tr>'
			.'<tr><td>'.$user->getFormFieldUserlist('to_users[]', 15, $to_users, 4).'</td>'
			.'<td colspan="2">'
			.'<textarea class="text" cols="90" name="text" rows="14" tabindex="2" wrap="hard">'
			.$text
			.'</textarea>'
			.'</td></tr><tr style="font-size: x-small;"><td colspan="3" valign="middle">'
			.'<input class="button" name="submit" tabindex="3" type="submit" value="Send">'
			.'&nbsp;<a href="profil.php?user_id='.$user->id.'&amp;box=inbox">Zur&uuml;ck</a>'
		;

		if($delete_message_id > 0) {
			$html .=
				'&nbsp;<input name="delete_message_id" tabindex="4" type="checkbox" value="'.$delete_message_id.'">'
				.'obige Nachricht l&ouml;schen'
			;
		}

		$html .=
			'</form>'
			.'</td>'
			.'</tr>'
			.'</tr></table>'
	  ;
	  return $html;
	}
	
	
	/**
	 * Message-Inbox/Outbox
	 * 
	 * Baut das HTML um die Nachrichten-Verwaltung anzuzeigen
	 * 
	 * @author [z]milamber, IneX
	 * @date 
	 * @version 2.0
	 * @since 1.0
	 *
	 * @param string $box Darstellung des Ein- oder Ausgangs (inbox|outbox)
	 * @param integer $pagesize Anzahl Nachrichten pro Seite (Default: 11, wegen Farbwechsel)
	 * @param integer $page Aktuelle Seite mit Nachrichten (Default: 1)
	 * @global $db Globales Class-Object mit allen MySQL-Methoden
	 * @global $user Globales Class-Object mit den User-Methoden & Variablen
	 * @return string
	 */
	function getInboxHTML($box, $pagesize=11, $page=1, $orderby='date')
	{
		global $db, $user;

		$page = ($page == '') ? 1 : $page;
		if($box == '') $box = 'inbox';
		
	  // Neuste (isread) immer zuoberst
	  $sql = "
		SELECT *, UNIX_TIMESTAMP(date) as date
		FROM messages where owner = ".$user->id ."
		AND from_user_id ".($box == "inbox" ? "<>" : "=").$user->id ."
		ORDER BY isread ASC, ".$orderby." DESC
		LIMIT ".($page-1) * $pagesize.",".$pagesize
	  ;

	  $result = $db->query($sql, __FILE__, __LINE__, __METHOD__);
	  $html .=
		'<form name="inboxform" action="'.$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'].'" method="POST">'
		//.'<input name="do" type="hidden" value="delete_messages">'
		.'<input type="hidden" name="url" value="'.base64_encode(getURL()).'">'
		.'<table class="border" width="100%">'
		.'<tr><th align="center" colspan="6"><b>Pers&ouml;nliche Nachrichten</b>'
		.' '
		.($box == "inbox" ? 'Empfangen' : '<a href="'.getChangedURL('box=inbox').'">Empfangen</a>')
		.' / '
		.($box == "outbox" ? 'Gesendet' : '<a href="'.getChangedURL('box=outbox').'">Gesendet</a>')
		.'<a href="'.$_SERVER['PHP_SELF'].'?user_id='.$user->id.'&newmsg"><button name="button_newMessage" class="button" type="button" style="float:right;">Neue Nachricht</button></a>'
		.'</td></tr>'
		.'<tr><td>'
		.'<input class="button" onClick="selectAll();" type="button" value="Alle">'
		.'</th>'
		.'<td>New</td>'
		.'<td>Sender</td>'
		.'<td>Empf&auml;nger</td>'
		.'<td>Subject</td>'
		.'<td>Datum</td>'
		.'</tr>'
	  ;

	  if($db->num($result) == 0) {
		$html .= '<tr><td align="center" colspan="5"><b> --- Postfach leer ---</b></td></tr>';
	  } else {

		  while($rs = $db->fetch($result)) {

			$i++;
			$color = ($i % 2 == 0) ?  BACKGROUNDCOLOR : TABLEBACKGROUNDCOLOR;
			if($rs['isread'] == 0) $color = NEWCOMMENTCOLOR;
			if($rs['from_user_id'] == $user->id) $color = OWNCOMMENTCOLOR;

			$html .=
				'<tr>'
				.'<td align="center" bgcolor="'.$color.'"><input name="message_id[]" type="checkbox" value="'.$rs['id'].'" onclick="document.getElementById(\'do_messages_as_unread\').disabled = false;document.getElementById(\'do_delete_messages\').disabled = false"></td>'
			    .($rs['isread'] == 0 ? '<td align="center" bgcolor="'.$color.'"><img src="/images/new_msg.png" width="16" height="16" /></td>' : '<td align="center" bgcolor="'.$color.'"></td>')
				.'<td align="center" bgcolor="'.$color.'">'.$user->link_userpage($rs['from_user_id']).'</td>'
				.'<td align="center" bgcolor="'.$color.'" width="30%">';

			foreach (explode(',', $rs['to_users']) as $value) {
				$html .= $user->link_userpage($value).' ';
			}

			$html .=
				'</td>'
				.'<td align="center" bgcolor="'.$color.'">'
				.'<a href="/messagesystem.php?message_id='.$rs['id'].'">'.str_pad($rs['subject'], 60, ' . ', STR_PAD_BOTH).'</a>'
				.'</td>'
				.'<td align="center" bgcolor="'.$color.'">'.datename($rs['date']).'</td>'
				.'</tr>'
			;
		  }

		  $html .= '<tr><td align="left" colspan="3">';

		  
		  $html .= '<button id="do_mark_all_as_read" name="do" class="button" type="submit" value="mark_all_as_read">ALLE als gelesen markieren</button>';
		  
		  
		  $html .= '<button id="do_messages_as_unread" name="do" class="button" type="submit" value="messages_as_unread" disabled>Markierte als ungelesen</button>';

		  
		  $html .= '<button id="do_delete_messages" name="do" class="button" type="submit" value="delete_messages" disabled>Markierte Nachrichten l&ouml;schen</button>';
		  
		  $html .= '</td><td align="right" colspan="3">';

		  $sql =
			"
			SELECT count(*) as num
			FROM messages where owner = ".$user->id."
			AND from_user_id ".($box == "inbox" ? "<>" : "=").$user->id
		  ;
		$rs = $db->fetch($db->query($sql, __FILE__, __LINE__, __METHOD__));
		$numpages = ceil($rs['num'] / $pagesize); // number of pages
		  $html .= '<b>Pages: ';
		  for($j = 1; $j <= $numpages; $j++) {
			if($page != $j) {
				$html .= ' <a href="'.getChangedURL('page='.$j).'">'.$j.'</a>';
			} else {
				$html .= ' '.$j;
			}
		  }

		  $html .= '</b></td></tr>';
	  }


	  $html .= '</table>';
	  $html .= '</form>';

	  $html .=
		'<script language="javascript">'
		.'function selectAll() {'
		.'  for(i=2; i < ('.$db->num($result).'+3); i++)'
		.'  document.inboxform.elements[i].checked = !document.inboxform.elements[i].checked;'
		.'}'
		.'</script>'
	  ;

	  return $html;
	}
	
	
	/**
	 * Anzahl neuer Nachrichten
	 * 
	 * Berechnet die Anzahl neuer Nachrichten
	 * 
	 * @author [z]milamber
	 * @date 
	 * @version 1.0
	 *
	 * @global $db Globales Class-Object mit allen MySQL-Methoden
	 * @global $user Globales Class-Object mit den User-Methoden & Variablen
	 * @return integer
	 */
	static function getNumNewMessages()
	{
		global $db, $user;

		if ($user->typ != USER_NICHTEINGELOGGT) {
			$sql = "SELECT count(*) as num FROM messages WHERE owner = ".$user->id." AND isread = '0'";
			$result = $db->query($sql, __FILE__, __LINE__, __METHOD__);
			$rs = $db->fetch($result);

			return $rs['num'];
		}
	}
	

	/**
	 * Nachricht anzeigen
	 * 
	 * Zeigt eine Message an
	 *
	 * @author [z]milamber
	 * @date 
	 * @version 1.0
	 * 
	 * @param int $id ID der Nachricht
	 * @return string
	 */
	function getMessage($id)
	{
		global $db, $user;

	  // Message holen http://www.zorg.ch
	  $sql =
		"
		SELECT
			messages.*
		, UNIX_TIMESTAMP(date) as date
		, CONCAT(user.clan_tag, user.username) AS from_user
		FROM messages
		LEFT JOIN user ON (messages.from_user_id = user.id)
		WHERE messages.id = ".$id
	  ;
		$rs = $db->fetch($db->query($sql, __FILE__, __LINE__, __METHOD__));

	  if ($rs['owner'] == $user->id) {
		  $html .=
			'<table class="border" width="100%">'
			.'<tr bgcolor="'.TABLEBACKGROUNDCOLOR.'" height="30">'
			.'<td align="left" width="80">'
				.(Messagesystem::getNextMessageid($rs['id']) > 0 ? '<a href="/messagesystem.php?message_id='.Messagesystem::getNextMessageid($rs['id']).'"><-- </a> | ' : '')
				.(Messagesystem::getPrevMessageid($rs['id']) > 0 ? '<a href="/messagesystem.php?message_id='.Messagesystem::getPrevMessageid($rs['id']).'"> --></a>' : '')
			.'</td>'
			.'<td align="right" width="80%">'
			.Messagesystem::getFormDelete($id)
				.'</td>'
			.'<td align="right" rowspan="5">'.$user->link_userpage($rs['from_user_id'], TRUE).'</td>'
			.'</tr>'

			.'<tr bgcolor="'.TABLEBACKGROUNDCOLOR.'">'
			.'<td align="left"><b>From</b></td>'
			.'<td align="left">'.$rs['from_user'].'</td>'
			.'</tr>'

			.'<tr bgcolor="'.TABLEBACKGROUNDCOLOR.'">'
			.'<td align="left"><b>Date</b></td>'
			.'<td align="left">'.datename($rs['date']).'</td></tr>'
			.'<tr bgcolor="'.TABLEBACKGROUNDCOLOR.'"><td align="left"><b>To</b></td>'
			.'<td align="left">'
		  ;

		  foreach (explode(',', $rs['to_users']) as $value) {
			$html .= $user->link_userpage($value).' ';
		  }

		  $html .=
			'</td>'
			.'</tr>'

			.'<tr bgcolor="'.TABLEBACKGROUNDCOLOR.'" height="40">'
			.'<td align="left" valign="top"><b>Subject</b></td>'
			.'<td align="left" valign="top" width="70%">'.$rs['subject'].'</td>'
			.'</tr>'
			.'<tr><td><img height="2" src="/images/pixel_trans.gif" width="100"></td></tr>'
			.'<tr><td align="left" colspan="3">'
			.maxwordlength(nl2br($rs['text']), 100)
			.'</td></tr>'
			.'</table>'
		  ;

		  // Als gelesen markieren
			$sql = "UPDATE messages set isread = '1' where id = $id;";
			$db->query($sql, __FILE__, __LINE__, __METHOD__);
	  } else {
		$html = t('invalid-permissions', 'messagesystem');
	  }



	  return $html;
	}
	
	
	/**
	 * Nächste Nachricht anzeigen
	 * 
	 * Holt die ID der jeweils älteren Nachricht gegenüber der aktuell geöffneten
	 * 
	 * @author [z]milamber
	 * @date 
	 * @version 1.0
	 *
	 * @param integer $id ID der aktuell angezeigten Nachricht
	 * @global $db Globales Class-Object mit allen MySQL-Methoden
	 * @global $user Globales Class-Object mit den User-Methoden & Variablen
	 * @return integer
	 */
	function getNextMessageid($id)
	{
		global $db, $user;

		$sql =
			"SELECT *, UNIX_TIMESTAMP(date) as date"
			." FROM messages"
			." WHERE owner = ".$user->id
			." AND from_user_id !=".$user->id
			." AND id > ".$id
			." ORDER BY id desc"
			." LIMIT 0,1"
		;
		$rs = $db->fetch($db->query($sql, __FILE__, __LINE__, __METHOD__));

		return $rs['id'];
	}

		
	/**
	 * Vorherige Nachricht anzeigen
	 * 
	 * Holt die ID der jeweils jüngeren Nachricht gegenüber der aktuell geöffneten
	 * 
	 * @author [z]milamber
	 * @date 
	 * @version 1.0
	 *
	 * @param integer $id ID der aktuell angezeigten Nachricht
	 * @global $db Globales Class-Object mit allen MySQL-Methoden
	 * @global $user Globales Class-Object mit den User-Methoden & Variablen
	 * @return integer
	 */
	function getPrevMessageid($id)
	{
		global $db, $user;

		$sql =
			"SELECT *, UNIX_TIMESTAMP(date) as date"
			." FROM messages"
			." WHERE owner = ".$user->id
			." AND from_user_id !=".$user->id
			." AND id < ".$id
			." ORDER BY id desc"
			." LIMIT 0,1"
		;
		$rs = $db->fetch($db->query($sql, __FILE__, __LINE__, __METHOD__));

		return $rs['id'];
	}

	
	/**
	 * Persönliche Nachricht senden
	 * 
	 * Speichert die gesendete Nachricht im Postfach des Empfängers und meinem Postausgang
	 * 
	 * @author [z]milamber
	 * @author IneX
	 * @date 17.03.2018
	 * @version 3.0
	 * @since 1.0
	 * @since 2.0 verschickt eine Notification über die neue Nachricht per E-Mail
	 * @since 3.0 verschickt eine Notification per Telegram Messenger
	 *
	 * @param integer	$from_user_id User-ID des Senders
	 * @param integer	$owner User-ID des Nachrichten-Owners
	 * @param string	$subject Titel der Nachricht
	 * @param string	$text (Optional) Nachrichten-Text
	 * @param string	$to_users (Optional) Liste aller Empfänger der Nachricht
	 * @param string	$isread (Optional) Lesestatus der Nachricht - ENUM('0','1'), Default: Ungelesen ('0')
	 * @global object	$db Globales Class-Object mit allen MySQL-Methoden
	 * @global object	$user Globales Class-Object mit den User-Methoden & Variablen
	 */
	function sendMessage(int $from_user_id, int $owner, $subject, $text='', $to_users='', $isread='0')
	{
		global $db, $user;

		if(!isset($to_users) || empty($to_users)) $to_users = $owner;
		if(empty($text)) $text = t('message-empty-text', 'messagesystem');

		/**
		 * Send Message to recipient(s)
		 */
		try {
			if (DEVELOPMENT) error_log("[DEBUG] Sending SINGLE Zorg Message '$subject' to user $owner");
			$sql = sprintf("INSERT INTO messages (from_user_id, owner, subject, text, date, isread, to_users)
							VALUES (%d, %d, '%s', '%s', NOW(), '%s', '%s')",
							$from_user_id, $owner, escape_text($subject), escape_text($text), $isread, $to_users);
			$db->query($sql, __FILE__, __LINE__, __METHOD__);
		} catch (Exception $e) {
			error_log($e->getMessage());
		}

		/** Send E-Mail Notification */
		if ($owner != $from_user_id)
		{
			try {
				Messagesystem::sendEmailNotification($from_user_id, $owner, $subject, $text);
			} catch (Exception $e) {
				error_log($e->getMessage());
			}

			/** Send Telegram Notification */
			try {
				$message = t('telegram-newmessage-notification', 'messagesystem', [ SITE_URL, $owner, $user->id2user($from_user_id, TRUE), SITE_HOSTNAME, text_width(remove_html($text, '<br>'), 140, '...') ] );
				Messagesystem::sendTelegramNotificationUser($message, $owner);
			} catch (Exception $e) {
				error_log($e->getMessage());
			}
		}
	}

	/**
	 * E-Mail Hinweis über neue Nachricht senden
	 * 
	 * Generiert eine E-Mail um einen Benutzer auf eine neue persönliche Nachricht hinzuweisen
	 * 
	 * @author IneX
	 * @date 15.05.2009
	 * @version 1.0
	 *
	 * @param	integer	$from_user_id	User-ID des Senders
	 * @param	integer	$to_user_id		User-ID des Empfängers
	 * @param	string	$titel			Titel der ursprünglichen Nachricht
	 * @param	string	$text			Ursprünglicher Text
	 * @global	object	$db				Globales Class-Object mit allen MySQL-Methoden
	 * @global	object	$user			Globales Class-Object mit den User-Methoden & Variablen
	 */
	function sendEmailNotification(int $from_user_id, int $to_user_id, $titel, $text)
	{
		global $db, $user;
		
		/** E-Mailnachricht bauen */
		if ($to_user_id != 0 && $to_user_id <> '' && is_numeric($to_user_id))
		{
			/** Get User E-Mail - if E-Mail Notifications are enabled */
			$empfaengerMail = $user->id2useremail($to_user_id);
			
			/** Nur, wenn User E-Mailbenachrichtigung aktiviert hat...! */
			if (!empty($empfaengerMail))
			{
				$empfaengerName = $user->id2user($to_user_id, TRUE);
				$senderName = $user->id2user($from_user_id, TRUE);
				
				$header = t('email-notification-header', 'messagesystem', [ SITE_HOSTNAME, ZORG_EMAIL, phpversion() ]);
				
				$subject = 	htmlspecialchars( t('email-notification-subject', 'messagesystem', [ $senderName, SITE_HOSTNAME ]), ENT_DISALLOWED, 'UTF-8' );
				
				$body = htmlspecialchars( t('email-notification-body', 'messagesystem', [ SITE_URL, $titel, $senderName, text_width(remove_html($text, '<br>'), 140, '...'), $to_user_id ]), ENT_DISALLOWED, 'UTF-8' );
				
				/** Vesende E-Mail an User */
				try {
					if (DEVELOPMENT) error_log("[DEBUG] mail() '$subject' to user: $empfaengerName");
					mail("$empfaengerName <$empfaengerMail>", $subject, $body, $header);
					//mail("$empfaengerName <$empfaengerMail>", utf8_encode($subject), utf8_encode($body), $header);
				} catch (Exception $e) {
					error_log($e->getMessage());
				}
			}
		}
	}


	/**
	 * Send Telegram Messenger Notification to a User
	 * Schickt eine Notification an einen Telegram Chat eines einzelnen Users
	 *
	 * @author	IneX
	 * @date	25.05.2018
	 * @version	1.0
	 * @since	3.0
	 *
	 * @see usersystem::userHasTelegram()
	 * @see Messagesystem::sendTelegramMessage()
	 * @see Messagesystem::sendTelegramPhoto()
	 * @param	integer	$to_user_id			User-Id des Empfängers
	 * @param	string	$notificationText	Content welcher an die Telegram Chats geschickt wird
	 * @param	string	$imageUrl			(Optional) URL to an Image to send along the Message
	 * @global	object	$user				Globales Class-Object mit den User-Methoden & Variablen
	 */
	static public function sendTelegramNotificationUser($notificationText, int $to_user_id, $imageUrl='')
	{
		global $user;

		/** Get the Telegram Chat-IDs */
		if (isset($to_user_id) && $to_user_id > 0 && is_numeric($to_user_id))
		{
			/** For a specific (list of) User-ID - only if Telegram-Notifications enabled */
			$telegramChatIds = $user->userHasTelegram($to_user_id);

			if (!empty($telegramChatIds))
			{
				if (DEVELOPMENT) error_log("[DEBUG] Found USER Telegram Chat-ID: $telegramChatIds");
				$notificationText_formatted = Messagesystem::getFormattedTelegramNotificationText($notificationText);

				/** Trigger Notification */
				if (!empty($sendPhoto)) Messagesystem::sendTelegramPhoto(['caption' => $notificationText_formatted, 'url' => $imageUrl], $telegramChatIds);
				else Messagesystem::sendTelegramMessage($notificationText_formatted, $telegramChatIds);

			} else {
				if (DEVELOPMENT) error_log("[DEBUG] NO Telegram Chat-ID found for USER $to_user_id");
				return false;
			}

		} else {
			error_log( t('invalid-userid', 'messagesystem') );
			return false;
		}

	}


	/**
	 * Send Telegram Messenger Notification to a Group
	 * Schickt eine Notification an einen Telegram Gruppenchat (mehrere User)
	 *     Default: TELEGRAM_GROUPCHAT_ID
	 *
	 * @author	IneX
	 * @date	25.05.2018
	 * @version	1.0
	 * @since	3.0
	 *
	 * @see $botconfigs
	 * @see Messagesystem::sendTelegramMessage()
	 * @see Messagesystem::sendTelegramPhoto()
	 * @param	string	$notificationText	Content welcher an die Telegram Chats geschickt wird
	 * @param	string	$imageUrl			(Optional) URL to an Image to send along the Message
	 * @global	array	$botconfigs			Array mit allen Telegram Bot-Configs
	 */
	static public function sendTelegramNotificationGroup($notificationText, $imageUrl='')
	{
		global $botconfigs;

		/** Get Telegram Group Chat ID */
		if (defined('TELEGRAM_GROUPCHAT_ID'))
		{
			$telegramChatIds = TELEGRAM_GROUPCHAT_ID;
			
			if (!empty($telegramChatIds))
			{
				if (DEVELOPMENT) error_log("[DEBUG] Found GROUP Telegram Chat-ID: $telegramChatIds");
				$notificationText_formatted = Messagesystem::getFormattedTelegramNotificationText($notificationText);

				/** Trigger Notification */
				if (!empty($imageUrl)) Messagesystem::sendTelegramPhoto(['caption' => $notificationText_formatted, 'url' => $imageUrl], $telegramChatIds);
				else Messagesystem::sendTelegramMessage($notificationText_formatted, $telegramChatIds);

			} else {
				error_log( t('invalid-telegram-chatid', 'messagesystem') );
				return false;
			}
		} else {
			return false;
		}

	}


	/**
	 * Send a Message via Telegram Messenger
	 * Schickt eine Notification an die Telegram Chats von Usern
	 *
	 * @author	IneX
	 * @date	17.03.2018
	 * @version	3.0
	 * @since	2.0
	 * @since	3.0
	 *
	 * @TODO integrate with TelegramBot\TelegramBotManager\BotManager
	 *
	 * @link https://core.telegram.org/bots/api#sendmessage
	 * @see Messagesystem::getFormattedTelegramNotificationText()
	 * @param	string			$notificationText	Content welcher an die Telegram Chats geschickt wird
	 * @param	string|array	$telegramChatIds	Telegram Chat-ID des Empfängers: one (as String) or more (as Array)
	 * @global	array			$botconfigs			Array mit allen Telegram Bot-Configs
	 */
	static public function sendTelegramMessage($notificationText, $telegramChatIds)
	{
		global $botconfigs;

		$telegramAPImethod = 'sendMessage';
		$telegramParseMode = 'html';

		/** Make sure a proper Notification Text is passed & the Telegram Bot-Configs exist */
		if ((!empty($notificationText) && strlen($notificationText) > 0) && (isset($botconfigs) && is_array($botconfigs)))
		{
			/** When we got at least 1 Chat-ID... */
			if (!empty($telegramChatIds))
			{
				/** For multiple users */
				if(is_array($telegramChatIds))
				{
					if (DEVELOPMENT) error_log("[DEBUG] " . __METHOD__ . " to MULTIPLE CHATS");

					/** ...send the Telegram Message to each of them */
					foreach ($telegramChatIds as $chatId)
					{
						/** Build API Call */
						$data = [
						    'chat_id' => $chatId,
						    'parse_mode' => $telegramParseMode,
						    'text' => $notificationText,
						];
						$telegramAPIcall = TELEGRAM_API_URI . "/$telegramAPImethod?" . http_build_query($data);

						/** Send the Telegram message */
						if (DEVELOPMENT) error_log("[DEBUG] " . __METHOD__ . " using '$telegramAPImethod' to Chat $chatId");
						if (DEVELOPMENT) error_log("[DEBUG] " . __METHOD__ . " API call: $telegramAPIcall");
						if (!empty($telegramAPImethod)) file_get_contents( $telegramAPIcall );
					}

				/** For a single Chat-ID */
				} else {
					if (DEVELOPMENT) error_log("[DEBUG] " . __METHOD__ . " to SINGLE CHAT");

					/** Build API Call */
					$chatId = $telegramChatIds;
					$data = [
					    'chat_id' => $chatId,
					    'parse_mode' => $telegramParseMode,
					    'text' => $notificationText,
					];
					$telegramAPIcall = TELEGRAM_API_URI . "/$telegramAPImethod?" . http_build_query($data);

					/** Send the Telegram message */
					if (DEVELOPMENT) error_log("[DEBUG] " . __METHOD__ . " using '$telegramAPImethod' to Chat $chatId");
					if (DEVELOPMENT) error_log("[DEBUG] " . __METHOD__ . " API call: $telegramAPIcall");
					if (!empty($telegramAPImethod)) file_get_contents( $telegramAPIcall );
				}
			}
		} else {
			error_log( t('invalid-message', 'messagesystem') );
		}
	}


	/**
	 * Send a Photo via Telegram Messenger
	 * Schickt eine Photo-Notification an die Telegram Chats von Usern
	 *
	 * @author	IneX
	 * @date	21.01.2018
	 * @version	2.0
	 * @since	2.0
	 * @since	3.0
	 *
	 * @TODO integrate with TelegramBot\TelegramBotManager\BotManager
	 * @TODO Alternative to file_get_contents -> https://stackoverflow.com/a/4247082/5750030
	 *
	 * @link https://core.telegram.org/bots/api#sendphoto
	 * @see Messagesystem::getFormattedTelegramNotificationText()
	 * @param	array	$imageData			Image data array: URL to an Image & Caption text
	 * @param	string	$telegramChatIds	Telegram Chat-ID des Empfängers: one (integer) or more (array)
	 * @global	array	$botconfigs			Array mit allen Telegram Bot-Configs
	 */
	static public function sendTelegramPhoto(array $imageData, $telegramChatIds)
	{
		global $botconfigs;

		$telegramAPImethod = 'sendPhoto';

		/** Make sure a proper Image Data Array is passed & the Telegram Bot-Configs exist */
		if (is_array($imageData) && (isset($imageData['url']) && strlen($imageData['url']) > 0) && (isset($botconfigs) && is_array($botconfigs)))
		{
			$image_caption = (isset($imageData['caption']) && strlen($imageData['caption']) > 0 ? $imageData['caption'] : false);

			/** Fix missing Server address in Links */
			$image_url = ( (strpos($imageData['url'], 'href="/') > 0) ? str_replace('href="/', 'href="' . SITE_URL . '/', $image_url) : $imageData['url'] );

			/** Test if the Image URL is valid: */
			if (urlExists($image_url))
			{
				if (DEVELOPMENT) error_log("[DEBUG] " . __METHOD__ . " valid image url: $image_url");

				/** When we got at least 1 Chat-ID... */
				if (!empty($telegramChatIds))
				{
					/** For multiple users */
					if(is_array($telegramChatIds))
					{
						if (DEVELOPMENT) error_log("[DEBUG] " . __METHOD__ . " to MULTIPLE CHATS");

						/** ...send the Telegram Pohot to each of them */
						foreach ($telegramChatIds as $chatId)
						{
							/** Build API Call */
							$data = [
							    'chat_id' => $chatId,
							    'caption' => $image_caption,
							    'photo' => $image_url
							];
							$telegramAPIcall = TELEGRAM_API_URI . "/$telegramAPImethod?" . http_build_query($data);

							/** Send the Telegram message */
							if (DEVELOPMENT) error_log("[DEBUG] " . __METHOD__ . " using '$telegramAPImethod' to Chat $chatId");
							if (DEVELOPMENT) error_log("[DEBUG] " . __METHOD__ . " API call: $telegramAPIcall");
							if (!empty($telegramAPImethod)) file_get_contents( $telegramAPIcall );
						}

					/** For a single Chat-ID */
					} else {
						if (DEVELOPMENT) error_log("[DEBUG] " . __METHOD__ . " to SINGLE CHAT");
						$chatId = $telegramChatIds;

						/** Build API Call */
						$data = [
							    'chat_id' => $chatId,
							    'caption' => $image_caption,
							    'photo' => $image_url
							];
						$telegramAPIcall = TELEGRAM_API_URI . "/$telegramAPImethod?" . http_build_query($data);

						/** Send the Telegram message */
						if (DEVELOPMENT) error_log("[DEBUG] " . __METHOD__ . " using '$telegramAPImethod' to Chat $chatId");
						if (DEVELOPMENT) error_log("[DEBUG] " . __METHOD__ . " API call: $telegramAPIcall");
						if (!empty($telegramAPImethod)) file_get_contents( $telegramAPIcall );
					}
				}
			} else {
				error_log( t('invalid-image-data', 'messagesystem'), E_USER_NOTICE);
			}
		} else {
			error_log( t('invalid-image-data', 'messagesystem'), E_USER_NOTICE);
		}
	}


	/**
	 * Cleanup Message for Telegram Messenger Notification
	 *
	 * @author	IneX
	 * @date	25.05.2018
	 * @version	1.0
	 * @since	3.0
	 *
	 * @link https://core.telegram.org/bots/api#html-style
	 * @param	string	$notificationText	Content welcher für die Telegram Nachricht vorgesehen ist
	 * @return	string						Returns formatted & cleaned up $notificationText as String
	 */
	static public function getFormattedTelegramNotificationText($notificationText)
	{
		if (DEVELOPMENT) error_log("[DEBUG] getFormattedTelegramNotificationText() passed raw string: $notificationText");

		/**
		 * Add missing Server address in HTML-Links inside Notification Text
		 */
		if (strpos($notificationText, 'href="/') > 0) $notificationText = str_replace('href="/', 'href="' . SITE_URL . '/', $notificationText);

		/**
		 * Strip away all HTML-tags & line breaks
		 * Except from the whitelist:
		 * <b>, <strong>, <i>, <a>, <code>, <pre>
		 */
		$notificationText = str_replace(array("\r", "\n", "&nbsp;"), ' ', $notificationText);
		$notificationText = strip_tags($notificationText, '<b><i><a><code><pre>');
		$notificationText = html_entity_decode($notificationText);

		if (DEVELOPMENT) error_log("[DEBUG] " . __METHOD__ . " passed raw string: $notificationText");
		return ( !empty($notificationText) ? $notificationText : false );
	}


	/**
	 * NOT IMPLEMENTED YET! - Format Link to Mention Telegram User inline
	 * Gibt einen Link aus, welcher Telegram benutzt um einen spezifischen Telegram Benutzer zu @mention
	 *    Example: <a href="tg://user?id=123456789">inline mention of a user</a>
	 *
	 * @author	IneX
	 * @date	25.05.2018
	 * @version	1.0
	 * @since	3.0
	 *
	 * @TODO Database column "telegram_user_id" must be added first, for this to work
	 * @TODO probably it's more common that a userNAME is passed? => needs usersystem::user2id()
	 *
	 * @link https://core.telegram.org/bots/api#html-style
	 * @see usersystem::id2user()
	 * @param	integer	$userid	User-ID (numeric String) dessen Telegram User mentioned werden soll
	 * @global	object	$db 	Globales Class-Object mit allen MySQL-Methoden
	 * @global	object	$user	Globales Class-Object mit den User-Methoden & Variablen
	 * @return	string			Returns HTML href-link formatted as Telegram readable User-IDs mention
	 */
	static public function getTelegramUserMentionLink($userid)
	{
		global $db, $user;

		try {
			if (isset($userid) && $userid > 0 && is_numeric($userid))
			{
				$sql = "SELECT
							telegram_user_id tui
						FROM
							user
						WHERE
							telegram_user_id IS NOT NULL
							AND id = $userid
						LIMIT 0,1";
				$telegramUserIds = mysql_fetch_assoc($db->query($sql, __FILE__, __LINE__, __METHOD__));
				$telegramUserId = $telegramUserIds['tui'];
				if (DEVELOPMENT) error_log("[DEBUG] " . __METHOD__ . " found Telegram User ID $telegramUserId");

				if (!empty($telegramUserId))
				{
					$username = $user->id2user($telegramUserId);
					$link = sprintf('<a href="tg://user?id=%d">%s</a>', $telegramUserId, $username);
					if (DEVELOPMENT) error_log("[DEBUG] " . __METHOD__ . " returns HTML-link: $link");
					return $telegramUserIds['tui'];
				} else {
					return false;
				}

			} else {
				error_log( t('invalid-userid', 'messagesystem') );
				return false;
			}

		} catch (Exception $e) {
			error_log($e->getMessage());
		}
	}

}
