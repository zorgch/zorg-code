<?php
/**
 * Notification System
 *
 * Das Notification System kümmert sich darum, dass
 * ein User nur über die von ihm gewählten Benachrichtigungs-Kanäle
 * der unterschiedlichen Notifications-Arten benachrichtigt wird.
 * Z.B. nur E-Mails oder Telegram und zorg Mesasges, etc.
 *
 * Diese Klasee benutzt folgende Tabellen aus der DB:
 *		user
 *		- notifications
 *		- email
 *
 * @author		IneX
 * @package		zorg\Usersystem
 */
/**
 * File includes
 * @include messagesystem.inc.php Required Messagesystem Class
 */
require_once __DIR__.'/messagesystem.inc.php' ;

/**
 * Class for Notification handling
 *
 * In dieser Klasse befinden sich alle Funktionen zum Senden von Notifications an User
 *
 * @author IneX
 * @package zorg\Usersystem
 * @version 1.0
 * @since 1.0 `21.10.2018` `IneX` Class added
 */
class Notification
{
	/**
	 * Send a Notification to a User
	 * Schickt eine Notification an einen User über die aktivierten Kanäle
	 *
	 * @author	IneX
	 * @version	1.1
	 * @since	1.0 `21.10.2018` `IneX` method added
	 * @since	1.1 `13.08.2021` `IneX` fixed Undefined index: games & Invalid argument supplied for foreach()
	 *
	 * @param integer $user_id Valid User-ID integer
	 * @param string $notification_source String representing the source of Notification to send $content for. E.g. 'messagesystem', 'mentions', 'games', etc...
	 * @param array $content Array mit weiteren Parametern & Content welcher an den User geschickt werden soll
	 * @global object $user Globales Class-Object mit den User-Methoden & Variablen
	 * @global object $telegram Globales Class-Object mit den Telegram-Methoden
	 * @return boolean Returns true or false
	 */
	public function send($user_id, $notification_source, $content)
	{
		global $user, $telegram;

		/** Validate passed parameters */
		if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> passed parameters: %d | %s | %s', __METHOD__, __LINE__, $user_id, $notification_source, (is_array($content) ? print_r($content,true) : strval($content))));
		if (!is_numeric($user_id) || $user_id <= 0) return false;
		if (is_numeric($notification_source) || is_array($notification_source)) return false;
		if (!is_array($content)) return false;

		/** Get the Notifications for $user_id */
		$userNotifications = $this->get($user_id);

		/**
		 * Check and send User Notifications
		 * @TODO harmonise $text & $message...
		 */
		if (is_array($userNotifications) && count($userNotifications)>0) // @FIXME what if the user has NO (0) Notifications activated?
		{
			if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> $userNotifications: %s', __METHOD__, __LINE__, print_r($userNotifications,true)));
			/** Make sure $notification_source = eixsts in $userNotifications */
			if (false === array_key_exists($notification_source, $userNotifications))
			{
				/** ...othwise fallback: add it from the Default Notification-Settings */
				$userDefaultNotificationsArr = json_decode($user->default_notifications, true); // JSON-DECODE to Array
				$userNotifications[$notification_source] = $userDefaultNotificationsArr[$notification_source];
			}
			foreach($userNotifications[$notification_source] as $notification_type => $notification_value)
			{
				if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> %s => %s', __METHOD__, __LINE__, $notification_type, $notification_value));

				/**
				 * zorg Message
				 */
				if ($notification_type === 'message' && $notification_value == 'true')
				{
					/** Validate $content for $notification_type 'message'
						$from_user_id, $owner, $subject, $text='', $to_users='', $isread='0'
					*/
					if (!empty($content['from_user_id']) && !empty($content['from_user_id']) && !empty($content['subject']) && !empty($content['text']))
					{
						/** Send notification */
						$messagesystem = new Messagesystem();
						$messagesystem->sendMessage($content['from_user_id'], $user_id, $content['subject'], $content['text'], $content['to_users']);
					} else {
						error_log(sprintf('[WARN] <%s:%d> $notification_type "message": invalid or incomplete $content! %s', __METHOD__, __LINE__, print_r($content,true)));
					}
				}

				/**
				 * E-Mail
				 */
				elseif ($notification_type === 'email' && $notification_value == 'true')
				{
					/** Validate $content for $notification_type 'message'
						$from_user_id, $owner, $subject, $text
					*/
					if (!empty($content['from_user_id']) && !empty($content['subject']) && !empty($content['text']))
					{
						/** Send notification */
						$this->sendEmailNotification($content['from_user_id'], $user_id, $content['subject'], $content['text']);
					} else {
						error_log(sprintf('[WARN] <%s:%d> $notification_type "email": invalid or incomplete $content! %s', __METHOD__, __LINE__, print_r($content,true)));
					}
				}

				/**
				 * Telegram
				 */
				elseif ($notification_type === 'telegram' && $notification_value == 'true')
				{
					/** Validate $content for $notification_type 'message' */
					if (!empty($content['message']) && !is_numeric($content['message']) && !is_array($content['message']))
					{
						/** Send notification */
						$content['parameters'] = ['disable_web_page_preview' => 'true']; // TEMP - REMOVE LATER!
						//$message = t('telegram-newmessage-notification', 'messagesystem', [ SITE_URL, $user_id, $user->id2user($content['from_user_id'], TRUE), SITE_HOSTNAME, text_width($text, 140, '...', true) ] );
						$telegram->send->message($user_id, $content['message'], $content['parameters']);
					} else {
						error_log(sprintf('[WARN] <%s:%d> $notification_type "telegram": invalid or incomplete $content! %s', __METHOD__, __LINE__, print_r($content,true)));
					}
				}
			}

			/** Notifications processed */
			return true;

		} else {
			if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> $userNotifications INVALID: %d', __METHOD__, __LINE__, print_r($userNotifications,true)));
			return false;
		}
	}


