<?php
/**
 * Telegram Integration
 * 
 * Mittels der Telegram Integration können Nachrichten bzw.
 * Daten (Bilder, Video, Files, Locations, etc.) mittels
 * einem Telegram-Bot entweder an einzelne Telegram-User
 * oder an Telegram-Gruppenchats übermittelt werden. Dies
 * erfolgt durch die Übergabe der jeweiligen Chat-ID oder
 * des Namens (z.B. @telegramuser).
 *
 * Diese Klasee benutzt folgende Tabellen aus der DB:
 *		messages_telegram_queue
 *
 * @author		IneX
 * @package		Zorg
 * @subpackage	Messagesystem
 */
/**
* Load up the Telegram Bot
* @const TELEGRAM_BOT Name of the Telegram Bot to use (Attention: use same name for the bot's config file!)
* @include TELEGRAM_BOT.php Include Telegram Bot Configs
*/
if (!defined('TELEGRAM_BOT')) define('TELEGRAM_BOT', 'zbarbaraharris_bot', true);
if ( file_exists(__DIR__.'/../../'.TELEGRAM_BOT.'.php') ) require_once( __DIR__ . '/../../' . TELEGRAM_BOT.'.php' );

/**
 * Telegram Messaging Class
 * 
 * In dieser Klasse befinden sich alle Funktionen zum Senden von Telegram-Messages über einen Telegram-Bot
 *
 * @author		IneX
 * @date		10.06.2018
 * @package		Zorg
 * @subpackage	Messagesystem
 * @version		3.0
 * @since		1.0 Initial Telegram integration
 * @since		2.0 Refactoring of sendMessage-Methods in 2 functions: for single User & for Groups
 * @since		3.0 Major refactoring of the whole Telegram Integration: Class-Object, Flexible send-Methods, More API-Options support
 */
