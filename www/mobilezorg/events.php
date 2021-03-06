<?php
/**
* Forum Threads
* 
* Gibt eine Liste mit Events aus, nach verschiedenen Detailstufen Jahr|Monat|Tag|Heute
* 
* @author IneX
* @version 1.0
* @package mobilezorg
* @subpackage events
*
* @global array $user Globales Array mit allen Uservariablen
* @global array $db Globales Array mit allen MySQL-Datenbankvariablen
*/

include_once($_SERVER['DOCUMENT_ROOT'].'/includes/main.inc.php');

global $user, $db;

if ($user->typ == USER_NICHTEINGELOGGT || !isset($user->typ)) { header('Location: login.php'); }


setlocale(LC_TIME,"de_CH");

$html = '';
$currMth = 13;
$currMthGroupOut = false;

$years = array();
$events = array();
$event = array();
$numTodayEvents = '';
$visitors = array();
$participants = array();
$year = $_GET['year'];
$event_id = $_GET['event_id'];

/**
* Event-Jahre auslesen
* 
* Ermittelt aus allen Events die benutzen Jahre und listet diese auf
* 
* @author IneX
* @version 1.0
* @package mobilezorg
* @subpackage events
*
* @global array $db Globales Array mit allen MySQL-Datenbankvariablen
* @global array $years Globales Array mit allen ermittelten Jahren
*/
function fetchYears()
{
	global $db, $years;
	
	// Fetch for today events --> will be displayed on top of the list
	numTodayEvents();
	
	$sql =	
		"
		SELECT
			DATE_FORMAT(startdate, '%Y') as year
		FROM 
			events
		GROUP BY
			year
		ORDER BY
			year DESC
		"; 
	 
	$result = $db->query($sql, __FILE__, __LINE__);
	
	while($rs = $db->fetch($result)) {
		array_push($years, $rs['year']);
	}
	
	return $years;
}


/**
* Anzahl heutiger Events
* 
* Zählt die Events welche heute stattfinden
* 
* @author IneX
* @version 1.0
* @package mobilezorg
* @subpackage events
*
* @global array $db Globales Array mit allen MySQL-Datenbankvariablen
* @global array $numTodayEvents Array mit Anzahl der heute stattfindenden Events
* @return array
*/
function numTodayEvents()
{

	global $db, $numTodayEvents;
	
	$today = date('Ymd', time());
	
	$sql = "SELECT UNIX_TIMESTAMP(startdate) AS startdate FROM events WHERE DATE_FORMAT(startdate, '%Y%m%e') = '$today'"; 
	$result = $db->query($sql, __FILE__, __LINE__);
	
	return $numTodayEvents = mysql_num_rows($result);
}


/**
* Events heute
* 
* Gibt die Events aus, welche heute stattfinden
* 
* @author IneX
* @version 1.0
* @package mobilezorg
* @subpackage events
*
* @global array $db Globales Array mit allen MySQL-Datenbankvariablen
* @global array $events Array-Packet mit Name, Start- & Enddatum aller heute stattfindenden Events
* @return array
*/
function fetchTodayEvents()
{

	global $db, $events;
	
	$today = date('Ymd', time());
	
	$sql =	
		"
		SELECT
			id
			, name
			, UNIX_TIMESTAMP(startdate) AS startdate
			, UNIX_TIMESTAMP(enddate) AS enddate
			, UNIX_TIMESTAMP(reportedon_date) AS reportedon_date
		FROM 
			events
		WHERE
			DATE_FORMAT(startdate, '%Y%m%e') = '$today'
		ORDER BY
			startdate DESC
			, enddate DESC
		"
	; 
	 
	$result = $db->query($sql, __FILE__, __LINE__);
	
	while($rs = $db->fetch($result)) {
		array_push($events, $rs);
	}
	
	return $events;
}


