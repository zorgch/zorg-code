<?php
/**
 * Messages
 * 
 * Nachrichtensystem für mobilezorg
 * 
 * @author IneX
 * @date 19.02.2010
 * @version 2.0
 * @package mobilezorg
 * @subpackage messagesystem
 * @see Messagesystem
 *
 * @global array $user Array mit allen Uservariablen
 * @global array $db Array mit allen MySQL-Datenbankvariablen
 */
/**
 * File Includes
 */
include_once($_SERVER['DOCUMENT_ROOT'].'/includes/main.inc.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/includes/messagesystem.inc.php');

global $user, $db;

if ($user->typ == USER_NICHTEINGELOGGT || !isset($user->typ)) { header('Location: login.php'); }

/**
 * Konstanten
 */
setlocale(LC_TIME,"de_CH");

//$newInboxMessages = Messagesystem::getNumNewMessages();

$searchFor = $_POST['searchFor'];
$markAllRead = $_POST['markAllRead'];

// Messages als gelesen markieren
if ($markAllRead != "")
{
	$sql = "UPDATE messages SET isread = '1' WHERE owner = ".$user->id." AND from_user_id = $markAllRead AND isread <> '1'";
	$db->query($sql, __FILE__, __LINE__);
}

// Query für Posteingang-Nachrichten
	// Suchresultate
if ($searchFor != "")
{
	$sql = "
	  	SELECT
	  		messages.*,
	  		CONCAT(user.clan_tag, user.username) AS from_user,
	  		UNIX_TIMESTAMP(date) as date
	  	FROM
	  		messages
			LEFT JOIN user ON (messages.from_user_id = user.id)
	  	WHERE
	  		messages.owner = ".$user->id."
	  		AND messages.from_user_id <> ".$user->id."
	  		AND (user.username LIKE '%$searchFor%' OR messages.subject LIKE '%$searchFor%' OR messages.text LIKE '%$searchFor%')
	  	ORDER BY date DESC
	  	LIMIT 0,23"
	;
}
	
	// Standard-Ansicht (Inbox)
else
{
	$sql = "
	  	SELECT
	  		messages.owner, messages.from_user_id, MIN(isread) AS unreads,
	  		CONCAT(user.clan_tag, user.username) AS from_user
	  	FROM
	  		messages
			LEFT JOIN user ON (messages.from_user_id = user.id)
	  	WHERE
	  		messages.owner = ".$user->id."
	  		AND messages.from_user_id <> ".$user->id."
	  	GROUP BY from_user
	  	ORDER BY
	  		unreads ASC,
			from_user ASC
	  ";
}
$result = $db->query($sql, __FILE__, __LINE__);
while ($rs = mysql_fetch_array($result)) {
	$inboxmessages[] = $rs;
}

// Maximale Anzahl Nachrichten ermitteln
if ($searchFor != "")
{
	$sql = "
		SELECT
			count(id) as maxMsgs
		FROM
			messages
		WHERE
			owner = ".$user->id."
			AND from_user_id <> ".$user->id."
			AND (messages.subject LIKE '%$searchFor%' OR messages.text LIKE '%$searchFor%')
		";
	
	$result = $db->query($sql, __FILE__, __LINE__);
	$rs = $db->fetch($result);
	$maxMsgs = $rs['maxMsgs'];
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
		<a class="button" href="#msgSearch">Suchen</a>
	</div>
	
	<!-- Messages -->
	<ul id="messages" title="Messages" selected="true">
		<li class="error"><h1>Noch in Bearbeitung...</h1></li>
		<?php /*<li><a class="linkLabel" href="#newMessage">Nachricht schreiben</a></li>*/
		foreach ((array) $inboxmessages as $n => $message) { ?>
		<li><a class="linkLabel" href="message_show.php?userID=<?php echo $message['from_user_id'] ?>"><?php echo $message['from_user'] ?><?php echo ($message['unreads'] == 0) ? '<span class="newItem"></span></li>' : '</li>'; ?></a>
		<?php } ?>
		<?php if ($maxMsgs > 23) { ?><li><a href="messages_inbox_more.php?prev=0<?php if ($searchFor != "") echo "&amp;searchFor=".$searchFor; ?>" target="_replace">Mehr...</a></li><?php } ?>
	</ul>
		
		<!-- New Message -->
		<form id="newMessage" class="dialog" action="messages.php" method="post">
			<fieldset>
				<h1>Neue Nachricht</h1>
				<a class="button leftButton" type="cancel">Cancel</a>
				<a class="button blueButton" type="submit">Senden</a>
				
				<input type="text" name="message"/>
			</fieldset>
		</form>
		
		<!-- Search Messages -->
		<form id="msgSearch" class="dialog" action="messages.php" method="post">
			<fieldset>
				<h1>Suche nach...</h1>
				<a class="button leftButton" type="cancel">Cancel</a>
				<a class="button blueButton" type="submit">Suchen</a>
				
				<input type="text" name="searchFor"/>
			</fieldset>
		</form>
</body>
</html>
