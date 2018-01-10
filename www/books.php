<?php
//coded by [z]keep3r
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/main.inc.php');

//echo head(35, "books");
$smarty->assign('tplroot', array('page_title' => 'books'));
$smarty->display('file:layout/head.tpl');
echo menu('main');
echo menu('user');
echo menu('books');

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
function get_title($kat_id) {
	global $db;
	$sql = "SELECT typ FROM books_title WHERE id = $kat_id";
	$result = $db->query($sql, __FILE__, __LINE__);
	$rs = $db->fetch($result, __FILE__, __LINE__);

	return $rs[typ];
}

/***************/
/* DB Routinen */
/***************/

// Aenderung an Buch in DB speichern
if($_POST['do'] == "edit_now" && $user->id > 0) {

  	//besitzt der user das buch?
  	$sql = "SELECT count(*) as anzahl FROM books_holder WHERE book_id = $_POST[book_id] AND user_id = ".$user->id;
  	$result = $db->query($sql, __FILE__, __LINE__);
  	$rs = $db->fetch($result, __FILE__, __LINE__);

  	if ($rs[anzahl] != 1){
  		echo "au jetzt nonig, tschalphorre";
  		exit;
	}

	$sql = "SELECT * FROM books_title WHERE id = $_POST[titel_id]";
	$result = $db->query($sql, __FILE__, __LINE__);
	$rs = $db->fetch($result, __FILE__, __LINE__);

	if ($rs["parent_id"] != 0) {
  		$titel_id = $rs["parent_id"];
  		$parent_id = $rs["id"];
  	} else {
  		$titel_id = $rs["id"];
  		$parent_id = "";
	}

  $sql = "UPDATE books set title = '$_POST[title]',
  	     autor = '$_POST[autor]',
  	     verlag = '$_POST[verlag]',
	     isbn = '$_POST[isbn]',
	     titel_id = '$titel_id',
	     parent_id = 'parent_id',
	     jahrgang = '$_POST[jahrgang]',
	     preis = '$_POST[preis]',
	     seiten = '$_POST[seiten]',
	     text = '$_POST[text]'
		 WHERE id = '$_POST[book_id]'";
  $db->query($sql, __FILE__, __LINE__);
  echo ("Boook ge?ndert");
  $_GET['do'] = "show";
  $_GET['book_id'] = $_POST[book_id];

// Buch in DB hinzufuegen
} elseif($_POST['do'] == "add_now" && $user->id > 0){

	$sql = "SELECT * FROM books_title WHERE id = $_POST[titel_id]";
	$result = $db->query($sql, __FILE__, __LINE__);
	$rs = $db->fetch($result, __FILE__, __LINE__);

	if ($rs["parent_id"] != 0) {
  		$titel_id = $rs["parent_id"];
  		$parent_id = $rs["id"];
  	} else {
  		$titel_id = $rs["id"];
  		$parent_id = "";
	}

  $sql = "INSERT INTO books(
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

	     )VALUES(

	     '$_POST[title]',
	     '$_POST[autor]',
	     '$_POST[verlag]',
	     '$_POST[isbn]',
	     '$titel_id',
	     '$parent_id',
	     '$_POST[jahrgang]',
	     '$_POST[preis]',
	     '$_POST[seiten]',
	     '$_POST[text]',
	     '$user->id'
	     )";
  	$db->query($sql,__FILE__, __LINE__);

	// fetch last entry
	$sql = "SELECT * FROM books ORDER BY id DESC";
	$result = $db->query($sql, __FILE__, __LINE__);
	$rs = $db->fetch($result, __FILE__, __LINE__);
	echo $rs[id];

	$sql = "INSERT INTO books_holder(book_id, user_id )
			VALUES('$rs[id]','$user->id')";

	$db->query($sql,__FILE__, __LINE__);

  	echo ("Boook hinzugef?gt");
  	$_GET['do'] = "";

