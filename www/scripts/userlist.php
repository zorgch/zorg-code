<?
global $db, $smarty;

switch ($_GET['sort']) {
	case 'username':
		$sort = 'ORDER BY u.username';
		break;
	case 'lastlogin':
		$sort = 'ORDER BY u.currentlogin';
		break;
	case 'ausgesperrt_bis':
		$sort = 'ORDER BY u.ausgesperrt_bis';
		break;
	case 'chnopf':
		$sort = 'ORDER BY u.button_use';
		break;
	case 'lostposts':
		$sort = 'ORDER BY u.posts_lost';
		break;
	case 'active':
		$sort = 'ORDER BY u.active';
		break;
	case 'zorger':
		$sort = 'ORDER BY u.zorger';
		break;
	case 'email':
		$sort = 'ORDER BY email';
		break;
	case 'unread':
		$sort = 'ORDER BY unread';
		break;
	default:
		$sort = 'ORDER BY u.currentlogin';
}

switch ($_GET['order']) {
	case 'ASC':
		$order = 'ASC';
		break;
	case 'DESC':
		$order = 'DESC';
		break;
	default:
		$order = 'DESC';
}

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
		GROUP by u.id ' . $sort . ' ' . $order;
$e = $db->query($sql, __FILE__, __LINE__, 'userlist.php');

while ($d = mysql_fetch_array($e)) {
	$list[] = $d;
}

$smarty->assign('userlist', $list);
