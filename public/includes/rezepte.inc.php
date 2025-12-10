<?php
/**
 * zorg Rezepte Datenbank
 *
 * @package zorg\Rezepte
 */

/**
 * File includes
 * @include config.inc.php
 * @include usersystem.inc.php
 */
require_once __DIR__.'/config.inc.php';
require_once INCLUDES_DIR.'usersystem.inc.php';

/**
 * zorg Rezepte Datenbank Klasse
 *
 * @package zorg\Rezepte
 *
 * @version 2.0
 * @since 1.0 `IneX` Class added
 * @since 2.0 `07.01.2024` `IneX` SQL-queries changed to prepared statements, added Functions from /actions/rezepte.php
 */
class Rezepte
{
	static function getRezept($rezept_id) {
		global $db;

		$sql = 'SELECT r.*, UNIX_TIMESTAMP(r.erstellt_date) AS erstellt_date, r_cat.id, r_cat.title AS kategorie FROM rezepte r LEFT OUTER JOIN rezepte_categories r_cat ON r.category_id = r_cat.id WHERE r.id = ?';
		$result = $db->query($sql, __FILE__, __LINE__, __METHOD__, [$rezept_id]);
		$rs = $db->fetch($result);

		if (isset($rs['title'])) $rs['title'] = html_entity_decode($rs['title']);
		if (isset($rs['zutaten'])) $rs['zutaten'] = html_entity_decode($rs['zutaten']);
		if (isset($rs['description'])) $rs['description'] = html_entity_decode($rs['description']);

		return $rs;
	}

	static function getRezeptNewest() {
		global $db;

		$sql = 'SELECT *, UNIX_TIMESTAMP(erstellt_date) AS erstellt_date FROM rezepte ORDER BY erstellt_date DESC LIMIT 1';
		$result = $db->query($sql, __FILE__, __LINE__, __METHOD__);
		$rs = $db->fetch($result);

		if (isset($rs['title'])) $rs['title'] = html_entity_decode($rs['title']);
		if (isset($rs['zutaten'])) $rs['zutaten'] = html_entity_decode($rs['zutaten']);
		if (isset($rs['description'])) $rs['description'] = html_entity_decode($rs['description']);

		return $rs;
	}

	static function getRezepte($category_id) {
		global $db;

		$rezepte = array();

		$sql = 'SELECT *, UNIX_TIMESTAMP(erstellt_date) AS erstellt_date FROM rezepte WHERE category_id=? ORDER BY category_id ASC, title ASC';
		$result = $db->query($sql, __FILE__, __LINE__, __METHOD__, [$category_id]);

		while($rs = $db->fetch($result))
		{
			if (isset($rs['title'])) $rs['title'] = html_entity_decode($rs['title']);
			if (isset($rs['zutaten'])) $rs['zutaten'] = html_entity_decode($rs['zutaten']);
			if (isset($rs['description'])) $rs['description'] = html_entity_decode($rs['description']);
			array_push($rezepte, $rs);
		}

		return $rezepte;
	}

	static function getNumNewRezepte() {
		global $db, $user;

		if($user->lastlogin > 0) {
			$sql = 'SELECT * FROM rezepte WHERE UNIX_TIMESTAMP(erstellt_date) > ?';
			return $db->num($db->query($sql, __FILE__, __LINE__, __METHOD__, [$user->lastlogin]));
		} else {
			return 0;
		}
	}

	static function getCategories() {
		global $db;

		$categories = array();

		$sql = 'SELECT * FROM rezepte_categories ORDER BY title ASC';
		$result = $db->query($sql, __FILE__, __LINE__, __METHOD__);

		while($rs = $db->fetch($result))
		{
			if (isset($rs['title'])) $rs['title'] = html_entity_decode($rs['title']);
			array_push($categories, $rs);
		}

		return $categories;
	}

	static function getScore($rezept_id) {
		global $db;

		$sql = 'SELECT AVG(score) as score FROM rezepte_votes WHERE rezept_id=?';
		$result = $db->query($sql, __FILE__, __LINE__, __METHOD__, [$rezept_id]);
		$rs = $db->fetch($result);

		return round($rs['score'], 1);
	}

	static function getNumVotes($rezept_id) {
		global $db;

		$sql = 'SELECT * FROM rezepte_votes WHERE rezept_id = ?';
		$result = $db->query($sql, __FILE__, __LINE__, __METHOD__, [$rezept_id]);

		return $db->num($result);
	}

	static function hasVoted($user_id, $rezept_id) {
		global $db;

		$sql = 'SELECT * FROM rezepte_votes WHERE rezept_id=? AND user_id=?';
		$result = $db->query($sql, __FILE__, __LINE__, __METHOD__, [$rezept_id, $user_id]);

		return $db->num($result);
	}

