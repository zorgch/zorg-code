<?php
/**
* Chat
* 
* Stellt den Chat für mobilezorg dar und erlaubt das hinzufügen von neuen Nachrichten
* 
* @author IneX
* @version 1.0
* @package mobilezorg
* @subpackage chat
*
* @global array $user Globales Array mit allen Uservariablen
* @global array $db Globales Array mit allen MySQL-Datenbankvariablen
*/

include_once($_SERVER['DOCUMENT_ROOT'].'/includes/main.inc.php');

global $user, $db;

if ($user->typ == USER_NICHTEINGELOGGT || !isset($user->typ)) { header('Location: login.php'); }


setlocale(LC_TIME,"de_CH");

// Post new Message
if ($_POST['message'] <> '')
{
	$sql = "INSERT INTO chat (user_id, date, from_mobile, text) VALUES ($user->id, now(), 1, '".$_POST['message']."')";
	$db->query($sql, __FILE__, __LINE__);
}

// Query for existing Messages
$chatmessages = array();

$sql =
	"
	SELECT
		chat.text
		, UNIX_TIMESTAMP(date) AS date
		, user.username AS username
		, user.clan_tag AS clantag
		, chat.user_id
		, chat.from_mobile
	FROM chat
	LEFT JOIN user ON (chat.user_id = user.id)
	ORDER BY date DESC
	LIMIT 23
	"
;
$result = $db->query($sql, __FILE__, __LINE__);
while ($rs = $db->fetch($result)) {
	$chatmessages[] = $rs;
}

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
 		"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>mobile@zorg</title>
<meta name="viewport" content="width=320; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;"/>
<style type="text/css" media="screen">@import "iui/iui.css";</style>
<script type="application/x-javascript" src="iui/iui.js"></script>
<!--
<script type="application/x-javascript" src="http://10.0.1.2:1840/ibug.js"></script>
-->
</head>

<body onclick="console.log('Hello', event.target);">
	<div class="toolbar">
		<h1 id="pageTitle"></h1>
		<a id="forceBackButton" class="button" href="index.php" target="_self">Zorg</a>
		<a class="button" href="#newMessage">Schreiben</a>
	</div>
	
	<!-- CHAT -->
	<ul id="chat" title="Chat" selected="true">
	<?php foreach ((array) $chatmessages as $n => $message) { ?>
		<li><small><?php echo $message['username']; ?> <?php echo($message['from_mobile'] == 1 ? "<img src=\"/images/mobile15x11px.gif\" border=\"none\" width=15 heigh=11 alt=\"von unterwegs geschrieben\">" : "") ?> @ <?php echo strftime('%e. %B %Y %H:%M Uhr', $message['date']) ?></small><br/><?php echo $message['text'] ?></li>
	<?php } ?>
	</ul>
		
		<!-- Chat new Message -->
		<form id="newMessage" class="dialog" action="chat.php" method="post">
			<fieldset>
				<h1>Chat Nachricht</h1>
				<a class="button leftButton" type="cancel">Cancel</a>
				<a class="button blueButton" type="submit">Senden</a>
				
				<!-- input type="hidden" name="url" value="aHR0cDovL3pvcmcuY2gvbW9iaWxlem9yZy9jaGF0LnBocCNfaG9tZQ=="/ -->
				<!-- Base64 Encoded URL: http://zorg.ch/mobilezorg/chat.php#_home -->
				
				<input type="text" name="message"/>
			</fieldset>
		</form>
</body>
</html>
