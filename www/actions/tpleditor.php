<?php
/**
 * File Includes
 */
require_once( __DIR__ .'/../includes/tpleditor.inc.php');

$error = "";
$state = "";

$frm = $_POST['frm'];

$access_error = "";
if (tpleditor_access_lock($_GET['tplupd'], $access_error)) {
	
	// check fields and put error msg. 
	if ($frm[read_rights]<0 || $frm[read_rights]>3) $error .= "Ungültiges Lese-Recht.<br />";
	if ($frm[write_rights]<1 || $frm[write_rights]>3) $error .= "Ungültiges Schreib-Recht.<br />";
	if ($frm[border]<0 || $frm[border]>2) $error .= "Ungültiger Rahmen-Typ.<br />";
	if (strlen(preg_replace("(\W*)", "", $frm[tpl])) <= 0) $error .= "Bitte keine leeren Seiten. <br />";
	
	if (strlen($frm[word]) > 30) $error .= "Word '$frm[word]' ist zu lang. Max. 30 Zeichen. <br/>";         
	if (preg_match("([^a-zA-Z0-9_-])", $frm[word])) {
		$error .= "Ungültige Zeichen im Word '$frm[word]'. Nur a-z, 0-9, _, - erlaubt. <br />";
		$frm[word] = "";
	}
	
	if (!smarty_brackets_ok($frm['tpl'], $brack_err)) $error .= $brack_err;
			         
	
	$frm[packages] = addslashes($frm[packages]);
	$frm[packages] = strip_tags($frm[packages]);
	$packs = preg_replace("(\s)", "", $frm[packages]);
	$packs = explode(";", $packs);
	$frm[packages] = "";
	foreach ($packs as $p) {
	   if ($p) {
	      if (!file_exists(package_path($p))) $error .= "Package <i>$p</i> existiert nicht. <br />";
	      $frm[packages] .= "$p; ";
	   }
	}
	
	/* 
	 * deaktiviert bis ein besserer syntax checker gebaut ist. 
	 * 
	$syntaxerr = html_syntax_check($frm[tpl]);
	if ($syntaxerr) $error .= "<br />HTML Syntax Error: $syntaxerr <br />";
	*/
	
	if (!$error) {         
	   $frm[tpl] = addslashes($frm[tpl]);
	   $frm[title] = addslashes($frm[title]);
	   $frm[title] = strip_tags($frm[title]);
	   $frm['page_title'] = htmlentities($frm['page_title'], ENT_NOQUOTES);
	   $frm['menus'] = htmlentities($frm['menus'], ENT_QUOTES);
	   
	   if (!$error && $frm[id] == "new") {
	      $frm[id] = $db->query("INSERT INTO templates (tpl, title, word, packages, border, owner, page_title,
	                              read_rights, write_rights, created, last_update, update_user) 
	                              VALUES ('$frm[tpl]', '$frm[title]', '$frm[word]', '$frm[packages]', '$frm[border]', '$user->id', 
	      								'$frm[page_title]', '$frm[read_rights]', '$frm[write_rights]', NOW(), NOW(), ".$user->id.")", __FILE__, __LINE__);
	      Thread::setRights('t', $frm[id], $frm['read_rights']);
	   	  $db->query("INSERT INTO templates_backup SELECT * FROM templates WHERE id='$frm[id]'", __FILE__, __LINE__);
	
	      $_GET['tplupd'] = $frm['id'];
	      $_GET['location'] = base64_encode("/?tpl=$frm[id]");
	      $smarty->assign("tplupdnew", 1);
	      $state = "Neue Seite wurde erstellt. ID: $frm[id].<br />";
	      
	      
	   }elseif (!$error) {
	   	if ($frm['word']) $set_word = ", word='$frm[word]'";
	      $db->query("UPDATE templates SET tpl='$frm[tpl]', title='$frm[title]', page_title='$frm[page_title]',
	                  read_rights='$frm[read_rights]', write_rights='$frm[write_rights]', 
	                  last_update=NOW(), update_user=".$user->id.", border='$frm[border]', 
	                  packages='$frm[packages]' $set_word, error='' WHERE id='$frm[id]'", __FILE__, __LINE__);
	      Thread::setRights('t', $frm[id], $frm['read_rights']);
	      $db->query("REPLACE INTO templates_backup SELECT * FROM templates WHERE id=$frm[id] AND unix_timestamp(now())-unix_timestamp(last_update) > (60*60*24*3)", __FILE__, __LINE__);
	   }
	}
	
	if (!$error) {
	   if (!$smarty->compile("tpl:$frm[id]", $compile_err)) {
	      for ($i=0; $i<sizeof($compile_err); $i++) {
	         $error .= "<br />".$compile_err[$i]."<br />";
	      }
	      $db->query("UPDATE templates SET error='".addslashes($error)."' WHERE id=$frm[id]", __FILE__, __LINE__);
	   }
	}
	
	
	if (!$error) {
		tpleditor_unlock($_GET['tplupd']);
		
		if (!$_GET['location']) $_GET['location'] = base64_encode("/?tpl=$_GET[tplupd]");
		
		unset($_GET['tplupd']);
		unset($_GET['tpleditor']);
		
		header('Location: '.base64_decode($_GET['location']));
		die();
		
	} else {
		$frm[tpl] = stripslashes(stripslashes($frm[tpl])); 
	    $frm[title] = stripslashes(stripslashes($frm[title])); 
	    $frm[packages] = stripslashes(stripslashes($frm[packages]));
	    // aus irgend einem grund ist das 2x nötig. sonst wird nur ein teil der slashes entfernt. wüsste gern wieso. (biko)
		
		$smarty->assign("tpleditor_error", $error);
		$frm['tpl'] = htmlentities($frm['tpl']);
	    $smarty->assign("tpleditor_frm", $frm);
	    $smarty->assign("tpleditor_state", $state);
   
		$smarty->display("file:layout/layout.tpl");
	}	   

} else {
	http_response_code(403); // Set response code 403 (access denied) and exit.
	user_error($access_error, E_USER_WARNING);
}
