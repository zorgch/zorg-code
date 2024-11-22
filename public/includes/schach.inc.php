<?php
/**
 * zorg Schach Functions
 *
 * @version 1.0
 * @package zorg\Games\Schach
 *
 * @fixme In der DB gibt es kein Table "chess_board", nur "chess_board_old"? / IneX, 18.04.2020
 */

/**
 * Neues Schach Spiel erstellen
 *
 * @version 1.1
 * @since 1.0 Function added
 * @since 1.1 `18.04.2020` `IneX` Code optimizations and migrate to mysqli_
 *
 * @global object $db Globales Class-Object mit allen MySQL-Methoden
 * @global object $user Globales Class-Object mit den User-Methoden & Variablen
 * @return int ID des neu erstellten Schach Games
 */
function createGame()
{
	global $db, $user;

	/** Spiel in der DB erstellen */
	$sql = 'INSERT INTO chess_game (`user1`) values ('.$user->id.')';
	$game_id = $db->query($sql, __FILE__, __LINE__, __FUNCTION__);

	/** SQL-Query für Schachbrett mit Figuren auf initaler Position definieren */
	$values = 'INSERT INTO chess_board VALUES ';
	/** Whites */
	$values .= '('.$game_id.', "w", "Bauer", 1, 2, 0)';
	$values .= ', ('.$game_id.', "w", "Bauer", 2, 2, 0)';
	$values .= ', ('.$game_id.', "w", "Bauer", 3, 2, 0)';
	$values .= ', ('.$game_id.', "w", "Bauer", 4, 2, 0)';
	$values .= ', ('.$game_id.', "w", "Bauer", 5, 2, 0)';
	$values .= ', ('.$game_id.', "w", "Bauer", 6, 2, 0)';
	$values .= ', ('.$game_id.', "w", "Bauer", 7, 2, 0)';
	$values .= ', ('.$game_id.', "w", "Bauer", 8, 2, 0)';
	$values .= ', ('.$game_id.', "w", "Turm", 1, 1, 0)';
	$values .= ', ('.$game_id.', "w", "Turm", 8, 1, 0)';
	$values .= ', ('.$game_id.', "w", "Pferd", 2, 1, 0)';
	$values .= ', ('.$game_id.', "w", "Pferd", 7, 1, 0)';
	$values .= ', ('.$game_id.', "w", "Laeufer", 3, 1, 0)';
	$values .= ', ('.$game_id.', "w", "Laeufer", 6, 1, 0)';
	$values .= ', ('.$game_id.', "w", "Dame", 4, 1, 0)';
	$values .= ', ('.$game_id.', "w", "Koenig", 5, 1, 0)';
	/** Blacks */
	$values .= ', ('.$game_id.', "b", "Bauer", 1, 7, 0)';
	$values .= ', ('.$game_id.', "b", "Bauer", 2, 7, 0)';
	$values .= ', ('.$game_id.', "b", "Bauer", 3, 7, 0)';
	$values .= ', ('.$game_id.', "b", "Bauer", 4, 7, 0)';
	$values .= ', ('.$game_id.', "b", "Bauer", 5, 7, 0)';
	$values .= ', ('.$game_id.', "b", "Bauer", 6, 7, 0)';
	$values .= ', ('.$game_id.', "b", "Bauer", 7, 7, 0)';
	$values .= ', ('.$game_id.', "b", "Bauer", 8, 7, 0)';
	$values .= ', ('.$game_id.', "b", "Turm", 1, 8, 0)';
	$values .= ', ('.$game_id.', "b", "Turm", 8, 8, 0)';
	$values .= ', ('.$game_id.', "b", "Pferd", 2, 8, 0)';
	$values .= ', ('.$game_id.', "b", "Pferd", 7, 8, 0)';
	$values .= ', ('.$game_id.', "b", "Laeufer", 3, 8, 0)';
	$values .= ', ('.$game_id.', "b", "Laeufer", 6, 8, 0)';
	$values .= ', ('.$game_id.', "b", "Dame", 4, 8, 0)';
	$values .= ', ('.$game_id.', "b", "Koenig", 5, 8, 0)';

	/** Initiale Spielerpositionen für neues Game in DB eintragen */
	$db->query($values, __FILE__, __LINE__, __FUNCTION__);

	return $game_id;
}

function xy2htmlXY($position)
{
	list($x, $y) = split("_", $position);

	$x = ($x - 1) * 64 + 2;
	$y = (8 - $y) * 64 + 45 + 2;

	return array($x, $y);
}

