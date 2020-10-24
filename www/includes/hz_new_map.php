<?php
/**
 * Hunting Z Image Map output
 * @package zorg\HuntingZ
 */
include_once dirname(__FILE__).'/usersystem.inc.php';

header("Content-Type: image/gif");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") ." GMT"); 
  
readfile( SITE_ROOT.'/../data/hz_maps/'.$user->id.'.gif');
