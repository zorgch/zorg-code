<?php
	
	// PHP >= 5.4 only: http_response_code(404);
	header('X-PHP-Response-Code: 404', true, 404); // PHP >= 4.3
	
	require_once($_SERVER['DOCUMENT_ROOT'].'/includes/main.inc.php');
	
	include($_SERVER['DOCUMENT_ROOT'].'/smarty.php');


?>