/**
 * Letzten Spielzug markieren
 *
 * @version 1.1
 * @since 1.0 Function added
 * @since 1.1 `18.04.2020` `IneX` Migrate to mysqli_
 *
 * @global object $db Globales Class-Object mit allen MySQL-Methoden
 * @global int $game_id Globaler Integer mit der aktuellen Schach Game-ID
 * @global string $myColor Globaler String mit der Farbe des aktuellen Spielers
 * @return void Funktion macht direkt Echo von generiertem Output
 */
function highlightLastMove()
{
	global $db, $game_id, $myColor;

	$sql = 'SELECT xFrom, yFrom, xTo, yTo FROM chess_history WHERE gameID='.$game_id.' ORDER BY id DESC LIMIT 1';
	$result = $db->query($sql, __FILE__, __LINE__, __FUNCTION__);

	if(!empty($db->num($result)))
	{
		$rs = $db->fetch($result);

		$xFrom = ($myColor == "Schwarz") ? 9 - $rs['xFrom'] : $rs['xFrom'];
		$yFrom = ($myColor == "Schwarz") ? 9 - $rs['yFrom'] : $rs['yFrom'];
		$xTo = ($myColor == "Schwarz") ? 9 - $rs['xTo'] : $rs['xTo'];
		$yTo = ($myColor == "Schwarz") ? 9 - $rs['yTo'] : $rs['yTo'];

		list($xFrom, $yFrom) = xy2htmlXY($xFrom . "_" . $yFrom);
		list($xTo, $yTo) = xy2htmlXY($xTo . "_" . $yTo);

		$xFrom -= 2;
		$yFrom -= 3;
		$xTo -= 2;
		$yTo -= 3;

		echo "<img src='images/chess/_border.gif' style='position:absolute;left:$xFrom;top:$yFrom;'>\n";
		echo "<img src='images/chess/_border.gif' style='position:absolute;left:$xTo;top:$yTo;'>\n";
	}
}

/**
 * Definiere Spieler der beginnt.
 *
 * @deprecated In der DB gibt es kein Table "game", nur "chess_game_OLD" (IneX, 18.04.2020)
 * @TODO Eventuell DEPRECATED? In der DB gibt es kein Table "game", nur "chess_game_OLD" / IneX, 18.04.2020
 */
function setStartingplayer($game_id)
{
	/**
	 * register user as opponent unless he's white or the opponent is already choosen
	 * @FIXME müsste statt "game" wohl der table "chess_game_OLD" sein... ev. DEPRECATED?
	 */
	$sql = "select (case when user1!=" . $_SESSION['user_id'] . " and user2 is null then 1 " .
	         "        else 0 end) as register from game where ID=$game_id";
	$result = $db->query($sql, __FILE__, __LINE__, __FUNCTION__);
	$rs = $db->fetch($result);
	if($rs['register'])
	{
		/** @FIXME müsste statt "game" wohl der table "chess_game_OLD" sein... ev. DEPRECATED? */
		$sql = "update game set user2=" . $_SESSION['user_id'] . " where ID=$game_id";
		$db->query($sql, __FILE__, __LINE__, __FUNCTION__);
	}
}

/**
 * Spiel-Titel mit Spielernamen und Game-ID erzeugen.
 *
 * @deprecated In der DB gibt es kein Table "chess_game", nur "chess_game_OLD" (IneX, 18.04.2020)
 * @TODO Eventuell DEPRECATED? In der DB gibt es kein Table "chess_game", nur "chess_game_OLD" / IneX, 18.04.2020
 */
function buildTitle($game_id)
{
	global $db, $user;
	/** @FIXME müsste statt "game" wohl der table "chess_game_OLD" sein... ev. DEPRECATED? */
	$sql = 'SELECT (CASE WHEN user2 IS NULL THEN \'noch kein Gegner\' WHEN user1=? THEN concat(\'Spiel \', ?, \' gegen <a href="mailto:\', u2.email, \'">\'. u2.username, \'</a>\') WHEN user2=? then concat(\'Spiel \', ?, \' gegen <a href="mailto:\', u1.email, \'">\'. u1.username, \'</a>\') end) as opponent from (chess_game g left outer join user u1 on user1=u1.ID) left outer join user u2 on user2=u2.ID WHERE g.ID=?';
	$result = $db->query($sql, __FILE__, __LINE__, __FUNCTION__, [$user->id, $game_id, $user->id, $game_id, $game_id]);
	$rs = $db->fetch($result);

	return $rs['opponent'];
}