/**
* Events dieses Jahr
* 
* Gibt die Events aus, welche heute stattfinden
* 
* @author IneX
* @version 1.0
* @package mobilezorg
* @subpackage events
*
* @param integer $year Das Jahr, von welchem alle Events ausgelesen werden sollen
* @global array $db Globales Array mit allen MySQL-Datenbankvariablen
* @global array $events Array-Packet mit Name, Start- & Enddatum aller Events dieses Jahres
* @return array
*/
function fetchYearEvents($year)
{

	global $db, $events;
	
	$sql =	
		"
		SELECT
			id
			, name
			, UNIX_TIMESTAMP(startdate) AS startdate
			, UNIX_TIMESTAMP(enddate) AS enddate
			, UNIX_TIMESTAMP(reportedon_date) AS reportedon_date
		FROM 
			events
		WHERE
			DATE_FORMAT(startdate, '%Y') = '$year'
		ORDER BY
			startdate DESC
			, enddate DESC
		"
	; 
	 
	$result = $db->query($sql, __FILE__, __LINE__);
	
	while($rs = $db->fetch($result)) {
		array_push($events, $rs);
	}
	
	return $events;
}


/**
* Event Details
* 
* Holt die Details eines Events
* 
* @author IneX
* @version 1.0
* @package mobilezorg
* @subpackage events
*
* @param integer $event_id ID des Events, für welchen die Details ausgegeben werden sollen
* @global array $db Globales Array mit allen MySQL-Datenbankvariablen
* @global array $event Array-Packet mit Name, Start- & Enddatum des angeforderten Events
* @return array
*/
function fetchEventDetails($event_id)
{

	global $db, $event;

	$sql =	
		"
		SELECT
			*
			, UNIX_TIMESTAMP(startdate) AS startdate
			, UNIX_TIMESTAMP(enddate) AS enddate
			, UNIX_TIMESTAMP(reportedon_date) AS reportedon_date
		FROM 
			events
		WHERE
			id = ".$event_id."
		"
	; 
	 
	$result = $db->query($sql, __FILE__, __LINE__);
	$event = $db->fetch($result);
	
	return $event;
}


/**
* Event Teilnehmer
* 
* Holt die Teilnehmer eines Events
* 
* @author IneX
* @version 1.0
* @package mobilezorg
* @subpackage events
*
* @param integer $event_id ID des Events, für welchen die Teilnehmer ermittelt werden sollen
* @global array $db Globales Array mit allen MySQL-Datenbankvariablen
* @global array $visitors Array mit allen User-IDs der Event-Teilnehmer
* @global array $participants Array mit Benutzernamen der Event-Teilnehmer
* @return array
*/
function fetchParticipants($event_id)
{
	
	global $db, $visitors, $participants;
	
	$sql = 
		"
		SELECT
			*
		FROM
			events_to_user e
		WHERE
			e.event_id = ".$event_id." 
		"
	;
	
	$result = $db->query($sql, __FILE__, __LINE__);
	
	while ($rs = $db->fetch($result)) {
		array_push($visitors, $rs);
	}
	
	foreach ($visitors as $participant) { array_push($participants, usersystem::id2user($participant['user_id'],true)); }
	// die Userpage-Links verreisen die Ansicht...: foreach ($visitors as $participant) { array_push($participants, usersystem::link_userpage($participant['user_id'],false)); }
	
	return $participants;
}


/**
* User ist bereits Teilnehmer
* 
* Prüft, ob ein Benutzer einen Event bereits gejoined ist (=Teilnehmer)
* 
* @author IneX
* @version 1.0
* @package mobilezorg
* @subpackage events
*
* @param integer $user_id ID des Benutzers, für welchen geprüft werden soll, ob er im Event bereits als Teilnehmer hinterlegt ist
* @param integer $event_id ID des Events, für welchen der User überprüft werden soll
* @global array $db Globales Array mit allen MySQL-Datenbankvariablen
* @return array
*/
function hasJoined($user_id, $event_id)
{
	global $db;
	
	$sql = 
		"
		SELECT 
		* 
		FROM events_to_user 
		WHERE user_id = ".$user_id."
		AND event_id = ".$event_id
	;
	$result = $db->query($sql, __FILE__, __LINE__);
	
	return $db->fetch($result);
}