Class Telegram
{
	/**
	* Define global default Telegram Bot Settings
	* (can be overwritten on a Message level by passing as $parameter)
	* @const TELEGRAM_BOT_PARSE_MODE Specifies the Message Format to use - either empty, Markdown or HTML
	* @const TELEGRAM_BOT_DISABLE_WEB_PAGE_PREVIEW Specifies whether link previews for links in the message should be enabled or disabled
	* @const TELEGRAM_BOT_DISABLE_NOTIFICATION Specifies whether the Bot's messages should be silent or regular notifications
	*/
	const PARSE_MODE = 'html';
	const DISABLE_WEB_PAGE_PREVIEW = 'false';
	const DISABLE_NOTIFICATION = 'false';
	
	/**
	 * Send a Message via Telegram Messenger
	 * Schickt eine Notification an die Telegram Chats von Usern
	 *
	 * @author	IneX
	 * @date	17.03.2018
	 * @version	4.0
	 * @since	1.0
	 *
	 * @TODO implement this with TelegramBot\TelegramBotManager\BotManager?
	 *
	 * @link https://core.telegram.org/bots/api
	 * @see $botconfigs
	 * @see usersystem::userHasTelegram()
	 * @see Telegram::formatText()
	 * @see Telegram::validateData()
	 * @param	integer|string	$userScope		Scope to whom to send the message to: User = User-ID integer, Group = 'group' string.
	 * @param	string			$messageType	Type of Message to be sent (e.g. 'sendMessage', 'sendPhoto', 'sendLocation',...)
	 * @param	array			$content		Array mit Content welcher an die Telegram Chats geschickt wird
	 * @global	object			$user			Globales Class-Object mit den User-Methoden & Variablen
	 * @global	array			$botconfigs		Array mit allen Telegram Bot-Configs
	 * @return	boolean							Returns true or false
	 */
	public function send($userScope, $messageType, $content)
	{
		global $user, $botconfigs;

		/** First of all: make sure the Telegram Bot-Configs exist */
		if (isset($botconfigs) && is_array($botconfigs))
		{
			/** Get the corresponding Telegram Chat-ID */
			if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> $userScope: %s', __METHOD__, __LINE__, $userScope));
			switch ($userScope)
			{
				/** USER: If $userScope = User-ID: get the Telegram Chat-ID */
				case is_numeric($userScope) && $userScope > 0:
					if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> Checking for User Telegram Chat-ID...', __METHOD__, __LINE__));
					$telegramChatId = $user->userHasTelegram($userScope);
					break;

				/** GROUP: If $userScope = 'group': get the Telegram Groupchat-ID */
				case 'group':
					if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> Checking for Group Telegram Chat-ID...', __METHOD__, __LINE__));
					$telegramChatId = TELEGRAM_GROUPCHAT_ID;
					break;

				/** DEFAULT: stop execution */
				default:
					error_log( t('invalid-telegram-chatid', 'messagesystem') );
					return false;
					break;
			}

			/** When we got a Telegram Chat-ID... */
			if (!empty($telegramChatId))
			{
				if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> found Telegram Chat-ID: %s', __METHOD__, __LINE__, $telegramChatId));

				/** Build API Call */
				$parameters = array_merge( $content, [ 'chat_id' => $telegramChatId ] );
				if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> Telegram Message $parameters Array:'."\n\r%s", __METHOD__, __LINE__, print_r($parameters, true)));
				if (is_array($parameters) && !empty($parameters))
				{
					/** Validate & compose the Parameter-Query for the API Call */
					$data = $this->validateData($messageType, $parameters);
					$telegramAPIcallParameters = http_build_query($data);
					$telegramAPIcall = TELEGRAM_API_URI . "/$messageType?" . $telegramAPIcallParameters;

					/** Send the Telegram message */
					if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> using "%s" to Chat "%s"', __METHOD__, __LINE__, $messageType, $telegramChatId));
					if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> API call: %s', __METHOD__, __LINE__, $telegramAPIcall));
					if (!empty($messageType)) file_get_contents( $telegramAPIcall );
				} else {
					error_log(sprintf('[WARN] <%s:%d> "%s" did not pass validation!', __METHOD__, __LINE__, $messageType));
					return false;
				}
			}
		} else {
			error_log( t('invalid-message', 'messagesystem') );
		}
	}


	/**
	 * (NOT IMPLEMENTED YET!) Format Link to Mention Telegram User inline
	 * Gibt einen Link aus, welcher Telegram benutzt um einen spezifischen Telegram Benutzer zu @mention
	 *    Example: <a href="tg://user?id=123456789">inline mention of a user</a>
	 *
	 * @author	IneX
	 * @date	25.05.2018
	 * @version	1.0
	 * @since	1.0
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
	public function mentionUser($userid)
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
				if (DEVELOPMENT) error_log("[DEBUG] <" . __METHOD__ . "> found Telegram User ID $telegramUserId");

				if (!empty($telegramUserId))
				{
					$username = $user->id2user($telegramUserId);
					$link = sprintf('<a href="tg://user?id=%d">%s</a>', $telegramUserId, $username);
					if (DEVELOPMENT) error_log("[DEBUG] <" . __METHOD__ . "> returns HTML-link: $link");
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


	/**
	 * Cleanup Message for Telegram Messenger Notification
	 *
	 * @author	IneX
	 * @date	25.05.2018
	 * @version	2.0
	 * @since	1.0 initial function
	 * @since	2.0 line breaks are possible using encoded "\n" - won't strip those anymore. Added missing allowed <strong> & <em>.
	 *
	 * @link https://core.telegram.org/bots/api#html-style
	 * @link https://stackoverflow.com/questions/31908527/php-telegram-bot-insert-line-break-to-text-message
	 * @link https://stackoverflow.com/questions/15433188/r-n-r-n-what-is-the-difference-between-them
	 * @param	string	$notificationText	Content welcher für die Telegram Nachricht vorgesehen ist
	 * @return	string						Returns formatted & cleaned up $notificationText as String
	 */
	public function formatText($notificationText)
	{
		if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> passed raw string: %s', __METHOD__, __LINE__, $notificationText));

		/**
		 * Add missing Server address in HTML-Links inside Notification Text
		 */
		if (strpos($notificationText, 'href="/') > 0) $notificationText = str_replace('href="/', 'href="' . SITE_URL . '/', $notificationText);

		/**
		 * Strip away all HTML-tags & unix line breaks
		 * Except from the whitelist:
		 * <b>, <strong>, <i>, <em>, <a>, <code>, <pre>
		 */
		$notificationText = str_replace('&nbsp;', ' ', $notificationText);
		$notificationText = str_replace(array("\r\n", "\r"), "\n", $notificationText);
		$notificationText = strip_tags($notificationText, '<b><strong><i><em><a><code><pre>');
		$notificationText = html_entity_decode($notificationText);

		if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> processed string for return: %s', __METHOD__, __LINE__, $notificationText));
		return ( !empty($notificationText) ? $notificationText : false );
	}


	/**
	 * Validate Data agains Model for various Telegram Message Types
	 *
	 * Check for valid parameters and returns Array with key:value pairs assigned
	 * This function is related (but no depending!) to the following MySQL-table:
	 * 		- Table: messages_telegram_queue
	 *			- Column: :method
	 *			- Column: :content
	 *			- Column: :content_additional
	 *
	 * @author	IneX
	 * @date	10.06.2018
	 * @version	1.0
	 * @since	1.0
	 *
	 * @link https://core.telegram.org/bots/api#sendmessage
	 * @link https://core.telegram.org/bots/api#sendphoto
	 * @link https://core.telegram.org/bots/api#sendmediagroup
	 * @link https://core.telegram.org/bots/api#sendaudio
	 * @link https://core.telegram.org/bots/api#senddocument
	 * @link https://core.telegram.org/bots/api#sendvideo
	 * @link https://core.telegram.org/bots/api#sendvideonote
	 * @link https://core.telegram.org/bots/api#sendvoice
	 * @link https://core.telegram.org/bots/api#sendlocation
	 * @param	string	$messageType	A valid Telegram Message Type, see Telegram Bot API docu
	 * @param	array	$parameters		A Multidimensional Array containing the key:value parameter-pairs that should be passed to the Message Type Model
	 * @return	array|boolean			Returns an Array (or "false" on error...) with key:value pairs assigned for the specified Message Type
	 */
	private function validateData($messageType, array $parameters)
	{
		static $_telegramMessageModels =
			[
				'general'			=>	[
										 'required' => [ 'chat_id' ],
										 'optional' => [ 'parse_mode', 'disable_web_page_preview', 'disable_notification', 'reply_to_message_id', 'reply_markup' ]
										]
				,'sendMessage'		=>	[
				 						 'required' => [ 'text' ]
				 						]

				,'sendPhoto'		=>	[
										 'required' => [ 'photo' ],
										 'optional' => [ 'caption' ]
										]

				,'sendMediaGroup'	=>	[
										 'required' => [ 'media' ]
										]

				,'sendAudio'		=>	[
										 'required' => [ 'audio' ],
										 'optional' => [ 'caption', 'duration', 'performer', 'title' ]
										]
				,'sendDocument'		=>	[
										 'required' => [ 'document' ],
										 'optional' => [ 'caption' ]
										]

				,'sendVideo'		=>	[
										 'required' => [ 'video' ],
										 'optional' => [ 'caption', 'duration', 'width', 'height', 'supports_streaming' ]
										]

				,'sendVideoNote'	=>	[
										 'required' => [ 'video_note' ],
										 'optional' => [ 'duration', 'length' ]
										]

				,'sendVoice'		=>	[
										 'required' => [ 'voice' ],
										 'optional' => [ 'caption', 'duration' ]
										]

				,'sendLocation'		=>	[
										 'required' => [ 'latitude', 'longitude' ],
										 'optional' => [ 'live_period' ]
										]

				,'sendVenue'		=>	[
										 'required' => [ 'latitude', 'longitude', 'title', 'address' ],
										 'optional' => [ 'foursquare_id' ]
										]

				,'sendContact'		=>	[
										 'required' => [ 'phone_number', 'first_name' ],
										 'optional' => [ 'last_name' ]
										]
			];

		/** Validate $parameters */
		if (!is_array($parameters) || empty($parameters))
		{
			error_log(sprintf('[WARN] <%s:%d> passed Parameters are empty or invalid', __METHOD__, __LINE__));
			return false;
		}

		/** Check if $messageType matches any available $_telegramMessageModels */
		if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> checking array_key_exists in $_telegramMessageModels for "%s"', __METHOD__, __LINE__, $messageType));
		if (isset($_telegramMessageModels[$messageType]))
		{
			if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> SUCCESS: $messageType "%s" found and is OK', __METHOD__, __LINE__, $messageType));

			/**
			 * Check if any of the passed $parameters matche to the $_telegramMessageModles[$messageType]['required'] keys
			 *
			 * @TODO Alternative approach, supporting multiple required parameters:
			 *	foreach ($_telegramMessageModels[$messageType]['required'] as $key=>$value) {
			 *		if ( !array_key_exists($parameters, $value) ) error_log(sprintf('[WARN] '.__METHOD__.': Value %s is required but was not passed!', $key+1));
			 *	}
			 */
			if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> Checking $parameters for presence of required parameter "%s"', __METHOD__, __LINE__, $_telegramMessageModels[$messageType]['required'][0]));
			if ( !isset($parameters[$_telegramMessageModels[$messageType]['required'][0]]) )
			{
				error_log(sprintf('[WARN] <%s:%d> Value %s is required but was not passed!', __METHOD__, __LINE__, $_telegramMessageModels[$messageType]['required'][0]));
				return false;
			} else {
				if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> SUCCESS: required parameter "%s" found and is OK', __METHOD__, __LINE__, $_telegramMessageModels[$messageType]['required'][0]));

				/**
				 * Build the Data-Array with key:value pairs assigned
				 *
				 * Example:
				 *	$data = [
				 *   	'chat_id' => $chatId,
				 *		'parse_mode' => $telegramParseMode,
				 *		'text' => $notificationText,
				 *	];
				 */
				if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> Building $data Array for Function return', __METHOD__, __LINE__));
				$data = [];

				/** Assign key=>value pairs for Global Parameters */
				if (!empty($parameters['chat_id']))
				{
					$data['chat_id'] = $parameters['chat_id'];
				} else {
					error_log(sprintf('[WARN] <%s:%d> Value "%s" is required but was not passed!', __METHOD__, __LINE__, 'chat_id'));
					return false;
				}
				$data['parse_mode'] = ( isset($parameters['parse_mode']) ? $parameters['parse_mode'] : self::PARSE_MODE );
				$data['disable_web_page_preview'] = ( isset($parameters['disable_web_page_preview']) ? $parameters['disable_web_page_preview'] : self::DISABLE_WEB_PAGE_PREVIEW );
				$data['disable_notification'] = ( isset($parameters['disable_notification']) ? $parameters['disable_notification'] : self::DISABLE_NOTIFICATION );
				if ( isset($parameters['reply_to_message_id']) ) $data['reply_to_message_id'] = $parameters['reply_to_message_id'];
				if ( isset($parameters['reply_markup']) ) $data['reply_markup'] = $parameters['reply_markup'];

				/** Assign key=>value pairs for $messageType Required Parameters */
				foreach ((array) $_telegramMessageModels[$messageType]['required'] as $requiredParameter)
				{
					if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> array_push to $data Array for key=>value pair "%s"', __METHOD__, __LINE__, $requiredParameter));
					$data[$requiredParameter] = $this->formatText($parameters[$requiredParameter]);
				}

				/** Assign key=>value pairs for $messageType Optional Parameters */
				foreach ((array) $_telegramMessageModels[$messageType]['optional'] as $optionalParameter)
				{
					if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> array_push to $data Array for key=>value pair "%s"', __METHOD__, __LINE__, $optionalParameter));
					if (!empty($parameters[$optionalParameter])) $data[$optionalParameter] = $parameters[$optionalParameter];
				}

				/** Return Data-Array with key:value pairs assigned */
				if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> Complete $data Array:'."\n\r%s", __METHOD__, __LINE__, print_r($data, true)));
				return $data;
			}

		} else {
			error_log(sprintf('[WARN] <%s:%d> Telegram Message Type "%s" is invalid!', __METHOD__, __LINE__, $messageType));
			return false;
		}
	}

}

