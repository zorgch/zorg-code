<?php
/**
 * Telegram Bot Configs - Example
 * for 'telegramexample_bot'
 *
 * @see /www/includes/telegrambot.inc.php
 */
$botconfigs = [
				 'api_key' 			=> '' // as provided by @BotFather
				,'my_secret'		=> '' // (string) A secret password required to authorise access to the webhook.
				,'valid_ips' 		=> [ // (array) When using `validate_request`, also allow these IPs.
									        //'1.2.3.4',         // single
									        //'192.168.1.0/24',  // CIDR
									        //'10/8',            // CIDR (short)
									        //'5.6.*',           // wildcard
									        //'1.1.1.1-2.2.2.2', // range
									        '*' // Any
									    ]
				,'admins'			=> [] // (array) An array of user ids that have admin access to your bot (must be integers).
				,'ssl_certificate' 	=> __DIR__ . '/server.crt' // (string) Path to a self-signed certificate (if necessary).
				,'logging_dirroot'	=> [ __DIR__ . '/data/errlog/telegramexample_bot' ] // (array) Paths where the log files should be put.
				,'files_dirroot'	=> [ __DIR__ . '/data/files/telegram/telegramexample_bot' ] // (array) List of configurable paths.
			];

if (!defined('TELEGRAM_API_URI')) define('TELEGRAM_API_URI', 'https://api.telegram.org/bot' . $botconfigs['api_key']);
if (!defined('TELEGRAM_GROUPCHAT_ID')) define('TELEGRAM_GROUPCHAT_ID', ''); // Telegram-Group Chat-ID to post generic messages to
