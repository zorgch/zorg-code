<?php
/**
 * zorg Code Stats
 * @package zorg\Code
 */
global $smarty, $user, $FORBIDDEN_DIRS;

$FORBIDDEN_DIRS = array('.', '..', 'smartylib', 'phpmyadmin', 'phpmyadmin.old', 'phpmyadmin_old');


// code stats
$rootdir = $_SERVER['DOCUMENT_ROOT'];
$stats = code_stats($rootdir);
$stats['avg_size'] = round($stats['size'] / $stats['files']);
$stats['avg_lines'] = round($stats['lines'] / $stats['files']);
$smarty->assign("code_stats", $stats);


function code_stats ($dir) {
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
				$ret['lines'] += sizeof(file($dir.'/'.$file));
			}
		}
	}
	closedir($hdir);
	
	return $ret;
}


// suche im code
if ($_POST['formid'] == 127 && $_POST['query'] && $user->typ == USER_MEMBER) {
	$smarty->assign("search_performed", 1);
	$smarty->assign("search_query", $_POST['query']);
	$smarty->assign("search_results", search_code($_POST['query']));
}
	
function search_code ($query, $dir='') {
	global $FORBIDDEN_DIRS;
	
	$ret = array();
	
	$hdir = opendir($rootdir.'/'.$dir);
	while (false !== ($file = readdir($hdir))) {
		if (!in_array($file, $FORBIDDEN_DIRS)) {
			if (is_dir($rootdir.'/'.$dir.'/'.$file)) {
				$ret = array_merge(search_code($query, $dir.'/'.$file), $ret);

			}elseif (is_file($rootdir.'/'.$dir.'/'.$file) && substr($file, -4) == '.php') {
				$lines = file($rootdir.'/'.$dir.'/'.$file);
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
