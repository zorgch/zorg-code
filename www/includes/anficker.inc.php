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
	 * @version 2.5
	 * @since 1.0 function added
	 * @since 2.0 `IneX` code enhancements
	 * @since 2.1 `16.04.2020` `IneX` migrated mysql-functions to mysqli
	 * @since 2.5 `21.01.2024` `IneX` Bug #667 : Anficks werden x-mal gespeichert
	 *
	 * @see self::logAnfick(), self::getId()
	 * @param integer $user_id ID des Users, welcher gerade mit Spresim batteld
	 * @param string $text Anfick des Users
	 * @param boolean $spresim_trainieren Gibt an, ob Anfick des Users gespeichert werden soll oder nicht
	 * @global object $db Globales Class-Object mit allen MySQL-Methoden
	 * @return bool
	 *
	 * // TODO Bug #487 : Unterschied, ob Spresim trainieren oder nur battlen sollte möglich sein (Mättä, 25.10.04) | IDEE: Eine möglich Lösung wäre, ein zusätzliches Flag in der Tabelle "battle_only" oder so...
	 */
	static function addAnfick($user_id, $text, $spresim_trainieren=FALSE)
	{
		global $db;

		if($user_id > 0 && !empty($text))
		{
			// nur Anfick speichern, wenn Spresim trainiert werden soll:
			//if ($spresim_trainieren == TRUE)
			//{
				/** Check if Anfick already exists */
				$existing_anfick_id = 0;
				$default_initial_score = 4.0;
				$sql_check = 'SELECT id, note, votes, user_id FROM anficker_anficks WHERE text=? LIMIT 1';
				$exists = $db->query($sql_check, __FILE__, __LINE__, __METHOD__, [$text]);
				if ($db->num($exists) > 0) {
					$af = $db->fetch($exists);
					$existing_anfick_id = intval($af['id']);
					$existing_anfick_score = floatval($af['note']);
					$existing_anfick_votes = intval($af['votes']);
					$existing_anfick_creator = intval($af['user_id']);
				}

				/** Update existing Anfick */
				if ($existing_anfick_id > 0)
				{
					$new_votes = $existing_anfick_votes+1;
					$new_score = number_format(($default_initial_score+$existing_anfick_score)*$existing_anfick_votes/$new_votes, 8, '.', '');
					$update = [
						 'note' => $new_score
						,'votes' => $new_votes
						,'user_id' => (empty($existing_anfick_creator) ? $user_id : $existing_anfick_creator)
					];
					$db->update('anficker_anficks', $existing_anfick_id, $update, __FILE__, __LINE__, __METHOD__);
				}
				/** Add new Anfick */
				else {
					$insert = [
						 'text' => $text
						,'user_id' => $user_id
						,'datum' => timestamp(true)
					];
					$insert_id = $db->insert('anficker_anficks', $insert, __FILE__, __LINE__, __METHOD__);
				}

				//else
				//{

					// Hier sollte was kommen, WENN SPRESIM NICHT TRAINIERT WERDEN SOLL... leider zur Zeit nicht realisierbar, IneX 8.6.09
					// IDEE: Eine möglich Lösung wäre, ein zusätzliches Flag in der Tabelle "battle_only" oder so... (IneX, 8.6.09)

				//}
			//}
			$anfick_id = ($existing_anfick_id > 0 ? $existing_anfick_id : $insert_id);
			$log = self::logAnfick($anfick_id, $user_id, $user_id);
			return (!$log ? false : true);
		}
		return false;
	}


	/**
	 * Anfick im Anfick-Log ergänzen
	 *
	 * @see self::addAnfick()
	 *
	 * @version 2.0
	 * @since 1.0 Method added
	 * @since 2.0 `IneX` SQL- and code optimziations
	 *
	 * @param integer $anfick_id ID des Anficks wo das Log ergänzt werden soll
	 * @param integer $user_id ID des Users, welcher angefickt wurde
	 * @param integer $anficker_id ID des Users, welcher den Anfick gemacht hat
	 * @global object $db Globales Class-Object mit allen MySQL-Methoden
	 * @return int|bool Returns INSERT id - or false
	 */
	static function logAnfick($anfick_id, $user_id, $anficker_id) {
		global $db;

		$result = false;
		if ($anfick_id > 0 && $user_id > 0 && $anficker_id > 0) {
			$insert = [
				 'datum' => timestamp(true)
				,'user_id' => intval($user_id)
				,'anficker_id' => intval($anficker_id)
				,'anfick_id' => intval($anfick_id)
			];
			$result = $db->insert('anficker_log', $insert, __FILE__, __LINE__, __METHOD__);
		}
		return $result;
	}


	static function deleteLog($user_id) {
		global $db;

		$result = false;
		if ($user_id > 0) {
			$sql = 'DELETE FROM anficker_log WHERE user_id=?';
			$result = $db->query($sql, __FILE__, __LINE__, __METHOD__, [$user_id]);
		}
		return $result;
	}


	static function getId($text) {
		global $db;

		$result = null;
		if (!empty($text)) {
			$sql = 'SELECT id FROM anficker_anficks WHERE text=?';
			$rs = $db->fetch($db->query($sql, __FILE__, __LINE__, __METHOD__, [$text]));
			$result = intval($rs['id']);
		}
		return $result;
	}


	/**
	 * Anfick-Log ausgeben
	 *
	 * @see self::logAnfick(), self::anfickenMit()
	 *
	 * @version 2.0
	 * @since 1.0 method added
	 * @since 2.0 `IneX` SQL- and code optimizations
	 *
	 * @param integer $user_id ID des Users, welcher gerade mit Spresim batteld
	 * @global object $db Globales Class-Object mit allen MySQL-Methoden
	 * @return string Gibt das gesamte bisherige Anfick-Log des Battles 'User vs. Spresim' aus
	 */
	static function getLog($user_id) {
		global $db;

		//self::addRandomAnfick2Log($user_id, ANFICKER_USER_ID);
		self::logAnfick(self::anfickenMit(), $user_id, ANFICKER_USER_ID);

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
	 * @see self::anfickenMit()
	 *
	 * @version 2.0
	 * @since 1.0 method added
	 * @since 2.0 `IneX` method deprecation mode activated
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
		self::logAnfick(self::anfickenMit(), $user_id, ANFICKER_USER_ID);
	}


	/**
	 * Spresim's Anfick an den User
	 *
	 * @author IneX
	 * @version 1.1
	 * @since 1.0 `08.06.2009` `IneX` function added
	 * @since 1.1 `16.04.2020` `IneX` code optimizations, migrated mysql-functions to mysqli
	 *
	 * @see self::getNumAnficks()
	 * @global object $db Globales Class-Object mit allen MySQL-Methoden
	 * @return integer ID des Anfick von Spresim
	 */
	static function anfickenMit()
	{
		global $db;

		$sql = 'SELECT * FROM anficker_anficks ORDER BY note ASC';
		$result = $db->query($sql, __FILE__, __LINE__, __METHOD__);

		/** zufällige id holen */
		$rs = self::getNumAnficks();
		$id = rand(0, $rs['num']-1); // Zufalls #
		$id = rand($id, $rs['num']-1); // die besten bevorzugen.
		mysqli_data_seek($result, $id);

		$rs = $db->fetch($result);

		return $rs['id'];
	}


	/**
	 * Anfick-Spruch benoten
	 *
	 * @version 1.1
	 * @since 1.0 method added
	 * @since 1.1 `IneX` SQL- and code optimizations
	 *
	 * @param integer $anfick_id ID des benoteten Anficks
	 * @param integer $note Bewertung des Anficks
	 * @global object $db Globales Class-Object mit allen MySQL-Methoden
	 * @return integer
	 */
	static function vote($anfick_id, $note)
	{
		global $db;

		$result = 0;
		if ($anfick_id > 0 && $note > 0)
		{
			$sql = 'UPDATE anficker_anficks SET note=((?+note)*votes/(votes+1)), votes=(votes+1) WHERE id=?';
			$result = $db->query($sql, __FILE__, __LINE__, __METHOD__, [intval($note), intval($anfick_id)]);
		}
		return $result;
	}
}
