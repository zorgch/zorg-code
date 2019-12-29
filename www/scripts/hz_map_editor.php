<?php
/**
 * Hunting z Map-Editor
 * @package zorg\Games\Hz
 */

/** File includes */
require_once(__DIR__.'/../includes/hz_map.inc.php');

global $user, $db, $smarty;

define('MAPFILE', __DIR__.'/../../data/hz_maps/'.$user->id.'.txt');

if ($_POST['formid'] == "hz_map") {				
	if (is_uploaded_file($_FILES['map']['tmp_name'])) {
		if ($_FILES['map']['type'] != "text/plain") {
			$error = "Ungültiger Datei-Typ ".$_FILES['map']['type'].". Datei muss eine Text-Datei (text/plain) sein.";
		}else{
			if (@move_uploaded_file($_FILES['map']['tmp_name'], MAPFILE)) {
				chmod(MAPFILE, 0664);
				
				$error = show_map ();
			}else{
				$error = "Datei-Indizierung fehlgeschlagen. <br />";
			}
		}
	}else{
		$error = "Datei-Upload fehlgeschlagen. <br />";
	}
}

if ($_GET['station_checker']) $error = show_map();

if ($_GET['hz_map_entry']) {
	$error = save_map(MAPFILE);
	if ($error) show_map ();
	else $state = "Map gespeichert";
}

$smarty->assign("hz_map_state", $state);
$smarty->assign("hz_map_error", $error);


function show_map () {
	global $smarty;
	
	$map_config = "";
	$imgfile = create_map (MAPFILE, $map_config, $error, $img_map);
	$smarty->assign("hz_new_map", $imgfile);
	$smarty->assign("hz_map_config", $map_config);
	$smarty->assign("hz_img_map", "<map name='station_checker'>$img_map</map>");
	return $error;
}
