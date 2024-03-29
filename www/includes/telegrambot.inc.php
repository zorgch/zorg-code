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
 * @package		zorg
 * @subpackage	Messagesystem
 */

/**
* Load up the Telegram Bot
*
* @include config.inc.php
* @const TELEGRAM_BOT Name of the Telegram Bot to use (Attention: use same name for the bot's config file!)
*/
require_once __DIR__.'/config.inc.php';
if (!defined('TELEGRAM_BOT') && isset($_ENV['TELEGRAM_BOT'])) define('TELEGRAM_BOT', $_ENV['TELEGRAM_BOT']);

/**
 * Telegram Messaging Class
 *
 * In dieser Klasse befinden sich alle Funktionen zum Senden von Telegram-Messages über einen Telegram-Bot
 *
 * @author		IneX
 * @date		10.06.2018
 * @package		zorg
 * @subpackage	Messagesystem
 * @version		3.0
 * @since		1.0 Initial Telegram integration
 * @since		2.0 Refactoring of sendMessage-Methods in 2 functions: for single User & for Groups
 * @since		3.0 Major refactoring of the whole Telegram Integration: Class-Object, Flexible send-Methods, More API-Options support
 */
class Telegram
{
	/**
	* Define global default Telegram Bot Settings
	* (can be overwritten on a Message level by passing as $parameter)
	* @const TELEGRAM_BOT_PARSE_MODE Specifies the Message Format to use - either empty, Markdown or HTML
	* @const TELEGRAM_BOT_DISABLE_WEB_PAGE_PREVIEW Specifies whether link previews for links in the message should be enabled or disabled
	* @const TELEGRAM_BOT_DISABLE_NOTIFICATION Specifies whether the Bot's messages should be silent or regular notifications
	*/
	const PARSE_MODE = 'html'; // TODO Replace with $_ENV['TELEGRAM_PARSE_MODE']
	const DISABLE_WEB_PAGE_PREVIEW = 'false'; // TODO Replace with $_ENV['TELEGRAM_DISABLE_WEBPAGE_PREVIEW']
	const DISABLE_NOTIFICATION = 'false'; // TODO Replace with $_ENV['TELEGRAM_DISABLE_NOTIFICATION']
	public $send = null;

