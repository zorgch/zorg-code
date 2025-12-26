<?php
/**
 * Show & sort Userprofile list
 *
 * @package zorg\Usersystem
 */

global $db, $smarty;

/** Validate passed Parameters */
$sortby = filter_input(INPUT_GET, 'sort', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? null; // $_GET['sort']
$orderby = filter_input(INPUT_GET, 'order', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? null; // $_GET['order']

switch ($sortby)
{
	case 'username': $sort = 'u.username'; break;
	case 'lastlogin': $sort = 'u.currentlogin'; break;
	case 'ausgesperrt_bis': $sort = 'u.ausgesperrt_bis'; break;
	case 'chnopf': $sort = 'u.button_use'; break;
	case 'lostposts': $sort = 'u.posts_lost'; break;
	case 'active': $sort = 'u.active'; break;
	case 'zorger': $sort = 'u.zorger'; break;
	case 'email': $sort = 'email'; break;
	case 'unread': $sort = 'unread'; break;
	default: $sort = 'u.currentlogin';
}

switch ($orderby)
{
	case 'ASC': $order = 'ASC'; break;
	case 'DESC': $order = 'DESC'; break;
	default: $order = 'DESC';
}

$list = array();
$sql = 'SELECT
			u.id,
			u.username,
			u.clan_tag,
			u.email,
			u.usertype,
			u.active,
			UNIX_TIMESTAMP(u.currentlogin) as currentlogin,
			UNIX_TIMESTAMP(u.ausgesperrt_bis) as ausgesperrt_bis,
			u.button_use,
			u.posts_lost,
			u.zorger,
			(SELECT count(*) FROM comments_unread WHERE user_id = u.id) as unread
		FROM user u
		WHERE u.active = 1
		GROUP by u.id
		ORDER BY ' . $sort . ' ' . $order;
$e = $db->query($sql, __FILE__, __LINE__, 'userlist.php');

while ($d = $db->fetch($e))
{
	$list[] = $d;
}

$smarty->assign('userlist', $list);
