<?php
/** Assign passed PHP CLI arguments to $_GET */
if (!empty($argv[1])) {
  parse_str($argv[1], $_GET);
}

require_once( __DIR__ .'/../www/includes/config.inc.php');
require_once( __DIR__ .'/../www/includes/stockbroker.inc.php');

foreach (Stockbroker::getStocksOldest() as $symbol) {
	Stockbroker::updateKurs($symbol);
}