	/**
	 * Neues Rezept hinzufügen.
	 * @version 1.0
	 * @since 1.0 `07.01.2024` `IneX` code moved from /actions/rezepte.php
	 *
	 * @param array $values Key-Value Pairs, like ['title' => '...']
	 * @return int|bool ID of new Rezept on success; or FALSE if insert failed.
	 */
	static function addRezept($values)
	{
		global $db, $user;

		// $sql = 'INSERT INTO rezepte (category_id, title, zutaten, anz_personen, prep_time, cook_time, difficulty, description, ersteller_id, erstellt_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
			// $newRezeptId = $db->query($sql, __FILE__, __LINE__, __METHOD__, [$categoryId, $title, $zutaten, $pax, $prepTime, $cookTime, $difficultyLevel, $description, $user->id, timestamp(true)]);
		$values = [
			'category_id' => intval($values['category_id']),
			'title' => htmlspecialchars($values['title'], ENT_QUOTES),
			'zutaten' => htmlspecialchars($values['zutaten'], ENT_QUOTES),
			'anz_personen' => intval($values['anz_personen']),
			'prep_time' => intval($values['prep_time']),
			'cook_time' => intval($values['cook_time']),
			'difficulty' => intval($values['difficulty']),
			'description' => htmlspecialchars($values['description'], ENT_QUOTES),
			'ersteller_id' => $user->id,
			'erstellt_date' => timestamp(true)
		];
		$rezept_id = $db->insert('rezepte', $values, __FILE__, __LINE__, __METHOD__);

		$return = false;
		if ($rezept_id > 0)
		{
			$return = $rezept_id;

			/** Activity Eintrag auslösen */
			Activities::addActivity($user->id, 0, t('activity-new', 'rezepte', [ SITE_URL, $rezept_id ]), 'r');
		}

		return $return;
	}

	/**
	 * Rezept aktualisieren.
	 * @version 1.0
	 * @since 1.0 `07.01.2024` `IneX` code moved from /actions/rezepte.php
	 *
	 * @param int $rezept_id ID of Rezept to update
	 * @param array $values Key-Value Pairs for updated Data, like ['title' => '...']
	 * @return boolean
	 */
	static function updateRezept($rezept_id, $update_values)
	{
		global $db;

		// $sql = 'UPDATE rezepte SET category_id=?, title=?, zutaten=?, anz_personen=?, prep_time=?, cook_time=?, difficulty=?, description=? WHERE id=?';
		// $db->query($sql, __FILE__, __LINE__, __METHOD__, [$categoryId, $title, $zutaten, $pax, $prepTime, $cookTime, $difficultyLevel, $description, $rezeptId]);
		$values = [];
		if (isset($update_values['category_id'])) $values['category_id'] = intval($update_values['category_id']);
		if (isset($update_values['title'])) $values['title'] = htmlspecialchars($update_values['title'], ENT_QUOTES);
		if (isset($update_values['zutaten'])) $values['zutaten'] = htmlspecialchars($update_values['zutaten'], ENT_QUOTES);
		if (isset($update_values['anz_personen'])) $values['anz_personen'] = intval($update_values['anz_personen']);
		if (isset($update_values['prep_time'])) $values['prep_time'] = intval($update_values['prep_time']);
		if (isset($update_values['cook_time'])) $values['cook_time'] = intval($update_values['cook_time']);
		if (isset($update_values['difficulty'])) $values['difficulty'] = intval($update_values['difficulty']);
		if (isset($update_values['description'])) $values['description'] = htmlspecialchars($update_values['description'], ENT_QUOTES);
		zorgDebugger::log()->debug('SQL: id=%d%s', [$rezept_id, print_r($values,true)]);
		$result = $db->update('rezepte', $rezept_id, $values, __FILE__, __LINE__, __METHOD__);

		return (!$result ? false : true);
	}

	/**
	 * Neue Rezepte-Kategorie hinzufügen.
	 * @version 1.0
	 * @since 1.0 `07.01.2024` `IneX` code moved from /actions/rezepte.php
	 *
	 * @param string $category Title-String of the new Category
	 * @return bool
	 */
	static function addCategory($category)
	{
		global $db;

		/** Validate parameters */
		if (empty($category) || !is_string($category)) return false;
		$categoryName = trim(htmlentities($category, ENT_QUOTES));

		/** Make sure the Category does NOT YET EXIST */
		$existingCategories = array_column(Rezepte::getCategories(), 'title');
		if (array_search($categoryName, $existingCategories) !== false) {
			/** Category already EXISTS */
			return false;
		}
		/** Add new Category */
		else {
			$db->insert('rezepte_categories', ['title' => $categoryName], __FILE__, __LINE__, __METHOD__);
			return true;
		}
	}

	/**
	 * Ein Rezept bewerten.
	 * @version 1.0
	 * @since 1.0 `07.01.2024` `IneX` code moved from /actions/rezepte.php
	 *
	 * @param int $rezept_id ID of Rezept to update its score
	 * @param int $vote User's voted value to add to Total Score
	 * @return bool
	 */
	static function addVote($rezept_id, $vote)
	{
		global $db, $user;

		/** Validate parameters */
		if (!is_numeric($rezept_id) || $rezept_id <= 0) return false;
		if (!is_numeric($vote) || $vote <= 0) return false;

		/** Add the Vote */
		$sql = 'REPLACE INTO rezepte_votes (rezept_id, user_id, score) VALUES (?, ?, ?)';
		$result = $db->query($sql, __FILE__, __LINE__, __METHOD__, [$rezept_id, $user->id, $vote]);

		return (!$result ? false : true);
	}
}