function buildInit($currentPlayer, $myColor, $wBoard, $bBoard) {

	if($currentPlayer == $myColor)
	{
		echo "if(ie)\n";
		echo "{\n";
			if($currentPlayer == "Weiss") { $board = $wBoard; $color = "w"; }
			else                          { $board = $bBoard; $color = "b"; }

			$i=0;
			foreach($board as $position => $figure)
			{
				echo "dragObj[" . $i++ . "] = document.all." . $color . "_" . $figure . $position . ";\n";
			}
		echo "} else\n";
		echo "{\n";
			$i=0;
			foreach($board as $position => $figure)
			{
				echo "dragObj[" . $i++ . "] = document.getElementById('" . $color . "_" . $figure . $position . "');\n";
			}
		echo "}\n";
	}
}

/**
 * Farbe des aktuellen Spielers (My) finden.
 *
 * @deprecated In der DB gibt es kein Table "chess_game", nur "chess_game_OLD" (IneX, 18.04.2020)
 * @TODO Eventuell DEPRECATED? In der DB gibt es kein Table "chess_game", nur "chess_game_OLD" / IneX, 18.04.2020
 */
function getMyFigureColor($game_id)
{
	global $db, $user;
	/** @FIXME müsste statt "game" wohl der table "chess_game_OLD" sein... ev. DEPRECATED? */
	$sql = "SELECT (CASE WHEN user1=? THEN 'Weiss' ELSE 'Schwarz' END) as color FROM chess_game WHERE id=?";
	$result = $db->query($sql, __FILE__, __LINE__, __FUNCTION__, [$user->id, $game_id]);
	$rs = $db->fetch($result);

	return $rs['color'];
}

function getCurrentPlayer($game_id) {

	global $db;

	$sql = "SELECT count(ID) AS noEntries FROM chess_history WHERE gameID=?";
	$result = $db->query($sql, __FILE__, __LINE__, __FUNCTION__, [$game_id]);
	$rs = $db->fetch($result);

	return ($rs['noEntries'] % 2 == 0) ? "Weiss" : "Schwarz";
}

function turnBoard() {
	global $wBoard, $bBoard;

	foreach($wBoard as $position => $figure) {// $wBoard['1_2'] -> 'Bauer'
		list($x, $y) = split("_", $position);
		$wBoard2[(9-$x) . "_" . (9-$y)] = $figure;
	}
	$wBoard = $wBoard2;

	foreach($bBoard as $position => $figure) { // $wBoard['1_2'] -> 'Bauer'
		list($x, $y) = split("_", $position);
		$bBoard2[(9-$x) . "_" . (9-$y)] = $figure;
	}
	$bBoard = $bBoard2;
}

/**
 * Spiel History anzeigen
 *
 * @version 1.1
 * @since 1.0 Function added
 * @since 1.1 `18.04.2020` `IneX` Code optimizations
 *
 * @return void Funktion macht direkt Echo von generiertem Output
 */
