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
	 * Message-Actions ausführen
	 * 
	 * Controller für diverse Message Actions
	 * 
	 * @author [z]milamber
	 * @date 
	 * @version 2.0
	 *
	 * @see BARBARA_HARRIS, Messagesystem::sendMessage()
	 * @param integer $messageid ID der ausgewählten Nachricht(en)
	 * @param integer $deleter_userid User-ID welcher die Nachricht(en) löscht
	 * @global object $db Globales Class-Object mit allen MySQL-Methoden
	 * @global object $user Globales Class-Object mit den User-Methoden & Variablen
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
			$headerLocation = ( !empty($_POST['url']) ? base64_decode($_POST['url']) . '&sent=successful' : sprintf('%s/profil.php?user_id=%d&box=outbox&sent=successful&%s=%s', SITE_URL, $user->id, session_name(), session_id()) );
			if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> header() Location: %s', __METHOD__, __LINE__, $headerLocation));
			header('Location: ' . $headerLocation);

			//exit;
		}


		if($_POST['do'] == 'delete_messages') {

			/** Delete all passed message_id's */
			for ($i=0; $i < count($_POST['message_id']); $i++) {
				if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> Deleting Message ID: %d', __METHOD__, __LINE__, $_POST['message_id']));
				Messagesystem::deleteMessage($_POST['message_id'][$i], $user->id);
			}

			/** If only singe passed message_id, redirect User to previous Message */
			if(count($_POST['message_id']) == 1) {
				$msgid = Messagesystem::getPrevMessageid($_POST['message_id'][0]);
				if($msgid > 0) {
					if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> Redirecting User to Message ID: %d', __METHOD__, __LINE__, $msgid));
					header("Location: messagesystem.php?message_id=".$msgid."&".session_name()."=".session_id()."&delete=done");
					//exit;
				} else {
					if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> Redirecting User to Userprofile: /user/%s', __METHOD__, __LINE__, $user->id));
					header("Location: /user/".$user->id."?".session_name()."=".session_id()."&delete=done");
					//exit;
				}
			}

			if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> Redirecting User back to Page: %s', __METHOD__, __LINE__, base64_decode($_POST['url'])));
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
	 * Nachricht als gelesn markieren
	 * 
	 * @author IneX
	 * @date 24.06.2018
	 * @version 1.0 initial method release
	 * @since 1.0 
	 *
	 * @param integer $messageid ID der ausgewählten Nachricht
	 * @global object $db Globales Class-Object mit allen MySQL-Methoden
	 * @return boolean Returns true or false depending on the completion
	 */
	function doMarkMessageAsRead($messageid)
	{
		global $db;

		if (!empty($messageid))
		{
			try {
				$sql = "UPDATE messages set isread='1' WHERE id=$messageid;";
				if ( $db->query($sql, __FILE__, __LINE__, __METHOD__) )
				{
					return true;
				} else {
					return false;
				}
			} catch (Exception $e) {
				error_log($e->getMessage());
				return false;
			}
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
	 * @author IneX
	 * @date 23.06.2018
	 * @version 2.0
	 * @since 1.0 initial method release
	 * @since 2.0 frontend is now a template - as it should be
	 * @global $user Globales Class-Object mit den User-Methoden & Variablen
	 * @param integer $id ID der ausgewählten Nachricht
	 
	 * @global object $smarty Globales Class-Object mit allen Smarty-Methoden
	 * @return string HTML des Message-Delete Form
	 */
	function getFormDelete($id)
	{
		global $user, $smarty;

		$smarty->assign('form_action', '/user/'.$user->id);
		$smarty->assign('form_url', base64_encode('/user/'.$user->id.'&delete=done'));
		$smarty->assign('message_id', $id);

		return $smarty->fetch('file:layout/partials/messages/messages_delete.tpl');
	}


	/**
	 * Nachrichten-Formular
	 * 
	 * Baut das HTML-Formular um eine neue Nachrichten zu versenden
	 * 
	 * @author [z]milamber
	 * @author IneX
	 * @date 23.06.2018
	 * @version 2.0
	 * @since 1.0 initial method release
	 * @since 2.0 frontend is now a template - as it should be
	 *
	 * @param string $to_users Alle Empfänger der Nachricht
	 * @param string $subject Titel der Nachricht
	 * @param string $text Nachrichten-Text
	 * @param integer $delete_message_id Löschstatus der Nachricht (Default: ungelöscht)
	 * @global $user Globales Class-Object mit den User-Methoden & Variablen
	 * @global object $smarty Globales Class-Object mit allen Smarty-Methoden
	 * @return string HTML des Send-Message Form
	 */
	function getFormSend($to_users, $subject, $text, $delete_message_id=0)
	{
		global $user, $smarty;

		$smarty->assign('form_action', base64_decode(getURL()));
		$smarty->assign('form_url', getURL());
		$smarty->assign('subject', $subject);
		$smarty->assign('text', $text);
		$smarty->assign('userlist', $user->getFormFieldUserlist('to_users[]', 15, $to_users, 4));
		$smarty->assign('backlink_url', '/user/'.$user->id.'?box=inbox');
		$smarty->assign('delete_message_id', $delete_message_id);

		return $smarty->fetch('file:layout/partials/messages/messages_send.tpl');
	}


	/**
	 * Message-Inbox/Outbox
	 * 
	 * Baut das HTML um die Nachrichten-Verwaltung anzuzeigen
	 * 
	 * @author [z]milamber, IneX
	 * @date 24.06.2018
	 * @version 2.0
	 * @since 1.0 initial method release
	 * @since 2.0 frontend is now a template - as it should be
	 *
	 * @param string $box Darstellung des Ein- oder Ausgangs (inbox|outbox)
	 * @param integer $pagesize Anzahl Nachrichten pro Seite (Default: 11, wegen Farbwechsel)
	 * @param integer $page Aktuelle Seite mit Nachrichten (Default: 1)
	 * @param integer $orderby Sortierung der Nachrichten (Default: date)
	 * @param integer $sortby Sortierreihenfolge der Nachrichten (Default: DESC)
	 * @global $db Globales Class-Object mit allen MySQL-Methoden
	 * @global $user Globales Class-Object mit den User-Methoden & Variablen
	 * @global object $smarty Globales Class-Object mit allen Smarty-Methoden
	 * @return string
	 */
	function getInboxHTML($box='inbox', $pagesize=11, $page=1, $orderby='date', $sortby='DESC')
	{
		global $db, $user, $smarty;

		/** Check and set integers which cannot be 0 */
		if (empty($box) || $box === '') $box = 'inbox';
		if (empty($pagesize) || $pagesize === 0) $pagesize = 11;
		if (empty($page) || $page === 0) $page = 1;

		/** Validate $orderby & $sortby */
		if (empty($orderby) || !in_array( $orderby, ['date','from_user_id','subject'], true)) $orderby = 'date';
		if (empty($sortby) || !in_array( $sortby, ['asc','desc'], true)) $sortby = 'DESC';

		$smarty->assign('form_action', base64_decode(getURL()));
		$smarty->assign('form_url', getURL());
		//$smarty->assign('newmsg_url', base64_decode(getURL()).'?newmsg');
		$smarty->assign('box', $box);
		$smarty->assign('current_page', $page);
		$smarty->assign('sort_order', $sortby);

		/** Query messages - Neuste (!isread) immer zuoberst */
		try {
			$sql = "
				SELECT *, UNIX_TIMESTAMP(date) as date
				FROM messages where owner = ".$user->id ."
				AND from_user_id ".($box == "inbox" ? "<>" : "=").$user->id ."
				ORDER BY isread ASC, ".$orderby." ".$sortby."
				LIMIT ".($page-1) * $pagesize.",".$pagesize
			;
			$result = $db->query($sql, __FILE__, __LINE__, __METHOD__);
			while($rs = $db->fetch($result)) {
				$messages[] = $rs;
		 	}
			$smarty->assign('messages', $messages);
			//if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> $messages: %s', __METHOD__, __LINE__, print_r($messages,true)));
		} catch (Exception $e) {
			error_log($e->getMessage());
			return false;
		}

		/** Calculate number of pages */
		$numMessages = Messagesystem::getNumUserMessages($user->id);
		if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> $numMessages: %s', __METHOD__, __LINE__, print_r($numMessages,true)));
		$numPages = (!empty($numMessages) ? ceil($numMessages[$box] / $pagesize) : $page);
		if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> $numPages: %s', __METHOD__, __LINE__, $numPages));
		$smarty->assign('pages', $numPages);

		return $smarty->fetch('file:layout/partials/messages/messages_list.tpl');
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
	 * Anzahl aller User Nachrichten
	 * 
	 * Berechnet die Anzahl aller Nachrichten eines Users.
	 * Wird benötigt für das Paginating in Messagesystem::getInboxHTML()
	 * 
	 * @author IneX
	 * @date 24.06.2018
	 * @version 1.0
	 * @since 1.0 initial method release
	 *
	 * @see Messagesystem::getInboxHTML()
	 * @param integer $userid User-ID welcher alle Nachricht(en) als gelesen markieren möchte
	 * @global $db Globales Class-Object mit allen MySQL-Methoden
	 * @return array|boolean Returns an array with the number of messages for inbox & outbox - or false, if an error occurred
	 */
	static function getNumUserMessages($userid)
	{
		global $db;

		try {
			/** A MySQL Sub-Query retrieving user's total messages for the inbox & outbox at the same time */
			$sql = "SELECT
						(SELECT count(id) as num FROM messages where owner = 117 AND from_user_id <> 117) num_inbox,
						(SELECT count(id) as num FROM messages where owner = 117 AND from_user_id = 117) num_outbox
					FROM messages
					LIMIT 1";
			$result = $db->query($sql, __FILE__, __LINE__, __METHOD__);
			$rs = $db->fetch($result);
	
			if (!empty($rs)) {
				return [ 'inbox' => $rs['num_inbox'], 'outbox' => $rs['num_outbox'] ];
			} else {
				return false;
			}
		} catch (Exception $e) {
			error_log($e->getMessage());
			return false;
		}
	}


	/**
	 * Nachricht anzeigen
	 * 
	 * Zeigt eine Message an
	 *
	 * @author [z]milamber, IneX
	 * @date 24.06.2018
	 * @version 2.0
	 * @since 1.0 initial method release
	 * @since 2.0 frontend is now a template - as it should be
	 * 
	 * @see Messagesystem::getUserMessages()
	 * @see Messagesystem::doMarkMessageAsRead()
	 * @param int $id ID der Nachricht
	 * @global object $db Globales Class-Object mit allen MySQL-Methoden
	 * @global $user Globales Class-Object mit den User-Methoden & Variablen
	 * @return string
	 */
	function getMessage($id)
	{
		global $user, $smarty;

		$messageDetails = Messagesystem::getUserMessage($id);

		if (!empty($messageDetails) && $messageDetails['owner'] === $user->id)
		{
			$smarty->assign('prevmessage_url', (Messagesystem::getNextMessageid($messageDetails['id']) > 0 ? '<a href="/messagesystem.php?message_id='.Messagesystem::getNextMessageid($messageDetails['id']).'"><-- </a> | ' : ''));
			$smarty->assign('nextmessage_url', (Messagesystem::getPrevMessageid($messageDetails['id']) > 0 ? '<a href="/messagesystem.php?message_id='.Messagesystem::getPrevMessageid($messageDetails['id']).'"> --></a>' : ''));
			$smarty->assign('deletemessage_html', Messagesystem::getFormDelete($id));
			$smarty->assign('messagedetails', $messageDetails);
			$smarty->assign('recipientslist', explode(',', $messageDetails['to_users']));

			Messagesystem::doMarkMessageAsRead($id);

		} else {
			$smarty->assign('error', t('invalid-permissions', 'messagesystem'));
		}

		return $smarty->fetch('file:layout/partials/messages/messages_view.tpl');
	}


	/**
	 * Message holen
	 * 
	 * @author IneX
	 * @date 24.06.2018
	 * @version 1.0
	 * @since 1.0 initial method release
	 *
	 * @param integer $messageid ID der Nachricht die abgefragt werden soll
	 * @global object $db Globales Class-Object mit allen MySQL-Methoden
	 * @return array|boolean Returns an Array containing the query results - or false if the query failed
	 */
	function getUserMessage($messageid)
	{
		global $db;

		if (!empty($messageid))
		{
			try {
				$sql = "SELECT
							 *, UNIX_TIMESTAMP(date) as date
						FROM messages
						WHERE id = ".$messageid."
						LIMIT 0,1";
				$rs = $db->fetch($db->query($sql, __FILE__, __LINE__, __METHOD__));
				return $rs;
			} catch (Exception $e) {
				error_log($e->getMessage());
				return false;
			}
		}
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

		try {
			$sql =
				"SELECT *, UNIX_TIMESTAMP(date) as date"
				." FROM messages"
				." WHERE owner = ".$user->id
				." AND from_user_id !=".$user->id
				." AND id > ".$id
				." ORDER BY id ASC"
				." LIMIT 0,1"
			;
			$rs = $db->fetch($db->query($sql, __FILE__, __LINE__, __METHOD__));

			return $rs['id'];
		} catch (Exception $e) {
			error_log($e->getMessage());
			return false;
		}
	}


	/**
	 * Vorherige Nachricht anzeigen
	 * 
	 * Holt die ID der jeweils jüngeren Nachricht gegenüber der aktuell geöffneten
	 * 
	 * @author [z]milamber, IneX
	 * @date 24.06.2018
	 * @version 2.0
	 * @since 1.0 initial method release
	 * @since 2.0 prev was always getting newewst message - fixed it
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
			." ORDER BY id DESC"
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
	 * @version 3.1
	 * @since 1.0
	 * @since 2.0 verschickt eine Notification über die neue Nachricht per E-Mail
	 * @since 3.0 verschickt eine Notification per Telegram Messenger
	 * @since 3.1 changed to new Telegram Send-Method
	 *
	 * @param integer	$from_user_id User-ID des Senders
	 * @param integer	$owner User-ID des Nachrichten-Owners
	 * @param string	$subject Titel der Nachricht
	 * @param string	$text (Optional) Nachrichten-Text
	 * @param string	$to_users (Optional) Liste aller Empfänger der Nachricht
	 * @param string	$isread (Optional) Lesestatus der Nachricht - ENUM('0','1'), Default: Ungelesen ('0')
	 * @global object	$db Globales Class-Object mit allen MySQL-Methoden
	 * @global object	$user Globales Class-Object mit den User-Methoden & Variablen
	 * @global object	$telegram Globales Class-Object mit den Telegram-Methoden
	 * @return boolean	Returns true or false, depening on the susccessful execution
	 */
	function sendMessage($from_user_id, $owner, $subject, $text='', $to_users='', $isread='0')
	{
		global $db, $user, $telegram;

		/** Validate function parameters */
		if(!isset($owner) || empty($owner) || $owner <= 0) {
			error_log(sprintf('<%s:%d> %s $owner ERROR: %s', __FILE__, __LINE__, __FUNCTION__, $owner));
			return false;
		}
		if(!isset($to_users) || empty($to_users)) $to_users = $owner;
		if(empty($text)) $text = t('message-empty-text', 'messagesystem');

		/**
		 * Send Message to recipient(s)
		 */
		try {
			if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> Sending SINGLE Zorg Message "%s" to $owner %d', $subject, $owner));
			$sql = sprintf('INSERT INTO messages (from_user_id, owner, subject, text, date, isread, to_users)
							VALUES (%d, %d, "%s", "%s", NOW(), "%s", "%s")',
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
				return false;
			}

			/** Send Telegram Notification */
			try {
				$message = t('telegram-newmessage-notification', 'messagesystem', [ SITE_URL, $owner, $user->id2user($from_user_id, TRUE), SITE_HOSTNAME, text_width(remove_html($text, '<br>'), 140, '...') ] );
				$telegram->send->message($owner, $message, ['disable_web_page_preview' => 'true']);
			} catch (Exception $e) {
				error_log($e->getMessage());
				return false;
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
	function sendEmailNotification($from_user_id, $to_user_id, $titel, $text)
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

}
