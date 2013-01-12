<?php
/**
 * Peter Funktionen
 * 
 * Hier ist die Hauptklasse zum Peter Spiel zu finden,
 * sowie all seine Funktionen.
 * 
 * @author [z]Duke, [z]domi
 * @package Zorg
 * @subpackage Peter
 */
/**
 * File Includes
 */
include_once($_SERVER['DOCUMENT_ROOT']."/includes/usersystem.inc.php");
include_once($_SERVER['DOCUMENT_ROOT']."/includes/messagesystem.inc.php");
include_once($_SERVER['DOCUMENT_ROOT']."/includes/forum.inc.php");


/**
 * Ausstehende Züge
 * 
 * Gibt die Anzahl ausstehenden Peter züge zurück
 * 
 * @return unknown
 */
function peter_zuege() {
	global $db;

	//Wenn user eingeloggt ist
	if($_SESSION['user_id']) {
		//Prüfen ob der User Peter züge machen kann
		$sql = "
		SELECT
			game_id
		FROM peter_games
		WHERE 
			next_player = $_SESSION[user_id]
			AND 
			status = 'lauft'";
		$result = $db->query($sql,__FILE__,__LINE__,__FUNCTION__);
		$rs = $db->fetch($result);
		
		$return = ($db->num($result)) ? "<a href='peter.php?game_id=$rs[game_id]'>".$db->num($result)." Peter </a> | " : "" ;
		return $return;
	}
}

/**
 * Peter Klasse
 * 
 * Dies ist die Hauptklasse zum Peter Spiel
 * 
 * @author [z]Duke, [z]domi
 * @version 1.0
 * @package Zorg
 * @subpackage Peter
 */
class peter {
	
	//zorg user id des Rosenverkäufers
	public $r_id = 439;
	
	/**
	 * Peter Klassenkonstruktor
	 * 
	 * Der Peter Klassenkonstruktor
	 * 
	 * @return klasse
	 * @param Game_ID $game_id
	 */
	function peter($game_id="") {
		global $db;
		if($game_id) {
			$this->game_id = $game_id;
			$this->lc = $this->lastcard();
		}	
		
	}
		
	/**
	 * Rosenverkäufer
	 * 
	 * Prüft und loggt den Rosenverkäufer an, gibt beim Prüfen zurück ob er angemeldet ist
	 * 
	 * @return int
	 * @param string $mode
	 */
	function rosenverkaufer($mode=1) {
		global $db;
		
		//Modus 1 = Rosenverkäufer zufällig einloggen
		if($mode == 1) {
			
			//würfeln ;)
			//srand(microtime()*1000000);
			//$rand = rand(1,5000);
			$rand = rand(1,100);
			
			//Wenn 23 gewürfelt wird
			if($rand == 23) {
				
				//Rosenverkäufer einloggen
				$sql = "
				UPDATE user 
				set lastlogin = currentlogin
				WHERE id = '".$this->r_id."'";
				$db->query($sql,__FILE__,__LINE__,__FUNCTION__);
				
				$sql = "
				UPDATE user 
				set currentlogin = now(),
				activity = now()
				WHERE id = '".$this->r_id."'";
				$db->query($sql,__FILE__,__LINE__,__FUNCTION__);
			}
		//Modes "alles" prüfen ob der Rosenverkäufer eingeloggt ist
		} else {
		
			//Prüfen auf Rosenverkäufer
			$sql = "
			SELECT
				UNIX_TIMESTAMP(activity) as act
			FROM user
			WHERE 
				id = '".$this->r_id."'
				AND
				(UNIX_TIMESTAMP(activity) + ".USER_TIMEOUT.") > UNIX_TIMESTAMP(now())";
			$result = $db->query($sql,__FILE__,__LINE__,__FUNCTION__);
			$rosen = $db->num($result);
			
			return $rosen;
		}
	}
	
	/**
	 * Karten Ausgeben
	 * 
	 * Gibt die Karten den Spielern aus
	 * 
	 * @return void
	 */
	function ausgeben() {
		global $db;
		
		//Game selektieren
		$sql = "
		SELECT 
		*
		FROM peter_games 
		WHERE 
			game_id = '".$this->game_id."'";
		$result = $db->query($sql,__FILE__,__LINE__,__FUNCTION__);
		$rs = $db->fetch($result);
		
		//anzahl spieler ermitteln
		$players = $rs['players'];
		
		//Wenn die Karten aufgehen
		if((36 % $players) == 0) {
			//Alle Karten selektieren
			$sql = "
			SELECT 
				*
			FROM peter";
		//Wenn die Karten nicht aufgehen
		} else {
			//Alle Karten selektieren ausser Eichel 6 (id 11)
			$sql = "
			SELECT 
				*
			FROM peter
			WHERE 
				card_id <> 11";
		}
		
		//karten selektieren
		$result = $db->query($sql,__FILE__,__LINE__,__FUNCTION__);
		$num_cards = $db->num($result);
		while($rs = $db->fetch($result)) {
			$karten[] = $rs['card_id'];	
		}
		
		//karten mischen
		shuffle($karten);
		
		//spieler selektieren
		$sql = "
		SELECT 
			*
		FROM peter_players
		WHERE 
			game_id = '".$this->game_id."'";
		$result = $db->query($sql,__FILE__,__LINE__,__FUNCTION__);
		while($rs = $db->fetch($result)) {
			$in = ($rs['join_id'] - 1);
			$pp[$in] = $rs['user_id'];
		}
		
		//karten den spielern ausgeben
		for($i = 0;$i<$num_cards;$i++) {
			$in = ($i % $players);
			
			$uu = $pp[$in];
			$sql = "
			INSERT into peter_cardsets
			(game_id, card_id, user_id, status, datum)
			VALUES
			('".$this->game_id."','$karten[$i]',$uu,'nicht gelegt', now())";
			$db->query($sql,__FILE__,__LINE__,__FUNCTION__);
			$card[$uu][] = $karten[$i];
		}
		
		//game status updaten
		$sql = "
		UPDATE peter_games set status = 'lauft' WHERE game_id = '".$this->game_id."'";
		$db->query($sql,__FILE__,__LINE__,__FUNCTION__);
	}
	
	
	
