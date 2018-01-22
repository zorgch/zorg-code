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
 */
//require_once( __DIR__ . '/main.inc.php');
require_once( __DIR__ . '/util.inc.php');

/**
 * Messagesystem Class
 * 
 * In dieser Klasse befinden sich alle Funktionen zum Senden & Verwalten der Nachrichten
 *
 * @author		[z]milamber
 * @date		
 * @version		2.0
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
	 * @param integer $messageid ID der ausgewählten Nachricht(en)
	 * @param integer $deleter_userid User-ID welcher die Nachricht(en) löscht
	 * @global $db Globales Array mit allen wichtigen MySQL-Datenbankvariablen
	 */
	function execActions()
	{
		global $db, $user;

		if($_POST['action'] == 'sendmessage') {

			$to_users = $_POST['to_users'];

			for ($i=0; $i < count($to_users); $i++) {
				
				// Wenn ich mir selber was schicke, dann nimm die Bärbe als Absender
				if ($to_users[$i] == $user->id) {
					Messagesystem::sendMessage(
						59,
						$to_users[$i],
						$_POST['subject'],
						$_POST['text'],
						implode(',', $to_users)
					);
				
				// Nachricht an andere Leute
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

			// Eigene Message für den 'Sent'-Ordner
			/* Moved to be a part of the "sendMessage" function (IneX, 29.08.2011)
			Messagesystem::sendMessage(
				$user->id,
				$user->id,
				$_POST['subject'],
				$_POST['text'],
				$to_users=implode(',', $to_users),
				1
			);*/
			
			// Wieso wird hier die deleteMessage-Funktion aufgerufen in der "sendmessage"-Aktion? Inex/28.10.2013
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
			
			// Change Message Status to UNREAD
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
			
			// Mark all Messages as read
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
	 * @global $db Globales Array mit allen wichtigen MySQL-Datenbankvariablen
	 */
	function deleteMessage($messageid, $deleter_userid)
	{
		global $db;

		$sql = "SELECT id, owner FROM messages where id = ".$messageid;
		$rs = $db->fetch($db->query($sql, __FILE__, __LINE__));

		if($rs['owner'] == $deleter_userid) {
	  	$sql =
	  		"DELETE FROM messages WHERE id = ".$messageid
	  	;
	  	$db->query($sql, __FILE__, __LINE__);
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
	 * @global $db Globales Array mit allen wichtigen MySQL-Datenbankvariablen
	 */
	function doMessagesUnread($messageid, $userid)
	{
		global $db;
		
		if ($messageid > 0 && $messageid != '' && $userid > 0 && $userid != '') // ok man könnte auch noch auf $user->id checken
		{
	  		$sql =
	  			"UPDATE messages SET isread='0' WHERE isread='1' AND id=$messageid AND owner=$userid";
	  		$db->query($sql, __FILE__, __LINE__);
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
	 * @global $db Globales Array mit allen wichtigen MySQL-Datenbankvariablen
	 */
	function doMarkAllAsRead($userid)
	{
		global $db;
		
		if ($userid > 0 && $userid != '') // man könnte auch noch auf $user->id checken
		{
	  		$sql =
	  			"UPDATE messages SET isread='1' WHERE isread='0' AND owner=$userid";
	  			$db->query($sql, __FILE__, __LINE__);
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
			.'<tr><td>'.usersystem::getFormFieldUserlist('to_users[]', 15, $to_users, 4).'</td>'
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
	 * @global $db Globales Array mit allen wichtigen MySQL-Datenbankvariablen
	 * @global $user Globales Array mit den User-Variablen
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

	  $result = $db->query($sql, __FILE__, __LINE__);
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
		  		.'<td align="center" bgcolor="'.$color.'">'.usersystem::link_userpage($rs['from_user_id']).'</td>'
		  		.'<td align="center" bgcolor="'.$color.'" width="30%">';

			foreach (explode(',', $rs['to_users']) as $value) {
		  		$html .= usersystem::link_userpage($value).' ';
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
	  	$rs = $db->fetch($db->query($sql, __FILE__, __LINE__));
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
	 * @global $db Globales Array mit allen wichtigen MySQL-Datenbankvariablen
	 * @global $user Globales Array mit den User-Variablen
	 * @return integer
	 */
	static function getNumNewMessages()
	{
		global $db, $user;

		if ($user->typ != USER_NICHTEINGELOGGT) {
			$sql = "SELECT count(*) as num FROM messages WHERE owner = ".$user->id." AND isread = '0'";
			$result = $db->query($sql, __FILE__, __LINE__);
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
		$rs = $db->fetch($db->query($sql, __FILE__, __LINE__));

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
		  	.'<td align="right" rowspan="5">'.usersystem::link_userpage($rs['from_user_id'], TRUE).'</td>'
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
		  	$html .= usersystem::link_userpage($value).' ';
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
			$db->query($sql, __FILE__, __LINE__);
	  } else {
	  	$html = "Sorry du darfst diese Message nicht lesen";
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
	 * @global $db Globales Array mit allen wichtigen MySQL-Datenbankvariablen
	 * @global $user Globales Array mit den User-Variablen
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
		$rs = $db->fetch($db->query($sql, __FILE__, __LINE__));

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
	 * @global $db Globales Array mit allen wichtigen MySQL-Datenbankvariablen
	 * @global $user Globales Array mit den User-Variablen
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
		$rs = $db->fetch($db->query($sql, __FILE__, __LINE__));

		return $rs['id'];
	}

	
	/**
	 * Persönliche Nachricht senden
	 * 
	 * Speichert die gesendete Nachricht im Postfach des Empfängers und meinem Postausgang
	 * 
	 * @author [z]milamber
	 * @date 
	 * @version 2.0
	 *
	 * @param integer $from_user_id User-ID des Senders
	 * @param integer $owner User-Id des Nachrichten-Owners
	 * @param string $subject Titel der Nachricht
	 * @param string $text Nachrichten-Text
	 * @param string $to_users Alle Empfänger der Nachricht
	 * @param integer $isread Lesestatus der Nachricht (Default: Ungelesen)
	 * @global $db Globales Array mit allen wichtigen MySQL-Datenbankvariablen
	 */
	function sendMessage($from_user_id, $owner, $subject, $text, $to_users="", $isread=0)
	{
		global $db;

		if($to_users == '') $to_users = $owner;
		if($text == '') $text = '---';
		
		// Send Message to recipient(s)
	  	$sql =
	  		"INSERT INTO messages (from_user_id, owner, subject, text, date, isread, to_users) values (
	  		".$from_user_id."
	  		, ".$owner."
	  		, '".addslashes(stripslashes($subject))."'
	  		, '".addslashes(stripslashes($text))."'
	  		, now()
	  		, '".$isread."'
	  		, '".$to_users."'
	  		)"
	  	;
	  	$db->query($sql, __FILE__, __LINE__);
	  	
	  	// Save copy for my Sent-Folder
	  	$sql =
	  		"INSERT INTO messages (from_user_id, owner, subject, text, date, isread, to_users) values (
	  		".$from_user_id."
	  		, ".$from_user_id."
	  		, '".addslashes(stripslashes($subject))."'
	  		, '".addslashes(stripslashes($text))."'
	  		, now()
	  		, '1'
	  		, '".$to_users."'
	  		)"
	  	;
	  	$db->query($sql, __FILE__, __LINE__);
		
		// Sende E-Mail Notification an Users (einzeln, nur sofern erlaubt)
		//for ($i=0; $i<count($to_users); $i++) --> auf $owner gehen!
			//{
		try {
			if ($owner != $from_user_id) Messagesystem::sendEmailNotification($from_user_id, $owner, $subject, $text);
		} catch (Exception $e) {
			user_error($e->getMessage(), E_USER_ERROR);
		}
		//}
		
		/**
		 * Send Telegram Notification
		 */
		if ($owner != $from_user_id && usersystem::userHasTelegram($owner))
		{
			try {
				$message = sprintf('Neue <a href="%s/user/%d">Nachricht</a> von <b>%s</b> auf %s: %s', SITE_URL, $owner, usersystem::id2user($from_user_id, TRUE), SITE_HOSTNAME, text_width(remove_html($text), 140, '...'));
				Messagesystem::sendTelegramNotification($message, $owner);
			} catch (Exception $e) {
				user_error($e->getMessage(), E_USER_ERROR);
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
	 * @param integer $from_user_id User-ID des Senders
	 * @param integer $to_user_id User-Id des Empfängers
	 * @param string $titel Titel der ursprünglichen Nachricht
	 * @param string $text Ursprünglicher Text
	 * @global $db Globales Array mit allen wichtigen MySQL-Datenbankvariablen
	 */
	function sendEmailNotification($from_user_id, $to_user_id, $titel, $text)
	{
		global $db;
		
		// E-Mailnachricht bauen
		if ($to_user_id != 0 && $to_user_id <> '' && is_numeric($to_user_id))
		{
			// Nur, wenn User E-Mailbenachrichtigung aktiviert hat...!
			if (usersystem::id2useremail($to_user_id) != false)
			{
				$empfaengerMail = usersystem::id2useremail($to_user_id);
				$empfaengerName = usersystem::id2user($to_user_id, TRUE);
				$senderName = usersystem::id2user($from_user_id, TRUE);
				
				$subject = 	"Neue Nachricht auf Zorg.ch";
				
				$body =		"Du hast eine neue Nachricht in deinem Posteingang auf " . SITE_URL . "\r\n";
				$body .=	"\r\n";
				$body .=	"Titel:	$titel\n";
				$body .=	"Von:	$senderName\n";
				$body .=	"Auszug: ".text_width(remove_html($text), 140, '...')."\r\n";
				$body .=	"\r\n";
				$body .=	"------------\n";
				$body .=	"Zorg.ch";
				
				$header  = 	"MIME-Version: 1.0\n";
				$header .= 	"Content-type: text/html; charset=iso-8859-1\n";
				$header .= 	"From: Zorg.ch <".ZORG_EMAIL.">\n";
			    $header .= 	"Reply-To: ".ZORG_EMAIL."\n";
			    $header .= 	"X-Mailer: PHP/".phpversion();
				
				// Vesende E-Mail an User
				mail("$empfaengerName <$empfaengerMail>", utf8_encode($subject), utf8_encode($body), $header);
			}
		}
			
	}

	/**
	 * Get List of Users Telegram Messenger Chat-ID where available
	 *
	 * @author	IneX
	 * @date	21.01.2017
	 * @version	1.0
	 * @since	2.0
	 *
	 * @param 	integer $userid (Optional) User-ID dessen Chat-ID ermittelt werden soll
	 * @global	array	$db 	Array mit allen MySQL-Datenbankvariablen
	 * @return	array			Returns list with Telegram Chat-IDs from User-Table
	 */
	static public function getAllUserTelegramChatIds($userid='')
	{
		global $db;
		
		try {
			$sql = "SELECT
						telegram_chat_id
					FROM
						user
					WHERE
						telegram_chat_id IS NOT NULL
					" . (isset($userid) && $userid > 0 && is_numeric($userid) ? 'AND id = ' . $userid : '' );
			$result = $db->query($sql, __FILE__, __LINE__);

			while($rs = $db->fetch($result)) {
				$telegramChatIds[] = $rs['telegram_chat_id'];
			}

			return $telegramChatIds;

		} catch (Exception $e) {
			user_error($e->getMessage(), E_USER_WARNING);
		}
	}

	/**
	 * Notfication via Telegram Messenger
	 * Schickt eine Notification an die Telegram Chats von Usern
	 *
	 * @author	IneX
	 * @date	21.01.2017
	 * @version	1.0
	 * @since	2.0
	 *
	 * @TODO integrate with TelegramBot\TelegramBotManager\BotManager
	 *
	 * @see Messagesystem::getAllUserTelegramChatIds()
	 * @param	string	$notificationText	Content welcher an die Telegram Chats geschickt wird
	 * @param	integer	$to_user_id			(Optional) User-Id des Empfängers
	 * @param	string	$sendPhoto			(Optional) Picture with URL to an Image & Caption, to send along the Message
	 * @global	array	$db					Array mit allen MySQL-Datenbankvariablen
	 * @global	array	$botconfigs			Array mit allen Telegram Bot-Configs
	 */
	static public function sendTelegramNotification($notificationText, $to_user_id='', $sendPhoto='')
	{
		global $db, $botconfigs;
		
		// Make sure a proper Notification Text is passed & the Telegram Bot-Configs exist
		if ((!empty($notificationText) && strlen($notificationText) > 0) && (isset($botconfigs) && is_array($botconfigs)))
		{
			// Encode Notification Text
				// Fix missing Server address in Links
				if (strpos($notificationText, 'href="/') > 0) $notificationText = str_replace('href="/', 'href="' . SITE_URL . '/', $notificationText);
				
				// Strip away all HTML-tags & line breaks; except from the whitelist <b>, <i>, <a>, <code> & <pre>
				$notificationText = str_replace(array("\r", "\n", "&nbsp;"), '', $notificationText);
				$notificationText = strip_tags($notificationText, '<b><i><a><code><pre>');
				//$notificationText = str_replace('IneX', '<a href="tg://user?id=28563309">IneX</a>', $notificationText);
				$notificationText = html_entity_decode($notificationText);

			// Get the Telegram Chat-IDs
			$telegramChatIds = Messagesystem::getAllUserTelegramChatIds( (isset($to_user_id) && $to_user_id > 0 && is_numeric($to_user_id) ? $to_user_id : '' ) );

			// When we got at least 1 Chat-ID...
			if(is_array($telegramChatIds))
			{
				// ...send the Telegram Message to each of them
				foreach ($telegramChatIds as $chatId)
				{
					$data = [
					    'chat_id' => $chatId,
					    'parse_mode' => 'html',
					    'text' => $notificationText,
					];
					file_get_contents( TELEGRAM_API_URI . '/sendMessage?' . http_build_query($data) );
					
					// Check if an Image URL has been passed, too
					
					if (isset($picUrl) && strlen($picUrl) > 0 && strpos($picUrl, 'http'))
					{
						if (urlExists($picUrl)) {
							$data = [
							    'chat_id' => $chatId,
							    'caption' => 'html',
							    'text' => $notificationText,
							];
						}
					}
				}
			}
		} else {
			user_error('Notification Text is empty or otherwise invalid', E_USER_NOTICE);
		}
	}


	/**
	 * Send a Photo via Telegram Messenger
	 * Schickt eine Photo-Notification an die Telegram Chats von Usern
	 *
	 * @author	IneX
	 * @date	21.01.2017
	 * @version	1.0
	 * @since	2.0
	 *
	 * @TODO integrate with TelegramBot\TelegramBotManager\BotManager
	 *
	 * @see Messagesystem::getAllUserTelegramChatIds()
	 * @param	array	$imageData			Image data: URL to an Image & Caption text
	 * @param	integer	$to_user_id			(Optional) User-Id des Empfängers
	 * @global	array	$db					Array mit allen MySQL-Datenbankvariablen
	 * @global	array	$botconfigs			Array mit allen Telegram Bot-Configs
	 */
	static public function sendTelegramPhoto($imageData, $to_user_id='')
	{
		global $db, $botconfigs;
		
		// Make sure a proper Image Data Array is passed & the Telegram Bot-Configs exist
		if (is_array($imageData) && (isset($imageData['url']) && strlen($imageData['url']) > 0) && (isset($botconfigs) && is_array($botconfigs)))
		{
			$image_url = $imageData['url'];
			$image_caption = (isset($imageData['caption']) && strlen($imageData['caption']) > 0 ? $imageData['caption'] : '');
			
			// Encode Image Caption Text
				// Fix missing Server address in Links
				if (strpos($image_url, 'href="/') > 0) $image_url = str_replace('href="/', 'href="' . SITE_URL . '/', $image_url);
			
				// Strip away all HTML-tags & line breaks; except from the whitelist <b>, <i>, <a>, <code> & <pre>
				$image_caption = str_replace(array("\r", "\n", "&nbsp;"), '', $image_caption);
				$image_caption = strip_tags($image_caption, '<b><i><a><code><pre>');
				$image_caption = html_entity_decode($image_caption);

			// Test if the Image URL is valid:
			if (urlExists($image_url))
			{
				// Get the Telegram Chat-IDs
				$telegramChatIds = Messagesystem::getAllUserTelegramChatIds( (isset($to_user_id) && $to_user_id > 0 && is_numeric($to_user_id) ? $to_user_id : '' ) );

				// When we got at least 1 Chat-ID...
				if(is_array($telegramChatIds))
				{
					// ...send the Telegram Message to each of them
					foreach ($telegramChatIds as $chatId)
					{
						$data = [
						    'chat_id' => $chatId,
						    'caption' => $image_caption,
						    'photo' => $image_url
						];
						file_get_contents( TELEGRAM_API_URI . '/sendPhoto?' . http_build_query($data) );
						
						/* Source: https://stackoverflow.com/a/4247082/5750030
						define('MULTIPART_BOUNDARY', '--------------------------'.microtime(true));
						$header = 'Content-Type: multipart/form-data; boundary='.MULTIPART_BOUNDARY;
						define('FORM_FIELD', 'uploaded_file'); 
						$file_contents = file_get_contents($image_url);    
						
						$content =  "--".MULTIPART_BOUNDARY."\r\n".
						            "Content-Disposition: form-data; name=\"".FORM_FIELD."\"; filename=\"".basename($image_url)."\"\r\n".
						            "Content-Type: image/jpeg\r\n\r\n".
						            $file_contents."\r\n";
						
						// add some POST fields to the request too: $_POST['foo'] = 'bar'
						$content .= "--".MULTIPART_BOUNDARY."\r\n".
						            "Content-Disposition: form-data; name=\"".$image_caption."\"\r\n\r\n".
						            $image_caption."\r\n";
						
						// signal end of request (note the trailing "--")
						$content .= "--".MULTIPART_BOUNDARY."--\r\n";
						$context = stream_context_create(array(
						    'http' => array(
						          'method' => 'POST',
						          'header' => $header,
						          'content' => $content,
						    )
						));
						file_get_contents('http://url/to/upload/handler', false, $context);
						*/
					}
				}
			} else {
				user_error('Image URL is not reachable or invalid', E_USER_NOTICE);
			}
		} else {
			user_error('Image Data is empty or otherwise invalid', E_USER_NOTICE);
		}
	}

}
