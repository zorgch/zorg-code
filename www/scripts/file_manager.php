<?php
/**
 * zorg File Manager functions
 * @package zorg\Filemanager
 */
global $smarty, $db, $user;

define("MAX_DISCSPACE", 10485760 * 5);   // 50 MB
define("USERPATH", $_SERVER['DOCUMENT_ROOT'].'/../data/files/'.$user->id.'/');

$error = "";
$state = "";

switch ($_GET[sort]) {
	case "datum":
			$sort = "ORDER BY upload_date DESC";
			break;
		case "size":
			$sort = "ORDER BY size DESC";
			break;
		default:
			$sort = "ORDER BY upload_date DESC"; break;
	}


if ($_POST['formid'] == "filemanager") {
	if (!@file_exists(USERPATH)) @mkdir(USERPATH, 0775);

	if (is_uploaded_file($_FILES['file']['tmp_name'])) {
		$e = $db->query("SELECT sum(size) size FROM files WHERE user=$user->id", __FILE__, __LINE__);
		$d = $db->fetch($e);
		if ($d['size'] + $_FILES['file']['size'] <= MAX_DISCSPACE) {
			if (file_exists(USERPATH.$_FILES['file']['name']))  {
				$index = 0;
				do {
					$index++;
					$filename = file_name($_FILES['file']['name']) . " ($index)" . file_ext($_FILES['file']['name']);
				} while (file_exists(USERPATH.$filename));
			}else{
				$filename = $_FILES['file']['name'];
			}
			if (@move_uploaded_file($_FILES['file']['tmp_name'], USERPATH.$filename)) {
				chmod(USERPATH.$filename, 0664);
				$db->query("INSERT INTO files (user, upload_date, name, size, mime)
					VALUES ($user->id, NOW(), '$filename', '".$_FILES['file']['size']."', '".$_FILES['file']['type']."')", __FILE__, __LINE__);
				$state = "File hinzugefügt. <br />";
			}else{
				$error = "Datei-Indizierung fehlgeschlagen. <br />";
			}
		}else{
			$error = "Maximale Disc Quota überschritten. <br />";
		}
	}else{
		$error = "Datei-Upload fehlgeschlagen. <br />";
	}
}

if ($_GET['fm_del']) {
	$e = $db->query("SELECT * FROM files WHERE id='$_GET[fm_del]' AND user=$user->id", __FILE__, __LINE__);
	$d = $db->fetch($e);
	if ($d) {
		if (file_exists(USERPATH.$d['name'])) {
			if (unlink(USERPATH.$d['name'])) {
				$db->query("DELETE FROM files WHERE id=$_GET[fm_del] AND user=$user->id", __FILE__, __LINE__);
				$state = "File '$d[name]' gelöscht.<br />";
			}else{
				$error = "File '$d[name]' konnte nicht gelöscht werden. <br />";
			}
		}else{
			$error = "File '$d[name]' existiert nicht. <br />";
		}
	}else{
		$error = "File existiert nicht oder gehört einem anderen User. <br />";
	}
}

$e = $db->query("SELECT sum(size) quota FROM files WHERE user=$user->id", __FILE__, __LINE__);
$d = $db->fetch($e);
$quota = $d[quota];

$e = $db->query("SELECT *, UNIX_TIMESTAMP(upload_date) upload_date FROM files WHERE user=$user->id $sort", __FILE__, __LINE__);
$files = array();
while ($d = $db->fetch($e)) {
	$d['delparam'] = "fm_del=$d[id]";
	array_push($files, $d);
}

$smarty->assign("fm_error", $error);
$smarty->assign("fm_state", $state);
$smarty->assign("fm_quota", $quota);
$smarty->assign("fm_maxquota", MAX_DISCSPACE);
$smarty->assign("files", $files);


function file_name ($file) {
	for ($i=strlen($file)-1; $i>=0; $i--) {
		if ($file[$i] == ".") break;
	}
	if ($i == 0) return $file;
	else return substr($file, 0, $i);
}

function file_ext ($file) {
	for ($i=strlen($file)-1; $i>=0; $i--) {
		if ($file[$i] == ".") break;
	}
	if ($i == 0) return "";
	else return substr($file, $i);
}