	/**
	 * Nächster Spieler
	 * 
	 * Schaltet den nächsten Player in einem Spiel frei
	 * 
	 * @return void
	 * @param User_ID $act_player
	 * @param num_players $players
	 */
	function next_player($act_player,$players) {
		global $db;
		
		//Join ID des aktuellen Spielers ermitteln
		$sql = "
		SELECT 
			join_id
		FROM peter_players pp
		WHERE
			pp.user_id = $act_player
			AND
			pp.game_id = ".$this->game_id;
		$rr = $db->query($sql,__FILE__,__LINE__,__FUNCTION__);
		$rr = $db->fetch($rr);
		
		//Join ID inkrementieren oder wenn max players erreicht wurde wieder bei 1 anfangen
		$next_join_id = ($rr['join_id'] == $players) ? 1 : $rr['join_id'] + 1;
		
		
		$sql = "
		SELECT
			user_id,
			make
		FROM peter_players pp
		WHERE
			pp.game_id = ".$this->game_id."
			AND
			pp.join_id = $next_join_id";
		$rr = $db->query($sql,__FILE__,__LINE__,__FUNCTION__);
		$rr = $db->fetch($rr);
		
		//Prüfen ob der Spieler nicht bereits fertig ist
		if($rr['make'] == "fertig") {
			$this->next_player($rr['user_id'],$players);
		}
		
		//Nächster Spieler freischalten
		$sql = "
		UPDATE peter_games set next_player = '$rr[user_id]', last_activity = now() WHERE game_id = ".$this->game_id;
		$db->query($sql,__FILE__,__LINE__,__FUNCTION__);
		
		//Sendet dem nächsten Spieler eine (random) Message, damit er weiss, dass er dran ist
		//IneX, 22.08.2011
		$subject = array(
					'Du bisch dra im Peter Spiel #'.$this->game_id,
					'Maaach mol din Zug im Peter!',
					'Hallo? Spielsch no mit im Peter oder wa?',
					'Peter ruft!',
					'Du Peter, spiel mol din Peter Zug!',
					'Blah blah blah isch din Zug im Peter blah blah',
					'Spiel Peter oder i segs dim Mami!');
		$text = 'I ha min Zug gmacht i &uuml;sem Peter Spiel, etz bisch du wieder dra!<br/><br/>&#8594; <a href="http://www.zorg.ch/peter.php?game_id='.$this->game_id.'">Mach doooo!</a>';
		$rand_subject = array_rand($subject,1);
		Messagesystem::sendMessage($act_player, $rr['user_id'], $subject[$rand_subject], $text);
		
	}
	
	/**
	 * POST-Befehl ausführen
	 * 
	 * Führt POST im Peter aus
	 * 
	 * @return void
	 */
	function exec_peter() {
		global $db;
		
		//Wenn POST Daten da sind
		if(count($_POST)) {
			//Prüfen auf "players" - heisst das ein neues spiel erstellt wird
			if($_POST['players']) {
				//Prüfen obs eine Zahl ist, sonst wird 4 angenommen
				$players = (!is_numeric($_POST['players']) ? 4 : $_POST['players']);
				//Prüfen das players nicht 6 übersteigt
				$players = ($players > 6) ? 6 : $players;
				//Wenns weniger sind als zwei, werden zwei verwendet
				$players = ($players == 1) ? 2 : $players;
				//Game aufmachen
				$this->peteruuf($players);
			} 
			//Wenn ein wunsch da ist ;)
			if($_POST['wunsch']) {
				$this->set_wunsch($_POST['wunsch']);
				header("Location: http://".$_SERVER['SERVER_NAME'].$_SERVER['PHP_SELF']."?game_id=".$this->game_id."&".session_name()."=".session_id());
			}
		}
		
		//Prüfen ob in dem spiel nicht autom. der letzte spieler wieder am zug ist
		if($this->lc['value'] > 0 && $this->game_id) {
			$this->auto_nextplayer();
		}
		
		//Inaktive Spieler übergehen
		$sql = "
		SELECT 
			* 
		FROM peter_games 
		WHERE
			(UNIX_TIMESTAMP(now()) - (86400*2)) > UNIX_TIMESTAMP(last_activity)
			AND
			status = 'lauft'" ;
		$result = $db->query($sql,__FILE__,__LINE__,__FUNCTION__);
		$old_game_id = $this->game_id;
		while($rs = $db->fetch($result)) {
			$this->game_id = $rs['game_id'];
			//Nächster Spieler freischalten
			$this->next_player($rs['next_player'],$rs['players']);
		}
		$this->game_id = $old_game_id;
	}
	
	/**
	 * Farbe wünschen
	 * 
	 * Setzt einen Wunsch für ein Game
	 * 
	 * @return void
	 * @param string $wunsch
	 */
	function set_wunsch($wunsch) {
		global $db;
		
		//Prüfen ob noch ein Wunsch gsetzt werden kann
		if(!$this->checkwunsch()) {
			
			//Wunsch setzten
			$sql = "
			INSERT into peter_wunsche
			(game_id, card_id, user_id, wunsch, datum)
			VALUES
			(".$this->game_id.", ".$this->lc['card_id'].", ".$_SESSION['user_id'].",'".$wunsch."', now())";
			$db->query($sql,__FILE__,__LINE__,__FUNCTION__);	
			
			//Anzahl Players ermitteln
			$sql = "
			SELECT
				players
			FROM peter_games
			WHERE 
				game_id = ".$this->game_id;
			$result = $db->query($sql,__FILE__,__LINE__,__FUNCTION__);
			$rs = $db->fetch($result);
			
			//Nächster Spieler freischalten
			$this->next_player($_SESSION['user_id'],$rs['players']);
		}
	}
	