function buildHistory($game_id)
{
	global $db;

	$html = '';
	$html .= '<div class="history">
				<span class="big">History</span><br>
					<table border="0">';
	$sql = 'select yFrom, yTo, (case when xFrom=1 then \'a\' when xFrom=2 then \'b\' when xFrom=3 then \'c\' when xFrom=4 then \'d\' when xFrom=5 then \'e\' when xFrom=6 then \'f\' when xFrom=7 then \'g\' when xFrom=8 then \'h\' end) as xFrom, (case when xTo=1 then \'a\' when xTo=2 then \'b\' when xTo=3 then \'c\' when xTo=4 then \'d\' when xTo=5 then \'e\' when xTo=6 then \'f\' when xTo=7 then \'g\' when xTo=8 then \'h\' end) as xTo, (case when figure=\'Laeufer\' then \'Läufer\' when figure=\'Koenig\' then \'König\' else figure end) as figure, (case when info=\'Laeufer\' then \'Läufer\' when info=\'Koenig\' then \'König\' else info end) as info FROM chess_history where gameID=? order by ID asc';
	$result = $db->query($sql, __FILE__, __LINE__, __FUNCTION__, [[$game_id]]);

	$i = 1;
	while($rs = $db->fetch($result))
	{
		if($rs['info'] == "o-o" || $rs['info'] == "o-o-o")
		{
			$str = $rs['info'];
		} else {
			$str = $rs['figure'] . ", " . $rs['xFrom'] . $rs['yFrom'] . " - " . $rs['xTo'] . $rs['yTo'];
			if($rs['info']) { $str .= "<br><small><i>(" . $rs['info'] . " geschlagen)</i></small>"; }
		}
		$html .=  "<tr>\n";
		$html .=  "   <td align='right' valign='top' nowrap>" . $i++ . ": &nbsp;&nbsp;</td>\n";
		$html .=  "   <td valign='top' nowrap>$str</td>\n";
		$html .=  "   <td>&nbsp;&nbsp;&nbsp;</td>\n";
		if($rs = $db->fetch($result))
		{
			if($rs['info'] == "o-o" || $rs['info'] == "o-o-o")
			{
				$str = $rs['info'];
			} else
			{
				$str = $rs['figure'] . ", " . $rs['xFrom'] . $rs['yFrom'] . " - " . $rs['xTo'] . $rs['yTo'];
				if($rs['info']) { $str .= "<br><small><i>(" . $rs['info'] . " geschlagen)</i></small>"; }
			}

			$html .=  "   <td valign='top' nowrap>$str</td>\n";
		}
		$html .=  "</tr>\n";
	}
	$html .= '</table>
		</div>';
	echo $html;
}

/**
 * Schachzug ausführen.
 *
 * @deprecated In der DB gibt es kein Table "chess_board", nur "chess_board_old" (IneX, 18.04.2020)
 * @TODO Eventuell DEPRECATED? In der DB gibt es kein Table "chess_board", nur "chess_board_old" / IneX, 18.04.2020
 */
function doMove($xFrom, $yFrom, $xTo, $yTo, $game_id, $currentPlayer)
{
	global $db;

	if(!figurePlacedOnBoard($xFrom, $yFrom, $xTo, $yTo)) { return 1; }
	if(!moveWasMade($xFrom, $yFrom, $xTo, $yTo)) { return 2; }

	$figure = getFigure($xFrom, $yFrom, $game_id);

	if(!$figure) { return 3; }

	// Standartregeln der Figuren eingehalten?
	if($figure == "Bauer" && !isPawnMoveValid($xFrom, $yFrom, $xTo, $yTo, $currentPlayer, $game_id)) { return 4; }
	if($figure == "Turm" && !isRookMoveValid($xFrom, $yFrom, $xTo, $yTo, $currentPlayer, $game_id)) { return 5; }
	if($figure == "Pferd" && !isKnightMoveValid($xFrom, $yFrom, $xTo, $yTo, $currentPlayer, $game_id)) { return 6; }
	if($figure == "Laeufer" && !isBishopMoveValid($xFrom, $yFrom, $xTo, $yTo, $currentPlayer, $game_id)) { return 7; }
	if($figure == "Dame" && !isQueenMoveValid($xFrom, $yFrom, $xTo, $yTo, $currentPlayer, $game_id)) { return 8; }
	if($figure == "Koenig" && !isKingMoveValid($xFrom, $yFrom, $xTo, $yTo, $currentPlayer, $game_id)) { return 9; }

	if(isKingInChess()) { return 10; } // ist König jetzt im Schach?

	writeHistory($xFrom, $yFrom, $xTo, $yTo, $figure, $game_id);
	writeMoveToDB($xFrom, $yFrom, $xTo, $yTo, $figure, $game_id);

	// add info to database for rochade
	if($figure == "Turm" || $figure == "Koenig")
	{
		/** @FIXME müsste statt "chess_board" wohl der table "chess_board_OLD" sein... ev. DEPRECATED? */
		$sql = "update chess_board set noMoves=noMoves+1 where x=$xTo and y=$yTo";
		$db->query($sql, __FILE__, __LINE__, __FUNCTION__);
	}
}

function figurePlacedOnBoard($xFrom, $yFrom, $xTo, $yTo) {
	return ($xFrom < 1 || $xFrom > 8 || $yFrom < 1 || $yFrom > 8 || $xTo < 1 || $xTo > 8 || $yTo < 1 || $yTo > 8) ? 0 : 1;
}

function moveWasMade($xFrom, $yFrom, $xTo, $yTo) {
	return ($xTo == $xFrom && $yTo == $yFrom) ? 0 : 1;
}

