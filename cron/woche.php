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

include_once( __DIR__ .'/../www/includes/config.inc.php');
include_once( __DIR__ .'/../www/includes/mysql.inc.php');
//include_once( __DIR__ .'/../www/includes/forum.inc.php');


/** Unread_comments älter als 30 Tage löschen */
$sql = 'DELETE FROM comments_unread
		USING comments, comments_unread 
		WHERE 
		comments.id = comments_unread.comment_id 
		AND 
		UNIX_TIMESTAMP(date) < (UNIX_TIMESTAMP(now())-60*60*24*30*3)';
$db->query($sql, __FILE__, __LINE__, 'DELETE FROM comments_unread');
