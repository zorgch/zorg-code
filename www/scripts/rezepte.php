<?
	global $db, $user, $smarty;

	$f = $db->query("
		SELECT *
		FROM rezepte
		ORDER by title ASC
		", __FILE__, __LINE__);

	$list = array();
	while ($f = mysql_fetch_array($g)) {
		array_push($list, $g);
	}


	$smarty->assign("rezepte", $list);

?>