/**
 * Infos einer Figur aus der DB holen.
 *
 * @deprecated In der DB gibt es kein Table "chess_board", nur "chess_board_old" (IneX, 18.04.2020)
 * @TODO Eventuell DEPRECATED? In der DB gibt es kein Table "chess_board", nur "chess_board_old" / IneX, 18.04.2020
 */
function getFigure($x, $y, $game_id)
{
	global $db;
	/** @FIXME müsste statt "chess_board" wohl der table "chess_board_OLD" sein... ev. DEPRECATED? */
	$sql = "SELECT figur FROM chess_board WHERE x=? and y=? AND id=?";
	$result = $db->query($sql, __FILE__, __LINE__, __FUNCTION__, [$x, $y, $game_id]);
	$rs = $db->fetch($result);

	return $rs['figur'];
}

function isPawnMoveValid($xFrom, $yFrom, $xTo, $yTo, $currentPlayer, $game_id) {

	if($currentPlayer == "Weiss") {
		// Einen Schritt nach vorne. Nur wenn keine Figur auf dem Zielfeld.
		if($yTo-$yFrom == 1 && $xTo == $xFrom && !isFieldTaken($xTo, $yTo, $game_id)) { return 1; }

		// Zwei Schritte nach vorne. Nur wenn noch auf der Startlinie und keine Figur auf dem Ziel- und Zwischenfeld.
		if($yFrom == 2 && $yTo-$yFrom == 2 && $xTo == $xFrom && !isFieldTaken($xTo, $yTo, $game_id) && !isFieldTaken($xTo, $yTo-1, $game_id)) { return 1; }

		// Einen Schritt nach links oder rechts oben. Nur wenn Feld vom Gegner besetzt.
		if($yTo-$yFrom == 1 && abs($xTo-$xFrom) == 1 && isPlayerOnField($xTo, $yTo, $currentPlayer, 1, $game_id)) return 1;
	} else {
		// Einen Schritt nach vorne. Nur wenn keine Figur auf dem Zielfeld.
		if($yTo-$yFrom == -1 && $xTo == $xFrom && !isFieldTaken($xTo, $yTo, $game_id)) { return 1; }

		// Zwei Schritte nach vorne. Nur wenn noch auf der Startlinie und keine Figur auf dem Ziel- und Zwischenfeld.
		if($yFrom == 7 && $yTo-$yFrom == -2 && $xTo == $xFrom && !isFieldTaken($xTo, $yTo, $game_id) && !isFieldTaken($xTo, $yTo+1, $game_id)) { return 1; }

		// Einen Schritt nach links oder rechts oben. Nur wenn Feld vom Gegner besetzt.
		if($yTo-$yFrom == -1 && abs($xTo-$xFrom) == 1 && isPlayerOnField($xTo, $yTo, $currentPlayer, 1, $game_id)) return 1;
	}

	return 0;
}

function isRookMoveValid($xFrom, $yFrom, $xTo, $yTo, $currentPlayer, $game_id) {

	if($xTo == $xFrom || $yTo == $yFrom) {
		if($xTo == $xFrom) {
			if($yTo > $yFrom) { $bigger = $yTo; $smaller = $yFrom; }
			else              { $bigger = $yFrom; $smaller = $yTo; }

			// Zwischen Start- und Endfeld darf sich keine Figur befinden.
			for($i=($smaller+1); $i<$bigger; $i++) { if(isFieldTaken($xTo, $i, $game_id)) return 0; }
		} else {
			if($xTo > $xFrom) { $bigger = $xTo; $smaller = $xFrom; }
			else              { $bigger = $xFrom; $smaller = $xTo; }

			// Zwischen Start- und Endfeld darf sich keine Figur befinden.
			for($i=($smaller+1); $i<$bigger; $i++) {
				if(isFieldTaken($i, $yTo, $game_id)) return 0;
			}
		}

		// Auf dem Endfeld darf keine eigene Figur stehen.
		if(isPlayerOnField($xTo, $yTo, $currentPlayer, 0, $game_id)) { return 0; }

		return 1;
	}

	return 0;
}

function isKnightMoveValid($xFrom, $yFrom, $xTo, $yTo, $currentPlayer, $game_id)
{
	$from = $xFrom*10+$yFrom;
	$to = $xTo*10+$yTo;
	$difference = abs($from-$to);

	// Die vier Möglichkeiten die ein Pferd hat (bei dem Differenzbetrag). Es darf keine eigene Figur auf dem Zielfeld sein.
	if(($difference == 8 || $difference == 12 || $difference == 19 || $difference == 21) && !isPlayerOnField($xTo, $yTo, $currentPlayer, 0, $game_id)) { return 1; }

	return 0;
}

