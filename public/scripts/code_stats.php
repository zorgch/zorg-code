<?php
/**
 * zorg Code Stats
 *
 * @package zorg\Code
 */
global $smarty, $user, $FORBIDDEN_DIRS;

$FORBIDDEN_DIRS = ['.', '..', 'smartylib', 'migration', 'vendor', basename(FILES_DIR), basename(GALLERY_DIR), basename(PHP_IMAGES_DIR)];


// code stats
$rootdir = APP_ROOT;
$stats = code_stats($rootdir);
$stats['avg_size'] = round($stats['size'] / $stats['files']);
$stats['avg_lines'] = round($stats['lines'] / $stats['files']);
$smarty->assign("code_stats", $stats);

function code_stats ($dir)
{
	global $FORBIDDEN_DIRS;

	$ret = array('files'=>0, 'size'=>0, 'lines'=>0);

	$hdir = opendir($dir);
	while (false !== ($file = readdir($hdir))) {
		if (!in_array($file, $FORBIDDEN_DIRS)) {
			if (is_dir($dir.'/'.$file)) {
				$t = code_stats($dir.'/'.$file);
				foreach ($t as $key=>$val) $ret[$key] += $val;
			}elseif (is_file($dir.'/'.$file) && substr($file, -4) == '.php') {
				$ret['files']++;
				$ret['size'] += filesize($dir.'/'.$file);
				$files = file($dir.'/'.$file);
				$ret['lines'] += (!$files ? 0 : sizeof($files));
			}
		}
	}
	closedir($hdir);

	return $ret;
}


// suche im code
if ($user->is_loggedin() && $user->typ >= USER_MEMBER)
{
	$form = strval(filter_input(INPUT_POST, 'formid', FILTER_SANITIZE_SPECIAL_CHARS) ?? 127);
	$search = filter_input(INPUT_POST, 'query', FILTER_SANITIZE_SPECIAL_CHARS) ?? null;
	if (isset($form) && !empty($search))
	{
		$smarty->assign("search_performed", 1);
		$smarty->assign("search_query", $search);
		$smarty->assign("search_results", search_code($search));
	}
}

function search_code ($query, $dir='')
{
	global $FORBIDDEN_DIRS;

	$ret = array();

	$hdir = opendir(APP_ROOT.'/'.$dir);
	while (false !== ($file = readdir($hdir))) {
		if (!in_array($file, $FORBIDDEN_DIRS)) {
			if (is_dir(APP_ROOT.'/'.$dir.'/'.$file)) {
				$ret = array_merge(search_code($query, $dir.'/'.$file), $ret);

			}elseif (is_file(APP_ROOT.'/'.$dir.'/'.$file) && substr($file, -4) == '.php') {
				$lines = file(APP_ROOT.'/'.$dir.'/'.$file);
				for ($i=0; $i<sizeof($lines); $i++) {
					if (stristr($lines[$i], $query)) {
						if (!isset($ret[$dir.'/'.$file])) $ret[$dir.'/'.$file] = array();
						$ret[$dir.'/'.$file][] = array('line'=>$i+1, 'text'=>$lines[$i]);
					}
				}
			}
		}
	}

	closedir($hdir);

	return $ret;
}