/**
 * Pseudo Sub-Class for Telegram Class
 * Used for properly preparing new Telegram Bot API Method Calls
 *
 * @TODO Add method-call routes for all additional Telegram Message types
 */
Class send extends Telegram
{
	/** sendMessage */
	public function message($scope, $text, $parameters=[]) {
		$this->send( $scope, 'sendMessage', array_merge(['text' => $text], $parameters) );
	}

	/** sendPhoto */
	public function photo($scope, $photo, $caption=NULL, $parameters=[]) {
		$this->send( $scope, 'sendPhoto', array_merge(['photo' => $photo], ['caption' => $caption], $parameters) );
	}

	/**
	 * sendMediaGroup
	 * @link https://core.telegram.org/bots/api/#inputmedia
	 */
	public function gallery($scope, array $inputMedia, $parameters=[]) {
		$this->send( $scope, 'sendMediaGroup', array_merge($inputMedia, $parameters) );
	}

	/** sendDocument */
	public function document($scope, $document, $caption=NULL, $parameters=[]) {
		$this->send( $scope, 'sendDocument', array_merge(['document' => $document], ['caption' => $caption], $parameters) );
	}

	/** sendLocation */
	public function location($scope, float $latitude, float $longitude, $live_period=NULL, $parameters=[]) {
		$this->send( $scope, 'sendLocation', array_merge(['latitude' => $latitude], ['longitude' => $longitude], ['live_period' => $live_period], $parameters) );
	}

	/** sendVenue */
	public function event($scope, float $latitude, float $longitude, $title, $address, $foursquare_id=NULL, $parameters=[]) {
		$this->send( $scope, 'sendVenue', array_merge(['latitude' => $latitude], ['longitude' => $longitude], ['title' => $title], ['address' => $address], ['foursquare_id' => $foursquare_id], $parameters) );
	}
}

/**
 * Instantiating new Telegram Class-Object
 * @TODO Fix this "dirty hack" with instantiated "$telegram->send"-object...
 */
$telegram = new Telegram();
$telegram->send = new send();
