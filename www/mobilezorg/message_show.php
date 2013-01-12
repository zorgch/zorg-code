<?php
/**
 * Message Anzeigen
 * 
 * Zeigt eine bestimmte Nachricht für mobilezorg an
 * 
 * @author IneX
 * @date 14.06.2009
 * @version 1.0
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


$msgFromUser = $_GET[userID];

// Query für Nachricht
if ($msgFromUser != "")
{
	$sql = "
	  	SELECT
	  		messages.id, messages.from_user_id, messages.owner, messages.subject, messages.text,
	  		CONCAT(user.clan_tag, user.username) AS from_user,
	  		UNIX_TIMESTAMP(date) as date
	  	FROM
	  		messages
			LEFT JOIN user ON (messages.from_user_id = user.id)
	  	WHERE
	  		(messages.from_user_id = $msgFromUser
	  		AND messages.owner = $user->id)
	  		OR (messages.from_user_id = $user->id
	  		AND messages.owner = $msgFromUser)
	  	ORDER BY date DESC
	  	LIMIT 0,23"
	  ;
	$result = $db->query($sql, __FILE__, __LINE__);
	while ($rs = mysql_fetch_array($result)) {
		$messages[] = $rs;
	}
}

?>
	<!-- Message -->
	<div id="showMessage" class="panel" title="<?php echo $message['from_user'] ?>" selected="true">
		<form action="messages.php" method="post" target="_self">
			<input type="hidden" name="markAllRead" value="<?php echo $msgFromUser ?>" />
			<a class="whiteButton" name="markReadButton" type="SUBMIT" href="#" onclick="return confirm('Diese Nachrichten wirklich als gelesen markieren?');">Als gelesen markieren</a>
		</form>
		<?php foreach ((array) $messages as $n => $message) { ?>
		<div class="message" id="<?php echo $message['id'] ?>">
			<div class="messageDate"><?php echo date("d.m.Y H:i", $message['date']) ?></div>
			<div class="message<?php echo ($message['owner'] == $user->id) ? 'In' : 'Out'; ?>Top"></div>
			<div class="message<?php echo ($message['owner'] == $user->id) ? 'In' : 'Out'; ?>Middle">
				<span class="msgSubject"><?php echo $message['subject'] ?></span>
				<?php echo $message['text'] ?></div>
			<div class="message<?php echo ($message['owner'] == $user->id) ? 'In' : 'Out'; ?>Bottom"></div>
		</div><?php } ?>
	</div>
	