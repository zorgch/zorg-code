<?php
http_response_code(404); // Set response code 404 (not found)

require_once dirname(__FILE__).'/includes/main.inc.php';

$smarty->display('file:layout/layout.tpl');
