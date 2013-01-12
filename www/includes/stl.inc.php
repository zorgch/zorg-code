<?php

// Gibt die Anzahl offener Spiele als Link zum ersten Spiel zurueck
function getOpenSTLGames() {
	global $db, $user;
	//selektiert games bei denen ich mitmache
	/*if ($_SESSION['user_id']) {
		$sql = "
		SELECT stl.game_id AS game_id, HOUR ( pl.last_shoot) AS last_shoot, HOUR (now( )) AS akt
		FROM stl
		LEFT JOIN stl_players pl ON pl.game_id = stl.game_id
		LEFT JOIN stl_positions p ON stl.game_id = p.game_id
		WHERE pl.user_id = $user->id AND stl.status = 1 AND p.ship_user_id = $user->id AND p.hit_user_id =0";
		
		//$result = $db->query($sql,__FILE__,__LINE__);
		$count = 0;
		//while($rs = $db->fetch($result)) {
		//	if ($rs[akt] != $rs[last_shoot]) { $count++; $id = $rs[game_id]; }		
		//}
	} else {
		$count = 0;
	}
	return $count;
	
	if ($count != 0) return '<a href="stl.php?do=game&game_id='.$id.'">'.$count.' shots</a>';*/
}

function getOpenSTLLink () {
	global $db, $user;
	//selektiert games bei denen ich mitmache
	/*if ($_SESSION['user_id']) {
		$sql = "
		SELECT stl.game_id AS game_id, HOUR ( pl.last_shoot) AS last_shoot, HOUR (now( )) AS akt
		FROM stl
		LEFT JOIN stl_players pl ON pl.game_id = stl.game_id
		LEFT JOIN stl_positions p ON stl.game_id = p.game_id
		WHERE pl.user_id = $user->id AND stl.status = 1 AND p.ship_user_id = $user->id AND p.hit_user_id =0";
		
		$result = $db->query($sql,__FILE__,__LINE__);
		$count = 0;
		while($rs = $db->fetch($result)) {
			if ($rs[akt] != $rs[last_shoot]) { $count++; $id = $rs[game_id]; }		
		}
		
		return '/stl.php?do=game&game_id='.$id;
	} */
}

?>