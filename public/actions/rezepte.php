<?php
/**
 * Rezepte Actions
 *
 * @package zorg\Rezepte
 */

/**
 * File includes
 */
require_once __DIR__.'/../includes/config.inc.php';
require_once INCLUDES_DIR.'rezepte.inc.php';

if ($user->is_loggedin())
{
	/** Validate passed Parameters */
	$doAction = filter_input(INPUT_POST, 'action', FILTER_DEFAULT, FILTER_REQUIRE_SCALAR) ?? null; // $_POST['action']
	$rezeptId = filter_input(INPUT_POST, 'rezept_id', FILTER_VALIDATE_INT) ?? null; // $_POST['rezept_id']
	$returnUrl = base64url_decode(filter_input(INPUT_POST, 'url', FILTER_SANITIZE_FULL_SPECIAL_CHARS)) ?? '/tpl/129?rezept_id='.$rezeptId; // $_POST['url']
	zorgDebugger::log()->debug('$doAction %s | $rezeptId %d | $returnUrl: %s', [$doAction, $rezeptId, $returnUrl]);

	switch ($doAction)
	{
		/** Neues Rezept hinzufügen */
		case 'new':
			$data = [
				'category_id' => filter_input(INPUT_POST, 'category', FILTER_VALIDATE_INT) ?? 0,
				'title' => filter_input(INPUT_POST, 'title', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? null,
				'zutaten' => filter_input(INPUT_POST, 'zutaten', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? null,
				'anz_personen' => filter_input(INPUT_POST, 'personen', FILTER_VALIDATE_INT) ?? 0,
				'prep_time' => filter_input(INPUT_POST, 'preparation', FILTER_VALIDATE_INT) ?? 0,
				'cook_time' => filter_input(INPUT_POST, 'cookingtime', FILTER_VALIDATE_INT) ?? 0,
				'difficulty' => filter_input(INPUT_POST, 'difficulty', FILTER_VALIDATE_INT) ?? 0,
				'description' => filter_input(INPUT_POST, 'description', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? null
			];
			$newRezeptId = Rezepte::addRezept($data);

			$returnUrl = ($newRezeptId > 0 ? '/tpl/129?rezept_id='.$newRezeptId : $returnUrl.'&error=Not%20added');
			header('Location: '.$returnUrl);
			exit;
			break;

		/** Rezept aktualisieren */
		case 'edit':
			if ($rezeptId > 0) {
				$data = [
					'category_id' => filter_input(INPUT_POST, 'category', FILTER_VALIDATE_INT) ?? 0,
					'title' => filter_input(INPUT_POST, 'title', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? null,
					'zutaten' => filter_input(INPUT_POST, 'zutaten', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? null,
					'anz_personen' => filter_input(INPUT_POST, 'personen', FILTER_VALIDATE_INT) ?? 0,
					'prep_time' => filter_input(INPUT_POST, 'preparation', FILTER_VALIDATE_INT) ?? 0,
					'cook_time' => filter_input(INPUT_POST, 'cookingtime', FILTER_VALIDATE_INT) ?? 0,
					'difficulty' => filter_input(INPUT_POST, 'difficulty', FILTER_VALIDATE_INT) ?? 0,
					'description' => filter_input(INPUT_POST, 'description', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? null
				];
				$updated = Rezepte::updateRezept($rezeptId, $data);
			}

			header('Location: '.$returnUrl.(!$updated ? '&error=Not%20updated' : ''));
			exit;
			break;

		/** Neue Rezepte-Kategorie hinzufügen */
		case 'newcategory':
			$newCategoryTitle = filter_input(INPUT_POST, 'new_category', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? null; // $_POST['new_category']

			if (!empty($newCategoryTitle)) {
				$added = Rezepte::addCategory($newCategoryTitle);
			}
			header('Location: '.$returnUrl.(!$added ? '&error=Category%20not%20added' : ''));
			exit;

		/** Ein Rezept bewerten */
		case 'benoten':
			$benotenScore = filter_input(INPUT_POST, 'score', FILTER_VALIDATE_INT) ?? 0; // $_POST['score']

			if ($rezeptId>0 && $benotenScore > 0 && $benotenScore <= 5)
			{
				$voted = Rezepte::addVote($rezeptId, $benotenScore);
			}
			header('Location: '.$returnUrl.(!$voted ? '&error=Vote%20not%20added' : ''));
			exit;
			break;
	}
}
/** User not logged in */
else {
	http_response_code(403); // Set response code 403 (not allowed) and exit.
	die('Permission denied!');
}
