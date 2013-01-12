<?php
/**
 * mobileZorg Home
 * 
 * Home-Screen von mobilezorg mit 1st Level Menü
 * 
 * @author IneX
 * @version 2.0
 * @package mobilezorg
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
define(USER_TIMEOUT, 200);

$unreadChats = array();
$unreadComments = array();
$onlineUsers = array();
$openAddle = array();
$todayEvents = array();
$unreadMessages = 0;


/**
 * Ungelesene Chat-Nachrichten
 * 
 * Zählt wieviele neue Chat-Nachrichten vorhanden sind
 * 
 * @author IneX
 * @version 1.0
 * @package mobilezorg
 * @subpackage chat
 *
 * @global array $user Globales Array mit allen Uservariablen
 * @global array $db Globales Array mit allen MySQL-Datenbankvariablen
 * @global array $unreadChats Array mit Anzahl der neuen Chat-Nachrichten
 * @return array
 */
function unreadChats()
{

	global $user, $db, $unreadChats;
	
	if ($user->typ != USER_NICHTEINGELOGGT) {
		
		if (isset($user->lastlogin)) {
			
			//$lastlogin_unixdate = date("Y-m-d H:i:s", $user->lastlogin);
			$lastlogin_unixdate = date("Ymd", $user->lastlogin);
			
			$sql =
				"
				SELECT
					date
				FROM
					chat
				WHERE
					DATE_FORMAT(date, '%Y%m%e') > '".$lastlogin_unixdate."'
				";
			
			$result = $db->query($sql, __FILE__, __LINE__);
			
			return $unreadChats = mysql_num_rows($result);
			
		}
		
	}
}


/**
 * Ungelesene Comments
 * 
 * Zählt wieviele ungelesene Comments vorhanden sind
 * 
 * @author IneX
 * @version 1.0
 * @package mobilezorg
 * @subpackage commenting
 *
 * @param integer $user_id ID des Benutzers, für welchen die ungelesenen Comments gezählt werden sollen
 * @global array $user Globales Array mit allen Uservariablen
 * @global array $db Globales Array mit allen MySQL-Datenbankvariablen
 * @global array $unreadComments Array mit Anzahl der ungelesenen Comments
 * @return array
 */
function unreadComments($user_id)
{

	global $user, $db, $unreadComments;
	
	if ($user->typ != USER_NICHTEINGELOGGT) {
		
		$sql =
			"
			SELECT
				*
			FROM
				comments_unread
			WHERE
				user_id = '".$user_id."'"
			;
		
		$result = $db->query($sql, __FILE__, __LINE__);
		
		return $unreadComments = mysql_num_rows($result);
		
	}
	
	// Original Unread Counter
	/*if($user->typ != USER_NICHTEINGELOGGT) {
	  $sql = "SELECT count(*) as numunread from comments_unread where user_id='".$user_id."'";
	  $rs = $db->fetch($db->query($sql, __FILE__, __LINE__));
	  return $rs['numunread'];
	}*/
}


/**
 * Anzahl Benutzer gerade online
 * 
 * Zählt wieviele Benutzer gerade online sind
 * 
 * @author IneX
 * @version 1.0
 * @package mobilezorg
 * @subpackage users
 *
 * @global array $user Globales Array mit allen Uservariablen
 * @global array $db Globales Array mit allen MySQL-Datenbankvariablen
 * @global array $onlineUsers Array mit Anzahl der gerade aktiven Benutzer
 * @return array
 */
function onlineUsers()
{

	global $user, $db, $onlineUsers;
	
	$sql = "
		SELECT
			id, username, clan_tag
		FROM
			user 
		WHERE
			UNIX_TIMESTAMP(activity) > (UNIX_TIMESTAMP(NOW()) - ".USER_TIMEOUT.")
		ORDER BY
			activity DESC
		";
	
	$result = $db->query($sql, __FILE__, __LINE__);
	
	return $onlineUsers = mysql_num_rows($result);
	
}


/**
 * Offene Addle Spiele
 * 
 * Zählt wieviele offene Addle Spiele vorhanden sind
 * 
 * @author IneX
 * @version 1.0
 * @package mobilezorg
 * @subpackage addle
 *
 * @param integer $user_id ID des Benutzers, für welchen die offenen Addle Spiele gezählt werden sollen
 * @global array $user Globales Array mit allen Uservariablen
 * @global array $db Globales Array mit allen MySQL-Datenbankvariablen
 * @global array $openAddle Array mit Anzahl der offenen Addle Spiele
 * @return array
 */