	/**
	 * Get a User's ENABLED Notification types
	 *
	 * @author IneX
	 * @since 1.1
	 * @since 1.0 `21.10.2018` `IneX` method added
	 * @since 1.1 `04.04.2021` `IneX` fixed wrong true/false-check on usersystem::id2user()
	 *
	 * @var array $default_notifications Array-Reference to Default Notification Settings
	 * @var array $notifications Array-Reference with Notifications Types
	 * @param integer $user_id Valid User-ID integer
	 * @global object $db Globales Class-Object mit allen MySQL-Methoden
	 * @global object $user Globales Class-Object mit den User-Methoden & Variablen
	 * @return array PHP-Array mit allen für den User aktivierten Notifications - default: usersystem::$default_notifications
	 */
	private function get($user_id)
	{
		global $db, $user;

		/** Validate passed parameters */
		if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> passed parameters: %d', __METHOD__, __LINE__, $user_id));
		if (!is_numeric($user_id) || $user_id <= 0 || true === is_array($user_id) || false === $user->id2user($user_id)) {
			if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> $user_id INVALID: %d', __METHOD__, __LINE__, $user_id));
			return false;
		} else {
			if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> $user_id VALID', __METHOD__, __LINE__));
		}

		$query = $db->query('SELECT notifications FROM user WHERE id=? LIMIT 1', __FILE__, __LINE__, __METHOD__, [$user_id]);
		$result = $db->fetch($query);
		if (!$result || empty($result))
		{
			/** Use fallback usersystem::$default_notifications if No query result / empty "notifications"-field */
			if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> Use fallback usersystem::$default_notifications for $user_id %d: %s', __METHOD__, __LINE__, $user_id, $user->default_notifications));
			$userEnabledNotifications = json_decode( $user->default_notifications, true ); // JSON-DECODE to Array
		} else {
			if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> Use $userEnabledNotifications for $user_id %d: %s', __METHOD__, __LINE__, $user_id, $result['notifications']));
			$userEnabledNotifications = json_decode( stripslashes($result['notifications']), true); // JSON-Decode to Array
		}

		return $userEnabledNotifications;
	}


	/**
	 * Check if a User's Notification type setting is set to TRUE
	 *
	 * @author IneX
	 * @version 2.1
	 * @since 1.0 `04.10.2018` `IneX` method added
	 * @since 2.0 `21.10.2018` `IneX` method moved from class usersystem() to class Notification()
	 * @since 2.1 `04.04.2021` `IneX` removed unnecessary try-catch
	 *
	 * @param string $notification_source String representing the source of Notification to check. E.g. 'messagesystem', 'mentions', 'games', etc...
	 * @param string $notification_type String representing the type of Notification to check. Valid values: 'message', 'email' & 'telegram'.
	 * @param integer $user_id User-ID
	 * @global object $db Globales Class-Object mit allen MySQL-Methoden
	 * @return boolean True or false - whether the requested $notification_source & $notification_type apply or not
	 */
	private function check($notification_source, $notification_type, $user_id)
	{
		global $db;

		/** Validate passed parameters */
		if (is_numeric($notification_source) || is_array($notification_source)) return false;
		if (is_numeric($notification_type) || is_array($notification_type)) return false;
		if (!is_numeric($user_id) || $user_id <= 0 || is_array($user_id)) return false;

		$query = $db->query('SELECT notifications FROM user WHERE id=? LIMIT 1', __FILE__, __LINE__, __METHOD__, [$user_id]);
		$result = $db->fetch($query);
		if (!$result || empty($result))
		{
			/** No query result / empty "notifications"-field */
			return false;
		} else {
			$userEnabledNotifications = json_decode( stripslashes($result['notifications']), true); // JSON-Decode to Array
		}

		/** Check if $notification_type is enabled */
		if (!empty($userEnabledNotifications[$notification_source][$notification_type]))
		{
			if ($userEnabledNotifications[$notification_source][$notification_type] != false)
			{
				/** $notification_type is ENABLED */
				return true;
			} else {
				/** $notification_type is DISABLED */
				return false;
			}
		} else {
			/** $notification_type is NOT FOUND (= DISABLED) */
			return false;
		}
	}


