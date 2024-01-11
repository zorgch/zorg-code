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
	 * @version 2.1
	 * @since 1.0 `[z]milamber` method added
	 * @since 2.0 `IneX` code optimizations
	 * @since 2.1 `04.04.2021` `IneX` fixed wrong check if own message, and PHP Deprecated: Non-static method Messagesystem::sendMessage()
	 *
	 * @uses BARBARA_HARRIS
	 * @uses Messagesystem::sendMessage()
	 * @param integer $messageid ID der ausgewählten Nachricht(en)
	 * @param integer $deleter_userid User-ID welcher die Nachricht(en) löscht
	 * @global object $user Globales Class-Object mit den User-Methoden & Variablen
	 */
	static function execActions()
	{
		global $user;

		if(isset($_POST['action']) && $_POST['action'] === 'sendmessage')
		{
			$to_users = ( empty($_POST['to_users']) ? $user->id : $_POST['to_users'] );

			for ($i=0; $i < count($to_users); $i++)
			{
				/** Wenn ich mir selber was schicke, dann nimm die Bärbel als Absender */
				if ($to_users[$i] == $user->id)
				{
					self::sendMessage(
						BARBARA_HARRIS,
						$to_users[$i],
						$_POST['subject'],
						$_POST['text'],
						implode(',', $to_users)
					);
				}

				/** Nachricht an andere Leute */
				else {
						self::sendMessage(
						$user->id,
						$to_users[$i],
						$_POST['subject'],
						$_POST['text'],
						implode(',', $to_users)
					);
				}

			}

			/** Eigene Message für den 'Sent'-Ordner */
			self::sendMessage(
				$user->id,
				$user->id,
				$_POST['subject'],
				$_POST['text'],
				$to_users=implode(',', $to_users),
				1
			);

			/** When the option "Delete message after sending" was checked... */
			if (isset($_POST['delete_message_id']) && $_POST['delete_message_id'] > 0) {
				Messagesystem::deleteMessage($_POST['delete_message_id'], $user->id);
			}

			$headerLocation = ( !empty($_POST['url']) ? base64url_decode($_POST['url']) . '&sent=successful' : sprintf('%s/profil.php?user_id=%d&box=outbox&sent=successful', SITE_URL, $user->id) );
			zorgDebugger::log()->debug('header() Location: %s', [$headerLocation]);
			header('Location: ' . $headerLocation);
		}

		if(isset($_POST['do']) && $_POST['do'] === 'delete_messages')
		{
			/** Delete all passed message_id's */
			for ($i=0; $i < count($_POST['message_id']); $i++) {
				if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> Deleting Message ID: %d', __METHOD__, __LINE__, $_POST['message_id']));
				self::deleteMessage($_POST['message_id'][$i], $user->id);
			}

			/** If only singe passed message_id, redirect User to previous Message */
			if(count($_POST['message_id']) == 1) {
				$msgid = self::getPrevMessageid($_POST['message_id'][0]);
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

			if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> Redirecting User back to Page: %s', __METHOD__, __LINE__, base64url_decode($_POST['url'])));
			header("Location: ".base64url_decode($_POST['url']));
			//exit;
		}

		if(isset($_POST['do']) && $_POST['do'] === 'messages_as_unread')
		{
			/** Change Message Status to UNREAD */
			for ($i=0; $i < count($_POST['message_id']); $i++) {
				self::doMessagesUnread($_POST['message_id'][$i], $user->id);
			}

			if(count($_POST['message_id']) == 1) {
				$msgid = self::getPrevMessageid($_POST['message_id'][0]);
				if($msgid > 0) {
					header("Location: messagesystem.php?message_id=".$msgid."&".session_name()."=".session_id());
					//exit;
				} else {
					header("Location: profil.php?user_id=".$user->id."&".session_name()."=".session_id());
					//exit;
				}
			}

			header("Location: ".base64url_decode($_POST['url']));
			//exit;
		}

		if(isset($_POST['do']) && $_POST['do'] === 'mark_all_as_read')
		{
			/** Mark all Messages as read */
			self::doMarkAllAsRead($user->id);

			if(count($_POST['message_id']) == 1) {
				$msgid = self::getPrevMessageid($_POST['message_id'][0]);
				if($msgid > 0) {
					header("Location: messagesystem.php?message_id=".$msgid."&".session_name()."=".session_id());
					//exit;
				} else {
					header("Location: profil.php?user_id=".$user->id."&".session_name()."=".session_id());
					//exit;
				}
			}

			header("Location: ".base64url_decode($_POST['url']));
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
	 * @global object $db Globales Class-Object mit allen MySQL-Methoden
	 */
	function deleteMessage($messageid, $deleter_userid)
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
	 * @global object $user Globales Class-Object mit den User-Methoden & Variablen
	 * @global object $smarty Globales Class-Object mit allen Smarty-Methoden
	 * @return string HTML des Send-Message Form
	 */
	static function getFormSend($to_users, $subject, $text, $delete_message_id=0)
	{
		global $user, $smarty;

		$smarty->assign('form_action', base64url_decode(getURL()));
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
	 * @return integer
	 */
	static function getNumNewMessages()
	{
		global $db, $user;

		if ($user->is_loggedin())
		{
			$sql = 'SELECT count(*) AS num FROM messages WHERE owner=? AND isread="0"'; // isread = ENUM(0;1)
			$result = $db->query($sql, __FILE__, __LINE__, __METHOD__, [$user->id]);
			$rs = $db->fetch($result);

			return intval($rs['num']);
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
			return [ 'inbox' => $rs['num_inbox'], 'outbox' => $rs['num_outbox'] ];
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

		$sql = "SELECT *, UNIX_TIMESTAMP(date) as date FROM messages
				WHERE owner=? AND from_user_id!=? AND id<? ORDER BY id DESC LIMIT 1";
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
	 * @param string	$subject Titel der Nachricht
	 * @param string	$text (Optional) Nachrichten-Text
	 * @param string	$to_users (Optional) Liste aller Empfänger der Nachricht
	 * @param string	$isread (Optional) Lesestatus der Nachricht - ENUM('0','1'), Default: Ungelesen ('0')
	 * @global object $db Globales Class-Object mit allen MySQL-Methoden
	 * @return boolean	Returns true or false, depening on the susccessful execution
	 */
	function sendMessage($from_user_id, $owner, $subject, $text='', $to_users='', $isread='0')
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
	}
}
