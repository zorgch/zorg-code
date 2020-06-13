<?php
/**
 * Migration script to update zorg database
 *
 * Start version update via Deploy Console,
 * or procedure using PHP CLI.
 * 
 * PHP CLI usage:
 * $ php -f /path/to/script.php "migration=start&dryrun=true"
 * 
 * Übersicht der Migrationsschritte und Ablauf
 * Packages:
 *   1) Existing table "packages"
 *      + add data
 * 
 * Templates "tpl"-row:
 *   8) Read "{include_php}" from table "templates"
 *      + add relation to table "tpl_packages"
 * 
 * Cleanup:
 *   9) Table "templates"
 *      - remove "{include_php file=...}" (row "tpl")
 *      + "force_recompile" auf allen DB-Templates setzen
 * 
 * Manuelle Folgetasks nach der Migration
 *   ? ...
 *
 * @author IneX
 * @package zorg\Scripts
 * @version 1.0
 * @since 1.0 <inex> 03.05.2020 Migration script added
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
	if (!require_once dirname(__FILE__).'/../../www/includes/config.inc.php') die('ERROR: Configurations could NOT be loaded!');
	if (!require_once dirname(__FILE__).'/../../www/includes/mysql.inc.php') die('ERROR: Database configurations could NOT be loaded!');
	error_log(sprintf('[INFO] <%s:%d> Included base configs', __FILENAME__, __LINE__));

	/* *****
	 * START UPDATE CHAIN
	 * Include update scripts & run update functions
	 ***** */
	error_log(sprintf('[INFO] <%s:%d> *** START UPDATE ***', __FILENAME__, __LINE__));

		/* Packages:
		   1) Add additional data to table "packages" */
		require_once( __DIR__ .'/packages_new_table.php');
			db_add_table_packages($dryrun_mode);
			db_add_content_packages($dryrun_mode);

		/* 3) Read "packages" from table "templates": add to table "tpl_packages" */
		require_once( __DIR__ .'/templates_convert_packages.php');
			if ($dryrun_mode === false) { // Only if Dry-run is --OFF--
				db_add_content_tplpackages($dryrun_mode);
			} else {
				error_log(sprintf('[INFO] <%s:%d> db_add_content_tplpackages(): SKIPPED (DRY-RUN)', __FILENAME__, __LINE__));
			}

		/* 9) Cleanup: Table "templates"
		   /* - remove "{include_php file=...}" from row "tpl" */
		   db_update_table_templates_remove_menus($dryrun_mode);

		   /* + set "force_recompile" for all DB-Templates */
		   db_update_table_templates_forcecompile($dryrun_mode);

	error_log(sprintf('[INFO] <%s:%d> *** UPDATE DONE ***', __FILENAME__, __LINE__));

	/**
	 * POST-UPDATE MESSAGES
	 */
	printf('[INFO] <%s:%d> %s'."\n", __FILENAME__, __LINE__, 'He did it! This motherfucker really did it!');

	/** Execution time (total) */
	printf('[INFO] <%s:%d> Execution completed within %g s'."\n", __FILENAME__, __LINE__, microtime(true) - $startAll);
}

/** Password mismatch */
else {
	user_error('Zauberwörtli bitte', E_USER_NOTICE);
}