	/**
	 * Spiel erstellen
	 * 
	 * Eröffnet ein Spiel, und joint den erstellen autom.
	 * 
	 * @return void
	 * @param num_players $players
	*/
	function peteruuf($players) {
		global $db;
	
		//spiel erstellen
		$sql = "
		INSERT into peter_games
		(players, next_player, status, last_activity)
		VALUES
		($players,".$_SESSION['user_id'].",'offen',now())";
		$db->query($sql,__FILE__,__LINE__,__FUNCTION__);
		
		$game_id = $db->lastid();
	
		//und grad joinen
		$sql = "
		INSERT into peter_players
		(game_id, join_id, user_id)
		VALUES
		('$game_id',1,'$_SESSION[user_id]')";
		$db->query($sql,__FILE__,__LINE__,__FUNCTION__);
	}
	
	/**
	 * Offene Spiele anzeigen
	 * 
	 * Gibt die offenen Spiele zurück
	 * 
	 * @return unknown
	*/
	function offene_spiele() {
		global $db;
		$html = "
		<br />
		<table cellpadding='5'><tr valign='top'><td>
		<table width='500' cellpadding='2' cellspacing='1' bgcolor='#".BORDERCOLOR."'>
		<tr><td colspan='4' class='title' align='center' bgcolor='#".TABLEBACKGROUNDCOLOR."'>
		<b>Offene Spiele</b></td></tr>
		<tr>
		<td class='title' bgcolor='#".TABLEBACKGROUNDCOLOR."'>Spiel</td>
		<td class='title' bgcolor='#".TABLEBACKGROUNDCOLOR."'>Spieler</td>
		<td class='title' colspan='2' bgcolor='#".TABLEBACKGROUNDCOLOR."'>Anzahl Spieler</td>
		</tr>";
		
		$sql = "
		SELECT
			*
		FROM peter_games
		WHERE 
			status = 'offen'";
		$result = $db->query($sql,__FILE__,__LINE__,__FUNCTION__);
		while($rs = $db->fetch($result)) {
			$html .= "
			<tr>
				<td bgcolor='#".BACKGROUNDCOLOR."'>
				".$rs['game_id']."
				</td><td bgcolor='#".BACKGROUNDCOLOR."'>";
			$sql = "
			SELECT
				*
			FROM peter_players pp
			LEFT JOIN user u
				ON u.id = pp.user_id
			WHERE 
				pp.game_id = '$rs[game_id]'
			ORDER by pp.join_id ASC";
			$resulti = $db->query($sql,__FILE__,__LINE__,__FUNCTION__);
			
			$gejoint = FALSE;
			while($rsi = $db->fetch($resulti)) {
				$html .= $rsi['username']." ";
				if($rsi['user_id'] == $_SESSION['user_id']) {
					$gejoint = TRUE;	
				}
			}	
			
			$html .= "
			<td bgcolor='#".BACKGROUNDCOLOR."'>".$rs['players']."</td>";
			
			if($gejoint == FALSE) {
				$html .= "
				</td><td bgcolor='#".BACKGROUNDCOLOR."'>
				<a href='$_SERVER[PHP_SELF]?game_id=$rs[game_id]' class='button'><B>join</B></a>
				</td></tr>";
			} else {
				$html .= "
				</td><td bgcolor='#".BACKGROUNDCOLOR."'>&nbsp;</td></tr>";
			}
		}
		$html .= "
		</table>
		</td><td>";
	
		$html .= $this->neu_form();
		
		$html .= "</td></tr></table>";
	
		return $html;
	}
	
	/**
	 * Laufende Spiele
	 * 
	 * Gibt die Laufenden Spiele zurück
	 * 
	 * @return unknown
	*/
	function laufende_spiele() {
		global $db;
		
		$html = "
		<table cellpadding='2' cellspacing='1' bgcolor='#".BORDERCOLOR."'>
		<tr><td align='center' class='title' colspan='2' bgcolor='#".TABLEBACKGROUNDCOLOR."'>
		Spiele
		</td></tr>
		<tr><td class='title' bgcolor='#".TABLEBACKGROUNDCOLOR."'>
		Spieler
		</td><td class='title' bgcolor='#".TABLEBACKGROUNDCOLOR."'>
		am Zug
		</td></tr>";
		
		$sql = "
		SELECT
			pg.game_id,
			pg.next_player,
			u.username
		FROM peter_players pp
		LEFT JOIN peter_games pg
			ON pg.game_id = pp.game_id
		LEFT JOIN user u
			ON u.id = pg.next_player
		WHERE 
			pg.status = 'lauft'
			AND
			pp.user_id = '$_SESSION[user_id]'";
		$result = $db->query($sql,__FILE__,__LINE__,__FUNCTION__);
		while($rs = $db->fetch($result)) {
			$game = "";
			$sql = "
			SELECT
				u.username
			FROM peter_players pp
			LEFT JOIN user u
				ON u.id = pp.user_id
			WHERE 
				pp.game_id = $rs[game_id]
			ORDER by pp.join_id ASC";
			$resulti = $db->query($sql,__FILE__,__LINE__,__FUNCTION__);
			while($rsi = $db->fetch($resulti)) {
				$game .= $rsi['username']." ";
			}
			$col = ($rs['next_player'] == $_SESSION['user_id']) ? "<b style='color: #FF0000;'>" : "<b>";
			
			$html .= "
			<tr align='left' bgcolor='#".BACKGROUNDCOLOR."'><td>
			<a href='$_SERVER[PHP_SELF]?game_id=$rs[game_id]'>".$col.$game."</b></a>
			</td>
			<td bgcolor='#".BACKGROUNDCOLOR."'>".$rs['username']."</td></tr>";
		}
		$html .= "</table>";	
		
		return $html;
		
	}
	
