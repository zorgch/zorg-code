<?php
/**
 * zorg cron jobs to run once per WEEK
 *
 * At 19:23 every Sunday:
 *		$ crontab -e
 *		  23 19 * * 7 php -f ./week.php wwwroot=/path/to/public/www/ > ../logs/cron/cron_week.log
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
	error_log(sprintf('[%s] [NOTICE] <%s> www-Root given: %s', date('d.m.Y H:i:s',time()), __FILE__, $wwwroot));

	error_log(sprintf('[%s] [NOTICE] <%s> Try including files...', date('d.m.Y H:i:s',time()), __FILE__));
	define('SITE_ROOT', $wwwroot); // Define own SITE_ROOT before loading general zConfigs
	require_once( SITE_ROOT.'/includes/config.inc.php');
	include_once( INCLUDES_DIR.'mysql.inc.php');
	//include_once( INCLUDES_DIR.'forum.inc.php');
	error_log(sprintf('[%s] [NOTICE] <%s> Files included', date('d.m.Y H:i:s',time()), __FILE__));

	/** Unread_comments älter als 30 Tage löschen */
	$sql = 'DELETE FROM comments_unread
			USING comments, comments_unread 
			WHERE comments.id = comments_unread.comment_id 
			AND comments.date < (DATE(NOW())-INTERVAL 30 DAY)';
	$result = $db->query($sql, __FILE__, __LINE__, 'DELETE FROM comments_unread');
	$numDeletedComments = $db->num($result);
	error_log(sprintf('[%s] [NOTICE] <%s> Finished deleting unread Comments: %d', date('d.m.Y H:i:s',time()), __FILE__, $numDeletedComments));
}
/** No www-Root path given */
else {
	error_log(sprintf('[%s] [WARNING] <%s> Missing Parameter 1 www-Root!', date('d.m.Y H:i:s',time()), __FILE__, $wwwroot));
	exit();
}

error_log(sprintf('[%s] [NOTICE] <%s> DONE - cron executed.', date('d.m.Y H:i:s',time()), __FILE__));
