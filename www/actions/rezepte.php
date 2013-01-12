<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/main.inc.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/mysql.inc.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/usersystem.inc.php');

if($_POST['action'] == 'new') {
  $sql =
  	"
  	INSERT INTO
  	rezepte
  		(category_id, title, zutaten, anz_personen, prep_time, cook_time, difficulty, description, ersteller_id, erstellt_date)
  	VALUES
  		(
	  		'".$_POST[category]."'
	  		, '".$_POST[title]."'
	  		, '".$_POST[zutaten]."'
	  		, '".$_POST[personen]."'
  			, '".$_POST[preparation]."'
  			, '".$_POST[cookingtime]."'
  			, '".$_POST[difficulty]."'
  			, '".$_POST[description]."'
  			, ".$user->id."
  			, now()
  		)
  	"
  ;
  $db->query($sql, __FILE__, __LINE__);
  header('Location: '.base64_decode($_POST['url']).'&rezept_id='.mysql_insert_id());
  exit;
}

else if($_POST['action'] == 'edit' ) {
	$sql =
	 "
		UPDATE `rezepte`
	 	SET
			category_id = '".$_POST['category']."'
			, title = '".$_POST['title']."'
			, zutaten = '".$_POST['zutaten']."'
			, anz_personen = '".$_POST['personen']."'
			, prep_time = '".$_POST['preparation']."'
	 		, cook_time = '".$_POST['cookingtime']."'
	 		, difficulty = ".$_POST['difficulty']."
	 		, description = '".$_POST['description']."'
		WHERE id = ".$_POST['id']."
	"
	;
	$db->query($sql, __FILE__, __LINE__);
	header('Location: '.base64_decode($_POST['url']).'&rezept_id='.$_POST['id']);
	exit;
}

elseif($_POST['action'] == 'newcategory') {
  $sql =
  	"
  	INSERT INTO
  	rezepte_categories
  	SET title = '".$_POST[new_category]."'
  	"
  ;
  $db->query($sql, __FILE__, __LINE__);
  header('Location: '.base64_decode($_POST['url']));
  exit;
}

elseif($_POST['action'] == 'benoten' && $_POST['score'] != '') {

	$sql =
	"REPLACE INTO rezepte_votes (rezept_id, user_id, score) "
  	." VALUES ("
  	.$_POST['rezept_id']
  	.', '.$user->id
  	.', '.$_POST['score']
  	.")"
  	;

  	$db->query($sql, __FILE__, __LINE__);
	header("Location: ".base64_decode($_POST['url']));
}
?>