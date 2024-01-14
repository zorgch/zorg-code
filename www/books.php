<?php
/**
 * Books
 *
 * coded by [z]keep3r
 *
 * @author [z]keep3r
 * @package zorg\Books
 */

/**
 * File includes
 */
require_once __DIR__.'/includes/config.inc.php';
require_once MODELS_DIR.'core.model.php';

/**
 * Initialise MVC Model
 */
$model = new MVC\Books();

/**
 * Validate Passed Parameters
 */
$book_id = filter_input(INPUT_GET, 'book_id', FILTER_VALIDATE_INT) ?? null; // $_GET['book_id']
$user_id = filter_input(INPUT_GET, 'user', FILTER_VALIDATE_INT) ?? ($user->is_loggedin() ? $user->id : null); // $_GET['user']
$doAction = filter_input(INPUT_GET, 'do', FILTER_DEFAULT, FILTER_REQUIRE_SCALAR) ?? null; // $_GET['do']
$postAction = filter_input(INPUT_POST, 'do', FILTER_DEFAULT, FILTER_REQUIRE_SCALAR) ?? null; // $_POST['do']

/**************/
/* Funktionen */
/**************/
/**
 * Kategorien-Bezeichnung holen
 *
 * Gibt Kategorie aus books_title zurück
 *
 * @param $kat_id int
 * @return string title
 */
function get_title($kat_id)
{
	global $db;

	$book_categoryid = filter_var($kat_id, FILTER_VALIDATE_INT) ?? null;

	if ($book_categoryid > 0) {
		$sql = 'SELECT typ FROM books_title WHERE id=?';
		$result = $db->query($sql, __FILE__, __LINE__, __FUNCTION__, [$kat_id]);
		$rs = $db->fetch($result);

		return htmlspecialchars($rs['typ'], ENT_QUOTES, 'UTF-8');
	} else {
		return false;
	}
}