function isBishopMoveValid($xFrom, $yFrom, $xTo, $yTo, $currentPlayer, $game_id)
{
	if(abs($xTo - $xFrom) == abs($yTo - $yFrom))
	{
		$xModifier = ($xTo > $xFrom) ? 1 : -1;
		$yModifier = ($yTo > $yFrom) ? 1 : -1;

		// Zwischen Start- und Endfeld darf sich keine Figur befinden.
		for($x=($xFrom+$xModifier); $x!=$xTo; $x=$x+$xModifier)
		{
			$yFrom = $yFrom + $yModifier;
			if(isFieldTaken($x, $yFrom, $game_id)) return 0;
		}

		// Auf dem Endfeld darf keine eigene Figur stehen.
		if(isPlayerOnField($xTo, $yTo, $currentPlayer, 0, $game_id)) { return 0; }

		return 1;
	}

	return 0;
}

function isQueenMoveValid($xFrom, $yFrom, $xTo, $yTo, $currentPlayer, $game_id)
{
	if(isRookMoveValid($xFrom, $yFrom, $xTo, $yTo, $currentPlayer, $game_id) ||
	   isBishopMoveValid($xFrom, $yFrom, $xTo, $yTo, $currentPlayer, $game_id))
	{
		return 1;
	} else
	{
		return 0;
	}
}

function isKingMoveValid($xFrom, $yFrom, $xTo, $yTo, $currentPlayer, $game_id)
{
	$from = $xFrom*10+$yFrom;
	$to = $xTo*10+$yTo;
	$difference = abs($from-$to);

	// Die vier Möglichkeiten die ein König hat. Es darf keine eigene Figur auf dem Zielfeld sein.
	if(($difference == 1 || $difference == 9 || $difference == 10 || $difference == 11) && !isPlayerOnField($xTo, $yTo, $currentPlayer, 0, $game_id)) { return 1; }

	// Rochade
	if($currentPlayer == "Weiss") { $y = 1; $color = "w"; }
	else                          { $y = 8; $color = "b"; }

	// kleine Rochade
	if($yFrom == $y && $yTo == $y && $xFrom == 5 && $xTo == 7 &&
	!isFieldTaken(6, $y, $game_id) && !isFieldTaken(7, $y, $game_id) &&
	isRookOnField(8 ,$y, $color) &&
	!hasBeenMoved(5, $y) && !hasBeenMoved(8, $y)) return 1;

	// grosse Rochade
	if($yFrom == $y && $yTo == $y && $xFrom == 5 && $xTo == 3 &&
	!isFieldTaken(4, $y, $game_id) && !isFieldTaken(3, $y, $game_id) && !isFieldTaken(2, $y, $game_id) &&
	isRookOnField(1 ,$y, $color) &&
	!hasBeenMoved(5, $y) && !hasBeenMoved(1, $y)) return 1;

	return 0;
}

/**
 * Check ob ein Feld besetzt oder frei ist.
 *
 * @deprecated In der DB gibt es kein Table "chess_board", nur "chess_board_old" / IneX, 18.04.2020
 * @TODO Eventuell DEPRECATED? In der DB gibt es kein Table "chess_board", nur "chess_board_old" / IneX, 18.04.2020
 */
function isFieldTaken($x, $y, $game_id)
{
	global $db;

	/** @FIXME müsste statt "chess_board" wohl der table "chess_board_OLD" sein... ev. DEPRECATED? */
	$sql = "select count(ID) as count from chess_board where x=$x and y=$y and ID=$game_id";
	$result = $db->query($sql, __FILE__, __LINE__, __FUNCTION__);
	$rs = $db->fetch($result);

	return ($rs['count'] == 0) ? 0 : 1;
}

/**
 * Check ob aktueller Spieler schon auf dem Feld ist.
 *
 * @deprecated In der DB gibt es kein Table "chess_board", nur "chess_board_old"
 * @TODO Eventuell DEPRECATED? In der DB gibt es kein Table "chess_board", nur "chess_board_old" / IneX, 18.04.2020
 */