	/**
	 * @return void
	 * @param num_players $players
	 * @desc Joint einen Spieler in ein Game
	*/
	function peter_join($players) {
		global $db;
		
		//Anzahl bisher gejointe ermitteln
		$sql = "
		SELECT 
			*
		FROM peter_players 
		WHERE 
			game_id = '".$this->game_id."'";
		$result = $db->query($sql,__FILE__,__LINE__,__FUNCTION__);
		$num = $db->num($result) + 1;
		
		//player eintragen
		$sql = "
		INSERT into peter_players
		(game_id, join_id, user_id)
		VALUES
		('".$this->game_id."', '$num', '$_SESSION[user_id]')";
		$db->query($sql,__FILE__,__LINE__,__FUNCTION__);
		
		//prüfen ob game gestartet werden soll
		if($num >= $players) {
			
			//Karten ausgeben
			$this->ausgeben();
			header("Location: http://".$_SERVER['SERVER_NAME'].$_SERVER['PHP_SELF']."?game_id=".$this->game_id."&".session_name()."=".session_id());
		}
		header("Location: http://".$_SERVER['SERVER_NAME'].$_SERVER['PHP_SELF']."?".session_name()."=".session_id());		
	}
	
	/**
	 * @return Array
	 * @desc Gibt alle wichtigen Infos zur zuletzt gelegten Karte als Array zurück
	*/
	function lastcard() {
		global $db;
		$sql = "
		SELECT
			p.description,
			p.value,
			p.col,
			pc.card_id,
			pc.spezial,
			u.username,
			pc.user_id
		FROM peter_cardsets pc
		LEFT JOIN peter p
			ON p.card_id = pc.card_id
		LEFT JOIN user u
			ON u.id = pc.user_id
		WHERE 
			pc.game_id = '".$this->game_id."'
			AND
			pc.status = 'gelegt'
		ORDER by pc.datum DESC
		LIMIT 0,1";
		$res = $db->query($sql,__FILE__,__LINE__,__FUNCTION__);
		
		if($res['spezial'] == "rosen") {
			$sql = "
			SELECT
				p.description,
				p.value,
				p.col,
				pc.card_id,
				pc.spezial,
				u.username
			FROM peter_cardsets pc
			LEFT JOIN peter_spezialregeln p
				ON p.card_id = pc.card_id
			LEFT JOIN user u
				ON u.id = pc.user_id
			WHERE 
				pc.game_id = '".$this->game_id."'
				AND
				pc.status = 'gelegt'
				AND
				p.typ = 'rosen'
			ORDER by pc.datum DESC
			LIMIT 0,1";
			$res = $db->query($sql,__FILE__,__LINE__,__FUNCTION__);
		}
		
		return $db->fetch($res);	
	}
	
	/**
	 * @return string
	 * @desc Gibt den Status (anzahl noch verbleibende Karten) zu jedem Spieler zurück
	*/
	function spielerstatus() {
		global $db;
	
		$sql = "
		SELECT
			pp.join_id,
			u.username,
			tt.num_cards
		FROM 
		(
			SELECT
				count(pc.card_id) as num_cards,
				pc.user_id as user_id
			FROM peter_cardsets pc
			WHERE 
				pc.game_id = ".$this->game_id."
				AND
				pc.status = 'nicht gelegt'
			GROUP by pc.user_id
		) as tt
		LEFT JOIN user u
			ON u.id = tt.user_id
		LEFT JOIN peter_players pp
			ON pp.user_id = tt.user_id
		WHERE 
			pp.game_id = ".$this->game_id."
		ORDER by join_id ASC";
		$result = $db->query($sql,__FILE__,__LINE__,__FUNCTION__);
		
		$html = "
		<table cellpadding='2' cellspacing='1' bgcolor='#".BORDERCOLOR."'><tr>";
		while($rs = $db->fetch($result)) {
			$html .= "<td bgcolor='#".TABLEBACKGROUNDCOLOR."'>".$rs['username']." : <b>".$rs['num_cards']."</b> Karten</td>";
		}	
		$html .= "</tr></table>";
		
		return $html;
	}
	
	/**
	 * @return void
	 * @desc Aktiviert autom. den nächsten Spieler wenn keiner eine höhere Karte hat
	*/
	function auto_nextplayer() {
		global $db;
		
		//Rosen prüfer
		$rosen = $this->rosenverkaufer("check");
		
		//Wenn der Rosenverkäufer da ist
		if(!$rosen == 1) {
			$sql = "
			SELECT
				*
			FROM peter_cardsets pc
			LEFT JOIN peter p
				ON p.card_id = pc.card_id
			WHERE
				pc.game_id = ".$this->game_id."
				AND
				pc.status = 'nicht gelegt'
				AND
				p.value > ".$this->lc['value'];
		}
		
		$result = $db->query($sql,__FILE__,__LINE__,__FUNCTION__);
		
		//Wenn niemand eine höhere Karte hat
		if(!$db->num($result)) {
			//Spieler nochmals aktivieren
			$sql = "
			UPDATE peter_games set next_player = '".$this->lc['user_id']."', last_activity = now()
			WHERE game_id = ".$this->game_id;
			$db->query($sql,__FILE__,__LINE__,__FUNCTION__);
			
			//Karten Daten neu laden
			$this->lc = $this->lastcard();
		}
	}
	/**
	 * @return bool
	 * @param $card_id Card_ID
	 * @desc ermittelt ob ein Zug zulässig ist oder nicht
	*/
	function regelcheck($card_id) {
		global $db;
		
		//Grundsätzlich den Zug einmal als falsch einstufen
		$set = 0;
		
		//Prüfen ob der Rosenverkäufer da ist
		$rosen = $this->rosenverkaufer("check");
		
		//Rosenverkäuferanpassungen
		$regel_table = ($rosen == 1) ? "peter_spezialregeln" : "peter";
		$regel_add = ($rosen == 1) ? "AND typ = 'rosen'" : " ";
			
		//Prüfen ob bereits eine Karte gelegt wurde
		$sql = "
		SELECT
			*
		FROM peter_cardsets pc
		WHERE
			pc.game_id = ".$this->game_id."
			AND
			pc.status = 'gelegt'";
		$rr = $db->query($sql,__FILE__,__LINE__,__FUNCTION__);
		
		//Bei Spiel Anfang kann alles gelegt werden
		if(!$db->num($rr)) {
			$set = 1;
		}
		
		//Prüfen ob der User die Karte noch hat
		$sql = "
		SELECT
			*
		FROM peter_cardsets pc
		WHERE
			pc.game_id = ".$this->game_id."
			AND
			pc.user_id = $_SESSION[user_id]
			AND
			pc.card_id = $card_id
			AND
			pc.status = 'nicht gelegt'";
		$rr = $db->query($sql,__FILE__,__LINE__,__FUNCTION__);
		if($db->num($rr)) {
			
			//Prüfen ob Karte gesetzt werden darf
			$sql = "
			SELECT
				*
			FROM ".$regel_table."
			WHERE 
				card_id = '$card_id' 
			".$regel_add;
			$ac = $db->query($sql,__FILE__,__LINE__,__FUNCTION__);
			$ac = $db->fetch($ac);
			
			//Wenn die zulegende Karte ein Zahl ist
			if($ac['value'] <= 5 && $this->lc['value'] != 0) {
				//Prüfen das die gleichen Farben gelegt werden
				if($ac['col'] == $this->lc['col']) {
					//prüfen das der wert der karte höher ist
					if($ac['value'] > $this->lc['value'] ) {
						$set = 1;	
					}
				}
			//Wenn keine Zahl und kein Nichtraucher
			} elseif($this->lc['value'] != 0 && $ac['value'] > 5) {
				//Prüfen das der wert der zulegenden Karte höher ist
				if($ac['value'] > $this->lc['value']) {
					$set = 1;	
				}	
			//Wenn die letzte Karte ein Nichtraucher wae
			} elseif($this->lc['value'] == 0) {
				//wunsch selektieren
				$wunsch = $this->get_wunsch();
				//Prüfen ob aktuell zulegende karte eine zahl ist
				if($ac['value'] <= 5) {
					//prüfen ob die farbe der zahl mit dem wunsch übereinstimmt
					if($ac['col'] == $wunsch['col_id']) {
						$set = 1;
					}
				//Wenns keine Zahl ist
				} else {
					$set = 1;
				}
				//Wenn ein Nichtraucher draufgelegt werden soll
				if($ac['value'] == 0) {
					$set = 1;
				}
			}
			//Wenn die letzte geleget Karte vom gleichen Spieler ist wie die zulegende
			if($this->lc['user_id'] == $_SESSION['user_id']) {
				$set = 1;	
			}
			
		}
		return $set;	
	}
	
