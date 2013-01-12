<?PHP

/*
die war isch alt

include_once($_SERVER['DOCUMENT_ROOT'].'/includes/mysql.inc.php');


/**
 * @return unknown
 * @param $id unknown
 * @param $activeid unknown
 * @desc gibt das HTML einer Menuzeile zurück
 
 
function menu($id, $activeid, $hier) {
	
	global $db, $numhiers, $user;
	
	$sql = "select * from menu where parent_id=".$id." order by name asc";
	$result = $db->query($sql, __FILE__, __LINE__);
	$html .= '<div align="center" class="tabs'.$numhiers.$hier.'">';
	while($rs = $db->fetch($result)) {
		if(
			$rs['status'] == USER_ALLE // alle
			|| $rs['status'] == USER_NICHTEINGELOGGT && $_SESSION['user_id'] == ''
			|| $rs['status'] == USER_EINGELOGGT && $_SESSION['user_id'] != ''
			|| $rs['status'] == USER_MEMBER && $user->member
		) {
			
			if($rs['id'] == $activeid) { // aktiver Menutab?
				$html .= '<a class="selected" href="'.$rs['url'].'">'.$rs['name'].'</a>';			
			} else { // nicht aktiv
				$html .= '<a href="'.$rs['url'].'">'.$rs['name'].'</a>';
			}
		}
	}
	$html .= '</div>'."\n";
	return $html;
}


/**
 * @return unknown
 * @param $id int
 * @param $activeid int
 * @param $i int
 * @desc Enter description here...
 
function menu_recur($id, $activeid, $i) {
	
	global $db, $numhiers;
	
	$numhiers = max(getNumHierarchies($id), $numhiers);
		
	if($id > 0 && getSubmenuid($id) > 0) {
		$i++;
				
		$html =
			menu_recur(getmenu_parentid($id), $id, $i)
			.menu($id, $activeid, $i)
			.$html
		;		
	} else if(getmenu_parentid($id) > 0) {
		$html = menu_recur(getmenu_parentid($id), $id,  $i);
	}

	return $html;
}

function getmenu_parentid($id) {
	global $db;
  $sql = "select * from menu where id = ".$id;
  $result = $db->query($sql, __FILE__, __LINE__);
  $rs = $db->fetch($result);
  
  return ($rs['parent_id'] > 0) ? $rs['parent_id'] : -1;
}


function getSubmenuid($menu_id) {
	global $db;
  $sql = "SELECT * FROM menu where id = ".$menu_id;
  $result = $db->query($sql, __FILE__, __LINE__);
  $rs = $db->fetch($result);
  return $rs['submenu_id'];
}

function getNumHierarchies($menu_id, $i=0) {
	global $db;
	$sql = "SELECT * FROM menu where id = ".$menu_id;
  $result = $db->query($sql, __FILE__, __LINE__);
  $rs = $db->fetch($result);
  $i++;
	if($rs['parent_id'] > 1) {
		return getNumHierarchies($rs['parent_id'], $i);
	} else {
		return $i;
	}
}

*/
?>