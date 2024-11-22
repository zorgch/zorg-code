<?php
/**
 * Hunting Z Image Map output
 * @package zorg\HuntingZ
 */
require_once __DIR__.'/config.inc.php';
require_once INCLUDES_DIR.'usersystem.inc.php';

header("Content-Type: image/gif");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") ." GMT");

readfile( HZ_MAPS_DIR.'/'.$user->id.'.gif');