	/**
	 * @return bool
	 * @desc Prüft ob bereits ein Wunsch zu einem Nichtraucher abgegeben wurde oder nicht
	*/
	function checkwunsch() {
		global $db;
		
		//Wenn die zuletzt gelegte Karte ein Nichtraucher ist und wenn auch eine gelegt wurde
		if($this->lc['value'] == 0 && $this->lc['card_id']) {
			
			//Prüfen ob bereits ein wunsch zu dieser Karte vorliegt
			$sql = "
			SELECT
				pw.wunsch,
				u.username
			FROM peter_wunsche pw
			LEFT JOIN user u
				ON
				u.id = pw.user_id
			WHERE
				pw.game_id = ".$this->game_id."
				AND
				pw.card_id = ".$this->lc[card_id];
			$result = $db->query($sql,__FILE__,__LINE__,__FUNCTION__);
			
			//Wenn bereits ein Wunschvorliegt
			if($db->num($result)) {
				return 1;	
			} else {
				return 0;
			}
		} else {
			return 1;
		}
	}
	
	/**
	 * @return array
	 * @desc Gibt die Daten zum letzten Wunsch als Array zurück
	*/
	function get_wunsch() {
		global $db;
	
		//Ermittelt den Letzten Wunsch zu einem game
		$sql = "
		SELECT
			pw.wunsch,
			u.username
		FROM peter_wunsche pw
		LEFT JOIN user u
			ON u.id = pw.user_id
		WHERE 
			pw.game_id = ".$this->game_id."
		ORDER by datum DESC
		LIMIT 0,1";
		$result = $db->query($sql,__FILE__,__LINE__,__FUNCTION__);
		$rs = $db->fetch($result);
		
		//zuweisungs Array
		$a_zw = array("Eichel" => 1, "Rosen" => 2, "Schellen" => 3, "Schilten" => 4);
		
		//Daten Array füllen
		$data['col_id'] = $a_zw[$rs['wunsch']];
		$data['col'] = $rs['wunsch'];
		$data['username'] = $rs['username'];
		
		return $data;
		
	}
	
