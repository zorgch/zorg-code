<?php
//=============================================================================
// includes
//=============================================================================
	require_once($_SERVER['DOCUMENT_ROOT'].'/includes/main.inc.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/includes/forum.inc.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/includes/layout.inc.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/includes/main.inc.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/includes/wiki.inc.php');

if($_POST['action'] == 'edit' && $_POST['word'] != '' && $_SESSION['user_id']) { 	 	
	
	if(Wiki::getThread_id($_POST['word'])) {
		$thread_id = Wiki::getThread_id($_POST['word']);
	} else {
		$thread_id = Wiki::getHighestThread_id()+1;
	}
	
  $sql = 
	  "INSERT into wiki(word, text, user_id, date, thread_id) values ('"
	  .$_POST['word']."','".$_POST['text']."',".$_SESSION['user_id'].", now(), ".$thread_id.")"
  ;
  $db->query($sql, __FILE__, __LINE__);
  
  // nur 10 letzten Änderungen behalten ---------------------------------------
  /*
  $sql = "select * from wiki where word='".$_POST[word]."'";
  $num = $db->num($db->query($sql, __FILE__, __LINE__));
  
  if($num > 10) {
  	$sql = "DELETE FROM wiki WHERE word='".$_POST[word]."' limit 10,".$num;
  	$db->query($sql, __FILE__, __LINE__);
  }
  */
  
  header("Location: ".base64_decode($_POST[url]));
}

if($_GET['word'] == '') {
	
		// insertform
		echo(
			head(32)
			.'<form action="'.$_SERVER['PHP_SELF'].'" method="post">'
			.'<input type="hidden" name="action" value="edit">'
			.'Word: <input name="word" type="text" class="text">'
			/*.'<br />
			Zugangsberechtigung: 	
			<input name="access" type="radio" value="1" checked>Alle
			<input name="access" type="radio" value="2">eingeloggte Users
			<input name="access" type="radio" value="3">Sch&ouml;ne
			<input name="access" type="radio" value="4">nur ich
			'*/
			.'<br /><br /><textarea cols="120" name="text" rows="30"></textarea>'
			.'<br /><input type="submit" value="inserten" class="button">'
			.'</form>'
			.foot(3)
		);
		
} else {
	
	
	
	if($_GET['mode'] == 'edit') {
		echo(
			head(32)
			// editform
			.'<form action="'.$_SERVER['PHP_SELF'].'" method="post">'
			.'<input type="hidden" name="action" value="edit">'
			.'<input type="hidden" name="url" value="'.$_GET['url'].'">'
			.'Word: <input name="word" type="text" value="'.$_GET['word'].'" readonly>'
			.'<br /><br /><textarea cols="120" name="text" rows="30">'
			.htmlentities(Wiki::getWord($_GET['word']))
			.'</textarea>'
			.'<br /><input type="submit" value="updaten">'
			.'</form>'
			.foot(3)
		);
	} else {
		// Content ausgeben
		echo(
			head(31)
			.Wiki::getContent($_GET['word'])
		);
		
		if($_GET[parent_id] == '') { // Das brauchts leider für das "Additional Posts" Zeug
    	$parent_id = Wiki::getThread_id($_GET['word']);
    } else {
    	$parent_id = $_GET[parent_id];
    }
		Forum::printCommentingSystem('w', Wiki::getThread_id($_GET['word']), $parent_id);
		
		echo foot();
	}
}
?>