/***************/
/* DB Routinen */
/***************/
/** Aenderung an Buch in DB speichern */
if($postAction === 'edit_now' && $user->is_loggedin())
{
	$book_id = filter_input(INPUT_POST, 'book_id', FILTER_VALIDATE_INT) ?? null; // $_POST['book_id']
	$book_title_id = filter_input(INPUT_POST, 'titel_id', FILTER_VALIDATE_INT) ?? null; // $_POST['titel_id']
	$title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? null;
	$autor = filter_input(INPUT_POST, 'autor', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? null;
	$verlag = filter_input(INPUT_POST, 'verlag', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? null;
	$isbn = filter_input(INPUT_POST, 'isbn', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? null;
	$titel_id = filter_input(INPUT_POST, 'titel_id', FILTER_VALIDATE_INT) ?? null;
	$parent_id = filter_input(INPUT_POST, 'parent_id', FILTER_VALIDATE_INT) ?? null;
	$jahrgang = filter_input(INPUT_POST, 'jahrgang', FILTER_VALIDATE_INT) ?? null;
	$preis = filter_input(INPUT_POST, 'preis', FILTER_VALIDATE_FLOAT) ?? null;
	$seiten = filter_input(INPUT_POST, 'seiten', FILTER_VALIDATE_INT) ?? null;
	$text = filter_input(INPUT_POST, 'text', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? null;

	/** besitzt der user das buch? */
	$sql = 'SELECT count(*) as anzahl FROM books_holder WHERE book_id=? AND user_id=?';
	$result = $db->query($sql, __FILE__, __LINE__, 'edit_now', [$book_id, $user->id]);
	$rs = $db->fetch($result);

	if ($rs['anzahl'] != 1)
	{
		$smarty->assign('error', ['type' => 'warn', 'dismissable' => 'true', 'title' => 'au jetzt nonig, tschalphorre']);
		//exit;
	} else {
		$sql = 'SELECT * FROM books_title WHERE id=?';
		$result = $db->query($sql, __FILE__, __LINE__, 'edit_now', [$book_title_id]);
		$rs = $db->fetch($result);

		if (isset($rs['parent_id']) && $rs['parent_id'] > 0)
		{
			$titel_id = $rs['parent_id'];
			$parent_id = $rs['id'];
		} else {
			$titel_id = $rs['id'];
			$parent_id = 0;
		}

		$sql = 'UPDATE books SET title=?, autor=?, verlag=?, isbn=?, titel_id=?, parent_id=?, jahrgang=?, preis=?, seiten=?, text=? WHERE id=?';
		$params = [
			$title,
			$autor,
			$verlag,
			$isbn,
			$titel_id,
			$parent_id,
			$jahrgang,
			$preis,
			$seiten,
			$text,
			$book_id
		];
		$db->query($sql, __FILE__, __LINE__, 'edit_now', $params);
		$smarty->assign('error', ['type' => 'success', 'dismissable' => 'true', 'title' => 'Boook geändert']);
		$doAction = "show";
		$book_id = (int)$_POST['book_id'];
	}
}
/** Buch in DB hinzufuegen */
elseif ($postAction === 'add_now' && $user->is_loggedin())
{
	$book_id = filter_input(INPUT_POST, 'book_id', FILTER_VALIDATE_INT) ?? null; // $_POST['book_id']
	$book_title_id = filter_input(INPUT_POST, 'titel_id', FILTER_VALIDATE_INT) ?? null; // $_POST['titel_id']
	$title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? null;
	$autor = filter_input(INPUT_POST, 'autor', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? null;
	$verlag = filter_input(INPUT_POST, 'verlag', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? null;
	$isbn = filter_input(INPUT_POST, 'isbn', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? null;
	$titel_id = filter_input(INPUT_POST, 'titel_id', FILTER_VALIDATE_INT) ?? null;
	$parent_id = filter_input(INPUT_POST, 'parent_id', FILTER_VALIDATE_INT) ?? null;
	$jahrgang = filter_input(INPUT_POST, 'jahrgang', FILTER_VALIDATE_INT) ?? null;
	$preis = filter_input(INPUT_POST, 'preis', FILTER_VALIDATE_FLOAT) ?? null;
	$seiten = filter_input(INPUT_POST, 'seiten', FILTER_VALIDATE_INT) ?? null;
	$text = filter_input(INPUT_POST, 'text', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? null;

	/** Kategorie und Parent-Kategorie finden */
	$sql = 'SELECT * FROM books_title WHERE id=?';
	$result = $db->query($sql, __FILE__, __LINE__, 'add_now', [$book_title_id]);
	$rs = $db->fetch($result);

	if (isset($rs['parent_id']) && $rs['parent_id'] > 0)
	{
		$titel_id = $rs['parent_id'];
		$parent_id = $rs['id'];
	} else {
		$titel_id = $rs['id'];
		$parent_id = 0;
	}

	/** Book adden */
	zorgDebugger::log()->debug('Insert Book $_POST: %s', [print_r($_POST)]);
	$sql = 'INSERT INTO books (title, autor, verlag, isbn, titel_id, parent_id, jahrgang, preis, seiten, text, ersteller)
			VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
	$params = [
		$title,
		$autor,
		$verlag,
		$isbn,
		$titel_id,
		$parent_id,
		$jahrgang,
		$preis,
		$seiten,
		$text,
		$user->id
	];
	$IdNewBook = $db->query($sql, __FILE__, __LINE__, 'add_now', $params);

	if ($IdNewBook !== false && $IdNewBook > 0)
	{
		$sql = 'INSERT INTO books_holder(book_id, user_id) VALUES(?, ?)';
		$db->query($sql, __FILE__, __LINE__, 'Neuer Buchbesitzer', [$IdNewBook, $user->id]);
		$smarty->assign('error', ['type' => 'success', 'dismissable' => 'true', 'title' => 'Boook #'.$IdNewBook.' hinzugefügt']);

		$doAction = 'show';
		$book_id = $IdNewBook;

		/** Activity Eintrag auslösen */
		Activities::addActivity($user->id, 0, t('activity-new', 'books', [ SITE_URL, $book_id, $title ]), 'bo');
	} else {
		$smarty->assign('error', ['type' => 'warn', 'dismissable' => 'true', 'title' => 'Fehler beim hinzufügen des Buches "'.(string)$_POST['title'].'"']);
		$doAction = null;
	}
}
/** Neue Kategorie in DB einfügen */
elseif ($postAction === 'insert_titel' && $user->is_loggedin())
{
	/** Hauptkategorie */
	if ($parent_id === "new")
	{
		$sql = 'INSERT INTO books_title (typ) VALUES (?)';
 		$db->query($sql, __FILE__, __LINE__, 'insert_titel', [$title]);

 	/** Unterkategorie */
 	} elseif (empty($parent_id) || $parent_id){
		$sql = 'INSERT INTO books_title (parent_id, typ) VALUES (?, ?)';
 		$db->query($sql, __FILE__, __LINE__, 'insert_titel', [$parent_id, $title]);
 	}

	$smarty->assign('error', ['type' => 'success', 'dismissable' => 'true', 'title' => 'Kategorie '.(string)$_POST['titel'].' hinzugefügt']);
	$doAction = null;
}
/** Besitzer in DB hinzufuegen */
elseif ($doAction === 'add_owner' && $user->id > 0)
{
	/** Testen ob bereits besitzer */
	$sql = 'SELECT user_id FROM books_holder WHERE book_id=? AND user_id=?';
	if ($db->num($db->query($sql, __FILE__, __LINE__, 'add_owner', [$book_id, $user->id])) == 0)
	{
		/** Neuen Benutzer hinzufuegen */
		$sql = 'INSERT INTO books_holder (book_id, user_id) VALUES (?, ?)';
		$db->query($sql, __FILE__, __LINE__, 'add_owner', [$book_id, $user->id]);
	}

	$doAction = 'show';
}
/** Besitzer in DB loeschen */
elseif ($doAction === 'delete_owner' && $user->id > 0)
{
	$sql = 'DELETE FROM books_holder WHERE book_id=? AND user_id=?';
	$db->query($sql, __FILE__, __LINE__, 'delete_owner', [$book_id, $user->id]);

	$doAction = 'show';
}
/** Buch in DB loeschen */
elseif ($doAction === 'delete_now' && $user->id > 0)
{
	$sql = 'SELECT * FROM books WHERE id=?';
	$result = $db->query($sql, __FILE__, __LINE__, 'delete_now', $book_id);
	$rs = $db->fetch($result);

	/** darf user buch loeschen? */
	if ($rs['ersteller'] == $user->id)
	{
		$sql = 'SELECT count(*) as anzahl FROM books_holder WHERE book_id=?';
		$result2 = $db->query($sql, __FILE__, __LINE__, 'delete_now', [$book_id]);
		$rs2 = $db->fetch($result2);

		/** wenn andere user dieses buch auch besitzen ist l?schen nicht erlaubt */
		if ($rs2['anzahl'] == 1)
		{
			$sql = 'DELETE FROM books WHERE id=?';
			$db->query($sql, __FILE__, __LINE__, 'delete_now', [$book_id]);
			$smarty->assign('error', ['type' => 'info', 'dismissable' => 'true', 'title' => 'Book gelöscht']);
		} else {
			$smarty->assign('error', ['type' => 'warn', 'dismissable' => 'false', 'title' => 'Noch andere Besitzer',
			'message' => 'Sorry, dieses Book besitzen auch noch andere Leute und kann darum nicht geloescht werden.']);
		}
		$doAction = null;
	} else {
		$smarty->assign('error', ['type' => 'warn', 'dismissable' => 'true', 'title' => 'ganz eifach: NEI! du schpehone!']);
	}
}

/*********************/
/* Ausgabe Routinen */
/*******************/
$htmlOutput = null;

/** Buecherliste */
if (empty($doAction))
{
	$htmlOutput .= '<h1>Bücherliste der zorger</h1>';

	$sql = 'SELECT * FROM books_title where parent_id = 0 ORDER BY typ ASC';
	$result = $db->query($sql, __FILE__, __LINE__, 'Bücherliste');
	while($rs = $db->fetch($result))
	{
		$htmlOutput .= '<h4>'.$rs['typ'].'</h4>';

		$htmlOutput .= '<ul>';
		$sql = 'SELECT * FROM books_title WHERE parent_id=?';
		$result2 = $db->query($sql, __FILE__, __LINE__, 'Bücherliste', [$rs['id']]);
		while ($rs2 = $db->fetch($result2))
		{
			$htmlOutput .= '<li>'.$rs2['typ'].'</li><ul>';

	  		$sql = 'SELECT * FROM books WHERE titel_id=? AND parent_id=?';
			$result3 = $db->query($sql, __FILE__, __LINE__, 'Bücherliste', [$rs['id'], $rs2['id']]);

			while ($rs3 = $db->fetch($result3)) {
				$htmlOutput .= '<li><a href="?do=show&book_id='.$rs3['id'].'">'.$rs3['title'].'</a></li>';
			}
			$htmlOutput .= '</ul><br>';
		}

		$sql = 'SELECT * FROM books WHERE titel_id=? AND parent_id=0';
		$result3 = $db->query($sql, __FILE__, __LINE__, 'Bücherliste', [$rs['id']]);

		while ($rs3 = $db->fetch($result3)) {
				$htmlOutput .= '<li><a href="?do=show&book_id='.$rs3['id'].'">'.$rs3['title'].'</a></li>';
		}

		$htmlOutput .= '</ul>';
	}
	$htmlOutput .= '</td></tr></table><br>';

	/** Ists ein angemeldeter User? */
	$sidebarHtml = '';
	if ($user->is_loggedin() && $user->typ >= USER_MEMBER)
	{
		/** Eingabe Screen für neue Kategorie */
		$sidebarHtml .= '<h2>Neue Kategorie</h2>'
			.'<form action="'.htmlspecialchars($_SERVER['PHP_SELF']).'" method="post" enctype="multipart/form-data">'
			.'<input type="hidden" name="do" value="insert_titel">'
			.'<table cellpadding="1" cellspacing="1" width="400" class="border" align="center">'
			.'<tr><td align="left" style="font-weight: 600;">'

			.'Name'
			.'</td><td align="left" style="color:#".FONTCOLOR."; background-color:#".BACKGROUNDCOLOR.";border-bottom-style: solid; border-bottom-color: #".BORDERCOLOR."; border-bottom-width: 1px; border-left-style: solid; border-left-color: #".BORDERCOLOR."; border-left-width: 1px;">'
			.'<input class="text" size="60" type="text" name="titel">'
			.'</td><tr>'
			.'<td align="left" style="font-weight: 600;">'

			.'Einfügen als'
			.'</td><td align="left" style="color:#".FONTCOLOR."; background-color:#".BACKGROUNDCOLOR.";border-bottom-style: solid; border-bottom-color: #".BORDERCOLOR."; border-bottom-width: 1px; border-left-style: solid; border-left-color: #".BORDERCOLOR."; border-left-width: 1px;">'
			.'<select name="parent_id">'
			.'<option value="new">Hauptkategorie</option>';

		$sql = 'SELECT * FROM books_title WHERE parent_id = 0';
		$result = $db->query($sql, __FILE__, __LINE__, 'Neue Kategorie');

		while($rs2 = $db->fetch($result)) {
			$sidebarHtml .= '<option value="'.$rs2['id'].'">Unterpunkt von "'.$rs2['typ'].'"</option>';
		}

		$sidebarHtml .= '</select></td></tr></table>'
			.'<input type="submit" class="button" name="send" value="speichern">'
			.'</form>';
	}

	/** HTML Output */
	$model->showOverview($smarty);
	$smarty->assign('sidebarHtml', $sidebarHtml);
	$smarty->display('file:layout/head.tpl');
	echo $htmlOutput;

}
/** Buch ansehen */
elseif ($doAction === 'show' && $book_id > 0)
{
	/** Get Book Details */
	$sql = 'SELECT * from books WHERE id=?';
	$rs = $db->fetch($db->query($sql, __FILE__, __LINE__, 'Buch ansehen', [$book_id]));

	if ($rs !== false && $rs !== null)
	{
		$sql = 'SELECT * from books_title WHERE id=?';
		$rs2 = $db->fetch($db->query($sql, __FILE__, __LINE__, 'Buch ansehen', [$rs['titel_id']]));

		$htmlOutput .= '<h1>'.htmlentities($rs['title']).'</h1>';
		$htmlOutput .= '<table cellpadding="1" cellspacing="1" class="shadedcells">'
			.'<tr><td class="strong">'
			.'Autor:'
			.'</td><td>'
			.htmlentities($rs['autor'])
			.'</td></tr><tr><td class="strong">'
			.'Verlag:'
			.'</td><td>'
			.htmlentities($rs['verlag'])
			.'</td></tr><tr><td class="strong">'
			.'ISBN:'
			.'</td><td>'
			.htmlentities($rs['isbn'])
			.'</td></tr><tr><td class="strong">'
			.'Thema:'
			.'</td><td>'
			.htmlentities($rs2['typ'])
			.'</td></tr><tr><td class="strong">'
			.'Druckjahr:'
			.'</td><td>'
			.intval($rs['jahrgang'])
			.'</td></tr><tr><td class="strong">'
			.'Preis:'
			.'</td><td>'
			.'CHF '.number_format(floatval($rs['preis']), 2, '.', '')
			.'</td></tr><tr><td class="strong">'
			.'Seiten:'
			.'</td><td>'
			.intval($rs['seiten'])
			.'</td></tr><tr><td class="strong">'
			.'Besitzer:'
			.'</td><td>'
			;

		/** besitzer auflisten */
		$sql = 'SELECT * from books_holder WHERE book_id=?';
		$result3 = $db->query($sql, __FILE__, __LINE__, 'Besitzer auflisten', [$rs['id']]);
		$alleBesitzer = '';
		while ($rs3 = $db->fetch($result3))
		{
			$alleBesitzer .= sprintf('<a href="?do=my&user=%d">%s</a>, ', $rs3['user_id'], $user->id2user($rs3['user_id'], 0));
		}
		$htmlOutput .= substr($alleBesitzer, 0, -2); // Entfernt das allerletzte Komma

		$htmlOutput .= '</td></tr></table>';

		$htmlOutput .= nl2br(htmlentities($rs['text']));

		/** Ists ein angemeldeter User? */
		if ($user->is_loggedin())
		{
			$htmlOutput .= '<h3>Rezensionen</h3>'; // CommentingSystem Title

			$sidebarHtml = '<h3>Boook Actions</h3>';

			/** Wer das Buch besitzt kanns loeschen, wer nicht kanns hinzufuegen */
			$sql = 'SELECT user_id FROM books_holder WHERE book_id=? AND user_id=?';
			if ($db->num($db->query($sql, __FILE__, __LINE__, 'Buchbesitzer', [$rs['id'], $user->id])) == 1)
			{
				$sidebarHtml .= '<a href="?do=edit&book_id='.$rs['id'].'">[edit]</a><br>'
								.'<a href="?do=delete_owner&book_id='.$rs['id'].'">[delete book from my list]</a><br>';
			} else {
				$sidebarHtml .= '<a href="?do=add_owner&book_id='.$rs['id'].'">[add book to my list]</a><br>';
			}

			/** nur ersteller kann loeschen, falls keine anderen besitzer vorhanden */
			if ($user->id == $rs['ersteller'])
			{
				$sidebarHtml .= '<a href="?do=delete&book_id='.$rs['id'].'">[delete]</a>';
			}
		} else {
			$sidebarHtml = '&nbsp;';
		}

		/** HTML Output */
		$model->showBook($smarty, $book_id, $rs['title']);
		$smarty->assign('sidebarHtml', $sidebarHtml);
		$smarty->display('file:layout/head.tpl');
		echo $htmlOutput;

		// Book Commenting ------------------------------------------
		Forum::printCommentingSystem('k', $rs['id']);
		// End Commenting -------------------------------------------
	}

	/** Invalid Book ID / Book not found */
	else {
		http_response_code(404); // Set response code 404 (not found) and exit.
		$model->notFound($smarty, $book_id);
		$htmlOutput = $smarty->fetch('file:layout/head.tpl');
		$smarty->assign('error', ['type' => 'danger', 'dismissable' => 'false', 'title' => 'Buch nicht gefunden!']);
		$htmlOutput .= $smarty->fetch('file:layout/elements/block_error.tpl');
		echo $htmlOutput;
	}
}
/** Buch bearbeiten */
elseif ($doAction === 'edit' && $user->is_loggedin())
{
	$model->showEdit($smarty, $book_id);
	$smarty->display('file:layout/head.tpl');

	/** besitzt der user das buch? */
	$sql = 'SELECT COUNT(*) AS anzahl FROM books_holder WHERE book_id=? AND user_id=?';
	$result = $db->query($sql, __FILE__, __LINE__, 'SELECT COUNT(*)', [$book_id, $user->id]);
	$rs = $db->fetch($result);

	if ($rs['anzahl'] != 1)
	{
		$smarty->assign('error', ['type' => 'warn', 'dismissable' => 'true', 'title' => 'genau nöd, tschalphorre']);
		$smarty->display('file:layout/elements/block_error.tpl');
		//exit;

	} else {
		$sql = 'SELECT * FROM books WHERE id=?';
		$result = $db->query($sql, __FILE__, __LINE__, 'SELECT FROM books', [$book_id]);
		$rs = $db->fetch($result);

		echo "<form action='".htmlspecialchars($_SERVER['PHP_SELF'])."' method='post' enctype='multipart/form-data'>"
			.'<input type="hidden" name="do" value="edit_now">'
			.'<input type="hidden" name="book_id" value="'.$rs["id"].'">'

			."<table width=\"$mainwidth\"><tr><td align=\"left\" class=\"title\">"
			."Edit Boook"
			."</td></tr></table>"
			."<br/>"
			."<table cellpadding=\"1\" cellspacing=\"1\" width=\"500\" class=\"border\" align=\"center\">"
			."<tr><td align=\"left\" style=\"font-weight: 600;\">"

			."Titel:"
			."</td><td align=\"left\" style=\"color:#".FONTCOLOR."; background-color:#".BACKGROUNDCOLOR.";border-bottom-style: solid; border-bottom-color: #".BORDERCOLOR."; border-bottom-width: 1px; border-left-style: solid; border-left-color: #".BORDERCOLOR."; border-left-width: 1px;\">"
			."<input class='text' size='80' type=\"text\" name=\"title\" value=\"".$rs["title"]."\">"
			."</td></tr><tr><td align=\"left\" style=\"font-weight: 600;\">"

			."Autor:"
			."</td><td align=\"left\" style=\"color:#".FONTCOLOR."; background-color:#".BACKGROUNDCOLOR.";border-bottom-style: solid; border-bottom-color: #".BORDERCOLOR."; border-bottom-width: 1px; border-left-style: solid; border-left-color: #".BORDERCOLOR."; border-left-width: 1px;\">"
			."<input class='text' size='80' type=\"text\" name=\"autor\" value=\"".$rs["autor"]."\">"
			."</td></tr><tr><td align=\"left\" style=\"font-weight: 600;\">"

			."Verlag:"
			."</td><td align=\"left\" style=\"color:#".FONTCOLOR."; background-color:#".BACKGROUNDCOLOR.";border-bottom-style: solid; border-bottom-color: #".BORDERCOLOR."; border-bottom-width: 1px; border-left-style: solid; border-left-color: #".BORDERCOLOR."; border-left-width: 1px;\">"
			."<input class='text' size='80' type=\"text\" name=\"verlag\" value=\"".$rs["verlag"]."\">"
			."</td></tr><tr><td align=\"left\" style=\"font-weight: 600;\">"

			."ISBN:"
			."</td><td align=\"left\" style=\"color:#".FONTCOLOR."; background-color:#".BACKGROUNDCOLOR.";border-bottom-style: solid; border-bottom-color: #".BORDERCOLOR."; border-bottom-width: 1px; border-left-style: solid; border-left-color: #".BORDERCOLOR."; border-left-width: 1px;\">"
			."<input class='text' size='80' type=\"text\" name=\"isbn\" value=\"".$rs["isbn"]."\">"
			."</td></tr><tr><td align=\"left\" style=\"font-weight: 600;\">"

			."Druckjahr:"
			."</td><td align=\"left\" style=\"color:#".FONTCOLOR."; background-color:#".BACKGROUNDCOLOR.";border-bottom-style: solid; border-bottom-color: #".BORDERCOLOR."; border-bottom-width: 1px; border-left-style: solid; border-left-color: #".BORDERCOLOR."; border-left-width: 1px;\">"
			."<input class='text' size='80' type=\"number\" step=\"1\" min=\"1900\" name=\"jahrgang\" value=\"".$rs["jahrgang"]."\">"
			."</td></tr><tr><td align=\"left\" style=\"font-weight: 600;\">"

			."Preis CHF:"
			."</td><td align=\"left\" style=\"color:#".FONTCOLOR."; background-color:#".BACKGROUNDCOLOR.";border-bottom-style: solid; border-bottom-color: #".BORDERCOLOR."; border-bottom-width: 1px; border-left-style: solid; border-left-color: #".BORDERCOLOR."; border-left-width: 1px;\">"
			."<input class='text' size='80' type=\"number\" step=\"0.01\" min=\"0\" lang=\"de-CH\" name=\"preis\" value=\"".$rs["preis"]."\">"
			."</td></tr><tr><td align=\"left\" style=\"font-weight: 600;\">"

			."Seiten:"
			."</td><td align=\"left\" style=\"color:#".FONTCOLOR."; background-color:#".BACKGROUNDCOLOR.";border-bottom-style: solid; border-bottom-color: #".BORDERCOLOR."; border-bottom-width: 1px; border-left-style: solid; border-left-color: #".BORDERCOLOR."; border-left-width: 1px;\">"
			."<input class='text' size='80' type=\"number\" step=\"1\" min=\"0\" name=\"seiten\" value=\"".$rs["seiten"]."\">"
			."</td></tr><tr><td align=\"left\" style=\"font-weight: 600;\">"

			."Typ:"
			."</td><td align=\"left\" style=\"color:#".FONTCOLOR."; background-color:#".BACKGROUNDCOLOR.";border-bottom-style: solid; border-bottom-color: #".BORDERCOLOR."; border-bottom-width: 1px; border-left-style: solid; border-left-color: #".BORDERCOLOR."; border-left-width: 1px;\">"
			."<select name=\"titel_id\">";

		/** Themen und Unterthemen in Listbox sortieren */
		$sql = 'SELECT * FROM books_title WHERE parent_id = 0';
		$result = $db->query($sql, __FILE__, __LINE__, 'SELECT FROM books_title');

		while ($rs2 = $db->fetch($result))
		{
			if ($rs["titel_id"] == $rs2["id"])
			{
				echo '<option value="'.$rs2['id'].'" selected>'.$rs2['typ'].'</option>';
			} else {
				echo '<option value="'.$rs2['id'].'">'.$rs2['typ'].'</option>';
			}

			$sql = 'SELECT * FROM books_title WHERE parent_id=?';
			$result2 = $db->query($sql, __FILE__, __LINE__, 'SELECT books_title with parent_id', [$rs2['id']]);

			while ($rs3 = $db->fetch($result2))
			{
				if ($rs["titel_id"] == $rs3["id"])
				{
					echo '<option value="'.$rs3['id'].'" selected> - '.$rs3['typ'].'</option>';
				} else {
					echo '<option value="'.$rs3['id'].'"> - '.$rs3['typ'].'</option>';
				}
			}
		}
		echo "</select>"
			."</td></tr><tr><td align=\"left\" style=\"font-weight: 600;\">"
			."Text:"
				."</td><td align=\"left\" style=\"color:#".FONTCOLOR."; background-color:#".BACKGROUNDCOLOR.";border-bottom-style: solid; border-bottom-color: #".BORDERCOLOR."; border-bottom-width: 1px; border-left-style: solid; border-left-color: #".BORDERCOLOR."; border-left-width: 1px;\">"
			."<textarea class='text' type=\"text\" name=\"text\" cols=\"80\" rows=\"10\">"
			.$rs['text']
			."</textarea>"
			."</td></tr></table>"
			."<input type='submit' class='button' name='send' value='speichern'>"
			."</form>";
	}
}

/** Buch hinzufuegen */
elseif ($doAction === 'add' && $user->is_loggedin())
{
	$model->showAddnew($smarty);
	$smarty->display('file:layout/head.tpl');

	echo '<h2>Add Boook</h2>'
		.'<form action="'.htmlspecialchars($_SERVER['PHP_SELF']).'" method="post" enctype="multipart/form-data">'
		.'<input type="hidden" name="do" value="add_now">'

		.'<table width="'.$mainwidth.'"><tr><td align="left" class="title">'
		."</td></tr></table>"
		."<br/>"
		."<table cellpadding=\"1\" cellspacing=\"1\" width=\"500\" class=\"border\" align=\"center\">"
		."<tr><td align=\"left\" style=\"font-weight: 600;\">"

		."Titel:"
		."</td><td align=\"left\" style=\"color:#".FONTCOLOR."; background-color:#".BACKGROUNDCOLOR.";border-bottom-style: solid; border-bottom-color: #".BORDERCOLOR."; border-bottom-width: 1px; border-left-style: solid; border-left-color: #".BORDERCOLOR."; border-left-width: 1px;\">"
		."<input class='text' size='80' type=\"text\" name=\"title\">"
		."</td></tr><tr><td align=\"left\" style=\"font-weight: 600;\">"

		."Autor:"
		."</td><td align=\"left\" style=\"color:#".FONTCOLOR."; background-color:#".BACKGROUNDCOLOR.";border-bottom-style: solid; border-bottom-color: #".BORDERCOLOR."; border-bottom-width: 1px; border-left-style: solid; border-left-color: #".BORDERCOLOR."; border-left-width: 1px;\">"
		."<input class='text' size='80' type=\"text\" name=\"autor\">"
		."</td></tr><tr><td align=\"left\" style=\"font-weight: 600;\">"

		."Verlag:"
		."</td><td align=\"left\" style=\"color:#".FONTCOLOR."; background-color:#".BACKGROUNDCOLOR.";border-bottom-style: solid; border-bottom-color: #".BORDERCOLOR."; border-bottom-width: 1px; border-left-style: solid; border-left-color: #".BORDERCOLOR."; border-left-width: 1px;\">"
		."<input class='text' size='80' type=\"text\" name=\"verlag\">"
		."</td></tr><tr><td align=\"left\" style=\"font-weight: 600;\">"

		."ISBN:"
		."</td><td align=\"left\" style=\"color:#".FONTCOLOR."; background-color:#".BACKGROUNDCOLOR.";border-bottom-style: solid; border-bottom-color: #".BORDERCOLOR."; border-bottom-width: 1px; border-left-style: solid; border-left-color: #".BORDERCOLOR."; border-left-width: 1px;\">"
		."<input class='text' size='80' type=\"text\" name=\"isbn\">"
		."</td></tr><tr><td align=\"left\" style=\"font-weight: 600;\">"

		."Druckjahr:"
		."</td><td align=\"left\" style=\"color:#".FONTCOLOR."; background-color:#".BACKGROUNDCOLOR.";border-bottom-style: solid; border-bottom-color: #".BORDERCOLOR."; border-bottom-width: 1px; border-left-style: solid; border-left-color: #".BORDERCOLOR."; border-left-width: 1px;\">"
		."<input class='text' size='80' type=\"number\" step=\"1\" min=\"1900\" name=\"jahrgang\">"
		."</td></tr><tr><td align=\"left\" style=\"font-weight: 600;\">"

		."Preis CHF:"
		."</td><td align=\"left\" style=\"color:#".FONTCOLOR."; background-color:#".BACKGROUNDCOLOR.";border-bottom-style: solid; border-bottom-color: #".BORDERCOLOR."; border-bottom-width: 1px; border-left-style: solid; border-left-color: #".BORDERCOLOR."; border-left-width: 1px;\">"
		."<input class='text' size='80' type=\"number\" step=\"0.01\" min=\"0\" name=\"preis\">"
		."</td></tr><tr><td align=\"left\" style=\"font-weight: 600;\">"

		."Seiten:"
		."</td><td align=\"left\" style=\"color:#".FONTCOLOR."; background-color:#".BACKGROUNDCOLOR.";border-bottom-style: solid; border-bottom-color: #".BORDERCOLOR."; border-bottom-width: 1px; border-left-style: solid; border-left-color: #".BORDERCOLOR."; border-left-width: 1px;\">"
		."<input class='text' size='80' type=\"number\" step=\"1\" min=\"0\" name=\"seiten\">"
		."</td></tr><tr><td align=\"left\" style=\"font-weight: 600;\">"

		."Typ:"
		."</td><td align=\"left\" style=\"color:#".FONTCOLOR."; background-color:#".BACKGROUNDCOLOR.";border-bottom-style: solid; border-bottom-color: #".BORDERCOLOR."; border-bottom-width: 1px; border-left-style: solid; border-left-color: #".BORDERCOLOR."; border-left-width: 1px;\">"
		."<select name=\"titel_id\">";

	/** Kategorien in Listbox sortieren */
	$sql = 'SELECT * FROM books_title WHERE parent_id = 0';
	$result = $db->query($sql, __FILE__, __LINE__, 'SELECT FROM books_title');

	while($rs2 = $db->fetch($result))
	{
		echo '<option value="'.$rs2['id'].'">'.$rs2['typ'].'</option>';

		$sql = 'SELECT * FROM books_title WHERE parent_id=?';
		$result2 = $db->query($sql, __FILE__, __LINE__, 'SELECT book_title WHERE parent_id', [$rs2['id']]);

		while ($rs3 = $db->fetch($result2))
		{
			echo '<option value="'.$rs3['id'].'" selected> - '.$rs3['typ'].'</option>';
		}
	}

	echo "</select>"
		."</td></tr><tr><td align=\"left\" style=\"font-weight: 600;\">"
		."Text:"
		."</td><td align=\"left\" style=\"color:#".FONTCOLOR."; background-color:#".BACKGROUNDCOLOR.";border-bottom-style: solid; border-bottom-color: #".BORDERCOLOR."; border-bottom-width: 1px; border-left-style: solid; border-left-color: #".BORDERCOLOR."; border-left-width: 1px;\">"
		."<textarea class='text' type=\"text\" name=\"text\" cols=\"80\" rows=\"10\">"
		."</textarea>"
		."</td></tr></table>"
		."<input type='submit' class='button' name='send' value='speichern'>"
		."</form>";

} elseif ($doAction === 'admin' && $user->typ === USER_MEMBER) {
/*
	$sql = 'SELECT * FROM books_title WHERE parent_id = 0 ORDER BY typ ASC';
		$result = $db->query($sql);
	echo "<table><tr><td align='left'>";
	while($rs = $db->fetch($result)) {
			echo($rs[typ]."<br><ul>");

			$sql = 'SELECT * FROM books_title WHERE parent_id = '.$rs['id'];
			$result2 = $db->query($sql);

			while ($rs2 = $db->fetch($result2)) {
				echo("<li><a href=\"$PHP_SELF?do=show&book_id=$rs2[id]\">$rs2[typ]</a></li>");
			}
			echo("</ul>");
		}
		echo "</td></tr></table>";*/

}
/** Buch wirklich löschen? */
elseif ($doAction === 'delete' && $user->is_loggedin())
{
	$sql = 'SELECT * FROM books where id=?';
	$result = $db->query($sql, __FILE__, __LINE__, 'SELECT FROM books', [$book_id]);
	$rs = $db->fetch($result);

	$model->showDelete($smarty, $book_id, $rs['title']);
	$smarty->display('file:layout/head.tpl');

	/*
	echo 'Willst du das Buch "'.$rs['title'].'" wirklich löschen?<br>'
		.'<a href="?do=delete_now&book_id='.$rs['id'].'"">ja</a>'
		.' / '
		.'<a href="?do=show&book_id='.$rs['id'].'">nein</a>';
	*/
	$confirmSubject = 'Willst du das Buch "'.$rs['title'].'" wirklich löschen?';
	$confirmMessage = '<a href="?do=delete_now&book_id='.$rs['id'].'"">ja</a> / <a href="?do=show&book_id='.$rs['id'].'">nein</a>';
	$smarty->assign('error', ['type' => 'warn', 'dismissable' => 'false', 'title' => $confirmSubject, 'message' => $confirmMessage]);
	$smarty->display('file:layout/elements/block_error.tpl');

}
/** Bücherliste von bestimmten User ausgeben */
elseif ($doAction === 'my' && $user_id>0)
{
	//$smarty->display('file:layout/head.tpl');
	//if ($smarty->get_template_vars('error') != null) $smarty->display('file:layout/elements/block_error.tpl');
	//if ($smarty->getTemplateVars('foo') != null) $smarty->display('file:layout/elements/block_error.tpl'); // Smarty 3.x

	$htmlOutput .= '<h2>Boooks von '.$user->id2user($user_id).'</h2>';

	$sql = 'SELECT DISTINCT titel_id FROM books, books_holder
			WHERE books.id=books_holder.book_id AND books_holder.user_id=?';
	$result = $db->query($sql, __FILE__, __LINE__, 'SELECT DISTING books', [$user_id]);
	while($rs = $db->fetch($result))
	{
		$htmlOutput .= '<h4>'.get_title(intval($rs['titel_id'])).'</h4>';
		$htmlOutput .= '<ul>';
		$sql = 'SELECT DISTINCT parent_id
				FROM books, books_holder
				WHERE
				books.parent_id <> 0 AND
				books.titel_id = '.$rs['titel_id'].' AND
				books.id = books_holder.book_id AND
				books_holder.user_id = '.$user_id;
		$result2 = $db->query($sql, __FILE__, __LINE__);
		while($rs2 = $db->fetch($result2))
		{
			$htmlOutput .= "<li>".get_title(intval($rs2['parent_id']))."</li><ul>";
			$sql = 'SELECT * FROM books, books_holder
					WHERE books.titel_id=? AND books.parent_id=? AND books.id = books_holder.book_id AND books_holder.user_id=?';
			$result3 = $db->query($sql, __FILE__, __LINE__, 'SELECT books_holder', [$rs['titel_id'], $rs2['parent_id'], $user_id]);
			while($rs3 = $db->fetch($result3))
			{
				$htmlOutput .= '<li><a href="?do=show&book_id='.$rs3['book_id'].'">'.$rs3['title'].'</a></li>';
			}
			$htmlOutput .= '</ul>';
		}

		$sql = 'SELECT * FROM books, books_holder
				WHERE books.titel_id=? AND books.parent_id=0 AND books.id=books_holder.book_id AND books_holder.user_id=?';
		$result4 = $db->query($sql, __FILE__, __LINE__, 'SELECT FROM books_holder', [$rs['titel_id'], $user_id]);
		while($rs4 = $db->fetch($result4)) {
			$htmlOutput .= '<li><a href="?do=show&book_id='.$rs4['book_id'].'">'.$rs4['title'].'</a></li>';
		}
		$htmlOutput .= '</ul>';
	}

	/** HTML Output */
	$model->showUserbooks($smarty, $user, $user_id);
	$smarty->assign('sidebarHtml', $user->userprofile_link($user_id, ['pic' => true, 'username' => true, 'clantag' => true, 'link' => true]));
	$smarty->display('file:layout/head.tpl');
	echo $htmlOutput;
}

$smarty->display('file:layout/footer.tpl');