	/**
	 * Send a Message via Telegram Messenger
	 * Schickt eine Notification an die Telegram Chats von Usern
	 *
	 * @author	IneX
	 * @version	4.1
	 * @since	1.0 `17.03.2018` `IneX` Method added
	 * @since	4.1 `29.12.2022` `IneX` Updated to use $_ENV for $botconfigs, instead of including keys/botname_bot.php
	 *
	 * @TODO implement this with TelegramBot\TelegramBotManager\BotManager?
	 *
	 * @link https://core.telegram.org/bots/api
	 * @uses usersystem::userHasTelegram()
	 * @uses Telegram::formatText(), Telegram::validateData()
	 * @param	integer|string	$userScope		Scope to whom to send the message to: User = User-ID integer, Group = 'group' string.
	 * @param	string			$messageType	Type of Message to be sent (e.g. 'sendMessage', 'sendPhoto', 'sendLocation',...)
	 * @param	array			$content		Array mit Content welcher an die Telegram Chats geschickt wird
	 * @global	object			$user			Globales Class-Object mit den User-Methoden & Variablen
	 * @return	boolean							Returns true or false
	 */
	public function send($userScope, $messageType, $content)
	{
		global $user;

		/** Parse $_ENV vars into $botconfigs */
		if (empty($_ENV['TELEGRAM_BOT_API_KEY']) || empty($_ENV['TELEGRAM_BOT'])) {
			zorgDebugger::log()->warn('Missing Telegram Bot Configs! TELEGRAM_BOT_API_KEY=%s | TELEGRAM_BOT=%s', [$_ENV['TELEGRAM_BOT_API_KEY'],$_ENV['TELEGRAM_BOT']]);
			return false;
		} else {
			$botconfigs = [  'api_key' => $_ENV['TELEGRAM_BOT_API_KEY']
							,'my_secret' => $_ENV['TELEGRAM_BOT_API_AUTH_PASSWORD']
							,'valid_ips' => explode(',', $_ENV['TELEGRAM_BOT_API_IPWHITELIST'])
							,'admins' => explode(',', $_ENV['TELEGRAM_BOT_API_USERWHITELIST'])
							,'ssl_certificate' => $_ENV['TELEGRAM_BOT_API_SSLCERT_PATH']
							,'chat_id' =>  $_ENV['TELEGRAM_BOT_API_CHAT']
							,'logging_dirroot' => $_ENV['TELEGRAM_BOT_API_LOG_DIR']
							,'files_dirroot' => explode(',', $_ENV['TELEGRAM_BOT_API_FILES_DIR'])
							,'TELEGRAM_API_URI' => $_ENV['TELEGRAM_BOT_API']
							,'TELEGRAM_GROUPCHAT_ID' => $_ENV['TELEGRAM_BOT_API_CHAT']
						];
		}

		/** First of all: make sure the Telegram Bot-Configs exist */
		if (isset($botconfigs) && is_array($botconfigs))
		{
			/** Get the corresponding Telegram Chat-ID */
			if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> $userScope: %s', __METHOD__, __LINE__, $userScope));
			switch ($userScope)
			{
				/** USER: If $userScope = User-ID: get the Telegram Chat-ID */
				case is_numeric($userScope) && $userScope > 0:
					zorgDebugger::log()->debug('Checking for User Telegram Chat-ID...');
					$telegramChatId = $user->userHasTelegram($userScope);
					break;

				/** GROUP: If $userScope = 'group': get the Telegram Groupchat-ID */
				case 'group':
					zorgDebugger::log()->debug('Checking for Group Telegram Chat-ID...');
					$telegramChatId = $botconfigs['TELEGRAM_GROUPCHAT_ID'];
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
				zorgDebugger::log()->debug('Telegram Message $parameters Array: %s', [print_r($parameters, true)]);
				if (is_array($parameters) && !empty($parameters))
				{
					/** Validate & compose the Parameter-Query for the API Call */
					$data = $this->validateData($messageType, $parameters);
					$telegramAPIcallParameters = http_build_query($data);
					$telegramAPIcall = $botconfigs['TELEGRAM_API_URI'].$messageType.'?'.$telegramAPIcallParameters;

					/**
					 * Sending the Telegram message
					 */
					zorgDebugger::log()->debug('Using "%s" to Chat "%s"', [$messageType, strval($telegramChatId)]);
					zorgDebugger::log()->debug('API call: %s', [$telegramAPIcall]);
					if (!empty($messageType))
					{
						/** Create a stream_context for the file_get_contents HTTP request */
						$httpContext = stream_context_create(array(
							'http' => array(
								'ignore_errors' => true
							)
						));
						$httpResponseBody = file_get_contents($telegramAPIcall, false, $httpContext);

						/**
						 * @global array $http_response_header The HTTP-request resul headers are available in $http_response_header that PHP creates in global scope
						 */
						if (is_array($http_response_header))
						{
							zorgDebugger::log()->debug('file_get_contents() $http_response_header: %s | $httpResponseBody: %s', [print_r($http_response_header, true), $httpResponseBody]);
							preg_match('{HTTP\/\S*\s(\d{3})}', $http_response_header[0], $match);
							if ($match[1] !== '200')
							{
								zorgDebugger::log()->error('Telegram %s failed with HTTP status code %s and response: %s', [$messageType, strval($match[1]), (false !== $httpResponseBody ? $httpResponseBody : 'false')]);
								return false;
							} else {
								return true;
							}
						} else {
							return true;
						}
					}
				} else {
					zorgDebugger::log()->warn('"%s" did not pass validation!', [$messageType]);
					return false;
				}
			}
		} else {
			zorgDebugger::log()->warn(t('invalid-telegram-chatid', 'messagesystem'));
			return false;
		}
	}


