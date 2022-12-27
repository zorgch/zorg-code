<?php
/**
 * Rezepte Actions
 * @package zorg\Rezepte
 */
/**
 * File includes
 */
require_once dirname(__FILE__).'/../includes/main.inc.php';

if (true === $user->is_loggedin())
{
	/** Validate passed Parameters */
	$doAction = (isset($_POST['action']) && is_string($_POST['action']) ? (string)$_POST['action'] : null);

	switch ($doAction)
	{
		/** Neues Rezept hinzufügen */
		case 'new':
			// TODO Change to $db->insert()
			$sql = 'INSERT INTO rezepte
						(category_id, title, zutaten, anz_personen, prep_time, cook_time, difficulty, description, ersteller_id, erstellt_date)
					VALUES
						(
							 '.(int)$_POST['category'].'
							,"'.$_POST['title'].'"
							,"'.$_POST['zutaten'].'"
							,'.(int)$_POST['personen'].'
							,'.(int)$_POST['preparation'].'
							,'.(int)$_POST['cookingtime'].'
							,'.(int)$_POST['difficulty'].'
							,"'.$_POST['description'].'"
							,'.$user->id.'
							,NOW()
						)';
			$rezeptId = $db->query($sql, __FILE__, __LINE__);
			header('Location: '.base64url_decode($_POST['url']).'&rezept_id='.$rezeptId);
			exit;
		break;

		/** Rezept aktualisieren */
		case 'edit':
			// TODO Change to $db->update()
			$sql = 'UPDATE rezepte
					SET
						 category_id = '.(int)$_POST['category'].'
						,title = "'.$_POST['title'].'"
						,zutaten = "'.$_POST['zutaten'].'"
						,anz_personen = '.(int)$_POST['personen'].'
						,prep_time = '.(int)$_POST['preparation'].'
						,cook_time = '.(int)$_POST['cookingtime'].'
						,difficulty = '.(int)$_POST['difficulty'].'
						,description = "'.$_POST['description'].'"
					WHERE id = '.(int)$_POST['id']; // TODO align by changing in Tpl & here to "rezept_id"
			$db->query($sql, __FILE__, __LINE__);
			header('Location: '.base64url_decode($_POST['url']).'&rezept_id='.$_POST['id']);
			exit;
		break;

		/** Neue Rezepte-Kategorie hinzufügen */
		case 'newcategory':
			$sql = 'INSERT INTO rezepte_categories SET title = "'.$_POST['new_category'].'"';
			$db->query($sql, __FILE__, __LINE__);
			header('Location: '.base64url_decode($_POST['url']));
			exit;
		break;

		/** Ein Rezept bewerten */
		case 'benoten':
			if (isset($_POST['score']) && is_numeric($_POST['score']) && (int)$_POST['score'] >= 1 && (int)$_POST['score'] < 6)
			{
				$sql = 'REPLACE INTO rezepte_votes (rezept_id, user_id, score)
						VALUES (
							 '.$_POST['rezept_id'].'
							,'.$user->id.'
							,'.(int)$_POST['score'].'
						)';
				$db->query($sql, __FILE__, __LINE__);
			}
			header('Location: '.base64url_decode($_POST['url']));
		break;
	}
}
/** User not logged in */
else {
	http_response_code(403); // Set response code 403 (not allowed) and exit.
	die('Permission denied!');
}