function openAddle($user_id)
{

	global $user, $db, $openAddle;
	
	if(isset($user_id)) {
		// Spieler am zug (nexttur) ist aktueller User und spiel ist nicht fertig
		$sql = "select id from addle where ( (player1 = $user_id and nextturn = 1) or ( player2 = $user_id and nextturn = 2) ) and finish = 0";
		$result = $db->query($sql, __FILE__, __LINE__);
		return $openAddle = $db->num($result);
	}
}


/**
 * Anzahl heutiger Events
 * 
 * Zählt wieviele Events heute stattfinden
 * 
 * @author IneX
 * @version 1.0
 * @package mobilezorg
 * @subpackage events
 *
 * @global array $user Globales Array mit allen Uservariablen
 * @global array $db Globales Array mit allen MySQL-Datenbankvariablen
 * @global array $todayEvents Array mit Anzahl der heute stattfindenden Events
 * @return array
 */
function todayEvents()
{

	global $user, $db, $todayEvents;
	
	$today = date('Ymd', time());
	
	//$sql = "SELECT UNIX_TIMESTAMP(startdate) AS startdate FROM events WHERE DATE_FORMAT(startdate, '%Y%d%e') = '$today'";
	$sql = "SELECT UNIX_TIMESTAMP(startdate) AS startdate FROM events WHERE DATE_FORMAT(startdate, '%Y%m%e') = '$today'"; 
	$result = $db->query($sql, __FILE__, __LINE__);
	
	return $todayEvents = mysql_num_rows($result);
}


/**
 * Ungelesene Nachrichten
 * 
 * Zählt wieviele ungelesene persönliche Nachrichten vorhanden sind
 * 
 * @author IneX
 * @version 1.0
 * @package mobilezorg
 * @subpackage messagesystem
 *
 * @param integer $user_id ID des Benutzers, für welchen die ungelesenen Nachrichten gezählt werden sollen
 * @global array $user Globales Array mit allen Uservariablen
 * @global array $db Globales Array mit allen MySQL-Datenbankvariablen
 * @global array $unreadMessages Array mit Anzahl der ungelesenen Nachrichten
 * @return array
 *
 * @DEPRECATED
 */
function unreadMessages($user_id)
{

	global $user, $db, $unreadMessages;

	if (isset($user_id)) {
		$sql = "SELECT count(id) as num FROM messages WHERE (owner = $user_id) AND (isread = '0')";
		$result = $db->query($sql, __FILE__, __LINE__);
	  	$rs = $db->fetch($result);

		return $unreadMessages = $rs['num'];
		//return $unreadMessages = $db->num($result);
	}
}


unreadChats();
unreadComments($user->id);
onlineUsers();
openAddle($user->id);
todayEvents();
unreadMessages($user->id);


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
		<a id="backButton" class="button" href="#"></a>
		<?php echo ($user->username <> '') ? '<a id="rightButton" class="button" href="profile.php" onclick="document.getElementById(\'addle\').click();">mein Profil</a>' : ''; ?>
	</div>
	
	