	/**
	 * (NOT IMPLEMENTED YET!) Format Link to Mention Telegram User inline
	 *
	 * Gibt einen Link aus, welcher Telegram benutzt um einen spezifischen Telegram Benutzer zu @mention.
	 * Example: <a href="tg://user?id=123456789">inline mention of a user</a>
	 *
	 * @author	IneX
	 * @date	25.05.2018
	 * @version	1.1
	 * @since	1.0 `IneX` 25.05.2018 Method added
	 * @since	1.1 `IneX` 18.04.2020 Code optimization and migration to mysqli_
	 *
	 * @TODO Database column "telegram_user_id" must be added first, for this to work
	 * @TODO probably it's more common that a userNAME is passed? => needs usersystem::user2id()
	 *
	 * @link https://core.telegram.org/bots/api#html-style
	 * @see usersystem::id2user()
	 * @param	integer	$userid	User-ID (numeric String) dessen Telegram User mentioned werden soll
	 * @global	object	$db	Globales Class-Object mit allen MySQL-Methoden
	 * @global	object	$user	Globales Class-Object mit den User-Methoden & Variablen
	 * @return	string			Returns HTML href-link formatted as Telegram readable User-IDs mention
	 */
	public function mentionUser($userid)
	{
		global $db, $user;

		if (isset($userid) && $userid > 0 && is_numeric($userid))
		{
			$sql = 'SELECT
						telegram_user_id tui
					FROM
						user
					WHERE
						telegram_user_id IS NOT NULL
						AND id = '.$userid.'
					LIMIT 1';
			$telegramUserIds = $db->fetch($db->query($sql, __FILE__, __LINE__, __METHOD__));
			$telegramUserId = $telegramUserIds['tui'];
			zorgDebugger::log()->debug('Found Telegram User ID "%d"', [$telegramUserId]);

			if (!empty($telegramUserId))
			{
				$username = $user->id2user($telegramUserId);
				$link = sprintf('<a href="tg://user?id=%d">%s</a>', $telegramUserId, $username);
				zorgDebugger::log()->debug('Returns HTML-link:', [$link]);
				return $telegramUserIds['tui'];
			} else {
				return false;
			}

		} else {
			error_log( t('invalid-userid', 'messagesystem') );
			return false;
		}
	}


	/**
	 * Cleanup Message for Telegram Messenger Notification
	 *
	 * @author	IneX
	 * @date	25.05.2018
	 * @version	3.0
	 * @since	1.0 initial function
	 * @since	2.0 line breaks are possible using encoded "\n" - won't strip those anymore. Added missing allowed `strong` & <em>.
	 * @since	3.0 22.10.2018 changed strip_html() to remove_html(), changed order of cleanup, removed valid HTML-Tags due to issue with nested tags
	 *
	 * @link https://core.telegram.org/bots/api#html-style
	 * @link https://stackoverflow.com/questions/31908527/php-telegram-bot-insert-line-break-to-text-message
	 * @link https://stackoverflow.com/questions/15433188/r-n-r-n-what-is-the-difference-between-them
	 * @param	string	$notificationText	Content welcher für die Telegram Nachricht vorgesehen ist
	 * @return	string						Returns formatted & cleaned up $notificationText as String
	 */
	public function formatText($notificationText)
	{
		zorgDebugger::log()->debug('Passed raw string: %s', [$notificationText]);

		/**
		 * Strip away all HTML-tags & unix line breaks
		 * Except from the whitelist:
		 * <b>, <strong>, <i>, <em>, <a>, <code>, <pre>
		 * -> However: "Tags must not be nested"!
		 */
		$notificationText = stripslashes($notificationText); // remove escaping slashes
		$notificationText = str_replace(array('&nbsp;', '  '), ' ', $notificationText); // spaces
		$notificationText = str_replace(array("\r\n", "\r\n ", "\r", "\r ", "\n "), "\n", $notificationText); // line-breaks
		$notificationText = remove_html($notificationText, '<b><strong><i><em><a><code><pre>'); // html-tags

		/**
		 * Cleanup nested HTML-Tags, e.g. <a ...><i>text</i></a>
		 * @link https://stackoverflow.com/a/47105562
		 */
		$dom = new DomDocument;
		$internalErrors = libxml_use_internal_errors(true); // evaporate XML warning
		$dom->loadHtml(mb_convert_encoding("<body>{$notificationText}</body>", 'HTML-ENTITIES', 'UTF-8'));
		$nodes = iterator_to_array($dom->getElementsByTagName('body')->item(0)->childNodes);
		$notificationText = implode(
			array_map(function($node) {
				$textContent = $node->nodeValue;
				if ($node->nodeName === '#text') {
					return $textContent;
				}
				$attr = implode(' ', array_map(function($attr) {
					return sprintf('%s="%s"', $attr->name, $attr->value);
				}, iterator_to_array($node->attributes)));

				return sprintf('<%1$s %3$s>%2$s</%1$s>', $node->nodeName, $textContent, $attr);
			}, $nodes)
		);

		/**
		 * Add missing Server address in HTML-Links inside Notification Text
		 */
		$notificationText = str_replace('href="/', 'href="' . SITE_URL . '/', $notificationText);
		$notificationText = str_replace('href="zorg.local/', 'href="' . SITE_URL . '/', $notificationText);
		$notificationText = str_replace('zorg.local', 'zorg.ch', $notificationText);

		/**
		 * Decode HTML-Entities
		 */
		$notificationText = html_entity_decode($notificationText);
		zorgDebugger::log()->debug('Processed string: %s', [$notificationText]);

		return ( !empty($notificationText) ? $notificationText : false );
	}


