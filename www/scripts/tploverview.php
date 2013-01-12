<?
   global $db, $smarty;

   
   switch ($_GET[sort]) {
	case "tpl":
		$sort = "ORDER BY id";
		break;
	case "titel":
		$sort = "ORDER BY title";
		break;
	case "word":
		$sort = "ORDER BY word";
		break;
	case "owner":
		$sort = "ORDER BY owner";
		break;
	case "update":
	default:
		$sort = "ORDER BY last_update"; break;
   }
   
   $order = $_GET['order'] == "ASC" ? "ASC" : "DESC";
   
   
   $e = $db->query("SELECT id, title, word, owner, LENGTH(tpl) size, UNIX_TIMESTAMP(last_update) updated, update_user FROM templates WHERE del='0' $sort $order", __FILE__, __LINE__);
   $list = array();
   $totalsize = 0;
   while ($d = mysql_fetch_array($e)) {
      $totalsize += $d[size];
      array_push($list, $d);
   }
   $anz = sizeof($list);
   
   $smarty->assign("tploverview", $list);
   $smarty->assign("notemplates", $anz);
   $smarty->assign("totalsize", $totalsize);
   $smarty->assign("avgsize", $totalsize/$anz);
   
?>