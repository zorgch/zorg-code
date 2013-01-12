<?php
/**
 * Message Inbox (more)
 * 
 * Gibt die nächsten 10 persönlichen Nachrichten auf mobilezorg aus
 * 
 * @author IneX
 * @date 14.06.2009
 * @version 1.0
 * @package mobilezorg
 * @subpackage messagesystem
 *
 * @global array $user Globales Array mit allen Uservariablen
 * @global array $db Globales Array mit allen MySQL-Datenbankvariablen
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

$first = $_GET[prev]+10;
$searchFor = $_GET[searchFor];

//$newInboxMessages = Messagesystem::getNumNewMessages();


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
	  	LIMIT 0,10"
	  ;
}
	// Standard-Ansicht (Inbox)
else
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
	  	ORDER BY date DESC
	  	LIMIT $first,10"
	  ;
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
}
else
{
	$sql = "
		SELECT
			count(id) as maxMsgs
		FROM
			messages
		WHERE
			owner = ".$user->id."
			AND from_user_id <> ".$user->id;
}
$result = $db->query($sql, __FILE__, __LINE__);
$rs = $db->fetch($result);
$maxMsgs = $rs['maxMsgs'];

?>
		<?php foreach ((array) $inboxmessages as $n => $message) { ?>
		<li><a class="linkLabel" href="messages.php?message=<?php echo $message['id'] ?>"></a><small><?php echo $message['from_user'] ?> @ <?php echo strftime('%e. %B %Y %H:%M Uhr', $message['date']) ?></small><br/><?php echo $message['subject'] ?><?php echo ($message['isread'] == '0') ? '<a class="unread-count">O</a></li>' : '</li>'; ?>
		<?php } ?>
		<?php if ($maxMsgs > $first+10) { ?><li><a href="messages_inbox_more.php?prev=<?php echo $first ?><?php if ($searchFor != "") echo "&amp;searchFor=".$searchFor; ?>" target="_replace">Mehr...</a></li><?php } ?>