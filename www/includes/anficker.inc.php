<?php
/**
 * Anficker
 * 
 * Gibt Spresim erst die Macht die er braucht, um so richtig anzuficken
 * 
 * @author ?
 * @package Zorg
 * @subpackage Anficker
 */
/**
 * File Includes
 */
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/mysql.inc.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/usersystem.inc.php');

/**
 * Konstante ANFICKER_USER_ID
 */
define(ANFICKER_USER_ID, 9999);

/**
 * Spresim Klasse
 * 
 * Klasse für den Anfick-Battle gegen Spresim
 * 
 * @author ?
 * @version 2.0
 *
 * @package Zorg
 * @subpackage Anficker
 */
Class Anficker {
	
	/**
	 * Anfick des User hinzufügen
	 * 
	 * @author ?, IneX
	 * @version 2.0
	 * @since 1.0
	 * @see Anficker::logAnfick(), Anficker::getId()
	 *
	 * @param integer $user_id ID des Users, welcher gerade mit Spresim batteld
	 * @param string $text Anfick des Users
	 * @param boolean $spresim_trainieren Gibt an, ob Anfick des Users gespeichert werden soll oder nicht
	 * @global array $db Array mit allen MySQL-Datenbankvariablen
	 *
	 * @todo Unterschied, ob Spresim trainieren oder nur battlen sollte möglich sein (Bug #487) (Mättä, 25.10.04) | IDEE: Eine möglich Lösung wäre, ein zusätzliches Flag in der Tabelle "battle_only" oder so...
	 * @todo Müsste es nicht "REPLACE INTO..." sein?? Jetzt werden x-Einträge mit gleichem Text gemacht! (IneX, 8.6.09)
	 */
	function addAnfick($user_id, $text, $spresim_trainieren=FALSE) {
		global $db;
		
		// nur Anfick speichern, wenn Spresim trainiert werden soll:
		//if ($spresim_trainieren == TRUE)
		//{
			if($text != '' && !empty($user_id))//usersystem::id2user($user_id)))
			{
				$sql = 
					"INSERT IGNORE
						INTO anficker_anficks (
							text,
							user_id,
							datum)
						VALUES (
							'".$text."',
							$user_id,
							now()
							)
					";
				$db->query($sql, __FILE__, __LINE__);
			}
			//else
			//{
				
				
				// Hier sollte was kommen, WENN SPRESIM NICHT TRAINIERT WERDEN SOLL... leider zur Zeit nicht realisierbar, IneX 8.6.09
				// IDEE: Eine möglich Lösung wäre, ein zusätzliches Flag in der Tabelle "battle_only" oder so... (IneX, 8.6.09)
				
				
			//}
		//}
		
		// DEBUGGING
		//error_log('[DEBUG] ' . __FILE__ . ':' . __LINE__ . ' mysql_insert_id() = ' . mysql_insert_id());
		//error_log('[DEBUG] ' . __FILE__ . ':' . __LINE__ . ' Anficker::getId($text) = `' . $text . '`');
		
		$anfick_id = (mysql_insert_id() > 0 ? mysql_insert_id() : Anficker::getId($text));
		
		Anficker::logAnfick($anfick_id, $user_id, $user_id);
	}
	
	
	/**
	 * Anfick im Anfick-Log ergänzen
	 * 
	 * @author ?, IneX
	 * @version 2.0
	 * @since 1.0
	 * @see Anficker::addAnfick()
	 *
	 * @param integer $anfick_id ID des Anficks wo das Log ergänzt werden soll
	 * @param integer $user_id ID des Users, welcher angefickt wurde
	 * @param integer $anficker_id ID des Users, welcher den Anfick gemacht hat
	 * @global array $db Array mit allen MySQL-Datenbankvariablen
	 */
	function logAnfick($anfick_id, $user_id, $anficker_id) {
		global $db;
		$sql = 
			"
			INSERT INTO 
				anficker_log (datum, user_id, anficker_id, anfick_id)
			VALUES(
				now(),
				$user_id,
				$anficker_id,
				$anfick_id
			)";
		//return $db->query($sql, __FILE__, __LINE__);
		$db->query($sql, __FILE__, __LINE__);
	}
	
	
	function deleteLog($user_id) {
		global $db;
		$sql = 
			"
			DELETE FROM 
				anficker_log 
			WHERE user_id = '".$user_id."'
			"
		;
		return $db->query($sql, __FILE__, __LINE__);
	}
	
	
	function getId($text) {
		global $db;
		$sql = 
			"
			SELECT 
				id
			FROM
				anficker_anficks
			WHERE text = '".$text."'
			"
		;
		$rs = $db->fetch($db->query($sql, __FILE__, __LINE__));
		return $rs['id'];
	}
	
	
	/**
	 * Anfick-Log ausgeben
	 * 
	 * @author ?, IneX
	 * @version 2.0
	 * @since 1.0
	 * @see Anficker::logAnfick(), Anficker::anfickenMit()
	 *
	 * @param integer $user_id ID des Users, welcher gerade mit Spresim batteld
	 * @global array $db Array mit allen MySQL-Datenbankvariablen
	 * @return string Gibt das gesamte bisherige Anfick-Log des Battles 'User vs. Spresim' aus
	 */
	function getLog($user_id) {
		global $db;
		
		//Anficker::addRandomAnfick2Log($user_id, ANFICKER_USER_ID);
		Anficker::logAnfick(Anficker::anfickenMit(), $user_id, ANFICKER_USER_ID);
		
		$sql = 
			"
			SELECT 
				anficker_anficks.note
				, anficker_anficks.id
				, anficker_log.datum
				, anficker_anficks.text
				, anficker_log.anficker_id
			FROM
				anficker_log
			LEFT JOIN anficker_anficks ON (anficker_anficks.id = anficker_log.anfick_id)
			WHERE anficker_log.user_id = '".$user_id."'
			ORDER BY anficker_log.id ASC
			"
		;
		
		$result = $db->query($sql, __FILE__, __LINE__);
		
		while($rs = $db->fetch($result)) {
			$dialog[] = $rs;
		}
		
		return $dialog;
	}
	
	
	/**
	 * Anzahl existierender Anficks holen
	 * 
	 * @author ?
	 * @version 1.0
	 * @since 1.0
	 * @see Anficker::anfickenMit()
	 *
	 * @param integer $user_id ID des Users, welcher gerade mit Spresim batteld
	 * @return array Gibt ein Array mit der Anzahl Anficks (für Funktion anfickenMit()), durchschnittlicher Noten und Anzahl Votes (für /packages/anficks.php) zurück
	 */
	function getNumAnficks($user_id) {
		global $db;		
		$sql = 
			"
			SELECT
				COUNT(*) AS num
				, AVG(note) AS note
				, SUM(votes) AS votes
			FROM anficker_anficks
			"
		;
		$rs = $db->fetch($db->query($sql, __FILE__, __LINE__));
		return $rs;
	}
	
	
	/**
	 * Anfick ins Log schreiben und selbiges Ausgeben
	 * 
	 * @author ?, IneX
	 * @version 2.0
	 * @since 1.0
	 * @see Anficker::anfickenMit()
	 *
	 * @param integer $user_id ID des Users, welcher gerade mit Spresim batteld
	 * @return array Gibt ganzes Log der Anfickerei für Ausgabe zurück
	 *
	 * @DEPRECATED
	 */
	function addRandomAnfick2Log($user_id) {
		/*global $db;		
		$sql = 
			"
			SELECT 
				* 
			FROM
				anficker_anficks
			ORDER BY note ASC
			"
		;
		$result = $db->query($sql, __FILE__, __LINE__);
		
		// zufällige id holen	
		$rs = Anficker::getNumAnficks();
		$id = rand(0, $rs['num']-1); // Zufalls #
		$id = rand($id, $rs['num']-1); // die besten bevorzugen.
		mysql_data_seek($result, $id);
		
		$rs = $db->fetch($result);*/
		
		//return Anficker::logAnfick($rs['id'], $user_id, ANFICKER_USER_ID);
		Anficker::logAnfick(Anficker::anfickenMit(), $user_id, ANFICKER_USER_ID);
	}
	
	
	/**
	 * Spresim's Anfick an den User
	 * 
	 * @author IneX
	 * @date 08.06.2009
	 * @version 1.0
	 * @since 2.0
	 *
	 * @global array $db Array mit allen MySQL-Datenbankvariablen
	 * @return integer ID des Anfick von Spresim
	 */
	function anfickenMit()
	{
		global $db;		
		
		$sql = 
			"
			SELECT 
				* 
			FROM
				anficker_anficks
			ORDER BY note ASC
			"
		;
		$result = $db->query($sql, __FILE__, __LINE__);
		
		// zufällige id holen	
		$rs = Anficker::getNumAnficks();
		$id = rand(0, $rs['num']-1); // Zufalls #
		$id = rand($id, $rs['num']-1); // die besten bevorzugen.
		mysql_data_seek($result, $id);
		
		$rs = $db->fetch($result);
		
		return $rs['id'];
	}
	
	
	/**
	 * Anfick-Spruch benoten
	 * 
	 * @author ?, IneX
	 * @version 1.1
	 * @since 1.0
	 *
	 * @param integer $anfick_id ID des benoteten Anficks
	 * @param integer $note Bewertung des Anficks
	 * @global array $db Array mit allen MySQL-Datenbankvariablen
	 */
	function vote($anfick_id, $note) {
		global $db;
		$sql = 
			"
			UPDATE 
				anficker_anficks
			SET 
				note = ((".$note." + note*votes)/(votes+1))
				, votes = (votes + 1)
			WHERE id = ".$anfick_id."
			"
		;
		//return $db->query($sql, __FILE__, __LINE__);
		$db->query($sql, __FILE__, __LINE__);
	}
	
}
?>
