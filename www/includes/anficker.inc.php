<?php
/**
 * Anficker
 *
 * Gibt Spresim erst die Macht die er braucht, um so richtig anzuficken
 *
 * @author ?
 * @package zorg\Games\Anficker
 */

/**
 * File Includes
 * @include mysql.inc.php
 * @include usersystem.inc.php
 */
require_once __DIR__.'/config.inc.php';
require_once INCLUDES_DIR.'mysql.inc.php';
require_once INCLUDES_DIR.'usersystem.inc.php';

/**
 * Konstante ANFICKER_USER_ID
 */
define('ANFICKER_USER_ID', 9999);

/**
 * Spresim Klasse
 *
 * Klasse für den Anfick-Battle gegen Spresim
 *
 * @author ?
 * @version 2.1
 * @since 2.1 `16.04.2020` `IneX` fixed notice Non-static method Anficker::...() should not be called statically
 *
 * @package zorg\Games\Anficker
 */
class Anficker
{
	/**
	 * Anfick des User hinzufügen
	 *
	 * @author ?
	 * @author IneX
	 * @version 2.1
	 * @since 1.0 function added
	 * @since 2.0 `IneX` code enhancements
	 * @since 2.1 `16.04.2020` `IneX` migrated mysql-functions to mysqli
	 *
	 * @see Anficker::logAnfick(), Anficker::getId()
	 * @param integer $user_id ID des Users, welcher gerade mit Spresim batteld
	 * @param string $text Anfick des Users
	 * @param boolean $spresim_trainieren Gibt an, ob Anfick des Users gespeichert werden soll oder nicht
	 * @global object $db Globales Class-Object mit allen MySQL-Methoden
	 * @global object $user Globales Class-Object mit den User-Methoden & Variablen
	 *
	 * @todo Unterschied, ob Spresim trainieren oder nur battlen sollte möglich sein (Bug #487) (Mättä, 25.10.04) | IDEE: Eine möglich Lösung wäre, ein zusätzliches Flag in der Tabelle "battle_only" oder so...
	 * @todo Müsste es nicht "REPLACE INTO..." sein?? Jetzt werden x-Einträge mit gleichem Text gemacht! (IneX, 8.6.09)
	 */
	static function addAnfick($user_id, $text, $spresim_trainieren=FALSE) {
		global $db, $user;

		// nur Anfick speichern, wenn Spresim trainiert werden soll:
		//if ($spresim_trainieren == TRUE)
		//{
			if($text != '' && !empty($user_id))//$user->id2user($user_id)))
			{
				$sql = 'INSERT IGNORE INTO anficker_anficks ( text, user_id, datum) VALUES (?, ?, ?)';
				$insert_id = $db->query($sql, __FILE__, __LINE__, __METHOD__, [$text, $user_id, timestamp(true)]);
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

		$anfick_id = ($insert_id > 0 ? $insert_id : Anficker::getId($text));

		Anficker::logAnfick($anfick_id, $user_id, $user_id);
	}


	/**
	 * Anfick im Anfick-Log ergänzen
	 *
	 * @author ?
	 * @author IneX
	 * @version 2.0
	 * @since 1.0
	 * @see Anficker::addAnfick()
	 *
	 * @param integer $anfick_id ID des Anficks wo das Log ergänzt werden soll
	 * @param integer $user_id ID des Users, welcher angefickt wurde
	 * @param integer $anficker_id ID des Users, welcher den Anfick gemacht hat
	 * @global object $db Globales Class-Object mit allen MySQL-Methoden
	 */
	static function logAnfick($anfick_id, $user_id, $anficker_id) {
		global $db;
		$sql = 'INSERT INTO anficker_log (datum, user_id, anficker_id, anfick_id) VALUES (?, ?, ?, ?)';
		//return $db->query($sql, __FILE__, __LINE__);
		$db->query($sql, __FILE__, __LINE__, __METHOD__, [timestamp(true), $user_id, $anficker_id, $anfick_id]);
	}


	static function deleteLog($user_id) {
		global $db;
		$sql = 'DELETE FROM anficker_log WHERE user_id=?';
		return $db->query($sql, __FILE__, __LINE__, __METHOD__, [$user_id]);
	}


	static function getId($text) {
		global $db;
		$sql = 'SELECT id FROM anficker_anficks WHERE text=?';
		$rs = $db->fetch($db->query($sql, __FILE__, __LINE__, __METHOD__, [$text]));
		return $rs['id'];
	}


	/**
	 * Anfick-Log ausgeben
	 *
	 * @author ?
	 * @author IneX
	 * @version 2.0
	 * @since 1.0
	 * @see Anficker::logAnfick(), Anficker::anfickenMit()
	 *
	 * @param integer $user_id ID des Users, welcher gerade mit Spresim batteld
	 * @global object $db Globales Class-Object mit allen MySQL-Methoden
	 * @return string Gibt das gesamte bisherige Anfick-Log des Battles 'User vs. Spresim' aus
	 */
	static function getLog($user_id) {
		global $db;

		//Anficker::addRandomAnfick2Log($user_id, ANFICKER_USER_ID);
		Anficker::logAnfick(Anficker::anfickenMit(), $user_id, ANFICKER_USER_ID);

		$sql = 'SELECT anficker_anficks.note, anficker_anficks.id, anficker_log.datum, anficker_anficks.text, anficker_log.anficker_id FROM anficker_log
				LEFT JOIN anficker_anficks ON (anficker_anficks.id = anficker_log.anfick_id) WHERE anficker_log.user_id=? ORDER BY anficker_log.id ASC';
		$result = $db->query($sql, __FILE__, __LINE__, __METHOD__, [$user_id]);

		while($rs = $db->fetch($result)) {
			$dialog[] = $rs;
		}

		return $dialog;
	}


	/**
	 * Anzahl existierender Anficks holen
	 *
	 * @author ?
	 * @version 1.1
	 * @since 1.0 function added
	 * @since 1.1 `16.04.2020` `IneX` changed function @param $user_id to be optional, fixed "Too few arguments to function Anficker::getNumAnficks(), 0 passed"
	 *
	 * @param integer $user_id (Optional) ID des Users, welcher gerade mit Spresim batteld
	 * @return array Gibt ein Array mit der Anzahl Anficks (für Funktion anfickenMit()), durchschnittlicher Noten und Anzahl Votes (für /packages/anficks.php) zurück
	 */
	static function getNumAnficks($user_id=null)
	{
		global $db;
		$params = [];
		$sql =  'SELECT COUNT(*) AS num, AVG(note) AS note, SUM(votes) AS votes FROM anficker_anficks'.(!empty($user_id) ? ' WHERE user_id=?' : '');
		if (!empty($user_id)) $params[] = $user_id;
		$rs = $db->fetch($db->query($sql, __FILE__, __LINE__, __METHOD__, [$params]));
		return $rs;
	}


	/**
	 * Anfick ins Log schreiben und selbiges Ausgeben
	 *
	 * @deprecated
	 *
	 * @author ?
	 * @author IneX
	 * @version 2.0
	 * @since 1.0
	 * @see Anficker::anfickenMit()
	 *
	 * @param integer $user_id ID des Users, welcher gerade mit Spresim batteld
	 * @return array Gibt ganzes Log der Anfickerei für Ausgabe zurück
	 */
	static function addRandomAnfick2Log($user_id) {
		/*global $db;
		$sql = "SELECT * FROM anficker_anficks ORDER BY note ASC";
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
	 * @version 1.1
	 * @since 1.0 `08.06.2009` `IneX` function added
	 * @since 1.1 `16.04.2020` `IneX` code optimizations, migrated mysql-functions to mysqli
	 *
	 * @see Anficker::getNumAnficks()
	 * @global object $db Globales Class-Object mit allen MySQL-Methoden
	 * @return integer ID des Anfick von Spresim
	 */
	static function anfickenMit()
	{
		global $db;

		$sql = 'SELECT * FROM anficker_anficks ORDER BY note ASC';
		$result = $db->query($sql, __FILE__, __LINE__, __FUNCTION__);

		/** zufällige id holen */
		$rs = Anficker::getNumAnficks();
		$id = rand(0, $rs['num']-1); // Zufalls #
		$id = rand($id, $rs['num']-1); // die besten bevorzugen.
		mysqli_data_seek($result, $id);

		$rs = $db->fetch($result);

		return $rs['id'];
	}


	/**
	 * Anfick-Spruch benoten
	 *
	 * @author ?
	 * @author IneX
	 * @version 1.1
	 * @since 1.0
	 *
	 * @param integer $anfick_id ID des benoteten Anficks
	 * @param integer $note Bewertung des Anficks
	 * @global object $db Globales Class-Object mit allen MySQL-Methoden
	 */
	static function vote($anfick_id, $note) {
		global $db;
		$sql = 'UPDATE anficker_anficks SET note=((?+note*votes)/(votes+1)), votes=(votes+1) WHERE id=?'
		;
		//return $db->query($sql, __FILE__, __LINE__);
		$db->query($sql, __FILE__, __LINE__, __METHOD__, [$note, $anfick_id]);
	}

}
