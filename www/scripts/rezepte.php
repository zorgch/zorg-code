<?php
/**
 * zorg Rezepte Datenbank
 * @package zorg\Rezepte
 */
global $db, $user, $smarty;

$f = $db->query('SELECT * FROM rezepte ORDER by title ASC', __FILE__, __LINE__, 'SELECT FROM rezepte');

$list = array();
while ($f = $db->fetch($g))
{
	array_push($list, $g);
}

$smarty->assign('rezepte', $list);
