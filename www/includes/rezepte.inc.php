<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/smarty.inc.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/usersystem.inc.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/util.inc.php');

Class Rezepte {

	function getRezept($rezept_id) {
		global $db;

		$sql =
			"
			SELECT
				r.*
				, UNIX_TIMESTAMP(r.erstellt_date) AS erstellt_date
				, r_cat.id
				, r_cat.title AS kategorie
			FROM
			rezepte r LEFT OUTER
			JOIN rezepte_categories r_cat ON r.category_id = r_cat.id
			WHERE
			r.id = ".$rezept_id."
			"
		;

		$result = $db->query($sql, __FILE__, __LINE__);

		return $db->fetch($result);
	}

	function getRezeptNewest() {
		global $db;

		$sql =
			"
			SELECT
				*
				, UNIX_TIMESTAMP(erstellt_date) AS erstellt_date
			FROM
			rezepte
			ORDER BY erstellt_date DESC
			LIMIT 0,1
			"
		;

		$result = $db->query($sql, __FILE__, __LINE__);

		return $db->fetch($result);
	}

	function getRezepte($category) {
		global $db;

		$rezepte = array();

		$sql =
			"
			SELECT
				*
				, UNIX_TIMESTAMP(erstellt_date) AS erstellt_date
			FROM
			rezepte
			WHERE
			category_id = ".$category."
			ORDER BY category_id ASC, title ASC
			"
		;

		$result = $db->query($sql, __FILE__, __LINE__);

		while($rs = $db->fetch($result)) {
			array_push($rezepte, $rs);
		}

		return $rezepte;
	}

	function getNumNewRezepte() {
		global $db, $user;

		if($user->lastlogin > 0) {
			$sql =
				"
				SELECT
				*
				FROM
				rezepte
				WHERE
				UNIX_TIMESTAMP(erstellt_date) > ".$user->lastlogin."
				"
			;

			return $db->num($db->query($sql, __FILE__, __LINE__));
		} else {
			return 0;
		}
	}

	function getCategories() {
		global $db;

		$categories = array();

		$sql =
			"
			SELECT
				*
			FROM
			rezepte_categories
			ORDER BY title ASC
			"
		;

		$result = $db->query($sql, __FILE__, __LINE__);

		while($rs = $db->fetch($result)) {
			array_push($categories, $rs);
		}

		return $categories;
	}

	function getScore($rezept_id) {
		global $db;

		$sql =
			"SELECT AVG(score) as score"
			." FROM rezepte_votes"
			." WHERE rezept_id = ".$rezept_id
		;
		$result = $db->query($sql, __FILE__, __LINE__);
		$rs = $db->fetch($result, __FILE__, __LINE__);

		return round($rs['score'], 1);
	}

	function getNumVotes($rezept_id) {
		global $db;

		$sql =
			"SELECT *"
			." FROM rezepte_votes"
			." WHERE rezept_id = ".$rezept_id
		;
		$result = $db->query($sql, __FILE__, __LINE__);

		return $db->num($result, __FILE__, __LINE__);
	}

	function hasVoted($user_id, $rezept_id) {
		global $db;

		$sql =
			"SELECT *"
			." FROM rezepte_votes"
			." WHERE rezept_id = '".$rezept_id."' AND user_id =".$user_id
		;
		$result = $db->query($sql, __FILE__, __LINE__);

		return $db->num($result, __FILE__, __LINE__);
	}




}

?>