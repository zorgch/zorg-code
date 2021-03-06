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
require_once dirname(__FILE__).'/includes/main.inc.php';
require_once MODELS_DIR.'core.model.php';

/**
 * Initialise MVC Model
 */
$model = new MVC\Books();

/**
 * Validate Passed Parameters
 */
/** GET */
if (isset($_GET['book_id']) && is_numeric($_GET['book_id'])) $book_id = (int)$_GET['book_id'];
if (isset($_GET['do']) && is_string($_GET['do'])) $action = (string)$_GET['do'];
$user_id = (isset($_GET['user']) && is_numeric($_GET['user']) ? (int)$_GET['user'] : $user->id);
/** POST */
$postAction = (isset($_POST['do']) && is_string($_POST['do']) ? (string)$_POST['do'] : null);

/**************/
/* Funktionen */
/**************/
/**
 * Kategorien-Bezeichnung holen
 *
 * Gibt Kategorie aus books_title zurück
 *
 * @return string title
 * @param $kat_id int
 */
function get_title($kat_id)
{
	global $db;
	$sql = 'SELECT typ FROM books_title WHERE id = '.$kat_id;
	$result = $db->query($sql, __FILE__, __LINE__);
	$rs = $db->fetch($result);

	return $rs['typ'];
}

/***************/
/* DB Routinen */
/***************/
/** Aenderung an Buch in DB speichern */
if($postAction === 'edit_now' && true === $user->is_loggedin())
{
	/** besitzt der user das buch? */
	$sql = 'SELECT count(*) as anzahl FROM books_holder WHERE book_id = '.(int)$_POST['book_id'].' AND user_id = '.$user->id;
	$result = $db->query($sql, __FILE__, __LINE__, 'edit_now');
	$rs = $db->fetch($result);

	if ($rs['anzahl'] != 1)
	{
		$smarty->assign('error', ['type' => 'warn', 'dismissable' => 'true', 'title' => 'au jetzt nonig, tschalphorre']);
		//exit;
	} else {
		$sql = 'SELECT * FROM books_title WHERE id = '.(int)$_POST['titel_id'];
		$result = $db->query($sql, __FILE__, __LINE__, 'edit_now');
		$rs = $db->fetch($result);

		if ($rs['parent_id'] != 0)
		{
			$titel_id = $rs['parent_id'];
			$parent_id = $rs['id'];
		} else {
			$titel_id = $rs['id'];
			$parent_id = 0;
		}

		$sql = 'UPDATE books set title = "'.addslashes(strip_tags((string)$_POST['title'])).'",
				autor = "'.addslashes(strip_tags((string)$_POST['autor'])).'",
				verlag = "'.addslashes(strip_tags((string)$_POST['verlag'])).'",
				isbn = "'.addslashes(strip_tags((string)$_POST['isbn'])).'",
				titel_id = "'.(string)$titel_id.'",
				parent_id = '.$parent_id.',
				jahrgang = '.(int)$_POST['jahrgang'].',
				preis = "'.(float)$_POST['preis'].'",
				seiten = "'.(int)$_POST['seiten'].'",
				text = "'.addslashes(strip_tags((string)$_POST['text'])).'"
				WHERE id = '.(int)$_POST['book_id'];
		$db->query($sql, __FILE__, __LINE__, 'edit_now');
		$smarty->assign('error', ['type' => 'success', 'dismissable' => 'true', 'title' => 'Boook geändert']);
		$action = "show";
		$book_id = (int)$_POST['book_id'];
	}
}
/** Buch in DB hinzufuegen */
elseif ($postAction === 'add_now' && $user->is_loggedin())
{
	/** Kategorie und Parent-Kategorie finden */
	$sql = 'SELECT * FROM books_title WHERE id = '.(int)$_POST['titel_id'];
	$result = $db->query($sql, __FILE__, __LINE__, 'add_now');
	$rs = $db->fetch($result);

	if ($rs['parent_id'] != 0)
	{
		$titel_id = $rs['parent_id'];
		$parent_id = $rs['id'];
	} else {
		$titel_id = $rs['id'];
		$parent_id = 0;
	}

	/** Book adden */
	if (DEVELOPMENT === true) error_log(sprintf('[DEBUG] <%s:%d> Insert Book $_POST: %s', __FILE__, __LINE__, print_r($_POST, true)));
	$bookTitle = (isset($_POST['title']) & is_string($_POST['title']) ? addslashes(strip_tags((string)$_POST['title'])) : '');
	$bookAutor = (isset($_POST['autor']) & is_string($_POST['autor']) ? addslashes(strip_tags((string)$_POST['autor'])) : '');
	$bookVerlag = (isset($_POST['autor']) & is_string($_POST['autor']) ? addslashes(strip_tags((string)$_POST['autor'])) : '');
	$bookIsbn = (isset($_POST['isbn']) & is_string($_POST['isbn']) ? addslashes(strip_tags((string)$_POST['isbn'])) : '');
	$bookJahr = (isset($_POST['jahrgang']) & is_numeric($_POST['jahrgang']) ? (int)$_POST['jahrgang'] : '');
	$bookPreis = (isset($_POST['preis']) & is_numeric($_POST['preis']) ? (float)$_POST['preis'] : 0);
	$bookSeiten = (isset($_POST['seiten']) & is_numeric($_POST['seiten']) ? (int)$_POST['seiten'] : '');
	$bookText = (isset($_POST['text']) & is_string($_POST['text']) ? addslashes(strip_tags((string)$_POST['text'])) : '');
	$sql = 'INSERT INTO books (
				title,
				autor,
				verlag,
				isbn,
				titel_id,
				parent_id,
				jahrgang,
				preis,
				seiten,
				text,
				ersteller
			) VALUES (
				 "'.$bookTitle.'"
				,"'.$bookAutor.'"
				,"'.$bookVerlag.'"
				,"'.$bookIsbn.'"
				,'.$titel_id.'
				,'.$parent_id.'
				,'.$bookJahr.'
				,'.$bookPreis.'
				,'.$bookSeiten.'
				,"'.$bookText.'"
				,'.$user->id.'
			)';
			if (DEVELOPMENT === true) error_log(sprintf('[DEBUG] <%s:%d> Insert Book SQL: %s', __FILE__, __LINE__, $sql));
	$IdNewBook = $db->query($sql, __FILE__, __LINE__, 'add_now');

	if ($IdNewBook !== false && !is_bool($IdNewBook) && $IdNewBook > 0)
	{
		$sql = 'INSERT INTO books_holder(book_id, user_id)
				VALUES('.$IdNewBook.','.$user->id.')';
		$db->query($sql, __FILE__, __LINE__, 'Neuer Buchbesitzer');

		$smarty->assign('error', ['type' => 'success', 'dismissable' => 'true', 'title' => 'Boook #'.$IdNewBook.' hinzugefügt']);
		
		$action = 'show';
		$book_id = $IdNewBook;
	} else {
		$smarty->assign('error', ['type' => 'warn', 'dismissable' => 'true', 'title' => 'Fehler beim hinzufügen des Buches "'.(string)$_POST['title'].'"']);
		$action = null;
	}
}
/** Neue Kategorie in DB einfügen */
elseif ($postAction === 'insert_titel' && true === $user->is_loggedin())
{
	/** Hauptkategorie */
	if ($_POST['parent_id'] == "new")
	{
		$sql = 'INSERT INTO books_title (typ) VALUES ("'.(string)$_POST['titel'].'")';
 		$db->query($sql, __FILE__, __LINE__, 'insert_titel');

 	/** Unterkategorie */
 	} elseif ($_POST['parent_id'] != ""){
		$sql = 'INSERT INTO books_title (parent_id, typ) VALUES ('.(int)$_POST['parent_id'].',"'.(string)$_POST['titel'].'")';
 		$db->query($sql, __FILE__, __LINE__, 'insert_titel');
 	}

	$smarty->assign('error', ['type' => 'success', 'dismissable' => 'true', 'title' => 'Kategorie '.(string)$_POST['titel'].' hinzugefügt']);
	$action = null;
}
/** Besitzer in DB hinzufuegen */
elseif ($action === 'add_owner' && $user->id > 0)
{
	/** Testen ob bereits besitzer */
	$sql = 'SELECT user_id FROM books_holder where book_id = '.$book_id.' AND user_id = '.$user->id;
	if ($db->num($db->query($sql, __FILE__, __LINE__, 'add_owner')) == 0)
	{ 
		/** Neuen Benutzer hinzufuegen */
		$sql = 'INSERT INTO books_holder (book_id, user_id) VALUES ('.$book_id.','.$user->id.')';
		$db->query($sql, __FILE__, __LINE__, 'add_owner');
	}
	
	$action = 'show';
}
/** Besitzer in DB loeschen */
elseif ($action === 'delete_owner' && $user->id > 0)
{

	$sql = 'DELETE FROM books_holder WHERE book_id = '.$book_id.' AND user_id = '.$user->id;
	$db->query($sql, __FILE__, __LINE__, 'delete_owner');

	$action = 'show';
}
/** Buch in DB loeschen */
elseif ($action === 'delete_now' && $user->id > 0)
{
	$sql = 'SELECT * FROM books WHERE id = '.$book_id;
	$result = $db->query($sql, __FILE__, __LINE__, 'delete_now');
	$rs = $db->fetch($result);

	/** darf user buch loeschen? */
	if ($rs['ersteller'] == $user->id)
	{
		$sql = 'SELECT count(*) as anzahl FROM books_holder WHERE book_id = '.$book_id;
		$result2 = $db->query($sql, __FILE__, __LINE__, 'delete_now');
		$rs2 = $db->fetch($result2);

		/** wenn andere user dieses buch auch besitzen ist l?schen nicht erlaubt */
		if ($rs2['anzahl'] == 1)
		{
			$sql = 'DELETE FROM books WHERE id = '.$book_id;
			$db->query($sql, __FILE__, __LINE__, 'delete_now');
			$smarty->assign('error', ['type' => 'info', 'dismissable' => 'true', 'title' => 'Book gelöscht']);
		} else {
			$smarty->assign('error', ['type' => 'warn', 'dismissable' => 'false', 'title' => 'Noch andere Besitzer',
			'message' => 'Sorry, dieses Book besitzen auch noch andere Leute und kann darum nicht geloescht werden.']);
		}
		$action = null;
	} else {
		$smarty->assign('error', ['type' => 'warn', 'dismissable' => 'true', 'title' => 'ganz eifach: NEI! du schpehone!']);
	}
}

