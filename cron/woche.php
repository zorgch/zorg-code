<?php
/**
 * zorg cron jobs to run once per WEEK
 *    $ sudo crontab -e
 *      15 7 * * * php -f /var/cron/week.php > /var/log/cron_week.log
 */
error_reporting(E_ERROR);

/** Assign passed PHP CLI arguments to $_GET */
if (!empty($argv[1])) {
  parse_str($argv[1], $_GET);
}

error_log(sprintf('[%s] [NOTICE] <%s> Starting...', date('d.m.Y H:i:s',time()), __FILE__));

include_once( dirname(__FILE__).'/../www/includes/config.inc.php');
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