<!-- HOME -->
	<ul id="home" title="Zorg" selected="true">
		<?php if ($_GET['error'] <> '') echo "<li class=\"error\"><h1>$_GET[error]</h1></li>"; ?>
		<li><a class="linkLabel" href="chat.php" target="_self">Chat</a>
			<?php echo ($unreadChats > 0) ? '<span class="newItemIndicator">'.$unreadChats.'</span>' : ''; ?></li>
		<li><a class="linkLabel" href="events.php" target="_self">Events</a>
			<?php echo ($todayEvents > 0) ? '<span class="newItemIndicator">'.$todayEvents.'</span>' : ''; ?></li>
		<!-- li><a class="linkLabel" href="#gallery">Gallery</a></li -->
		<li><a class="linkLabel" href="#forum">Forum</a>
			<?php echo ($unreadComments > 0) ? '<span class="newItemIndicator">'.$unreadComments.'</span>' : ''; ?></li>
		<li><a class="linkLabel" href="#games">Games</a>
			<?php echo ($openAddle > 0) ? '<span class="newItemIndicator">'.$openAddle.'</span>' : ''; ?></li>
		<li><a class="linkLabel" href="#users">Users</a>
			<?php echo ($onlineUsers > 0) ? '<span class="newItemIndicator">'.$onlineUsers.'</span>' : ''; ?></li>
		<!-- li><form action="../smarty.php" method="post" redirect="true"><a class="linklabel" name="logout" value="true" type="submit" href="#" target="_self">"<?php echo $user->username; ?>" abmelden</a></form></li -->
		<li><a class="linkLabel" href="messages.php" target="_self">Messages</a>
			<?php echo ($unreadMessages > 0) ? '<span class="newItemIndicator">'.$unreadMessages.'</span>' : ''; ?></li>
		<li><a class="linkLabel" href="#community">Community</a></li>
	</ul>
	
	
	<!-- Forum -->
	<ul id="forum" title="Forum">
		<li><a class="linklabel" href="forum_unread.php?numUnreads=<?=$unreadComments?>">Ungelesen</a>
			<?php echo ($unreadComments > 0) ? '<span class="newItemIndicator">'.$unreadComments.'</span>' : ''; ?></li>
		<li><a class="linklabel" href="forum_newest.php">Neuste Threads</a></li>
		<li><a class="linklabel" href="#">Alle Threads</a>
		<li><a class="linklabel" href="forum_favorites.php">Markierte Kommentare</a>
	</ul>
	
	
	<!-- Games -->
	<ul id="games" title="Games">
		<li><a class="linklabel" href="addle.php" target="_self">Addle</a>
			<?php echo ($openAddle > 0) ? '<span class="newItemIndicator">'.$openAddle.'</span>' : ''; ?></li>
	</ul>
			
	
	<!-- Users -->
	<ul id="users" title="Users">
		<li><a class="linkLabel" href="users_online.php">Online</a>
			<?php echo ($onlineUsers > 0) ? '<span class="newItemIndicator">'.$onlineUsers.'</span>' : ''; ?></li>
		<li><a class="linkLabel" href="users_activetoday.php">Heute aktiv gewesen</a></li>
		<li><a class="linkLabel" href="userlist.php">Alle Benutzer</a></li>
	</ul>
	
	
	<!-- Community -->
	<ul id="community" title="Community">
		<li><a class="linkLabel" href="verein.php">Zorg Verein</a></li>
		<li><a class="linkLabel" href="#blogs">Blogs</a></li>
		<li><a class="linkLabel" href="http://www.facebook.com/pages/Saint-Gallen-Switzerland/zorgch/27011066579" target="_self" onclick="return confirm('mobile@zorg verlassen?');">Zorg @ Facebook</a></li>
	</ul>
		
		<!-- Blogs -->
		<ul id="blogs" title="Blogs">
			<li><a href="http://7-weeks-in-nz.blogspot.com/" target="_self" onclick="return confirm('mobile@zorg verlassen?');">7 weeks in NZ</a></li>
			<li><a href="http://www.cedi.ch/" target="_self" onclick="return confirm('mobile@zorg verlassen?');">cedi.ch</a></li>
			<li><a href="http://www.die-republik.ch/" target="_self" onclick="return confirm('mobile@zorg verlassen?');">Die Republik</a></li>
			<li><a href="http://www.evesnewyear.com/" target="_self" onclick="return confirm('mobile@zorg verlassen?');">Eves' New Year</a></li>
			<li><a href="http://www.fnord.ch/blog" target="_self" onclick="return confirm('mobile@zorg verlassen?');">fnord.ch</a></li>
			<li><a href="http://www.mättä.ch/blog" target="_self" onclick="return confirm('mobile@zorg verlassen?');">heizen oder nicht heizen</a></li>
			<li><a href="http://mike.unserland.ch/" target="_self" onclick="return confirm('mobile@zorg verlassen?');">Just a Blog</a></li>
			<li><a href="http://projectdream.org/wordpress/" target="_self" onclick="return confirm('mobile@zorg verlassen?');">Lukas Beeler's IT Blog</a></li>
			<li><a href="http://www.manaia.ch/" target="_self" onclick="return confirm('mobile@zorg verlassen?');">Manaia</a></li>
			<li><a href="http://raduner.ch/blog" target="_self" onclick="return confirm('mobile@zorg verlassen?');" target="_self" onclick="return confirm('mobile@zorg verlassen?');">oliver@blogging</a></li>
			<li><a href="http://www.onewayticket.ch/" target="_self" onclick="return confirm('mobile@zorg verlassen?');">OneWayTicket</a></li>
			<li><a href="http://www.ponyfleisch.ch/" target="_self" onclick="return confirm('mobile@zorg verlassen?');" target="_self" onclick="return confirm('mobile@zorg verlassen?');">ponyfleisch</a></li>
			<li><a href="http://www.zeller.cx/" target="_self" onclick="return confirm('mobile@zorg verlassen?');">RTW2007-2008</a></li>
		</ul>

</body>
</html>
