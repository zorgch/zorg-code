<?
   global $db, $smarty;

   
   switch ($_GET[sort]) {
   	case "tpl": 
   		$sort = "ORDER BY id ASC";
   		break;
   	case "titel":
   		$sort = "ORDER BY title ASC";
   		break;
   	case "update": 
   	default:
   		$sort = "ORDER BY last_update DESC"; break;
   }
   
   $e = $db->query("SELECT *, LENGTH(tpl) size, UNIX_TIMESTAMP(last_update) updated, FROM templates WHERE del='0' $sort", __FILE__, __LINE__);
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