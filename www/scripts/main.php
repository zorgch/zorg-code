<?

	global $smarty, $db, $user, $_TPLROOT;
	
	// assign's für top-site
	if ($_GET['word']) $where = "word='$_GET[word]'";
	else $where = "id='$_GET[tpl]'";
	
	$e = $db->query("SELECT id, packages, title, word, LENGTH(tpl) size, owner, update_user, page_title,
  							UNIX_TIMESTAMP(last_update) last_update, UNIX_TIMESTAMP(created) created, read_rights, 
  							write_rights, force_compile, border FROM templates WHERE $where", __FILE__, __LINE__);
  
	
	$d = $db->fetch($e);  	
  	
  	if ($_GET['word']) $_GET['tpl'] = $d['id'];		
	
  	$smarty->assign("page_title", $d['page_title']);
  	$smarty->assign("tplroot", $d);
  	$_TPLROOT = $d;
	
?>