/**
* User einem Event als Teilnehmer joinen
* 
* Fügt eine Benutzer-ID einem Event hinzu, um ihn als Teilnehmer zu hinterlegen
* 
* @author IneX
* @version 1.0
* @package mobilezorg
* @subpackage events
*
* @param integer $user_id ID des Benutzers, welcher dem Event als Teilnehmer hinzugefügt werden soll
* @param integer $event_id ID des Events, für welchen der User hinzugefügt werden soll
* @global array $db Globales Array mit allen MySQL-Datenbankvariablen
* @global array $user Globales Array mit allen Uservariablen
*/
function joinEvent($user_id, $event_id) { // User besucht Event
	global $user, $db;
	$sql = "Insert into events_to_user values($user->id, ".$_GET['join'].")";
	$db->query($sql,__FILE__, __LINE__);
}


/**
* User einem Event als Teilnehmer UNjoinen
* 
* Entfernt eine Benutzer-ID einem Event, welchem er bisher als Teilnehmer hinterlegt war
* 
* @author IneX
* @version 1.0
* @package mobilezorg
* @subpackage events
*
* @param integer $user_id ID des Benutzers, welcher dem Event als Teilnehmer entfernt werden soll
* @param integer $event_id ID des Events, für welchen der User entfernt werden soll
* @global array $db Globales Array mit allen MySQL-Datenbankvariablen
* @global array $user Globales Array mit allen Uservariablen
*/
function unjoinEvent($user_id, $event_id) { // User besucht Event nicht mehr
	global $user, $db;
	$sql = "delete from events_to_user where user_id = $user->id and event_id = ".$_GET['unjoin'];
	$db->query($sql,__FILE__, __LINE__);
}


// forceBackButton Builder
if ($year > 0) {
	if ($event_id > 0) {
		$backURL = "events.php?year=$year";
		$backTitle = $year;
	} else {
		$backURL = "events.php";
		$backTitle = "Events";
	}
} else {
	$backURL = "index.php";
	$backTitle = "Zorg";
}


// Button Builder
if ($year > 0) {
	if ($event_id > 0) {
		$buttonURL = "events.php?event_id=$event_id&amp;edit=true";
		$buttonTitle = "Edit";
	} else {
		$buttonURL = "events.php?year=$year";
		$buttonTitle = "Erstellen";
	}
} else {
	$buttonURL = "events.php";
	$buttonTitle = "Erstellen";
}


isset($_GET['join']) ? joinEvent($user->id, $_GET['join']) : '' ;
isset($_GET['unjoin']) ? unjoinEvent($user->id, $_GET['unjoin']) : '' ;


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
<script type="text/javascript">
function toggleButton(state)
{
	<?php /*) (hasJoined($user->id, $event_id)) ? "document.getElementByName('buttonTeilnehmen').setAttribute('toggled', document.getElementByName('buttonTeilnehmen').getAttribute('toggled') != 'true');" : "" ; */ ?>
	document.getElementByName('buttonTeilnehmen').setAttribute(state, document.getElementByName('buttonTeilnehmen').getAttribute('toggled') != 'true');
	
}
</script>
</head>

