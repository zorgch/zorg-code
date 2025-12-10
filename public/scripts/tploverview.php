<?php
global $db, $smarty;

$sort_by = filter_input(INPUT_GET, 'sort', FILTER_SANITIZE_SPECIAL_CHARS) ?? null;
$order_by = filter_input(INPUT_GET, 'order', FILTER_SANITIZE_SPECIAL_CHARS) ?? 'DESC';

switch ($sort_by)
{
	case 'tpl':
		$sort = 'id';
		break;
	case 'titel':
		$sort = 'title';
		break;
	case 'word':
		$sort = 'word';
		break;
	case 'owner':
		$sort = 'owner';
		break;
	default:
		$sort = 'last_update';
}

$sort_order = sprintf('ORDER BY %s %s', $sort, $order_by);
$e = $db->query('SELECT id, title, word, owner, LENGTH(tpl) size, UNIX_TIMESTAMP(last_update) updated, update_user, read_rights, write_rights FROM templates WHERE del="0" '.$sort_order, __FILE__, __LINE__, 'SELECT All Templates');
$list = [];
$totalsize = 0;
while ($d = $db->fetch($e)) {
	$totalsize += $d['size'];
	array_push($list, $d);
}
$anz = sizeof($list);

$smarty->assign('tploverview', $list);
$smarty->assign('notemplates', $anz);
$smarty->assign('totalsize', $totalsize);
$smarty->assign('avgsize', $totalsize/$anz);
