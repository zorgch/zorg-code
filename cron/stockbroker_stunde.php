<?php
/**
 * Stockbroker Hourly Cronjob
 *
  * At every minute:
  *		$ crontab -e
  *		  * * * * * php -f ./week.php wwwroot=/path/to/public/www/ > ../logs/cron/cron_week.log
  *
  * @package zorg\Games\Stockbroker
 */
error_reporting(E_ERROR);

/** Assign passed PHP CLI arguments to $_GET */
if (!empty($argv[1])) {
  parse_str($argv[1], $_GET);
}

error_log(sprintf('[%s] [NOTICE] <%s> Starting...', date('d.m.Y H:i:s',time()), __FILE__));

/** Check passed Parameters */
if (isset($_GET['wwwroot']) && is_string($_GET['wwwroot'])) $wwwroot = rtrim((string)$_GET['wwwroot'], '/\\'); // NO trailing Slash / !

/** www-Root available */
if (isset($wwwroot) && file_exists($wwwroot.'/includes/config.inc.php'))
{
	error_log(sprintf('[%s] [NOTICE] <%s> Starting...', date('d.m.Y H:i:s',time()), __FILE__));
	error_log(sprintf('[%s] [NOTICE] <%s> www-Root given: %s', date('d.m.Y H:i:s',time()), __FILE__, $wwwroot));

	error_log(sprintf('[%s] [NOTICE] <%s> Try including files...', date('d.m.Y H:i:s',time()), __FILE__));
	define('SITE_ROOT', $wwwroot.'/'); // Define own SITE_ROOT before loading general zConfigs
	require_once( SITE_ROOT.'/includes/config.inc.php');
	require_once( INCLUDES_DIR.'stockbroker.inc.php');

	foreach (Stockbroker::getStocksTraded() as $symbol) {
		Stockbroker::updateKurs($symbol);
	}
}
/** No www-Root path given */
else {
	error_log(sprintf('[%s] [WARNING] <%s> Missing Parameter 1 www-Root!', date('d.m.Y H:i:s',time()), __FILE__, $wwwroot));
	exit();
}

error_log(sprintf('[%s] [NOTICE] <%s> DONE - cron executed.', date('d.m.Y H:i:s',time()), __FILE__));