	/**
	 * E-Mail Hinweis über neue Nachricht senden
	 *
	 * Generiert eine E-Mail um einen Benutzer auf eine neue persönliche Nachricht hinzuweisen
	 *
	 * @author IneX
	 * @version 2.1
	 * @since 1.0 `15.05.2009` `IneX` method added
	 * @since 2.0 `21.10.2018` `IneX` method moved from class Messagesystem() to class Notification()
	 * @since 2.1 `04.04.2021` `IneX` added better string encoding for MIME header of To: & Subject: lines in e-mail
	 *
	 * @param	integer	$from_user_id	User-ID des Senders
	 * @param	integer	$to_user_id		User-ID des Empfängers
	 * @param	string	$titel			Titel der ursprünglichen Nachricht
	 * @param	string	$text			Ursprünglicher Text
	 * @global	object	$db				Globales Class-Object mit allen MySQL-Methoden
	 * @global	object	$user			Globales Class-Object mit den User-Methoden & Variablen
	 * @return	boolean					Returns 'true' or 'false', depending if mail() was successful or not
	 */
	private function sendEmailNotification($from_user_id, $to_user_id, $titel, $text)
	{
		global $db, $user;

		/** Validate passed parameters */
		if (!empty($to_user_id) && is_numeric($to_user_id))
		{
			/** Get User E-Mail - if E-Mail Notifications are enabled */
			$empfaengerMail = $user->id2useremail($to_user_id);

			/** Nur, wenn User E-Mailbenachrichtigung aktiviert hat...! */
			if (!empty($empfaengerMail))
			{
				/** E-Mailnachricht bauen */
				$empfaengerName = $user->id2user($to_user_id, TRUE);
				$senderName = $user->id2user($from_user_id, TRUE);

				$header = t('email-notification-header', 'messagesystem', [ SITE_HOSTNAME, SENDMAIL_EMAIL, phpversion() ]);

				$subject = sprintf('=?UTF-8?Q?%s?=', quoted_printable_encode(remove_html(t('email-notification-subject', 'messagesystem', [ $senderName, SITE_HOSTNAME ]), ENT_DISALLOWED, 'UTF-8')));

				$body = htmlspecialchars( t('email-notification-body', 'messagesystem', [ SITE_URL, $titel, $senderName, text_width(remove_html($text, '<br>'), 140, '...'), $to_user_id ]), ENT_DISALLOWED, 'UTF-8' );

				/** Sende E-Mail an User */
				$empfaengerNameMail = sprintf('=?UTF-8?B?%s?= <%s>', base64_encode($empfaengerName), $empfaengerMail); // => =?UTF-8?B?W3pdQmFyYmFyYSBIYXJyaXM=?= <b=C3=A4rbel@zorg.ch>
				if (DEVELOPMENT === true) error_log(sprintf('[DEBUG] <%s:%d> mail() "%s" to user: %s', __METHOD__, __LINE__, $subject, $empfaengerNameMail));
				if (mail($empfaengerNameMail, $subject, $body, $header))
				{
					return true;
				} else {
					error_log(sprintf('[WARN] <%s:%d> mail() ERROR to %s', __METHOD__, __LINE__, $empfaengerNameMail));
					return false;
				}
			} else {
				error_log(sprintf('[WARN] <%s:%d> mail() ERROR: empty($empfaengerMail) for user_id %d', __METHOD__, __LINE__, $to_user_id));
				return false;
			}
		} else {
			error_log(sprintf('[WARN] <%s:%d> mail() ERROR: $to_user_id: %d | $titel: "%s"', __METHOD__, __LINE__, $to_user_id, $titel));
			return false;
		}
	}
}


/**
 * Instantiating new Notification Class-Object
 */
$notification = new Notification();