	/**
	 * @return void
	 * @param Card_ID $card_id
	 * @param (karte|aus) $make
	 * @param num_players $players
	 * @desc Führt einen Zug aus
	*/
	function zug($card_id="", $make, $players) {
		global $db, $user;
		
		
		//Prüfen ob der Rosenverkäufer umherschleicht
		$rosen = $this->rosenverkaufer("check");
		$spezial = ($rosen == 1) ? "rosen" : " ";
		
		//Wenn eine Karte gesetzt werden soll
		if($make == "karte") {
			//prüfen ob der zug zulässig ist
			if($this->regelcheck($card_id)) {
				//Zug ausführen
				$sql = "
				UPDATE peter_cardsets set 
					datum = now(), 
					status = 'gelegt',
					spezial = '$spezial'
				WHERE 
					game_id = ".$this->game_id."
					AND 
					card_id = $card_id";
				$db->query($sql,__FILE__,__LINE__,__FUNCTION__);
				
				
				### Zug als Comment eintragen, damit man darüber diskutieren kann ###
				/*$sql = "
					SELECT *
					FROM peter_cardsets
					WHERE card_id = '".$card_id."'
					";
				
				$result = $db->query($sql,__FILE__,__LINE__,__FUNCTION__);
				$rs = $db->fetch($result);
				
				$text = "Ich habe die Karte '".$rs[description]."' gelegt.";
				Forum::post($this->game_id, 'p', $user->id, $text, $msg_users='');
				*/
				
				
				//Spieler Table updaten
				$sql = "
				UPDATE peter_players set make = '$make' 
				WHERE user_id = $_SESSION[user_id] AND game_id = ".$this->game_id;
				$db->query($sql,__FILE__,__LINE__,__FUNCTION__);
				
				//Prüfen ob nicht noch ein Wunsch nötig ist bevor der nächste am zug ist
				if($this->checkwunsch()) {
					//Nächster Player aktivieren
					$this->next_player($_SESSION['user_id'],$players);
				}
			}
		}
		
		if($make == "aus") {
			### Zug als Comment eintragen, damit man darüber diskutieren kann ###
			/*$text = "Ich setze diese Runde aus.";
			Forum::post($this->game_id, 'p', $user->id, $text, $msg_users='');
			*/
			
			//Spieler Table updaten
			$sql = "
			UPDATE peter_players set make = '$make' 
			WHERE user_id = $_SESSION[user_id] AND game_id = ".$this->game_id;
			$db->query($sql,__FILE__,__LINE__,__FUNCTION__);
			
			$this->next_player($_SESSION['user_id'],$players);
			
			// Wenn der User noch weitere offen Züge hat, direkt weiterleiten
			// Prüfen, ob noch Züge offen sind
			$sqli = "
			SELECT
				game_id
			FROM peter_games
			WHERE 
				next_player = $_SESSION[user_id]
				AND 
				status = 'lauft'";
			$resulti = $db->query($sql,__FILE__,__LINE__,__FUNCTION__);
			$rsi = $db->fetch($resulti);
			
			// Wenn noch offene Züge, dann direkt ins nächste Spiel weiterleiten
			if ($db->num($result)) header("Location: http://www.zorg.ch/peter.php?game_id=$rs[game_id]");

		}
		
		//Prüfen ob Spiel beendet werden soll
		$sql = "
		SELECT
			*
		FROM peter_cardsets pc
		WHERE
			pc.game_id = ".$this->game_id."
			AND
			pc.user_id = $_SESSION[user_id]
			AND
			pc.status = 'nicht gelegt'";
		$rr = $db->query($sql,__FILE__,__LINE__,__FUNCTION__);
		
		//Wenn das Spiel beendet werden kann
		if(!$db->num($rr)) {
			
			//Spiel beenden
			$sql = "
			UPDATE peter_games set status = 'geschlossen', winner_id = $_SESSION[user_id]
			WHERE game_id = ".$this->game_id;
			$db->query($sql,__FILE__,__LINE__,__FUNCTION__);
		}	
	}
	
	/**
	 * @return unknown
	 * @desc Gibt das Formular zum Spiel erstellen zurück
	*/
	function neu_form() {
		
		$html = "
		<form action='$_SERVER[PHP_SELF]' method='post'>
		<table  cellpadding='2' cellspacing='1' bgcolor='#".BORDERCOLOR."'>
		<tr><td colspan='2' align='center' class='title' bgcolor='#".TABLEBACKGROUNDCOLOR."'>
		Neues Spiel
		</td></tr><tr><td bgcolor='#".BACKGROUNDCOLOR."'>
		Anzahl Spieler: 
		</td><td bgcolor='#".BACKGROUNDCOLOR."'>
		<input type='text' name='players' class='text' size='4'>
		</td></tr><tr><td colspan='2' align='right' bgcolor='#".BACKGROUNDCOLOR."'>
		<input type='submit' name='maach' class='button' value='starten'>
		</td></tr></table>
		</form>	";
		
		return $html;
		
	}
	
	/**
	 * @return Array
	 * @param User_ID $user_id
	 * @desc Gibt das Cardset für einen User in einem Game als Array zurück
	*/
	function player_cardset($user_id) {
		global $db;
		
		//Prüfen ob der Rosenverkäufer...
		$rosen = $this->rosenverkaufer("check");
		
		//Wenn der Rosenverkäufer da ist
		if($rosen == 1 ) {
			//cardset für den betreffenden user im game selektieren und auf rosen regeln achten
			$sql = "
			SELECT
				*
			FROM peter_cardsets pc
			LEFT JOIN peter_spezialregeln p
				ON pc.card_id = p.card_id
			WHERE 
				pc.user_id = '$user_id'
				AND
				pc.game_id = '".$this->game_id."'
				AND
				pc.status = 'nicht gelegt'
				AND
				p.typ = 'rosen'
			ORDER by p.value DESC";
		} else {
			//Cardset für den User selektieren (normale regeln)
			$sql = "
			SELECT
				*
			FROM peter_cardsets pc
			LEFT JOIN peter p
				ON pc.card_id = p.card_id
			WHERE 
				pc.user_id = '$user_id'
				AND
				pc.game_id = '".$this->game_id."'
				AND
				pc.status = 'nicht gelegt'
			ORDER by p.value DESC";
		}
		return $db->query($sql,__FILE__,__LINE__,__FUNCTION__);	
	}
	
	/**
	 * @return unknown
	 * @desc Gibt die Highscore Seite zurück
	*/
	function peterscore() {
		global $db;	
		
		$html = "
		<br /><br />
		<table cellpadding='2' cellspacing='1'  bgcolor='#".BORDERCOLOR."'>
		<tr><td colspan='3' class='title' align='center' bgcolor='#".TABLEBACKGROUNDCOLOR."'>
		Haiskor
		</td></tr>
		<tr><td class='title' align='center' bgcolor='#".TABLEBACKGROUNDCOLOR."'>
		Spieler
		</td><td class='title' align='center' bgcolor='#".TABLEBACKGROUNDCOLOR."'>
		Anzahl Spiele
		</td><td class='title' align='center' bgcolor='#".TABLEBACKGROUNDCOLOR."'>
		Gewonnen
		</td></tr>";
		
		//Score Query
		$sql = "
		SELECT 
			gp.username, 
			gp.num_games_played, 
			COALESCE( gw.num_games, '0' ) as num_games
		FROM 
		(
			SELECT 
				u.username, 
				count( pp.game_id ) AS 
				num_games_played
			FROM peter_players pp
			LEFT JOIN peter_games pg 
				ON pg.game_id = pp.game_id
			LEFT JOIN user u 
				ON u.id = pp.user_id
			WHERE 
				pg.status = 'geschlossen'
			GROUP BY u.username
		) AS gp
		LEFT JOIN (
			SELECT 
				u.username, 
				count( pg.game_id ) AS num_games
			FROM peter_games pg
			LEFT JOIN user u 
				ON u.id = pg.winner_id
			WHERE 
			pg.status = 'geschlossen'
			GROUP BY u.username
			) AS gw 
			ON gp.username = gw.username
		ORDER BY num_games DESC";
	
		$result = $db->query($sql,__FILE__,__LINE__,__FUNCTION__);
		
		while($rs = $db->fetch($result)) {
			$html .= "
			<tr><td bgcolor='#".BACKGROUNDCOLOR."'>
			".$rs['username']."
			</td><td bgcolor='#".BACKGROUNDCOLOR."'>
			".$rs['num_games_played']."
			</td><td bgcolor='#".BACKGROUNDCOLOR."'>
			".$rs['num_games']."
			</td></tr>";	
		}
		
		$html .= "</table>";
		
		return $html;
	}
	
	
	
