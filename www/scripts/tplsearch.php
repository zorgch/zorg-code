<?
	global $db, $smarty;

		
	if ($_GET[query]) {
		$_GET[tpl] = 33;
		
		$_GET[query] = strip_tags($_GET[query]);
		$_GET[query] = addslashes($_GET[query]);
		
		$found = 0;
		$e = $db->query("SELECT id, title FROM templates WHERE MATCH (title, tpl) AGAINST ('$_GET[query]')", __FILE__, __LINE__);
		$search_results = array();
		while ($d = mysql_fetch_array($e)) {
			$d[title] = stripslashes($d[title]);
			array_push($search_results, $d);
			$found++;
		}
		
		$smarty->assign("search_results", $search_results);
		$smarty->assign("search_found", $found);
		$smarty->assign("search_query", stripslashes(stripslashes($_GET[query])));
	}
	
?>