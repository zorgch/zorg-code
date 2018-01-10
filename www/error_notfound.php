<?php
http_response_code(404); // Set response code 404 (not found)

require_once($_SERVER['DOCUMENT_ROOT'].'/includes/main.inc.php');

$smarty->display("file:layout/layout.tpl");

?>