// Neue Kategorie in DB einf?gen
} elseif($_POST['do'] == "insert_titel" && $user->id > 0){
	//Hauptkategorie
	if ($_POST['parent_id'] == "new"){
	   $sql = "INSERT INTO books_title (typ) VALUES ('$_POST[titel]')";
 	   $db->query($sql,__FILE__, __LINE__);

 	//Unterkategorie
 	} elseif ($_POST['parent_id'] != ""){
	   $sql = "INSERT INTO books_title (parent_id, typ) VALUES ('$_POST[parent_id]','$_POST[titel]')";
 	   $db->query($sql,__FILE__, __LINE__);
 	}

  echo ("Kategorie ".$_POST[titel]." hinzugef?gt");
  $_GET['do'] = "";


// Besitzer in DB hinzufuegen
} elseif($_GET['do'] == "add_owner" && $user->id > 0) {

  // Testen ob bereits besitzer
  $sql = "SELECT user_id FROM books_holder where book_id = $_GET[book_id] AND user_id = ". $user->id;
  if ( $db->num($db->query($sql,__FILE__, __LINE__)) == 0 ) { 
  	  
  	  // Neuen Benutzer hinzufuegen	
	  $sql = "INSERT INTO books_holder (book_id, user_id) VALUES ('$_GET[book_id]','$user->id')";
	  $db->query($sql,__FILE__, __LINE__);
  }
  
  $_GET["do"] = "show";
  
// Besitzer in DB loeschen
} elseif($_GET['do'] == "delete_owner" && $user->id > 0) {

  $sql = "DELETE FROM books_holder WHERE book_id = '$_GET[book_id]' AND user_id = '$user->id'";
  $db->query($sql,__FILE__, __LINE__);

  $_GET["do"] = "show";
  
// Buch in DB loeschen
} elseif($_GET['do'] == "delete_now" && $user->id > 0){

	$sql = "SELECT * FROM books WHERE id = $_GET[book_id]";
	$result = $db->query($sql, __FILE__, __LINE__);
	$rs = $db->fetch($result, __FILE__, __LINE__);

	// darf user buch loeschen?
	if ($rs[ersteller] == $user->id){

		$sql = "SELECT count(*) as anzahl FROM books_holder WHERE book_id = $_GET[book_id]";
		$result2 = $db->query($sql, __FILE__, __LINE__);
		$rs2 = $db->fetch($result2, __FILE__, __LINE__);

		//wenn andere user dieses buch auch besitzen ist l?schen nicht erlaubt
		if ($rs2[anzahl] == 1){

			$sql = "DELETE FROM books WHERE id = $_GET[book_id]";
			$db->query($sql,__FILE__, __LINE__);
			echo "Boook gel?scht<br>";
		} else {
			echo "Sorry, dieses Boook besitzen auch noch andere Leute und kann darum nicht geloescht werden.<br>";
		}
		$_GET['do'] = "";
	}else{
		echo "ganz eifach, nei du schpehone!";
	}
}

/*********************/
/* Ausgabe Routinen */
/*******************/

