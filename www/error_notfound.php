<?php
require_once dirname(__FILE__).'/includes/main.inc.php';

http_response_code(404); // Set response code 404 (not found)
$smarty->display(SMARTY_404PAGE_TPL);
