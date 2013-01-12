<?
	global $db, $smarty;

	switch ($_GET[sort]) {
		case "datum":
   			$sort = "ORDER BY upload_date DESC";
   			break;
   		case "user":
   			$sort = "ORDER BY user ASC";
   			break;
   		case "size":
   			$sort = "ORDER BY size DESC";
   			break;
   		default:
   			$sort = "ORDER BY upload_date DESC"; break;
   	}

	$e = $db->query("SELECT f.*, u.username, UNIX_TIMESTAMP(upload_date) upload_date FROM files f, user u WHERE f.user=u.id $sort", __FILE__, __LINE__);
	$files = array();
	while ($d = $db->fetch($e)) {
		array_push($files, $d);
	}

	$smarty->assign("files", $files);
?>