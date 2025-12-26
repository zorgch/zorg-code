<?php
ini_set( 'display_errors', true );
error_reporting(E_ALL);

require_once __DIR__.'/../../public/includes/telegrambot.inc.php';

$baselink = ERRORLOG_DIR . '/irc';
$channel = 'localhost';
$room = '#zooomclan';
//$date = '2018-01-12'; // Unused

$filename = "$baselink/$channel/$room.log";

$regex_link = '/(ftp|https?):\/\/(\w+:?\w*@)?(\S+)(:[0-9]+)?(\/([\w#!:.?+=&%@!\/-])?)?/';
$regex_nick = '/\<[ \+\@][^\>]+\>/';
$regex_timestamp = '/^[0-9]{2}:[0-9]{2}/';

$api_base_uri = $_ENV['TELEGRAM_BOT_API'];
//$api_token = $_ENV['TELEGRAM_BOT_API_KEY']; // Part of $api_base_uri
$chat_id = $_ENV['TELEGRAM_BOT_API_CHAT'];
$command = 'sendMessage';

$logfile = fopen($filename, "r") or die('logfile ' . $filename . ' could not open');
while(!feof($logfile)) {
	$line = fgets($logfile);

	//skip irssi notifications
	$pos = strpos($line, "-!- Irssi:");
	if($pos !== false) {
		continue;
	}
	$pos = strpos($line, "--- Log");
	if($pos !== false) {
		continue;
	}

	//define header containing timestamp and nick
	preg_match($regex_timestamp, $line, $matches);
	$timestamp = $matches[0];

	preg_match($regex_nick, $line, $matches, PREG_OFFSET_CAPTURE);
	$nick = $matches[0][0];
	$pos_start_content = $matches[0][1] + strlen($nick) + 1;

	//define content message
	$content = substr($line, $pos_start_content);

	//print output
	/*
	print '<span class="timestamp">' . htmlspecialchars($timestamp) . '</span> ';
	print '<span class="nick">' . htmlspecialchars($nick) . '</span> ';
	print htmlspecialchars($content);
	print "<br>";
	*/

	//telegram bot
	/*
		URL="https://api.telegram.org/bot$KEY/sendMessage"
		TEXT="Hello world"

		curl -s --max-time $TIME -d "chat_id=$CHATID&disable_web_page_preview=1&text=$TEXT" $URL >/dev/null
	*/

	$data = [
	    'text' => sprintf('%s %s %s', $timestamp, $nick, htmlspecialchars($content)),
	    'chat_id' => $chat_id
	];

	file_get_contents($api_base_uri . $command . '?' . http_build_query($data) );
}

fclose($logfile);
