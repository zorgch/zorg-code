<?
	global $smarty, $db;
	
	// template force compile action
	if ($_GET[force_compile]) {
		$db->query("UPDATE templates SET force_compile='1'", __FILE__, __LINE__);
		$smarty->assign("admin_state", "Force Recompile executed");
	}
	
	
	/**
	 * Zorg Code Doku neu generieren
	 * 
	 * Ruft das Shell-Script auf, welches den Zorg Code neu parst<br><br>
	 * <b>Update: Die Doku sollte NUR via SHELL generiert werden:</b>
	 * cd /zooomclan/phpdocumentor
	 * ./phpdocu -c zorgcode.ini
	 * 
	 * @author IneX
	 * @date 27.05.2009
	 *
	 * @deprecated
	 */
	//if ($_GET[doku_generieren]) (shell_exec('/phpdocumentor/scripts/zorgcode_parse.sh')) ? $smarty->assign("admin_state", "Zorg Code Doku neu generiert") : $smarty->assign("admin_state", "FEHLER!");
	
	
	// comment force compile infos
	$path = $_SERVER['DOCUMENT_ROOT']."/smartylib/templates_c/";
	$handle = opendir($path);

	$comments = 0;
	$comments_size = 0;
	$tpls = 0;
	$tpls_size = 0;
	while (false !== ($file = readdir ($handle))) {
		if (strstr($file, 'comments')) {		
			$comments++;
			$comments_size += filesize($path.$file);
		}else if (strstr($file, 'tpl') || strstr($file, 'word')) {
			$tpls++;
			$tpls_size += filesize($path.$file);
		}
	}
	closedir($handle);
	$smarty->assign("compiled_comments_found", $comments);
	$smarty->assign('compiled_comments_size', $comments_size);
	$smarty->assign('compiled_tpls_found', $tpls);
	$smarty->assign('compiled_tpls_size', $tpls_size);
	
	$e = $db->query(
		"SELECT unix_timestamp(last_seen) date, count(*) anz 
		FROM comments_threads 
		WHERE last_seen!='000-00-00' 
		GROUP BY last_seen 
		ORDER BY last_seen DESC",
		__FILE__, __LINE__
	);
	$last_seen = array();
	while ($d = $db->fetch($e)) {
		$last_seen[] = $d;
	}
	$smarty->assign('comments_last_seen', $last_seen);
	$smarty->assign('comments_last_seen_del', THREAD_TPL_TIMEOUT);
	
	
	// quota
	$e = $db->query("SELECT sum(f.size) quota, u.username FROM files f, user u WHERE u.id=f.user GROUP BY user ORDER BY quota DESC", __FILE__, __LINE__);
	$quota = array();
	while ($d = $db->fetch($e)) {
		array_push($quota, $d);
	}
	$smarty->assign("admin_quota", $quota);
?>