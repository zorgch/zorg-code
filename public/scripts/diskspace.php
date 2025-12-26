<?php
global $smarty;

$root = explode("/", $_SERVER['DOCUMENT_ROOT']);
array_pop($root);
$root = implode("/", $root);
$root .= "/";

/*  execute; nur n test

  $cmd = "ls";
  echo "<p align=left>$cmd</p>";
  $out = array();
  exec($cmd, $out, $ret);
  echo "$ret <br>";
  foreach ($out as $data) {
     echo "<div align=left>$data<br></div>";
  }

*/

/*  php-funktionen
if (!$_GET[dir]) $path = $_GET[dir] = $root;
else $path = $_GET[dir];


$total = $files = filesize($path);
$dirs = dirsizes($path);
$total + array_sum($dirs);


$smarty->assign("path", $path);
$smarty->assign("size_total", $total);
$smarty->assign("size_dirs", $dirs);
$smarty->assign("size_files", $files);


function dirsizes ($path) {
  $ret = array();
  $dir = opendir($path);
  while (false !== ($f = readdir($dir))) {
     if (is_dir($f) && $f!="." && $f!="..") {
        $ret[$f] = disk_total_space($f);
     }
  }
  return $ret;
}


function filesize ($path) {
  $size = 0;      
  $dir = opendir($path);
  if (!$dir) return -1;
  
  while (false !== ($file = readdir($dir))) {
     if (is_file($file)) $size += filesize($file);
  }
  
  closedir($dir);
  return $size;
}*/