	/**
	 * @return string
	 * @desc Gibt das Formular zum Wünschen einer Farbe zurück
	*/
	function wunscher() {
		global $db;
	
		$html = "
		<form action='$_SERVER[PHP_SELF]?game_id=".$this->game_id."' method='post'>
		<table cellpadding='2' cellspacing='1' bgcolor='#".BORDERCOLOR."'>
		<tr><td bgcolor='#".BACKGROUNDCOLOR."'>
		<input type='radio' name='wunsch' value='Eichel'> Eichel
		</td><td bgcolor='#".BACKGROUNDCOLOR."'>
		<input type='radio' name='wunsch' value='Rosen'> Rosen
		</td><td bgcolor='#".BACKGROUNDCOLOR."'>
		<input type='radio' name='wunsch' value='Schellen'> Schellen
		</td><td bgcolor='#".BACKGROUNDCOLOR."'>
		<input type='radio' name='wunsch' value='Schilten'> Schilten
		</td></tr bgcolor='#".BACKGROUNDCOLOR."'>
		<tr><td colspan='4' align='right' bgcolor='#".BACKGROUNDCOLOR."'>
		<input type='submit' value='wünschen' class='button'>
		</td></tr>
		</table>
		</form>";
		
		return $html;
	}
	
	/**
	* @return string
	* @param Array $gd
	* @param Card_ID $card_id
	* @param (karte|aus) $make
	* @desc Zeigt ein Spiel an
	*/
	function game($gd,$card_id="",$make="") {
		global $db;
		
		
		//cardset für den betreffenden user im game selektieren
		$resultp = $this->player_cardset($_SESSION['user_id']);
		
		$html = "
		<br />
		<b>Aktuelle Karte</b>
		<br />".$this->lc['description']."<br />
		<hr size='1' width='100%'>
		<table width='100%'>
		<tr>
		<td align='center' valign='middle'>
		<br /><br />
		<img src='".$_SERVER['PHP_SELF']."?img=karten&game_id=".$this->game_id."' title='".$this->lc[description]."'>";
		
		//Wenn das Spiel läuft
		if($gd['status'] == "lauft") {
			
		$html .= "
		<br /><b>Gelegt von: ".$this->lc['username']."</b><br />
		<br />
		Am Zug ist: ".$gd['username']."<br /><br />
		";
		
			//Wenn der Spieler noch Karten hat
			if($db->num($resultp)) {
	
				//zug als richtig ansehen
				$zug = TRUE;
				//Wenn ein nichtraucher gelegt wurde UND noch kein Wunsch vorliegt
				if(!$this->checkwunsch() && $_SESSION['user_id'] == $this->lc['user_id'] && $this->lc['value'] == 0) {
					//Formular zum wünschen ausgeben
					$html .= $this->wunscher();
					//zug falsen
					$zug = FALSE;
					
				//Wenn ein nihtraucher gelegt wurde und ein Wunsch vorliegt
				} elseif ($this->lc['value'] == 0 && $this->checkwunsch() && $this->lc['card_id']) {
					
					//Daten über den Wunsch ermitteln
					$wunsch = $this->get_wunsch();
					//Wunsch anzeigen
					$html .= "<br /><b>".$wunsch['username']." hat ".$wunsch['col']." gewünscht!</b><br /><br />";	
				}
				
				$html .= "</td></tr></table>";
				
	
				if($make) {
					
					//Zug ausführen
					$this->zug($card_id,$make,$gd['players']);
					header("Location: http://".$_SERVER['SERVER_NAME'].$_SERVER['PHP_SELF']."?game_id=".$this->game_id."&".session_name()."=".session_id());
				}
				
				//Wenn der Spieler am Zug ist, ANZEIGE
				$add = ($gd['next_player'] == $_SESSION['user_id']) ? "<br /><blink><b>!!! Du bist am Zug !!!</b></blink>" : "";
				
				$html .= "
				<br />
				<hr size='1' width='100%'>"
				.$this->spielerstatus()
				."<br />
				<b>Meine Karten</b>$add
				<br /><hr size='1' width='100%'>
				<br />";
				
				//Aussetzen Button anzeigen wenn der spieler am zug ist
				if ($gd['next_player'] == $_SESSION['user_id']) {
					$html .= "
					<form action='$_SERVER[PHP_SELF]?game_id=".$this->game_id."&make=aus' method='post'>
					<input type='submit' value='aussetzen' class='button'>
					</form><br /><br />";
				}
				
				//Ausgabe der restlichen karten des Spielers
				while($rs = $db->fetch($resultp)) {
					if($gd['next_player'] == $_SESSION['user_id'] && $zug) {
						$html .= "
						<a href='$_SERVER[PHP_SELF]?game_id=".$this->game_id."&card_id=$rs[card_id]&make=karte'>
						<img border='0' src='/images/peter/".$rs['card_id'].".gif' alt='$rs[description]' title='$rs[description]'>
						</a>";
					} else {
						$html .= "
						<img border='0' src='/images/peter/".$rs['card_id'].".gif' alt='$rs[description]' title='$rs[description]'>
						";
					}
				}
		
			}
		//Wenn Spiel beendet wurde
		} elseif($gd['status'] == "geschlossen") {
			$sql = "
			SELECT
				username
			FROM user WHERE id = '$gd[winner_id]'";
			$res = $db->query($sql,__FILE__,__LINE__,__FUNCTION__);
			$rs = $db->fetch($res);
			
			//Gewinner anzeigen
			$html .= "
			<br /><b> Gewinner: ".$rs['username']."</b><br /><br />";
		}
		
		ob_end_flush();
		ob_start();
		//thread ausgabe
		echo "<br /><br />".Forum::printCommentingSystem('p', $this->game_id);
		$html .= ob_get_contents();
		ob_end_clean();
		ob_start();
		
		return $html;
	}
	