/*********************/
/* Ausgabe Routinen */
/*******************/
$htmlOutput = null;

/** Buecherliste */
if (!isset($action) || empty($action))
{
	$htmlOutput .= '<h1>Bücherliste der zorger</h1>';

	$sql = 'SELECT * FROM books_title where parent_id = 0 ORDER BY typ ASC';
	$result = $db->query($sql, __FILE__, __LINE__, 'Bücherliste');
	while($rs = $db->fetch($result))
	{
		$htmlOutput .= '<h4>'.$rs['typ'].'</h4>';

		$htmlOutput .= '<ul>';
		$sql = 'SELECT * FROM books_title WHERE parent_id = '.$rs['id'];
		$result2 = $db->query($sql, __FILE__, __LINE__, 'Bücherliste');
		while ($rs2 = $db->fetch($result2))
		{
			$htmlOutput .= '<li>'.$rs2['typ'].'</li><ul>';

	  		$sql = 'SELECT * FROM books WHERE titel_id = '.$rs['id'].' AND parent_id = '.$rs2['id'];
			$result3 = $db->query($sql, __FILE__, __LINE__, 'Bücherliste');

			while ($rs3 = $db->fetch($result3)) {
				$htmlOutput .= '<li><a href="?do=show&book_id='.$rs3['id'].'">'.$rs3['title'].'</a></li>';
			}
			$htmlOutput .= '</ul><br>';
		}

		$sql = 'SELECT * FROM books WHERE titel_id = '.$rs['id'].' AND parent_id = 0';
		$result3 = $db->query($sql, __FILE__, __LINE__, 'Bücherliste');

		while ($rs3 = $db->fetch($result3)) {
				$htmlOutput .= '<li><a href="?do=show&book_id='.$rs3['id'].'">'.$rs3['title'].'</a></li>';
		}

		$htmlOutput .= '</ul>';
	}
	$htmlOutput .= '</td></tr></table><br>';

	/** Ists ein angemeldeter User? */
	if ($user->is_loggedin() && $user->typ >= USER_MEMBER)
	{
		/** Eingabe Screen für neue Kategorie */
		$sidebarHtml = null;
		$sidebarHtml .= '<h2>Neue Kategorie</h2>'
			.'<form action="'.$_SERVER['PHP_SELF'].'" method="post" enctype="multipart/form-data">'
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
elseif ($action === 'show' && isset($book_id))
{
	$sql = 'SELECT * from books WHERE id = '.$book_id;
	$rs = $db->fetch($db->query($sql, __FILE__, __LINE__, 'Buch ansehen'));

	$sql = 'SELECT * from books_title WHERE id = '.$rs['titel_id'];
	$rs2 = $db->fetch($db->query($sql, __FILE__, __LINE__, 'Buch ansehen'));

	$htmlOutput .= '<h1>'.htmlentities($rs['title']).'</h1>';
	$htmlOutput .= '<table cellpadding="1" cellspacing="1" class="border" align="center" style="max-width: 100%;">'
		.'<tr><td align="left" style="font-weight: 600;">'
		.'Autor:'
		.'</td><td align="left" style="color:#'.FONTCOLOR.'; background-color:#'.BACKGROUNDCOLOR.'; border-bottom-style: solid; border-bottom-color: #'.BORDERCOLOR.'; border-bottom-width: 1px; border-left-style: solid; border-left-color: #'.BORDERCOLOR.'; border-left-width: 1px;">'
		.htmlentities($rs['autor'])
		.'</td></tr><tr><td align="left" style="font-weight: 600;">'
		.'Verlag:'
		.'</td><td align="left" style="color:#'.FONTCOLOR.'; background-color:#'.BACKGROUNDCOLOR.'; border-bottom-style: solid; border-bottom-color: #'.BORDERCOLOR.'; border-bottom-width: 1px; border-left-style: solid; border-left-color: #'.BORDERCOLOR.'; border-left-width: 1px;">'
		.htmlentities($rs['verlag'])
		.'</td></tr><tr><td align="left" style="font-weight: 600;">'
		.'ISBN:'
		.'</td><td align="left" style="color:#'.FONTCOLOR.'; background-color:#'.BACKGROUNDCOLOR.'; border-bottom-style: solid; border-bottom-color: #'.BORDERCOLOR.'; border-bottom-width: 1px; border-left-style: solid; border-left-color: #'.BORDERCOLOR.'; border-left-width: 1px;">'
		.htmlentities($rs['isbn'])
		.'</td></tr><tr><td align="left" style="font-weight: 600;">'
		.'Thema:'
		.'</td><td align="left" style="color:#'.FONTCOLOR.'; background-color:#'.BACKGROUNDCOLOR.'; border-bottom-style: solid; border-bottom-color: #'.BORDERCOLOR.'; border-bottom-width: 1px; border-left-style: solid; border-left-color: #'.BORDERCOLOR.'; border-left-width: 1px;">'
		.htmlentities($rs2['typ'])
		.'</td></tr><tr><td align="left" style="font-weight: 600;">'
		.'Druckjahr:'
		.'</td><td align="left" style="color:#'.FONTCOLOR.'; background-color:#'.BACKGROUNDCOLOR.'; border-bottom-style: solid; border-bottom-color: #'.BORDERCOLOR.'; border-bottom-width: 1px; border-left-style: solid; border-left-color: #'.BORDERCOLOR.'; border-left-width: 1px;">'
		.$rs['jahrgang']
		.'</td></tr><tr><td align="left" style="font-weight: 600;">'
		.'Preis:'
		.'</td><td align="left" style="color:#'.FONTCOLOR.'; background-color:#'.BACKGROUNDCOLOR.'; border-bottom-style: solid; border-bottom-color: #'.BORDERCOLOR.'; border-bottom-width: 1px; border-left-style: solid; border-left-color: #'.BORDERCOLOR.'; border-left-width: 1px;">'
		.'CHF '.htmlentities($rs['preis'])
		.'</td></tr><tr><td align="left" style="font-weight: 600;">'
		.'Seiten:'
		.'</td><td align="left" style="color:#'.FONTCOLOR.'; background-color:#'.BACKGROUNDCOLOR.'; border-bottom-style: solid; border-bottom-color: #'.BORDERCOLOR.'; border-bottom-width: 1px; border-left-style: solid; border-left-color: #'.BORDERCOLOR.'; border-left-width: 1px;">'
		.htmlentities($rs['seiten'])
		.'</td></tr><tr><td align="left" style="font-weight: 600;">'
		.'Besitzer:'
		.'</td><td align="left" style="color:#'.FONTCOLOR.'; background-color:#'.BACKGROUNDCOLOR.'; border-bottom-style: solid; border-bottom-color: #'.BORDERCOLOR.'; border-bottom-width: 1px; border-left-style: solid; border-left-color: #'.BORDERCOLOR.'; border-left-width: 1px;">'
		;

	/** besitzer auflisten */
	$sql = 'SELECT * from books_holder WHERE book_id = '.$rs['id'];
	$result3 = $db->query($sql, __FILE__, __LINE__, 'Besitzer auflisten');
	$alleBesitzer = '';
	while ($rs3 = $db->fetch($result3))
	{
		$alleBesitzer .= sprintf('<a href="?do=my&user=%d">%s</a>, ', $rs3['user_id'], $user->id2user($rs3['user_id'], 0));
	}
	$htmlOutput .= substr($alleBesitzer, 0, -2); // Entfernt das allerletzte Komma

	$htmlOutput .= '</td></tr></table>';

	$htmlOutput .= nl2br(htmlentities($rs['text']));

	/** Ists ein angemeldeter User? */
	if (true === $user->is_loggedin())
	{
		$sidebarHtml = '<h3>Boook Actions</h3>';

		/** Wer das Buch besitzt kanns loeschen, wer nicht kanns hinzufuegen */
		$sql = 'SELECT user_id FROM books_holder WHERE book_id = '.$rs['id'].' AND user_id = '.$user->id;
		if ($db->num($db->query($sql, __FILE__, __LINE__, 'Buchbesitzer')) == 1)
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

}
/** Buch bearbeiten */
elseif ($action === 'edit' && true === $user->is_loggedin())
{
	$model->showEdit($smarty, $book_id);
	$smarty->display('file:layout/head.tpl');

	/** besitzt der user das buch? */
	$sql = 'SELECT count(*) as anzahl FROM books_holder WHERE book_id = '.$book_id.' AND user_id = '.$user->id;
	$result = $db->query($sql, __FILE__, __LINE__);
	$rs = $db->fetch($result);

	if ($rs['anzahl'] != 1)
	{
		$smarty->assign('error', ['type' => 'warn', 'dismissable' => 'true', 'title' => 'genau nöd, tschalphorre']);
		$smarty->display('file:layout/elements/block_error.tpl');
		//exit;

	} else {
		$sql = 'SELECT * from books WHERE id = '.$book_id;
		$result = $db->query($sql, __FILE__, __LINE__);
		$rs = $db->fetch($result);

		echo "<form action='$_SERVER[PHP_SELF]' method='post' enctype='multipart/form-data'>"
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
		$result = $db->query($sql, __FILE__, __LINE__);

		while ($rs2 = $db->fetch($result))
		{
			if ($rs["titel_id"] == $rs2["id"])
			{
				echo '<option value="'.$rs2['id'].'" selected>'.$rs2['typ'].'</option>';
			} else {
				echo '<option value="'.$rs2['id'].'">'.$rs2['typ'].'</option>';
			}

			$sql = 'SELECT * FROM books_title WHERE parent_id = '.$rs2['id'];
			$result2 = $db->query($sql, __FILE__, __LINE__);

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
elseif ($action === 'add' && true === $user->is_loggedin())
{
	$model->showAddnew($smarty);
	$smarty->display('file:layout/head.tpl');

	echo '<h2>Add Boook</h2>'
		.'<form action="'.$_SERVER['PHP_SELF'].'" method="post" enctype="multipart/form-data">'
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
	$result = $db->query($sql, __FILE__, __LINE__);

	while($rs2 = $db->fetch($result))
	{
		echo '<option value="'.$rs2['id'].'">'.$rs2['typ'].'</option>';

		$sql = 'SELECT * FROM books_title WHERE parent_id = '.$rs2['id'];
		$result2 = $db->query($sql, __FILE__, __LINE__);

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

} elseif ($action === 'admin' && $user->typ == USER_MEMBER) {
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
elseif ($action === 'delete' && true === $user->is_loggedin())
{
	$sql = 'SELECT * FROM books where id = '.$book_id;
	$result = $db->query($sql, __FILE__, __LINE__);
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
elseif ($action === 'my' && isset($user_id))
{
	//$smarty->display('file:layout/head.tpl');
	//if ($smarty->get_template_vars('error') != null) $smarty->display('file:layout/elements/block_error.tpl');
	//if ($smarty->getTemplateVars('foo') != null) $smarty->display('file:layout/elements/block_error.tpl'); // Smarty 3.x

	$htmlOutput .= '<h2>Boooks von '.$user->id2user($user_id).'</h2>';

	$sql = 'SELECT DISTINCT titel_id 
			FROM books, books_holder 
			WHERE 
			books.id = books_holder.book_id AND 
			books_holder.user_id = '.$user_id;
	$result = $db->query($sql, __FILE__, __LINE__);
	while($rs = $db->fetch($result))
	{
		$htmlOutput .= '<h4>'.get_title($rs['titel_id']).'</h4>';
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
			$htmlOutput .= "<li>".get_title($rs2['parent_id'])."</li><ul>";
			$sql = 'SELECT * 
					FROM books, books_holder 
					WHERE 
					books.titel_id = '.$rs['titel_id'].' AND 
					books.parent_id = '.$rs2['parent_id'].' AND 
					books.id = books_holder.book_id AND 
					books_holder.user_id = '.$user_id;
			$result3 = $db->query($sql, __FILE__, __LINE__);
			while($rs3 = $db->fetch($result3))
			{
				$htmlOutput .= '<li><a href="?do=show&book_id='.$rs3['book_id'].'">'.$rs3['title'].'</a></li>';
			}
			$htmlOutput .= '</ul>';
		}

		$sql = 'SELECT * 
				FROM books, books_holder 
				WHERE 
				books.titel_id = '.$rs['titel_id'].' AND 
				books.parent_id = 0 AND 
				books.id = books_holder.book_id AND 
				books_holder.user_id = '.$user_id;
		$result4 = $db->query($sql, __FILE__, __LINE__);
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
