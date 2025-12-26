<?php
/**
 * Migration script to update zorg database
 * Start version update procedure using PHP CLI.
 *
 * PHP CLI usage:
 * $ php -f /path/to/script.php "migration=start&dryrun=true"
 *
 * Übersicht der Migrationsschritte und Ablauf
 * 1) 'user' Table UPDATE
 *    - telegram_chat_id 0 => NULL
 * 
 * 2) 'user' Table DROP COLUMN
 *    - email_notification
 *    - icq
 *    - street
 *    - zip
 *    - city
 *    - phone_home
 *    - phone_mobile
 *    - phone_office
 *
 * @author IneX
 * @package zorg\Scripts
 * @version 1.0
 * @since 1.0 <inex> 05.12.2019 Migration script added
 */
/** Error reporting */
ini_set( 'display_errors', true );
error_reporting(E_ALL);

/** convert the arguments of PHP CLI call like "migration=start&dryrun=true" into the well known $_GET-array */
if (!empty($argv[1])) {
  parse_str($argv[1], $_GET);
}

if ($_GET['migration'] === 'start')
{
	error_log(sprintf('[INFO] <%s:%d> Starting...', __FILE__, __LINE__));
	define('__FILENAME__', basename(__FILE__));
	$dryrun_mode = ($_GET['dryrun'] === 'false' ? FALSE : TRUE); // Dry-Run Mode: <true>Enable/<false>Disable

	/** Start execution time measurement (total) */
	$startAll = microtime(true);

	/**
	 * Include base configs
	 * @include config.inc.php required
	 * @include mysql.inc.php required
	 */
	require_once( __DIR__ .'/../../www/includes/config.inc.php');
	require_once( __DIR__ .'/../../www/includes/mysql.inc.php');
	error_log(sprintf('[INFO] <%s:%d> Included base configs', __FILENAME__, __LINE__));

	/* *****
	 * START UPDATE CHAIN
	 * Include update scripts & run update functions
	 ***** */
	error_log(sprintf('[INFO] <%s:%d> *** START UPDATE ***', __FILENAME__, __LINE__));
	require_once( __DIR__ .'/user_modify_table.php');

		/* 1) 'user' Table UPDATE telegram_chat_id 0 => NULL */
		db_table_user_update($dryrun_mode);

		/* 2) 'user' Table DROP COLUMNs */
		db_table_user_drop_column_emailnotification($dryrun_mode);
		db_table_user_drop_column_icq($dryrun_mode);
		db_table_user_drop_column_street($dryrun_mode);
		db_table_user_drop_column_zip($dryrun_mode);
		db_table_user_drop_column_city($dryrun_mode);
		db_table_user_drop_column_phonehome($dryrun_mode);
		db_table_user_drop_column_phonemobile($dryrun_mode);
		db_table_user_drop_column_phoneoffice($dryrun_mode);

	/**
	 * END UPDATE
	 */
	error_log(sprintf('[INFO] <%s:%d> *** UPDATE DONE ***', __FILENAME__, __LINE__));

	/** Execution time (total) */
	printf('[INFO] <%s:%d> Execution completed within %g s'."\n", __FILENAME__, __LINE__, microtime(true) - $startAll);
}

/** Password mismatch */
else {
	user_error('Zauberwörtli bitte', E_USER_NOTICE);
}