function isPlayerOnField($x, $y, $currentPlayer, $isOpponentAsked, $game_id)
{
	global $db;

	$player = ($currentPlayer == "Weiss") ? "w" : "b";
	if($isOpponentAsked) { $player = ($player == "w") ? "b" : "w"; }

	/** @FIXME müsste statt "chess_board" wohl der table "chess_board_OLD" sein... ev. DEPRECATED? */
	$sql = "select count(ID) as count from chess_board where x=$x and y=$y and farbe='$player' and ID=$game_id";
	$result = $db->query($sql, __FILE__, __LINE__, __FUNCTION__);
	$rs = $db->fetch($result);

	return $rs['count'];
}

function isKingInChess()
{
	// TODO :-)

	return 0;
}

/**
 * Check ob Turm auf Feld ist
 *
 * @deprecated In der DB gibt es kein Table "chess_board", nur "chess_board_old" / IneX, 18.04.2020
 * @TODO Eventuell DEPRECATED? In der DB gibt es kein Table "chess_board", nur "chess_board_old" / IneX, 18.04.2020
 *
 * @version 1.1
 * @since 1.0 Function added
 * @since 1.1 `18.04.2020` `IneX` Code optimizations and migrate to mysqli_
 *
 * @param int $x Zu prüfende X-Coordinate im Spielbrett
 * @param int $y Zu prüfende Y-Coordinate im Spielbrett
 * @param string $color Zu prüfende Farbe des Turms
 * @global object $db Globales Class-Object mit allen MySQL-Methoden
 * @return int Anzahl gefundener Treffer in der DB
 */
function isRookOnField($x, $y, $color)
{
	global $db;
	/** @FIXME müsste statt "chess_board" wohl der table "chess_board_OLD" sein... ev. DEPRECATED? */
	$sql = 'SELECT x,y FROM chess_board WHERE x='.$x.' AND y='.$y.' AND farbe="'.$color.'" AND figur="Turm"';
	$result = $db->query($sql, __FILE__, __LINE__, __FUNCTION__);

	/** @FIXME bool statt int */
	return $db->num($result);
}

/**
 * Check ob Turm auf Feld ist
 *
 * @deprecated In der DB gibt es kein Table "chess_board", nur "chess_board_old" / IneX, 18.04.2020
 * @TODO Eventuell DEPRECATED? In der DB gibt es kein Table "chess_board", nur "chess_board_old" / IneX, 18.04.2020
 *
 * @version 1.1
 * @since 1.0 Function added
 * @since 1.1 `18.04.2020` `IneX` Code optimizations and migrate to mysqli_
 *
 * @param int $x Zu prüfende X-Coordinate im Spielbrett
 * @param int $y Zu prüfende Y-Coordinate im Spielbrett
 * @global object $db Globales Class-Object mit allen MySQL-Methoden
 * @return int Anzahl gefundener Treffer in der DB
 */
function hasBeenMoved($x, $y)
{
	global $db;
	/** @FIXME müsste statt "chess_board" wohl der table "chess_board_OLD" sein... ev. DEPRECATED? */
	$sql = "SELECT noMoves FROM chess_board WHERE x=$x and y=$y";
	$result = $db->query($sql, __FILE__, __LINE__, __FUNCTION__);
	$rs = $db->fetch($result);

	return $rs['noMoves'];
}

/**
 * Spielzug in die Spielhistory schreiben
 *
 * @deprecated In der DB gibt es kein Table "history", nur "chess_history" & "chess_history_old" / IneX, 18.04.2020
 * @TODO Eventuell DEPRECATED? In der DB gibt es kein Table "history", nur "chess_history" & "chess_history_old" / IneX, 18.04.2020
 *
 * @version 1.1
 * @since 1.0 Function added
 * @since 1.1 `18.04.2020` `IneX` Code optimizations and migrate to mysqli_
 *
 * @param int $xFrom Bewegung von dieser X-Coordinate...
 * @param int $yFrom Bewegung von dieser Y-Coordinate...
 * @param int $xTo ...zu dieser X-Coordinate im Spielbrett
 * @param int $yTo ...zu dieser Y-Coordinate im Spielbrett
 * @param string $figure Name der bewegten Schachfigur
 * @param int $game_id Die ID des Schachspiels in welchem der Zug stattfand
 * @global object $db Globales Class-Object mit allen MySQL-Methoden
 * @return void
 */
