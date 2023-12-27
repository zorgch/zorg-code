<?php
global $db, $smarty;

$search = sanitize_userinput($_GET['query']);

if (!empty($search))
{
	$_GET['tpl'] = 33;

	$found = 0;
	try {
		$e = $db->query('SELECT id, title FROM templates WHERE MATCH (title, tpl) AGAINST (?)', __FILE__, __LINE__, 'SELECT FROM templates', [$search]);
		while ($d = $db->fetch($e))
		{
			$d['title'] = stripslashes($d['title']);
			$search_results[] = $d;
			$found++;
		}
	} catch(Exception $e) {
		trigger_error($e->getMessage(), E_USER_ERROR);
	}

	$smarty->assign("search_results", $search_results);
	$smarty->assign("search_found", $found);
	$smarty->assign("search_query", $search);
}
