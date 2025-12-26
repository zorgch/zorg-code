<?php
/**
 * zorg File Manager functions
 * @package zorg\Filemanager
 */
global $smarty, $db, $user;

$error = '';
$state = '';

if ($user->is_loggedin())
{
	define('MAX_DISCSPACE', 10485760 * 5); // 50 MB
	define('USERPATH', FILES_DIR.$user->id.'/');

	$sortby = filter_input(INPUT_GET, 'sort', FILTER_SANITIZE_SPECIAL_CHARS) ?? null; // $_GET['sort']
	switch ($sortby) {
		case "datum":
			$sort = 'datum';
			break;
		case "size":
			$sort = 'size';
			break;
		default:
			$sort = 'upload_date';
			break;
	}

	$showform = filter_input(INPUT_POST, 'formid', FILTER_SANITIZE_SPECIAL_CHARS) ?? null; // $_POST['formid']
	if ($showform === "filemanager") {
		if (!@file_exists(USERPATH)) @mkdir(USERPATH, 0775);

		if (is_uploaded_file($_FILES['file']['tmp_name'])) {
			$e = $db->query('SELECT SUM(size) size FROM files WHERE user=? LIMIT 1', __FILE__, __LINE__, 'SELECT FROM files', [$user->id]);
			if ($e !== false && $db->num($e) > 0)
			{
				$d = $db->fetch($e);
				if ($d['size'] + $_FILES['file']['size'] <= MAX_DISCSPACE) {
					if (file_exists(USERPATH.$_FILES['file']['name']))  {
						$index = 0;
						do {
							$index++;
							$filename = file_name($_FILES['file']['name']) . " ($index)" . file_ext($_FILES['file']['name']);
						} while (file_exists(USERPATH.$filename));
					}else{
						$filename = filter_var(basename($_FILES['file']['name']), FILTER_DEFAULT);
					}
					if (@move_uploaded_file($_FILES['file']['tmp_name'], USERPATH.$filename)) {
						chmod(USERPATH.$filename, 0664);
						$db->query('INSERT INTO files (user, upload_date, name, size, mime)
									VALUES (?, ?, ?, ?, ?)', __FILE__, __LINE__, 'INSERT INTO files'
									,[$user->id, timestamp(true), $filename, $_FILES['file']['size'], $_FILES['file']['type']]);
						$state = "File hinzugefügt. <br />";
					}else{
						$error = "Datei-Indizierung fehlgeschlagen. <br />";
					}
				}else{
					$error = "Maximale Disc Quota überschritten. <br />";
				}
			}
		}else{
			$error = "Datei-Upload fehlgeschlagen. <br />";
		}
	}

	$deleteFileID = filter_input(INPUT_GET, 'fm_del', FILTER_VALIDATE_INT) ?? null; // $_GET['id']
	if (!empty($deleteFileID))
	{
		$e = $db->query('SELECT * FROM files WHERE id=? AND user=?', __FILE__, __LINE__, 'SELECT fm_del', [$deleteFileID, $user->id]);
		$d = $db->fetch($e);
		if ($d) {
			if (file_exists(USERPATH.$d['name'])) {
				if (unlink(USERPATH.$d['name'])) {
					$db->query('DELETE FROM files WHERE id=? AND user=?', __FILE__, __LINE__, 'DELETE FROM files', [$deleteFileID, $user->id]);
					$state = "File '".$d['name']."' gelöscht.<br />";
				}else{
					$error = "File '".$d['name']."' konnte nicht gelöscht werden. <br />";
				}
			}else{
				$error = "File '".$d['name']."' existiert nicht. <br />";
			}
		}else{
			$error = "File existiert nicht oder gehört einem anderen User. <br />";
		}
	}

	$e = $db->query('SELECT SUM(size) quota FROM files WHERE user=?', __FILE__, __LINE__, 'SELECT quota', [$user->id]);
	$d = $db->fetch($e);
	$quota = $d['quota'];

	$e = $db->query('SELECT *, UNIX_TIMESTAMP(upload_date) upload_date FROM files WHERE user=? ORDER BY ? DESC', __FILE__, __LINE__, 'SELECT Userfiles', [$user->id, $sort]);
	$files = array();
	while ($d = $db->fetch($e)) {
		$d['delparam'] = "fm_del=$d[id]";
		array_push($files, $d);
	}

	$smarty->assign("fm_quota", $quota);
	$smarty->assign("fm_maxquota", MAX_DISCSPACE);
	$smarty->assign("files", $files);
}
else {
	$error = 'Du musst eingeloggt sein um den File Manager zu benutzen<br />';
}
$smarty->assign("fm_error", $error);
$smarty->assign("fm_state", $state);

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