<body onclick="console.log('Hello', event.target);" onload="javascript:toggleButton('toggled');">
	<div class="toolbar">
		<h1 id="pageTitle"></h1>
		<!-- a class="button" href="<?php echo $buttonURL ?>" target="_self"><?php echo $buttonTitle ?></a -->
		<a id="forceBackButton" class="button" href="<?php echo $backURL ?>" target="_self"><?php echo $backTitle ?></a>
	</div>
	
	<!-- EVENTS -->
	<ul id="events" title="Events" selected="true">
		<?php if ($_GET['error'] <> '') echo "<li class=\"error\"><h1>$_GET[error]</h1></li>"; ?>
	<?php // ...by YEAR
	if (!$event_id)
	{
	
		if (!$year)
		{
			
			fetchYears();
			
			if ($numTodayEvents > 0) $html .= '<li><a href="events.php?year=heute" target="_self">Heute</a><span class="newItemIndicator">'.$numTodayEvents.'</span></li>';
			
			foreach ($years as $year) {
				$html .= "<li><a href=\"events.php?year=$year\" target=\"_self\">$year</a></li>";
			}
			
			echo $html;
		
		}
		
		else
		
		{
		
			if ($year == 'heute') {
			
				fetchTodayEvents();
				
				foreach ($events as $n => $event)
				{
				
					// Idea: It would probably make sense to Group the Events by location!
					
					$html .= "<li><small>";
					$html .= date('H:i', $event['startdate'])." bis ".date('H:i', $event['enddate'])." Uhr";
					$html .= "</small><br/>";
					$html .= "<a href=\"events.php?year=".date('Y', $event['startdate'])."&amp;event_id=".$event['id']."\" target=\"_self\">".$event['name']."</a></li>";
					
				}
				
				echo $html;
			
			}
			
			else
			
			{
			
				fetchYearEvents($year);
				
				foreach ($events as $n => $event)
				{
					
					// if Events are sorted DESCENDING (December on top, Jan on bottom)
					if ($currMth > date('n', $event['startdate'])) {
						$currMth = date('n', $event['startdate']);
						$html .= "<li class=\"group\">".strftime('%B', $event['startdate'])."</li>";
					}
					
					// if Events are sorted ASCENDING (January on top, Dec on bottom)
					/*if ($currMth < date('n', $event['startdate'])) { $currMth = date('n', $event['startdate']); $currMthGroupOut = false; }
					
					if ($currMth == date('n', $event['startdate']) && $currMthGroupOut == false) {
						$html .= "<li class=\"group\">".strftime('%B', $event['startdate'])."</li>";
						$currMthGroupOut = true;
					}*/
					
					$html .= "<li><small>";
					$html .= (strftime('%d', $event['startdate']) == strftime('%d', $event['enddate'])) ? strftime('%e. %B %Y', $event['enddate']) : strftime('%e. %B', $event['startdate'])." - ".strftime('%e. %B %Y', $event['enddate']);
					$html .= "</small><br/>";
					$html .= "<a href=\"events.php?year=$year&amp;event_id=".$event['id']."\" target=\"_self\">".$event['name']."</a></li>";
					
				}
				
				echo $html;
				
			}
			
		}
		
	} else {
	
		fetchEventDetails($event_id);
		
		fetchParticipants($event_id);
		$parts_out = implode(', ', $participants);
		
		$html .= ($event['link'] == "http://" || $event['link'] == "") ? "<li>".$event['name']."</li>" : "<li><a href=\"".$event['link']."\" target=\"_self\">".$event['name']."</a></li>";
		$html .= "<li><small>Datum</small><br/>".date("d.m.Y H:i", $event['startdate'])."<br/> - ".date("d.m.Y H:i", $event['enddate'])."</li>";
		$html .= "<li><small>Ort</small><br/>".$event['location']."</li>";
		$html .= "<li><small>Anwesend</small><br/>".$parts_out."</li>";
		$html .= "<li><small>Beschreibung</small><br/>".$event['description']."</li>";
		$html .= "<li><a href=\"event_comments.php\">Kommentare</a></li>";
		$html .=  ($event['gallery_id'] > 0) ? "<li><a href=\"http://zorg.ch/gallery.php?show=albumThumbs&albID=".$event['gallery_id']."\" target=\"_self\">Gallery (Web View)</a></li>" : "";
		$html .= !($event['review_url'] == "http://" || $event['link'] == "") ? "<li><a href=\"".$event['review_url']."\" target=\"_self\">Review (Web View)</a></li>" : "";
		$html .= '
		<fieldset>
			<div class="row">
				<label>Teilnehmen</label>
				<div class="toggle" name="buttonTeilnehmen" ';
		$html .= !(hasJoined($user->id, $event_id)) ? 'onclick="window.location.href=\'events.php?year='.$year.'&amp;event_id='.$event_id.'&amp;join='.$event_id.'\'"' : 'toggled="true" onclick="window.location.href=\'events.php?year='.$year.'&amp;event_id='.$event_id.'&amp;unjoin='.$event_id.'\'"';
		$html .= ' target="_self"><span class="thumb"></span><span class="toggleOn">Ja</span><span class="toggleOff">Nein</span></div>
			</div>
		</fieldset>';
		$html .= "<li><small>Gemeldet von ".usersystem::id2user($event['reportedby_id'],true)." @ ".date("d.m.Y H:i", $event['reportedon_date'])."</small></li>";
		
		echo $html;
	
	}
	?>
	</ul>
</body>
</html>
