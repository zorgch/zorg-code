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
 * @package		zorg\Messagesystem
 */
/**
 * File Includes
 * @include config.inc.php		Required global configs
 * @include util.inc.php		Required Helper Functions
 * @include usersystem.inc.php	Required User Class and Functions
 */
require_once __DIR__.'/config.inc.php';
require_once INCLUDES_DIR.'usersystem.inc.php';

/**
 * Messagesystem Class
 *
 * In dieser Klasse befinden sich alle Funktionen zum Senden & Verwalten der Nachrichten
 *
 * @package		zorg
 * @subpackage	Messagesystem
 *
 * @version		4.0
 * @since		1.0 `[z]milamber` class added
 * @since		2.0 `17.03.2018` IneX` added e-mail notification
 * @since		3.0 `25.05.2018` ` `IneX` implemented with telegrambot.inc.php
 * @since		4.0 `21.10.2018` `IneX` implemented with notifications.inc.php
 */
class Messagesystem
{
	/**
	 * Message-Actions ausführen
	 *
	 * Controller für diverse Message Actions
	 *
	 * @version 2.3
	 * @since 1.0 `[z]milamber` method added
	 * @since 2.0 `IneX` code optimizations
	 * @since 2.1 `04.04.2021` `IneX` fixed wrong check if own message, and PHP Deprecated: Non-static method Messagesystem::sendMessage()
	 * @since 2.2 `04.12.2024` `IneX` fixed passing NULL to htmlspecialchars_decode() stringg parameter is deprecated
	 * @since 2.3 `15.11.2025` `IneX` Code hardenings
	 *
	 * @uses BARBARA_HARRIS
	 * @uses Messagesystem::sendMessage()
	 * @param integer $messageid ID der ausgewählten Nachricht(en)
	 * @param integer $deleter_userid User-ID welcher die Nachricht(en) löscht
	 * @global object $user Globales Class-Object mit den User-Methoden & Variablen
	 */
	static function execActions($doAction=null)
	{
		global $user;

		/** Validate parameters */
		$doAction = filter_var($doAction, FILTER_SANITIZE_SPECIAL_CHARS) ?? null;
		zorgDebugger::log()->debug('$doAction: %s', [$doAction]);
		if (isset($_POST['message_id']) && is_array($_POST['message_id'])) { // $_POST['message_id'] (multiple)
			$i=0;
			for ($i;$i<count($_POST['message_id']);$i++) {
				$messageId[] = intval(filter_var($_POST['message_id'][$i], FILTER_VALIDATE_INT)) ?? null;
			}
		} else {
			$messageId = filter_input(INPUT_POST, 'message_id', FILTER_VALIDATE_INT) ?? null; // $_POST['message_id'] (single)
		}
		zorgDebugger::log()->debug('$messageId: %s', [(is_array($messageId)? print_r($messageId,true) : $messageId)]);
		$deleteMessageId = filter_input(INPUT_POST, 'delete_message_id', FILTER_VALIDATE_INT) ?? null; // $_POST['delete_message_id']
		$msgSubject = htmlspecialchars_decode(filter_input(INPUT_POST, 'subject', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?: '', ENT_COMPAT | ENT_SUBSTITUTE);
		$msgText = htmlspecialchars_decode(filter_input(INPUT_POST, 'text', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?: '', ENT_COMPAT | ENT_SUBSTITUTE);
		$headerLocation = base64url_decode(filter_input(INPUT_POST, 'url', FILTER_SANITIZE_ENCODED)) ?? sprintf('%s/user/%d?box=inbox', SITE_URL, $user->id);
		zorgDebugger::log()->debug('header() Location: %s', [$headerLocation]);

		if($doAction === 'sendmessage')
		{
			$to_users = [];
			if (isset($_POST['to_users']) && is_array($_POST['to_users'])) {
				$i=0;
				for ($i;$i<count($_POST['to_users']);$i++) {
					$to_users[] = intval(filter_var($_POST['to_users'][$i], FILTER_VALIDATE_INT)) ?? null;
				}
			}
			if (empty($to_users)) $to_users[] = $user->id; // Fallback: der Sender kriegt die Message...
			$to_user_ids_string = implode(',', $to_users);

			foreach ($to_users as $to_user_id)
			{
				/** Wenn ich mir selber was schicke, dann nimm die Bärbel als Absender */
				if ($to_user_id === $user->id)
				{
					$sent = self::sendMessage(BARBARA_HARRIS, $to_user_id, $msgSubject, $msgText, $to_user_ids_string);
				}

				/** Nachricht an andere Leute */
				else {
					$sent = self::sendMessage($user->id, $to_user_id, $msgSubject, $msgText, $to_user_ids_string);
				}
			}

			/** Eigene Message für den 'Sent'-Ordner & direkt als gelesen markieren */
			self::sendMessage($user->id, $user->id, $msgSubject, $msgText, $to_user_ids_string, '1');

			/** If the option "Delete message after sending" was checked... */
			if ($deleteMessageId > 0) {
				Messagesystem::deleteMessage($deleteMessageId, $user->id);
			}

			if ($sent) $headerLocation = changeUrl($headerLocation, 'sent=successful');
			header('Location: ' . $headerLocation);
			exit;
		}

		if($doAction === 'delete_messages')
		{
			/** If only singe passed message_id, redirect User to previous Message */
			if(is_numeric($messageId) && $messageId > 0)
			{
				zorgDebugger::log()->debug('Deleting single Message ID: %d', [$messageId]);
				self::deleteMessage($messageId, $user->id);
			}
			/** Delete multiple passed message_id's */
			elseif (is_array($messageId) && count($messageId) > 0)
			{
				foreach ($messageId as $delmsgid) {
					zorgDebugger::log()->debug('Deleting Message ID of multiples: %d', [$delmsgid]);
					self::deleteMessage($delmsgid, $user->id);
				}
			}

			$msgid = self::getPrevMessageid($messageId);
			if($msgid > 0) {
				zorgDebugger::log()->debug('Redirecting User to Message ID: %d', [$msgid]);
				header("Location: /messagesystem.php?message_id=".$msgid."&delete=done");
				exit;
			} else {
				zorgDebugger::log()->debug('Redirecting User to Userprofile: /user/%s', [$user->id]);
				header("Location: /user/".$user->id."?&delete=done");
				exit;
			}

			if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> Redirecting User back to Page: %s', __METHOD__, __LINE__, $headerLocation));
			header("Location: ".$headerLocation);
			exit;
		}

		if($doAction === 'messages_as_unread')
		{
			/** Change Message Status to UNREAD */
			if(is_numeric($messageId) && $messageId > 0)
			{
				self::doMessagesUnread($messageId, $user->id);
				$msgid = self::getPrevMessageid($messageId);
				$headerLocation = '/messagesystem.php?message_id='.$msgid;
			}
			elseif (is_array($messageId) && count($messageId) > 0)
			{
				foreach ($messageId as $unreadmsgid) {
					self::doMessagesUnread($unreadmsgid, $user->id);
				}
				$headerLocation = '/user/'.$user->id;
			}
			header("Location: ".$headerLocation);
			exit;
		}

		if($doAction === 'mark_all_as_read')
		{
			/** Mark all Messages as READ */
			self::doMarkAllAsRead($user->id);

			if(is_numeric($messageId) && $messageId > 0)
			{
				$msgid = self::getPrevMessageid($messageId);
				$headerLocation = '/messagesystem.php?message_id='.$msgid;
			}
			elseif (is_array($messageId) && count($messageId) > 0)
			{
				$headerLocation = '/user/'.$user->id;
			}

			header("Location: ".$headerLocation);
			exit;
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
	 * @global object $db Globales Class-Object mit allen MySQL-Methoden
	 */
	static function deleteMessage($messageid, $deleter_userid)
	{
		global $db;

		$sql = "SELECT id, owner FROM messages where id=?";
		$rs = $db->fetch($db->query($sql, __FILE__, __LINE__, __METHOD__, [$messageid]));

		if($rs['owner'] == $deleter_userid) {
		$sql = "DELETE FROM messages WHERE id=?";
		$db->query($sql, __FILE__, __LINE__, __METHOD__, [$messageid]);
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
	 * @global object $db Globales Class-Object mit allen MySQL-Methoden
	 */
	function doMessagesUnread($messageid, $userid)
	{
		global $db;

		if ($messageid > 0 && $messageid != '' && $userid > 0 && $userid != '') // ok man könnte auch noch auf $user->id checken
		{
			$sql = "UPDATE messages SET isread='0' WHERE isread='1' AND id=? AND owner=?";
			$db->query($sql, __FILE__, __LINE__, __METHOD__, [$messageid, $userid]);
		}
	}


	/**
	 * Nachricht als gelesn markieren
	 *
	 * @author IneX
	 * @date 24.06.2018
	 * @version 1.0
	 * @since 1.0 initial method release
	 *
	 * @param integer $messageid ID der ausgewählten Nachricht
	 * @global object $db Globales Class-Object mit allen MySQL-Methoden
	 * @return boolean Returns true or false depending on the completion
	 */
	static function doMarkMessageAsRead($messageid)
	{
		global $db;

		if (!empty($messageid))
		{
			$sql = "UPDATE messages set isread='1' WHERE id=?";
			if (false !== $db->query($sql, __FILE__, __LINE__, __METHOD__, [$messageid]))
			{
				return true;
			} else {
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
	 * @global object $db Globales Class-Object mit allen MySQL-Methoden
	 */
	static function doMarkAllAsRead($userid)
	{
		global $db;

		if (!empty($userid) && is_numeric($userid) && $userid > 0) // man könnte auch noch auf $user->id checken
		{
			$sql = 'UPDATE messages SET isread="1" WHERE isread="0" AND owner=?';
			$db->query($sql, __FILE__, __LINE__, __METHOD__, [$userid]);
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
	 * @global object $user Globales Class-Object mit den User-Methoden & Variablen
	 * @param integer $id ID der ausgewählten Nachricht
	 * @global object $smarty Globales Class-Object mit allen Smarty-Methoden
	 * @return string HTML des Message-Delete Form
	 */
	static function getFormDelete($id)
	{
		global $user, $smarty;

		$smarty->assign('form_action', '/user/'.$user->id);
		$smarty->assign('form_url', base64url_encode('/user/'.$user->id.'&delete=done'));
		$smarty->assign('message_id', $id);

		return $smarty->fetch('file:layout/partials/messages/messages_delete.tpl');
	}


	/**
	 * Nachrichten-Formular
	 *
	 * Baut das HTML-Formular um eine neue Nachrichten zu versenden
	 *
	 * @version 2.0
	 * @since 1.0 `[z]milamber`initial method release
	 * @since 2.0 `23.06.2018` `IneX` frontend is now a template - as it should be
	 *
	 * @param string $to_users Alle Empfänger der Nachricht
	 * @param string $subject Titel der Nachricht
	 * @param string $text Nachrichten-Text
	 * @param integer $delete_message_id Löschstatus der Nachricht (Default: ungelöscht)
	 * @global object $user Globales Class-Object mit den User-Methoden & Variablen
	 * @global object $smarty Globales Class-Object mit allen Smarty-Methoden
	 * @return string HTML des Send-Message Form
	 */
	static function getFormSend($to_users, $subject, $text, $delete_message_id=0)
	{
		global $user, $smarty;

		$smarty->assign('form_action', base64url_decode(getURL()));
		$smarty->assign('form_url', base64url_encode('/profil.php?user_id='.strval($user->id).'&box=outbox'));
		$smarty->assign('subject', $subject);
		$smarty->assign('text', $text);
		$smarty->assign('userlist', $user->getFormFieldUserlist('to_users[]', 15, $to_users, 4));
		$smarty->assign('backlink_url', '/profil.php?user_id='.strval($user->id).'&box=inbox');
		$smarty->assign('delete_message_id', $delete_message_id);

		return $smarty->fetch('file:layout/partials/messages/messages_send.tpl');
	}


	/**
	 * Message-Inbox/Outbox
	 *
	 * Baut das HTML um die Nachrichten-Verwaltung anzuzeigen
	 *
	 * @author [z]milamber
	 * @author IneX
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
	 * @global object $db Globales Class-Object mit allen MySQL-Methoden
	 * @global object $user Globales Class-Object mit den User-Methoden & Variablen
	 * @global object $smarty Globales Class-Object mit allen Smarty-Methoden
	 * @return string
	 */
	static function getInboxHTML($box='inbox', $pagesize=11, $page=1, $orderby='date', $sortby='DESC')
	{
		global $db, $user, $smarty;

		/** Check and set integers which cannot be 0 */
		if (empty($box) || $box === '') $box = 'inbox';
		if (empty($pagesize) || $pagesize === 0) $pagesize = 11;
		if (empty($page) || $page === 0) $page = 1;

		/** Validate $orderby & $sortby */
		if (empty($orderby) || !in_array( $orderby, ['date','from_user_id','subject'], true)) $orderby = 'date';
		if (empty($sortby) || !in_array( $sortby, ['asc','desc'], true)) $sortby = 'DESC';

		$smarty->assign('form_action', base64url_decode(getURL()));
		$smarty->assign('form_url', getURL());
		//$smarty->assign('newmsg_url', base64url_decode(getURL()).'?newmsg');
		$smarty->assign('box', $box);
		$smarty->assign('current_page', $page);
		$smarty->assign('sort_order', $sortby);

		/** Query messages - Neuste (!isread) immer zuoberst */
		$messages = [];
		$sql = "SELECT *, UNIX_TIMESTAMP(date) as date
				FROM messages WHERE owner=?
				AND from_user_id ".($box == "inbox" ? "<>?" : "=?")."
				ORDER BY isread ASC, ".$orderby." ".$sortby."
				LIMIT ?,?";
		$result = $db->query($sql, __FILE__, __LINE__, __METHOD__, [$user->id, $user->id, ($page-1)*$pagesize, $pagesize]);

		while($rs = $db->fetch($result)) {
			$messages[] = $rs;
		}
		$smarty->assign('messages', $messages);

		/** Calculate number of pages */
		$numMessages = self::getNumUserMessages($user->id);
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
	 * @global object $db Globales Class-Object mit allen MySQL-Methoden
	 * @global object $user Globales Class-Object mit den User-Methoden & Variablen
	 * @return integer|void No response if a user is not logged-in
	 */
	static function getNumNewMessages()
	{
		global $db, $user;

		if ($user->is_loggedin())
		{
			$sql = 'SELECT COUNT(*) AS num FROM messages WHERE owner=? AND isread="0"'; // isread = ENUM(0;1)
			$rs = $db->fetch($db->query($sql, __FILE__, __LINE__, __METHOD__, [$user->id]));
			$numNewMessages = (isset($rs['num']) && $rs['num']>0 ? intval($rs['num']) : 0 );

			return $numNewMessages;
		}
	}


	/**
	 * Anzahl aller User Nachrichten
	 *
	 * Berechnet die Anzahl aller Nachrichten eines Users.
	 * Wird benötigt für das Paginating in Messagesystem::getInboxHTML()
	 *
	 * @version 1.0
	 * @since 1.0 `24.06.2018` `IneX` initial method release
	 * @since 1.1 `03.01.2024` `IneX` removed my own user's static ID from the sql query... Dafuq ^^
	 *
	 * @see Messagesystem::getInboxHTML()
	 * @param integer $userid User-ID welcher alle Nachricht(en) als gelesen markieren möchte
	 * @global object $db Globales Class-Object mit allen MySQL-Methoden
	 * @return array|boolean Returns an array with the number of messages for inbox & outbox - or false, if an error occurred
	 */
	static function getNumUserMessages($userid)
	{
		global $db;

		/** A MySQL Sub-Query retrieving user's total messages for the inbox & outbox at the same time */
		$sql = "SELECT
					(SELECT count(id) as num FROM messages where owner=? AND from_user_id<>?) num_inbox,
					(SELECT count(id) as num FROM messages where owner=? AND from_user_id=?) num_outbox
				FROM messages LIMIT 1";
		$result = $db->query($sql, __FILE__, __LINE__, __METHOD__, [$userid, $userid, $userid, $userid]);
		$rs = $db->fetch($result);

		if ($db->num($result) > 0 && false !== $rs)
		{
			return [ 'inbox' => (isset($rs['num_inbox']) && $rs['num_inbox']>0 ? intval($rs['num_inbox']) : 0)
					,'outbox' => (isset($rs['num_outbox']) && $rs['num_outbox']>0 ? intval($rs['num_outbox']) : 0) ];
		} else {
			return false;
		}
	}


	/**
	 * Nachricht anzeigen
	 *
	 * Zeigt eine Message an
	 *
	 * @version 2.0
	 * @since 1.0 `[z]milamber` initial method release
	 * @since 2.0 `24.06.2018` `IneX` frontend is now a template - as it should be
	 *
	 * @see Messagesystem::getMessageDetails()
	 * @see Messagesystem::doMarkMessageAsRead()
	 * @param int $id ID der Nachricht
	 * @global object $db Globales Class-Object mit allen MySQL-Methoden
	 * @global object $user Globales Class-Object mit den User-Methoden & Variablen
	 * @return string
	 */
	static function displayMessage($messageid)
	{
		global $user, $smarty;

		$messageDetails = self::getMessageDetails($messageid);

		if (false !== $messageDetails && !empty($messageDetails) && intval($messageDetails['owner']) === $user->id)
		{
			$smarty->assign('prevmessage_url', (self::getNextMessageid($messageid) > 0 ? '<a href="/messagesystem.php?message_id='.self::getNextMessageid($messageid).'"><-- </a> | ' : ''));
			$smarty->assign('nextmessage_url', (self::getPrevMessageid($messageid) > 0 ? '<a href="/messagesystem.php?message_id='.self::getPrevMessageid($messageid).'"> --></a>' : ''));
			$smarty->assign('deletemessage_html', self::getFormDelete($messageid));
			$smarty->assign('messagedetails', $messageDetails);
			$smarty->assign('recipientslist', explode(',', $messageDetails['to_users']));

			self::doMarkMessageAsRead($messageid);

		} else {
			$smarty->assign('error', t('invalid-permissions', 'messagesystem'));
		}

		return $smarty->fetch('file:layout/partials/messages/messages_view.tpl');
	}


	/**
	 * Message holen
	 *
	 * @author IneX
	 * @version 2.0
	 * @since 1.0 `24.06.2018` `IneX` Method added
	 * @since 2.0 `13.05.2021` `IneX` Code and query refactoring, returns false on error
	 *
	 * @param integer $messageid ID der Nachricht die abgefragt werden soll
	 * @global object $db Globales Class-Object mit allen MySQL-Methoden
	 * @return array|boolean Returns an Array containing the query results - or false if the query failed
	 */
	static function getMessageDetails($messageid)
	{
		global $db;

		if (!empty($messageid) && $messageid > 0)
		{
			$sql = 'SELECT *, UNIX_TIMESTAMP(date) as date FROM messages WHERE id=? LIMIT 1';
			$rs = $db->fetch($db->query($sql, __FILE__, __LINE__, __METHOD__, [$messageid]));
			if (false !== $rs && !empty($rs)) return $rs;
			else return false;
		} else {
			return false;
		}
	}


	/**
	 * Nächste Nachricht anzeigen
	 *
	 * Holt die ID der jeweils älteren Nachricht gegenüber der aktuell geöffneten
	 *
	 * @version 1.0
	 * @since 1.0 `[z]milamber` Function added
	 *
	 * @param integer $id ID der aktuell angezeigten Nachricht
	 * @global object $db Globales Class-Object mit allen MySQL-Methoden
	 * @global object $user Globales Class-Object mit den User-Methoden & Variablen
	 * @return integer
	 */
	static function getNextMessageid($id)
	{
		global $db, $user;

		$sql = "SELECT *, UNIX_TIMESTAMP(date) as date FROM messages
				WHERE owner=? AND from_user_id!=? AND id>? ORDER BY id ASC LIMIT 1";
		$rs = $db->fetch($db->query($sql, __FILE__, __LINE__, __METHOD__, [$user->id, $user->id, $id]));
		if (false !== $rs && !empty($rs)) return intval($rs['id']);
		else return false;
	}


	/**
	 * Vorherige Nachricht anzeigen
	 *
	 * Holt die ID der jeweils jüngeren Nachricht gegenüber der aktuell geöffneten
	 *
	 * @version 2.0
	 * @since 1.0 `[z]milamber` initial method release
	 * @since 2.0 `24.06.2018` `IneX` prev was always getting newewst message - fixed it
	 *
	 * @param integer $id ID der aktuell angezeigten Nachricht
	 * @global object $db Globales Class-Object mit allen MySQL-Methoden
	 * @global object $user Globales Class-Object mit den User-Methoden & Variablen
	 * @return integer
	 */
	static function getPrevMessageid($id)
	{
		global $db, $user;

		$sql = "SELECT *, UNIX_TIMESTAMP(date) as date FROM messages WHERE owner=? AND from_user_id!=? AND id<? ORDER BY id DESC LIMIT 1";
		$rs = $db->fetch($db->query($sql, __FILE__, __LINE__, __METHOD__, [$user->id, $user->id, $id]));
		if (false !== $rs && !empty($rs)) return intval($rs['id']);
		else return false;
	}


	/**
	 * Persönliche Nachricht senden
	 *
	 * Speichert die gesendete Nachricht im Postfach des Empfängers und meinem Postausgang
	 *
	 * @version 4.0
	 * @since 1.0 `[z]milamber` method added
	 * @since 2.0 `IneX` verschickt eine Notification über die neue Nachricht per E-Mail
	 * @since 3.0 `IneX` verschickt eine Notification per Telegram Messenger
	 * @since 3.1 `17.03.2018` `IneX` changed to new Telegram Send-Method
	 * @since 3.2 `15.10.2018` `IneX` added array-implode for passed $to_users parameter
	 * @since 4.0 `21.10.2018` `IneX` connected to new Notification() Class
	 *
	 * @see Notification::send()
	 * @param integer	$from_user_id User-ID des Senders
	 * @param integer	$owner User-ID des Nachrichten-Owners
	 * @param string	$subject Titel der Nachricht !NOTE: cannot exceed 40 characters
	 * @param string	$text (Optional) Nachrichten-Text
	 * @param string	$to_users (Optional) Liste aller Empfänger der Nachricht
	 * @param string	$isread (Optional) Lesestatus der Nachricht - ENUM('0','1'), Default: Ungelesen ('0')
	 * @global object $db Globales Class-Object mit allen MySQL-Methoden
	 * @return boolean	Returns true or false, depening on the susccessful execution
	 */
	static function sendMessage($from_user_id, $owner, $subject, $text='', $to_users='', $isread='0')
	{
		global $db, $notification;

		/** Validate function parameters */
		if (!isset($owner) || empty($owner) || $owner <= 0) {
			error_log(sprintf('<%s:%d> %s $owner ERROR: %s', __FILE__, __LINE__, __METHOD__, $owner));
			return false;
		}
		if (!isset($to_users) || empty($to_users)) $to_users = $owner;
		if (is_array($to_users)) implode(',', $to_users);
		if (empty($text)) $text = t('message-empty-text', 'messagesystem');
		if (strlen($subject) > 40) $subject = text_width($subject, 40, '…', false, true); // Trim too long Subject-Texts to <=40

		/**
		 * Send zorg Message to recipient
		 */
		zorgDebugger::log()->debug('Type SINGLE to $owner %d: %s', [$owner, $subject]);
		$sql = 'INSERT INTO messages (from_user_id, owner, subject, text, date, isread, to_users)
				VALUES (?, ?, ?, ?, ?, ?, ?)';
		$db->query($sql, __FILE__, __LINE__, __METHOD__, [$from_user_id, $owner, $subject, $text, timestamp(true), strval($isread), $to_users]);

		/**
		 * Notify $owner about new zorg Message
		 * ...ausser wenn der $from_user_id & $owner identisch sind,
		 * siehe 'Eigene Message für den 'Sent'-Ordner'
		 */
		if ($from_user_id != $owner)
		{
			$notification_status = $notification->send($owner, 'messagesystem', ['from_user_id'=>$from_user_id, 'subject'=>$subject, 'text'=>$text, 'message'=>$text]);
			if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> $notification_status: %s', __METHOD__, __LINE__, ($notification_status == 'true' ? 'true' : 'false')));
		}
		return true;
	}
}
