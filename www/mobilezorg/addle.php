<?php
/**
* Addle
* 
* Zeigt die Übersicht des Addle Spiels inklusive Anleitung, Highscores, etc.
* 
* @author IneX
* @version 1.0
* @package mobilezorg
* @subpackage addle
*
* @global array $user Globales Array mit allen Uservariablen
* @global array $db Globales Array mit allen MySQL-Datenbankvariablen
*/

include_once($_SERVER['DOCUMENT_ROOT'].'/includes/main.inc.php');

global $user, $db;

if ($user->typ == USER_NICHTEINGELOGGT || !isset($user->typ)) { header('Location: login.php'); }


$html = '';

$addleGames = array();
$addleMyStats = array();
$addleTop10 = array();


// Query for Games Overview
$sql =
	"
	SELECT
		*
	FROM
		addle
	WHERE
		(player1 = $_SESSION[user_id] OR player2 = $_SESSION[user_id])
		AND finish = '0'
	ORDER BY
		id DESC
	"
;
$result = $db->query($sql, __FILE__, __LINE__);
while ($rs = $db->fetch($result)) {
	$addleGames[] = $rs;
}
	

// Query for my Addle Stats
$sql =
	"
	SELECT
		rank
		, user
		, score
	FROM addle_dwz
	WHERE user = ".$user->id."
	ORDER BY rank DESC
	"
;
$result = $db->query($sql, __FILE__, __LINE__);
$addleMyStats = $rs = $db->fetch($result);


// Query for Addle Top 10 Scores
$sql =
	"
	SELECT
		adwz.rank
		, adwz.user
		, adwz.score
		, user.username AS username
		, user.clan_tag AS clantag
	FROM addle_dwz adwz
	LEFT JOIN user ON (adwz.user = user.id)
	ORDER BY rank ASC
	LIMIT 0,10
	"
;
$result = $db->query($sql, __FILE__, __LINE__);
while ($rs = $db->fetch($result)) {
	$addleTop10[] = $rs;
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
		$result = $db->query($sql);
		return $openAddle = $db->num($result);
	}
}


openAddle($user->id);


// forceBackButton Builder
switch ($_GET['show'])
{
	case 'spiele':
		$backURL = "addle.php";
		$backTitle = "Addle";
		break;
	
	case 'highscore':
		$backURL = "addle.php";
		$backTitle = "Addle";
		break;
		
	case 'anleitung':
		$backURL = "addle.php";
		$backTitle = "Addle";
		break;
		
	default:
		$backURL = "index.php";
		$backTitle = "Games";
		break;
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
		<!-- a id="backButton" class="button" href="#"></a -->
		<a id="forceBackButton" class="button" href="<?php echo $backURL ?>" target="_self"><?php echo $backTitle ?></a>
		<a class="button" href="#newAddle">Neu</a>
	</div>
	
	
<!-- ADDLE -->
<?php
switch ($_GET['show'])
{
	default:
?>
	<ul id="addle" title="Addle" selected="true">
		<?php if ($_GET['error'] <> '') echo "<li class=\"error\"><h1>$_GET[error]</h1></li>"; ?>
		<li><a class="linklabel" href="?show=spiele" target="_self">Spiele</a>
			<?php echo ($openAddle > 0) ? '<span class="newItemIndicator">'.$openAddle.'</span>' : ''; ?></li>
		<li><a class="linklabel" href="?show=highscore" target="_self">Highscore</a></li>
		<li><a class="linklabel" href="?show=anleitung" target="_self">Anleitung</a></li>
		<!-- li><a class="linklabel" href="index.php" target="_self"><-- zur&uuml;ck</a></li -->
	</ul>
	
<?php
	break;
	
case 'spiele':	
?>

	<ul id="spiele" title="Meine Spiele" selected="true">
		<!-- li class="error"><h1>Achtung: ungetestet!</h1></li -->
		<?php foreach ((array) $addleGames as $n => $game) {
				$myplayerid = ($game['player1'] != $_SESSION['user_id']) ? 2 : 1;
				$otherpl = ($game['player1'] != $_SESSION['user_id']) ? $game['player1'] : $game['player2'];
				$html .= '<li><a class="linklabel" href="addle_game.php?game_id='.$game['id'].'">#'.$game['id'].' vs. '.$user->id2user($otherpl).'</a>';
				$html .= ($game['nextturn'] == $myplayerid) ? '<span class="newItem"></span></li>' : '</li>';
			}
				echo $html;
			?>
	</ul>
	
<?php
	break;

case 'highscore':	
?>	
	<ul id="highscore" title="Highscore" selected="true">
		<li class="group">Meine Stats</li>
			<li>DWZ Punkte: <?php echo $addleMyStats['score']; ?></li>
			<li>DWZ Rang: <?php echo $addleMyStats['rank']; ?></li>
		<li class="group">Highscores</li>
			<?php foreach ((array) $addleTop10 as $n => $topScore) {
				echo '<li><a class="linklabel" href="userlist.php?user_id='.$topScore['user'].'">'.$topScore['rank'].'. '.$topScore['username'].' ('.$topScore['score'].')</a></li>';
			} ?>
	</ul>
	
<?php
	break;

case 'anleitung':
?>
	<ul id="anleitung" title="Anleitung" selected="true">
		<li>Ziel des Spiels Addle ist es, m&ouml;glichst viele Punkte zu erzielen.<br/>
		Um Punkte zu bekommen, w&auml;hle ein Feld aus deiner markierten Linie aus. Du erh&auml;lst die entsprechende Punktzahl. Anschliessend
		darf dein Gegner von seiner markierten Linie ausw&auml;hlen. Die Linie wechselt jeweils von der Vertikalen in die Horizontalen
		deines gew&auml;hlten Feldes, und umgekehrt. Der erste Spiele w&auml;hlt immer von aus einer horizontalen Linie aus, der zweite immer
		aus einer vertikalen Linie. Das Spiel ist fertig, wenn ein Spieler kein Feld mehr aus seiner Linie ausw&auml;hlen kann.<br/>
		Die Spielerin Barabara Harris ist eine KI, ihr spielt dabei also gegen den Computer.</li>
	</ul>

<?php
	break;
		
}
?>
</body>
</html>