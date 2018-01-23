<?php
include_once( __DIR__ .'/usersystem.inc.php');

header("Content-Type: image/gif");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") ." GMT"); 
  
readfile( __DIR__ ."/../../data/hz_maps/$user->id.gif");