function writeHistory($xFrom, $yFrom, $xTo, $yTo, $figure, $game_id)
{
	global $db;

	if($figure == "Koenig" && abs($xTo-$xFrom) > 1) // rochade was made
	{
		$which = ($xTo-$xFrom == 2) ? "o-o" : "o-o-o";
		/** @FIXME müsste statt "history" wohl der table "chass_history_OLD" sein... ev. DEPRECATED? */
		$sql = "INSERT INTO history (gameID, info) values ($game_id, '$which')";
	} else
	{
		/**
		 * Check ob Figur geschlagen und History schreiben
		 * @FIXME müsste statt "chess_board" wohl der table "chess_board_OLD" sein... ev. DEPRECATED?
		 */
		$sql = "select figur from chess_board where x=$xTo and y=$yTo and ID=$game_id";
		$result = $db->query($sql, __FILE__, __LINE__, __FUNCTION__);

		if($db->num($result) === 0) // Es befindet sich keine Figur auf dem Zielfeld
		{
			/** @FIXME müsste statt "chess_history" wohl der table "chass_history_OLD" sein... ev. DEPRECATED? */
			$sql = "insert into chess_history (gameID, figure, xFrom, yFrom, xTo, yTo) " .
			         "values ($game_id, '$figure', $xFrom, $yFrom, $xTo, $yTo)";
		} else
		{
			$rs = $db->fetch($result);
			/** @FIXME müsste statt "chess_history" wohl der table "chass_history_OLD" sein... ev. DEPRECATED? */
			$sql = "INSERT INTO chess_history (gameID, figure, xFrom, yFrom, xTo, yTo, info) VALUES (?, ?, ?, ?, ?, ?, ?)";
			/**
			 * delete the eaten figure
			 * @FIXME müsste statt "chess_board" wohl der table "chess_board_OLD" sein... ev. DEPRECATED?
			 */
			$delQuery = "delete from chess_board where x=$xTo and y=$yTo and ID=$game_id";
			$db->query($delQuery, __FILE__, __LINE__, __FUNCTION__, [$game_id, $figure, $xFrom, $yFrom, $xTo, $yTo, $rs['figur']]);
		}
	}

	$db->query($sql, __FILE__, __LINE__, __FUNCTION__);
	/** @FIXME Return bool statt nix */
}

/**
 * Schachzug in die Datenbank speichern.
 *
 * @deprecated In der DB gibt es kein Table "chess_board", nur "chess_board_OLD" / IneX, 18.04.2020
 * @TODO Eventuell DEPRECATED? In der DB gibt es kein Table "chess_board", nur "chess_board_OLD" / IneX, 18.04.2020
 */
function writeMoveToDB($xFrom, $yFrom, $xTo, $yTo, $figure, $game_id)
{
	global $db;

	// Rochade
	if($figure == "Koenig" && abs($xTo-$xFrom) > 1)
	{
		if($xTo-$xFrom == 2)
		{
			/** @FIXME müsste statt "chess_board" wohl der table "chess_board_OLD" sein... ev. DEPRECATED? */
			$sql = "update chess_board set x=6, y=$yTo where x=8 and y=$yTo and ID=$game_id";
			$db->query($sql, __FILE__, __LINE__, __FUNCTION__);
		} else
		{
			/** @FIXME müsste statt "chess_board" wohl der table "chess_board_OLD" sein... ev. DEPRECATED? */
			$sql = "update chess_board set x=4, y=$yTo where x=1 and y=$yTo and ID=$game_id";
			$db->query($sql, __FILE__, __LINE__, __FUNCTION__);
		}
	}
	/**
	 * End Rochade
	 * @FIXME müsste statt "chess_board" wohl der table "chess_board_OLD" sein... ev. DEPRECATED?
	 */
	$sql = "update chess_board set x=$xTo, y=$yTo where x=$xFrom and y=$yFrom and ID=$game_id";
	$db->query($sql, __FILE__, __LINE__, __FUNCTION__);
}

function getOpenChessGames($userID) {
/*
	global $db;

	$openGames = 0;
	if (isset($userID)) {
		$sql = "select g.ID, (case when user1=$userID then 'Weiss' " .
			 "                   when user2=$userID then 'Schwarz' end) as color ".
			 "from (chess_game g left outer join user u1 on user1=u1.ID) left outer join user u2 on user2=u2.ID " .
		       " where user1=$userID or user2=$userID";
		$result = $db->query($sql);
		while ( $rs = $db->fetch($result) ){
			if (getCurrentPlayer($rs[ID]) == $rs[color]) $openGames++;
		}

		return $openGames;
	}*/

}