	/**
	 * Validate Data against Model for various Telegram Message Types
	 *
	 * Check for valid parameters and returns Array with key:value pairs assigned
	 * This function is related (but no depending!) to the following MySQL-table:
	 *		- Table: messages_telegram_queue
	 *			- Column: :method
	 *			- Column: :content
	 *			- Column: :content_additional
	 *
	 * @author	IneX
	 * @version	1.1
	 * @since	1.0 `10.06.2018` `IneX` Method added
	 * @since	1.1 `16.05.2021` `IneX` Added "sendPoll" method support
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
	 * @link https://core.telegram.org/bots/api#sendpoll
	 *
	 * @uses self::PARSE_MODE, self::DISABLE_WEB_PAGE_PREVIEW, self::DISABLE_NOTIFICATION
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
				,'sendPoll'			=>	[
										 'required' => [ 'chat_id', 'question', 'options' ],
										 'optional' => [ 'is_anonymous', 'type', 'allows_multiple_answers', 'correct_option_id' ]
										]
			];

		/** Validate $parameters */
		if (!is_array($parameters) || empty($parameters))
		{
			error_log(sprintf('[WARN] <%s:%d> passed Parameters are empty or invalid', __METHOD__, __LINE__));
			return false;
		}

		/** Check if $messageType matches any available $_telegramMessageModels */
		zorgDebugger::log()->debug('Checking array_key_exists in $_telegramMessageModels for "%s"', [$messageType]);
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
			zorgDebugger::log()->debug('Checking $parameters for presence of required parameter "%s"', [$_telegramMessageModels[$messageType]['required'][0]]);
			if ( !isset($parameters[$_telegramMessageModels[$messageType]['required'][0]]) )
			{
				error_log(sprintf('[WARN] <%s:%d> Value %s is required but was not passed!', __METHOD__, __LINE__, $_telegramMessageModels[$messageType]['required'][0]));
				return false;
			} else {
				if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> SUCCESS: required parameter "%s" found and is OK', __METHOD__, __LINE__, $_telegramMessageModels[$messageType]['required'][0]));
				zorgDebugger::log()->debug('SUCCESS: required parameter "%s" found and is OK', [$_telegramMessageModels[$messageType]['required'][0]]);

				/**
				 * Build the Data-Array with key:value pairs assigned
				 *
				 * Example:
				 *	$data = [
				 *  	'chat_id' => $chatId,
				 *		'parse_mode' => $telegramParseMode,
				 *		'text' => $notificationText,
				 *	];
				 */
				$data = [];
				zorgDebugger::log()->debug('Building $data Array for Function return');


				/** Assign key=>value pairs for Global Parameters */
				if (!empty($parameters['chat_id']))
				{
					$data['chat_id'] = $parameters['chat_id'];
				} else {
					error_log(sprintf('[WARN] <%s:%d> Value "%s" is required but was not passed!', __METHOD__, __LINE__, 'chat_id'));
					return false;
				}
				$data['parse_mode'] = ( isset($parameters['parse_mode']) ? $parameters['parse_mode'] : self::PARSE_MODE ); // TODO Replace with $_ENV['TELEGRAM_PARSE_MODE']
				$data['disable_web_page_preview'] = ( isset($parameters['disable_web_page_preview']) ? $parameters['disable_web_page_preview'] : self::DISABLE_WEB_PAGE_PREVIEW ); // TODO Replace with $_ENV['TELEGRAM_DISABLE_WEBPAGE_PREVIEW']
				$data['disable_notification'] = ( isset($parameters['disable_notification']) ? $parameters['disable_notification'] : self::DISABLE_NOTIFICATION ); // TODO Replace with $_ENV['TELEGRAM_DISABLE_NOTIFICATION']
				if ( isset($parameters['reply_to_message_id']) ) $data['reply_to_message_id'] = $parameters['reply_to_message_id'];
				if ( isset($parameters['reply_markup']) ) $data['reply_markup'] = $parameters['reply_markup'];

				/** Assign key=>value pairs for $messageType Required Parameters */
				foreach ((array) $_telegramMessageModels[$messageType]['required'] as $requiredParameter)
				{
					zorgDebugger::log()->debug('array_push to $data Array for key=>value pair "%s"', [$requiredParameter]);
					$data[$requiredParameter] = $this->formatText($parameters[$requiredParameter]);
				}

				/** Assign key=>value pairs for $messageType Optional Parameters */
				if (isset($_telegramMessageModels[$messageType]['optional']))
				{
					foreach ((array) $_telegramMessageModels[$messageType]['optional'] as $optionalParameter)
					{
						zorgDebugger::log()->debug('array_push to $data Array for key=>value pair "%s"', [$optionalParameter]);
						if (!empty($parameters[$optionalParameter])) $data[$optionalParameter] = $parameters[$optionalParameter];
					}
				}

				/** Return Data-Array with key:value pairs assigned */
				zorgDebugger::log()->debug('Complete $data Array: %s', [print_r($data, true)]);
				return $data;
			}

		} else {
			zorgDebugger::log()->warn('Telegram Message Type "%s" is invalid!', [$messageType]);
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
class send extends Telegram
{
	/**
	 * sendMessage
	 *
	 * Send as regular Chat-Message
	 */
	public function message($scope, $text, $parameters=[]) {
		return $this->send( $scope, 'sendMessage', array_merge(['text' => $text], $parameters) );
	}

	/**
	 * sendPhoto
	 *
	 * Send as Photo
	 */
	public function photo($scope, $photo, $caption=NULL, $parameters=[]) {
		return $this->send( $scope, 'sendPhoto', array_merge(['photo' => $photo], ['caption' => $caption], $parameters) );
	}

	/**
	 * sendMediaGroup
	 *
	 * Send a compilation of multiple Media files (e.g. Photo Gallery)
	 * @link https://core.telegram.org/bots/api/#inputmedia
	 */
	public function gallery($scope, array $inputMedia, $parameters=[]) {
		return $this->send( $scope, 'sendMediaGroup', array_merge($inputMedia, $parameters) );
	}

	/**
	 * sendDocument
	 *
	 * Send as File
	 */
	public function document($scope, $document, $caption=NULL, $parameters=[]) {
		return $this->send( $scope, 'sendDocument', array_merge(['document' => $document], ['caption' => $caption], $parameters) );
	}

	/**
	 * sendLocation
	 *
	 * Send a Location Ping for a temporary amount of time
	 */
	public function location($scope, float $latitude, float $longitude, $live_period=NULL, $parameters=[]) {
		return $this->send( $scope, 'sendLocation', array_merge(['latitude' => $latitude], ['longitude' => $longitude], ['live_period' => $live_period], $parameters) );
	}

	/**
	 * sendVenue
	 *
	 * Send a static Location info for a certain Place
	 */
	public function event($scope, float $latitude, float $longitude, $title, $address, $foursquare_id=NULL, $parameters=[]) {
		return $this->send( $scope, 'sendVenue', array_merge(['latitude' => $latitude], ['longitude' => $longitude], ['title' => $title], ['address' => $address], ['foursquare_id' => $foursquare_id], $parameters) );
	}

	/**
	 * sendPoll
	 *
	 * Send a native Telegram poll or quiz
	 *
	 * @TODO Stop Poll on close via chat_id using https://core.telegram.org/bots/api#stoppoll
	 */
	public function poll($scope, $question, $options, $is_anonymous=true, $type='regular', $allows_multiple_answers=false, $correct_option_id=null, $parameters=[]) {
		return $this->send( $scope, 'sendPoll', array_merge(
			 ['question' => $question] // 1-300 characters
			,['options' => $options] // JSON-serialized list of answer options, 2-10 strings 1-100 characters each
			,['is_anonymous' => ($is_anonymous ? 'true' : 'false')] // (Optional) True, if the poll needs to be anonymous, defaults to True
			,['type' => $type] // (Optional) Poll type, “quiz” or “regular”, defaults to “regular”
			,['allows_multiple_answers' => ($allows_multiple_answers ? 'true' : 'false')] // (Optional) True to allow multiple answers, ignored for quiz, default: False
			,['correct_option_id' => $correct_option_id] // (Optional / required for "quiz") 0-based identifier of the correct answer option
			,$parameters
		));
	}
}

/**
 * Instantiating new Telegram Class-Object
 * @TODO Fix this "dirty hack" with instantiated "$telegram->send"-object...
 */
$telegram = new Telegram();
$telegram->send = new send();
