<?
	global $db, $smarty;


	switch ($_GET[sort]) {
		case "username":
			$sort = "ORDER BY u.username";
			break;
		case "lastlogin":
			$sort = "ORDER BY u.currentlogin";
			break;
		case "ausgesperrt_bis":
			$sort = "ORDER BY u.ausgesperrt_bis";
			break;
		case "chnopf":
			$sort = "ORDER BY u.button_use";
			break;
		case "lostposts":
			$sort = "ORDER BY u.posts_lost";
			break;
		case "active":
			$sort = "ORDER BY u.active";
			break;
		case "zorger":
			$sort = "ORDER BY u.zorger";
			break;
		case "email":
			$sort = "ORDER BY email";
			break;
		case "unread":
			$sort = "ORDER BY unread";
			break;
		default:
			$sort = "ORDER BY u.currentlogin";
	}

	switch ($_GET[order]) {
		case 'ASC':
			$order = 'ASC';
			break;
		case 'DESC':
			$order = 'DESC';
			break;
		default:
			$order = 'DESC';
	}

	$e = $db->query("
					SELECT u.id, u.username, u.clan_tag, u.email, u.usertype, u.active, UNIX_TIMESTAMP(u.currentlogin) as currentlogin, UNIX_TIMESTAMP(u.ausgesperrt_bis) as ausgesperrt_bis, u.button_use, u.posts_lost, u.zorger, count(c.comment_id) as unread
					FROM user u
					LEFT JOIN comments_unread c
					ON u.id = c.user_id
					GROUP by u.id $sort $order
					", __FILE__, __LINE__);
	$list = array();
	while ($d = mysql_fetch_array($e)) {
		array_push($list, $d);
	}

	$smarty->assign("userlist", $list);
?>