	/**
	 * Kartenberg
	 * 
	 * Gibt das Kartenberg Image komplett zurück
	 * 
	 * @return img_handle
	 */
	function kartenberg() {
		global $db;
		
		$sql = "
		SELECT
			pc.card_id,
			u.username,
			p.description
		FROM peter_cardsets pc
		LEFT JOIN user u
			ON u.id = pc.user_id
		LEFT JOIN peter p
			ON p.card_id = pc.card_id
		WHERE 
			pc.game_id = '".$this->game_id."'
			AND
			pc.status = 'gelegt'
		ORDER by datum ASC";
		
		$result = $db->query($sql,__FILE__,__LINE__,__FUNCTION__);
		$i = 0;
		
		if (mysql_num_rows($result) > 0 && mysql_num_rows($result) != NULL)
		{
		
			while($rs = $db->fetch($result)) {
				if($i == 0) {
					$img = $this->img_kartenberg($rs['username'],$rs['card_id'],1,1);
				} else {
					$img = $this->img_kartenberg($rs['username'],$rs['card_id'],$img,2,$i);
				}
				$i++;
			}
			
			$db->seek($result,0);
			$i = 0;
			
			//Schriftfarbe anlegen
			$red = hexdec(substr(FONTCOLOR,0,2));
			$green = hexdec(substr(FONTCOLOR,2,2));
			$blue = hexdec(substr(FONTCOLORR,4,2));
			$fontc = imagecolorallocate($img,$red,$green,$blue);
			
			while($rs = $db->fetch($result)) {
				imagettftext($img,8,0,5,350+($i * 11),$fontc,"images/peter/verdana.ttf",$rs['description']);
				$i++;
			}
		}
		else
		{
			$img = imagecreatefromgif($_SERVER['DOCUMENT_ROOT']."images/peter/jassteppich.gif");
		}
		
		return $img;
	}
	
	
	
	/**
	 * Bild der Karten
	 * 
	 * Generiert die Kartenberge, legt ein Bild auf ein anderes
	 * 
	 * @return object
	 * @param string $username
	 * @param Card_ID $card_id
	 * @param object $old_img
	 * @param (1|2) $mode
	 * @param int $depth
	*/
	function img_kartenberg($username,$card_id,$old_img,$mode=2,$depth=0) {
		global $db, $new_y_pos;
		if(imagesx($old_img) > 600) {
			$xx = 90;
		} else {
			$xx = 50;
		}
		//Wenn das Image das erste ist
		if($mode == 1) {
			//start image erstellen
			$old_img = imagecreatefromgif($_SERVER['DOCUMENT_ROOT']."/images/peter/".$card_id.".gif");
			
			//Font color für Namen bestimmen
			$fontc = imagecolorallocate($old_img,0,0,0);
			
			//Text auf Bild schreiben
			imagettftext($old_img,7,0,13,9,$fontc,"images/peter/verdana.ttf",$username);
			
			return $old_img;
			
		//Wenn bereits ein Image besteht
		} elseif($mode == 2) {
			
			// x-achsen verschiebung ermitteln
			srand(microtime()*1000000);
			$x_verschiebung = rand(20,45);
			
			//y-achsen verschiebung ermitteln
			srand(microtime()*1000000);
			$y_verschiebung = rand(20,45);
			
			//hinzuzufügende karte in ein image handle laden
			$add_img = imagecreatefromgif($_SERVER['DOCUMENT_ROOT']."/images/peter/".$card_id.".gif");
			
			//Schriftfarbe anlegen
			$fontc = imagecolorallocate($add_img,0,0,0);
			
			//Namen auf die Karten schreiben
			imagettftext($add_img,7,0,13,9,$fontc,"images/peter/verdana.ttf",$username);
	
			//w/h vom alten bild ermitteln
			$o_width = imagesx($old_img);	
			$o_height = imagesy($old_img);
			
			//x pos der neuen karte ermitteln
			srand(microtime()*1000000);
			//recht verschiebung
			if(rand(0,100) > $xx) {
				$new_x_pos = ($o_width+$x_verschiebung) - 125;
			//links verschiebung
			} else {
				$new_x_pos = $o_width - 125 - $x_verschiebung;
			}
			$new_x_pos = ($new_x_pos < 0 ? 0 : $new_x_pos);
			
			//y pos der neuen karten ermitteln
			$new_y_pos = ($y_verschiebung + $new_y_pos);
			$new_h = (($new_y_pos) > ($o_height + $y_verschiebung - 195)) ? $new_y_pos + 195 : $o_height + $y_verschiebung;
			
			//neues bild erstellen
			$new_img = imagecreatetruecolor($o_width + $x_verschiebung, $new_h);
			
			//bg color
			$red = hexdec(substr(BACKGROUNDCOLOR,0,2));
			$green = hexdec(substr(BACKGROUNDCOLOR,2,2));
			$blue = hexdec(substr(BACKGROUNDCOLOR,4,2));
			$bg = imagecolorallocate($new_img,$red,$green,$blue);
			
			//bg color verwenden und bg füllen
			imagefill($new_img,0,0,$bg);
		
			//das alte bild ins neue kopieren
			imagecopy($new_img,$old_img,0,0,0,0,$o_width,$o_height);
			
			//neue karte ins bild einfügen
			imagecopy($new_img,$add_img,$new_x_pos,$new_y_pos,0,0,125,195);
			
			return $new_img;
		}
	}
}
?>