//Buecherliste
if($_GET['do'] == "") {
	$sql = "SELECT * FROM books_title where parent_id = 0 ORDER BY typ ASC";
  	$result = $db->query($sql, __FILE__, __LINE__);
	echo "<table><tr><td align='left'>";
	while($rs = $db->fetch($result, __FILE__, __LINE__)) {
    	echo($rs[typ]."<br><ul>");

    	$sql = "SELECT * FROM books_title WHERE parent_id = ".$rs[id];
  		$result2 = $db->query($sql, __FILE__, __LINE__);

  		while ($rs2 = $db->fetch($result2, __FILE__, __LINE__)) {

			echo("<li>".$rs2[typ]."</li><ul>");

    		$sql = "SELECT * FROM books WHERE titel_id = $rs[id] AND parent_id = $rs2[id]";
  			$result3 = $db->query($sql, __FILE__, __LINE__);

  			while ($rs3 = $db->fetch($result3, __FILE__, __LINE__)) {
  				echo("<li><a href=\"$PHP_SELF?do=show&book_id=$rs3[id]\">$rs3[title]</a></li>");
  			}
  			echo("</ul><br>");
		}

		$sql = "SELECT * FROM books WHERE titel_id = $rs[id] AND parent_id = 0";
		$result3 = $db->query($sql, __FILE__, __LINE__);

		while ($rs3 = $db->fetch($result3, __FILE__, __LINE__)) {
  			echo("<li><a href=\"$PHP_SELF?do=show&book_id=$rs3[id]\">$rs3[title]</a></li>");
		}

		echo ("</ul>");
  	}
  	echo "</td></tr></table><br>";
  	

  	if($user->id > 0) { // Ists ein angemeldeter User?
  	
		// Eingabe Screen f?r neue Kategorie
		echo ("Neue Kategorie<br><br>"
	  	     ."<form action='$_SERVER[PHP_SELF]' method='post' enctype='multipart/form-data'>"
	  		 .'<input type="hidden" name="do" value="insert_titel">'
	         ."<table cellpadding=\"1\" cellspacing=\"1\" width=\"400\" class=\"border\" align=\"center\">"
	         ."<tr><td align=\"left\" style=\"font-weight: 600;\">"
	
		     ."Name"
		     ."</td><td align=\"left\" style=\"color:#".FONTCOLOR."; background-color:#".BACKGROUNDCOLOR.";border-bottom-style: solid; border-bottom-color: #".BORDERCOLOR."; border-bottom-width: 1px; border-left-style: solid; border-left-color: #".BORDERCOLOR."; border-left-width: 1px;\">"
		     ."<input class='text' size='60' type=\"text\" name=\"titel\">"
		     ."</td><tr>"
		     ."<td align=\"left\" style=\"font-weight: 600;\">"
	
		     ."Einf?gen als"
		     ."</td><td align=\"left\" style=\"color:#".FONTCOLOR."; background-color:#".BACKGROUNDCOLOR.";border-bottom-style: solid; border-bottom-color: #".BORDERCOLOR."; border-bottom-width: 1px; border-left-style: solid; border-left-color: #".BORDERCOLOR."; border-left-width: 1px;\">"
		     ."<select name=\"parent_id\">"
			 ."<option value=\"new\">Hauptkategorie</option>");
	
		$sql = "SELECT * FROM books_title WHERE parent_id = 0";
	  	$result = $db->query($sql, __FILE__, __LINE__);
	
		while($rs2 = $db->fetch($result, __FILE__, __LINE__)) {
			echo ("<option value=\"".$rs2["id"]."\">Unterpunkt von".$rs2["typ"]."</option>");
		}
	
		echo ("</select></td></tr></table>"
		     ."<input type='submit' class='button' name='send' value='speichern'>"
	    	 ."</form>");
	}

// Buch ansehen
} elseif($_GET['do'] == "show") {

  $sql = "SELECT * from books WHERE id = ".$_GET['book_id'];
  $rs = $db->fetch($db->query($sql, __FILE__, __LINE__));

  $sql = "SELECT * from books_title WHERE id = ".$rs["titel_id"];
  $rs2 = $db->fetch($db->query($sql, __FILE__, __LINE__));

  echo(
    "<table width=\"$mainwidth\"><tr><td align=\"left\" class=\"title\">"
    ."Boook Detail"
    ."</td></tr></table>"
    ."<br/>"
    ."<table cellpadding=\"1\" cellspacing=\"1\" width=\"500\" class=\"border\" align=\"center\">"
    ."<tr><td align=\"left\" style=\"font-weight: 600;\">"
    ."Titel:"
    ."</td><td align=\"left\" style=\"color:#".FONTCOLOR."; background-color:#".BACKGROUNDCOLOR.";border-bottom-style: solid; border-bottom-color: #".BORDERCOLOR."; border-bottom-width: 1px; border-left-style: solid; border-left-color: #".BORDERCOLOR."; border-left-width: 1px;\">"
    .htmlentities($rs["title"])
    ."</td></tr><tr><td align=\"left\" style=\"font-weight: 600;\">"
    ."Autor:"
    ."</td><td align=\"left\" style=\"color:#".FONTCOLOR."; background-color:#".BACKGROUNDCOLOR.";border-bottom-style: solid; border-bottom-color: #".BORDERCOLOR."; border-bottom-width: 1px; border-left-style: solid; border-left-color: #".BORDERCOLOR."; border-left-width: 1px;\">"
    .htmlentities($rs["autor"])
    ."</td></tr><tr><td align=\"left\" style=\"font-weight: 600;\">"
    ."Verlag:"
    ."</td><td align=\"left\" style=\"color:#".FONTCOLOR."; background-color:#".BACKGROUNDCOLOR.";border-bottom-style: solid; border-bottom-color: #".BORDERCOLOR."; border-bottom-width: 1px; border-left-style: solid; border-left-color: #".BORDERCOLOR."; border-left-width: 1px;\">"
    .htmlentities($rs["verlag"])
    ."</td></tr><tr><td align=\"left\" style=\"font-weight: 600;\">"
    ."ISBN:"
    ."</td><td align=\"left\" style=\"color:#".FONTCOLOR."; background-color:#".BACKGROUNDCOLOR.";border-bottom-style: solid; border-bottom-color: #".BORDERCOLOR."; border-bottom-width: 1px; border-left-style: solid; border-left-color: #".BORDERCOLOR."; border-left-width: 1px;\">"
    .htmlentities($rs["isbn"])
    ."</td></tr><tr><td align=\"left\" style=\"font-weight: 600;\">"
    ."Thema:"
    ."</td><td align=\"left\" style=\"color:#".FONTCOLOR."; background-color:#".BACKGROUNDCOLOR.";border-bottom-style: solid; border-bottom-color: #".BORDERCOLOR."; border-bottom-width: 1px; border-left-style: solid; border-left-color: #".BORDERCOLOR."; border-left-width: 1px;\">"
    .htmlentities($rs2["typ"])
    ."</td></tr><tr><td align=\"left\" style=\"font-weight: 600;\">"
    ."Druckjahr:"
    ."</td><td align=\"left\" style=\"color:#".FONTCOLOR."; background-color:#".BACKGROUNDCOLOR.";border-bottom-style: solid; border-bottom-color: #".BORDERCOLOR."; border-bottom-width: 1px; border-left-style: solid; border-left-color: #".BORDERCOLOR."; border-left-width: 1px;\">"
    .$rs["jahrgang"]
    ."</td></tr><tr><td align=\"left\" style=\"font-weight: 600;\">"
    ."Preis:"
    ."</td><td align=\"left\" style=\"color:#".FONTCOLOR."; background-color:#".BACKGROUNDCOLOR.";border-bottom-style: solid; border-bottom-color: #".BORDERCOLOR."; border-bottom-width: 1px; border-left-style: solid; border-left-color: #".BORDERCOLOR."; border-left-width: 1px;\">"
    .htmlentities($rs["preis"])." CHF"
    ."</td></tr><tr><td align=\"left\" style=\"font-weight: 600;\">"
    ."Seiten:"
    ."</td><td align=\"left\" style=\"color:#".FONTCOLOR."; background-color:#".BACKGROUNDCOLOR.";border-bottom-style: solid; border-bottom-color: #".BORDERCOLOR."; border-bottom-width: 1px; border-left-style: solid; border-left-color: #".BORDERCOLOR."; border-left-width: 1px;\">"
    .htmlentities($rs["seiten"])
    ."</td></tr><tr><td align=\"left\" style=\"font-weight: 600;\">"
    ."Besitzer:"
    ."</td><td align=\"left\" style=\"color:#".FONTCOLOR."; background-color:#".BACKGROUNDCOLOR.";border-bottom-style: solid; border-bottom-color: #".BORDERCOLOR."; border-bottom-width: 1px; border-left-style: solid; border-left-color: #".BORDERCOLOR."; border-left-width: 1px;\">"
    );

    //besitzer auflisten
    $sql = "SELECT * from books_holder WHERE book_id = $rs[id]";
    $result3 = $db->query($sql, __FILE__, __LINE__);
  	while ($rs3 = $db->fetch($result3)){
  		    echo $user->id2user($rs3["user_id"], 0).", ";
  	}

    echo "</td></tr></table><table><tr><td>";

	if($user->id > 0) { // Ists ein angemeldeter User?
		
		// Wer das Buch besitzt kanns loeschen, wer nicht kanns hinzufuegen
		$sql = "SELECT user_id FROM books_holder WHERE book_id = $rs[id] AND user_id = ".$user->id;
		if ($db->num($db->query($sql, __FILE__, __LINE__)) == 1){
			echo "<a href=\"$PHP_SELF?do=edit&book_id=$rs[id]\">[edit]</a>"
	  		."<a href=\"$PHP_SELF?do=delete_owner&book_id=$rs[id]\"> [delete book from my list]</a>";
		} else {
			echo "<a href=\"$PHP_SELF?do=add_owner&book_id=$rs[id]\"> [add book to my list]</a>";
		}
	}

	//nur ersteller kann loeschen, falls keine anderen besitzer vorhanden
	if($user->id == $rs[ersteller]) {
		echo "</td><td><a href=\"$PHP_SELF?do=delete&book_id=$rs[id]\"> [delete]</a>";
	}
	
	echo "</td></tr></table>";

	echo("<br><table><tr><td align=\"center\">"
		.nl2br(htmlentities($rs["text"])) );
	echo("</td></tr></table>");


// Buch bearbeiten
} elseif($_GET['do'] == "edit" && $user->id > 0) {


  //besitzt der user das buch?
  $sql = "SELECT count(*) as anzahl FROM books_holder WHERE book_id = $_GET[book_id] AND user_id = ".$user->id;
  $result = $db->query($sql, __FILE__, __LINE__);
  $rs = $db->fetch($result, __FILE__, __LINE__);

  if ($rs[anzahl] != 1){
  	echo "genau n?d, tschalphorre";
  	exit;
  }


  $sql = "SELECT * from books WHERE id = ".$_GET['book_id'];
  $result = $db->query($sql, __FILE__, __LINE__);
  $rs = $db->fetch($result, __FILE__, __LINE__);
  echo(
  	 "<form action='$_SERVER[PHP_SELF]' method='post' enctype='multipart/form-data'>"
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
    ."<input class='text' size='80' type=\"text\" name=\"jahrgang\" value=\"".$rs["jahrgang"]."\">"
    ."</td></tr><tr><td align=\"left\" style=\"font-weight: 600;\">"

    ."Preis:"
    ."</td><td align=\"left\" style=\"color:#".FONTCOLOR."; background-color:#".BACKGROUNDCOLOR.";border-bottom-style: solid; border-bottom-color: #".BORDERCOLOR."; border-bottom-width: 1px; border-left-style: solid; border-left-color: #".BORDERCOLOR."; border-left-width: 1px;\">"
    ."<input class='text' size='80' type=\"text\" name=\"preis\" value=\"".$rs["preis"]."\">"
    ."</td></tr><tr><td align=\"left\" style=\"font-weight: 600;\">"

    ."Seiten:"
    ."</td><td align=\"left\" style=\"color:#".FONTCOLOR."; background-color:#".BACKGROUNDCOLOR.";border-bottom-style: solid; border-bottom-color: #".BORDERCOLOR."; border-bottom-width: 1px; border-left-style: solid; border-left-color: #".BORDERCOLOR."; border-left-width: 1px;\">"
    ."<input class='text' size='80' type=\"text\" name=\"seiten\" value=\"".$rs["seiten"]."\">"
    ."</td></tr><tr><td align=\"left\" style=\"font-weight: 600;\">"

    ."Typ:"
    ."</td><td align=\"left\" style=\"color:#".FONTCOLOR."; background-color:#".BACKGROUNDCOLOR.";border-bottom-style: solid; border-bottom-color: #".BORDERCOLOR."; border-bottom-width: 1px; border-left-style: solid; border-left-color: #".BORDERCOLOR."; border-left-width: 1px;\">"
   	."<select name=\"titel_id\">");

	// Themen und Unterthemen in Listbox sortieren
	$sql = "SELECT * FROM books_title WHERE parent_id = 0";
  	$result = $db->query($sql, __FILE__, __LINE__);

	while($rs2 = $db->fetch($result, __FILE__, __LINE__)) {

		if ($rs["titel_id"] == $rs2["id"]){
			echo ("<option value=\"".$rs2["id"]."\"selected>".$rs2["typ"]."</option>");
		} else {
			echo ("<option value=\"".$rs2["id"]."\">".$rs2["typ"]."</option>");
		}

		$sql = "SELECT * FROM books_title WHERE parent_id = ".$rs2[id];
  		$result2 = $db->query($sql, __FILE__, __LINE__);

  		while ($rs3 = $db->fetch($result2, __FILE__, __LINE__)){

			if ($rs["titel_id"] == $rs3["id"]){
				echo ("<option value=\"".$rs3["id"]."\"selected> - ".$rs3["typ"]."</option>");
			} else {
				echo ("<option value=\"".$rs3["id"]."\"> - ".$rs3["typ"]."</option>");
			}
  		}
	}
	echo ("</select>"
	     ."</td></tr><tr><td align=\"left\" style=\"font-weight: 600;\">"

         ."Text:"
   		 ."</td><td align=\"left\" style=\"color:#".FONTCOLOR."; background-color:#".BACKGROUNDCOLOR.";border-bottom-style: solid; border-bottom-color: #".BORDERCOLOR."; border-bottom-width: 1px; border-left-style: solid; border-left-color: #".BORDERCOLOR."; border-left-width: 1px;\">"
         ."<textarea class='text' type=\"text\" name=\"text\" cols=\"80\" rows=\"10\">"
         .$rs["text"]
         ."</textarea>"
    	 ."</td></tr></table>"
    	 ."<input type='submit' class='button' name='send' value='speichern'>"
    	 ."</form>");

// Buch hinzufuegen
} elseif($_GET['do'] == "add" && $user->id > 0) {
   echo(
  	 "<form action='$_SERVER[PHP_SELF]' method='post' enctype='multipart/form-data'>"
  	.'<input type="hidden" name="do" value="add_now">'

    ."<table width=\"$mainwidth\"><tr><td align=\"left\" class=\"title\">"
    ."Add Boook"
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
    ."<input class='text' size='80' type=\"text\" name=\"jahrgang\">"
    ."</td></tr><tr><td align=\"left\" style=\"font-weight: 600;\">"

    ."Preis:"
    ."</td><td align=\"left\" style=\"color:#".FONTCOLOR."; background-color:#".BACKGROUNDCOLOR.";border-bottom-style: solid; border-bottom-color: #".BORDERCOLOR."; border-bottom-width: 1px; border-left-style: solid; border-left-color: #".BORDERCOLOR."; border-left-width: 1px;\">"
    ."<input class='text' size='80' type=\"text\" name=\"preis\">"
    ."</td></tr><tr><td align=\"left\" style=\"font-weight: 600;\">"

    ."Seiten:"
    ."</td><td align=\"left\" style=\"color:#".FONTCOLOR."; background-color:#".BACKGROUNDCOLOR.";border-bottom-style: solid; border-bottom-color: #".BORDERCOLOR."; border-bottom-width: 1px; border-left-style: solid; border-left-color: #".BORDERCOLOR."; border-left-width: 1px;\">"
    ."<input class='text' size='80' type=\"text\" name=\"seiten\">"
    ."</td></tr><tr><td align=\"left\" style=\"font-weight: 600;\">"

    ."Typ:"
    ."</td><td align=\"left\" style=\"color:#".FONTCOLOR."; background-color:#".BACKGROUNDCOLOR.";border-bottom-style: solid; border-bottom-color: #".BORDERCOLOR."; border-bottom-width: 1px; border-left-style: solid; border-left-color: #".BORDERCOLOR."; border-left-width: 1px;\">"
   	."<select name=\"titel_id\">");

	// Kategorien in Listbox sortieren
	$sql = "SELECT * FROM books_title WHERE parent_id = 0";
  	$result = $db->query($sql, __FILE__, __LINE__);

	while($rs2 = $db->fetch($result, __FILE__, __LINE__)) {

		echo ("<option value=\"".$rs2["id"]."\">".$rs2["typ"]."</option>");

		$sql = "SELECT * FROM books_title WHERE parent_id = ".$rs2[id];
  		$result2 = $db->query($sql, __FILE__, __LINE__);

  		while ($rs3 = $db->fetch($result2, __FILE__, __LINE__)){
			echo ("<option value=\"".$rs3["id"]."\"selected> - ".$rs3["typ"]."</option>");
  		}
	}

	echo ("</select>"
	     ."</td></tr><tr><td align=\"left\" style=\"font-weight: 600;\">"

         ."Text:"
   		 ."</td><td align=\"left\" style=\"color:#".FONTCOLOR."; background-color:#".BACKGROUNDCOLOR.";border-bottom-style: solid; border-bottom-color: #".BORDERCOLOR."; border-bottom-width: 1px; border-left-style: solid; border-left-color: #".BORDERCOLOR."; border-left-width: 1px;\">"
         ."<textarea class='text' type=\"text\" name=\"text\" cols=\"80\" rows=\"10\">"
         ."</textarea>"
    	 ."</td></tr></table>"
    	 ."<input type='submit' class='button' name='send' value='speichern'>"
    	 ."</form>");

} elseif($_GET['do'] == "admin" && $user->typ == USER_MEMBER) {

/*
	$sql = "SELECT * FROM books_title WHERE parent_id = 0 ORDER BY typ ASC";
  	$result = $db->query($sql);
	echo "<table><tr><td align='left'>";
	while($rs = $db->fetch($result)) {
    	echo($rs[typ]."<br><ul>");

    	$sql = "SELECT * FROM books_title WHERE parent_id = ".$rs[id];
  		$result2 = $db->query($sql);

  		while ($rs2 = $db->fetch($result2)) {
  			echo("<li><a href=\"$PHP_SELF?do=show&book_id=$rs2[id]\">$rs2[typ]</a></li>");
  		}
  		echo("</ul>");
  	}
  	echo "</td></tr></table>";*/

// Buch wirklich l?schen?
} elseif($_GET['do'] == "delete" && $user->id > 0) {
	$sql = "SELECT * FROM books where id = $_GET[book_id]";
	$result = $db->query($sql, __FILE__, __LINE__);
	$rs = $db->fetch($result, __FILE__, __LINE__);

	echo ("Willst du das Buch \"$rs[title]\" wirklich l?schen?<br>"
	     ."<a href=$PHP_SELF?do=delete_now&book_id=$rs[id]>ja</a>"
	     ." / "
	     ."<a href=$PHP_SELF?do=show&book_id=$rs[id]>nein</a>");

// B?cherliste von bestimmten User ausgeben
} elseif($_GET['do'] == "my") {

	if ($_GET['user'] != ""){
		$usr = $_GET['user'];
	} else {
		$usr = $user->id;
	}

	echo "Boooks von ".$user->id2user($usr);
	echo "<br><br><table><tr><td align='left'>";

  	$sql = "SELECT DISTINCT titel_id
			FROM books, books_holder
			WHERE
			books.id = books_holder.book_id AND
			books_holder.user_id = '".$usr."'";
			
  	$result = $db->query($sql, __FILE__, __LINE__);
	while($rs = $db->fetch($result, __FILE__, __LINE__)) {
		echo get_title($rs[titel_id])."<br><ul>";
		$sql = "SELECT DISTINCT parent_id
				FROM books, books_holder
				WHERE
				books.parent_id <> 0 AND
				books.titel_id = $rs[titel_id] AND
				books.id = books_holder.book_id AND
				books_holder.user_id = $usr";
  		$result2 = $db->query($sql, __FILE__, __LINE__);
		while($rs2 = $db->fetch($result2, __FILE__, __LINE__)) {
			echo "<li>".get_title($rs2[parent_id])."</li><ul>";
			$sql = "SELECT *
					FROM books, books_holder
					WHERE
					books.titel_id = $rs[titel_id] AND
					books.parent_id = $rs2[parent_id] AND
					books.id = books_holder.book_id AND
					books_holder.user_id = $usr";
			$result3 = $db->query($sql, __FILE__, __LINE__);
			while($rs3 = $db->fetch($result3, __FILE__, __LINE__)) {
				echo "<li>$rs3[title]</li>";
			}
			echo "</ul>";

		}

		$sql = "SELECT *
			   FROM books, books_holder
			   WHERE
			   books.titel_id = $rs[titel_id] AND
			   books.parent_id = 0 AND
			   books.id = books_holder.book_id AND
			   books_holder.user_id = $usr";
		$result4 = $db->query($sql, __FILE__, __LINE__);
		while($rs4 = $db->fetch($result4, __FILE__, __LINE__)) {
			echo "<li>$rs4[title]</li>";
		}
		echo "</ul>";
	}
	echo "</td></tr></table>";

}

//echo foot(52);
$smarty->display('file:layout/footer.tpl');
?>
