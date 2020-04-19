<?php
/**
 * Stockbroker Hourly Cronjob
 * @package zorg\Games\Stockbroker
 */
/** Assign passed PHP CLI arguments to $_GET */
if (!empty($argv[1])) {
  parse_str($argv[1], $_GET);
}

require_once( dirname(__FILE__).'/../www/includes/config.inc.php');
require_once( INCLUDES_DIR.'stockbroker.inc.php');

foreach (Stockbroker::getStocksTraded() as $symbol) {
	Stockbroker::updateKurs($symbol);
}
