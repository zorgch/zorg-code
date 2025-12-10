<?php
/**
 * Migration script to update zorg database
 * Start version update procedure using PHP CLI.
 *
 * PHP CLI usage:
 * $ php -f /path/to/script.php "migration=start&dryrun=true"
 *
 * Übersicht der Migrationsschritte und Ablauf
 * Packages:
 *   1) New table "packages"
 *      + add data
 *   2) New table "tpl_packages" adden
 *   3) Read "packages" from table "templates"
 *      + add to table "tpl_packages"
 * templates_convert_comments.php
 * Templates:
 *   4) Bestehender Table "templates"
 *      + add sidebar_tpl
 *      + add allow_comments
 * 
 * Comments:
 *   5) Convert {comments} => allow_comments:true
 * 
 * Menus:
 *   6) Bestehender Table "menus"
 *      + add primary_key
 *   7) New table "tpl_menus" adden
 *   8) Read "menus" from table "templates"
 *      + add to table "tpl_menus"
 * 
 * Cleanup:
 *   9) Table "templates"
 *      - remove row "packages"
 *      - remove "{menu name=...}" (row "tpl")
 *      + "force_recompile" setzen
 * 
 * Manuelle Folgetasks nach der Migration
 *   + Templates die eine "Sidebar" haben: entsprechend updaten
 *
 * @author IneX
 * @package zorg
 * @subpackage Scripts
 * @version 1.0
 * @since 1.0 <inex> 29.05.2019 Migration script added
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

		/* Packages:
		   1) New table "packages" + add data */
		require_once( __DIR__ .'/packages_new_table.php');
			db_add_table_packages($dryrun_mode);
			db_add_content_packages($dryrun_mode);

		/* 2) New table "tpl_packages" adden */
		require_once( __DIR__ .'/tplpackages_new_table.php');
			db_add_table_tplpackages($dryrun_mode);

		/* 3) Read "packages" from table "templates": add to table "tpl_packages" */
		require_once( __DIR__ .'/templates_convert_packages.php');
			if ($dryrun_mode === false) { // Only if Dry-run is --OFF--
				db_add_content_tplpackages($dryrun_mode);
			} else {
				error_log(sprintf('[INFO] <%s:%d> db_add_content_tplpackages(): SKIPPED (DRY-RUN)', __FILENAME__, __LINE__));
			}

		/* Templates & Templates_Backup:
			4a) Bestehender Table "templates" & "templates_backup": add sidebar_tpl */
		require_once( __DIR__ .'/templates_modify_table.php');
			db_update_table_templates_sidebartpl($dryrun_mode);
		/* 4b) Bestehender Table "templates" & "templates_backup": add allow_comments */
			db_update_table_templates_allow_comments($dryrun_mode);

		/* 5) Comments:
			convert {comments} => allow_comments:true */
		require_once( __DIR__ .'/templates_convert_comments.php');
			db_add_content_allow_comments($dryrun_mode);

		/* Menus:
		   6) Bestehender Table "menus": add primary_key */
		require_once( __DIR__ .'/menus_modify_table.php');
			db_update_table_menus_primarykey($dryrun_mode);

		/* 7) New table "tpl_menus" adden */
		require_once( __DIR__ .'/tplmenus_new_table.php');
			db_add_table_tplmenus($dryrun_mode);

		/* 8) Read "menus" from table "templates": add to table "tpl_menus" */
		require_once( __DIR__ .'/templates_convert_menus.php');
			if ($dryrun_mode === false) { // Only if Dry-run is --OFF--
				db_add_content_tplmenus($dryrun_mode);
			} else {
				error_log(sprintf('[INFO] <%s:%d> db_add_content_tplmenus(): SKIPPED (DRY-RUN)', __FILENAME__, __LINE__));
			}

		/* 9) Cleanup: Table "templates"
		   - remove row "packages" */
		   db_update_table_templates_remove_packages($dryrun_mode);

		   /* - remove "{menu name=...}" (row "tpl") */
		   db_update_table_templates_remove_menus($dryrun_mode);

		   /* + "force_recompile" setzen */
		   db_update_table_templates_forcecompile($dryrun_mode);

	error_log(sprintf('[INFO] <%s:%d> *** UPDATE DONE ***', __FILENAME__, __LINE__));

	/**
	 * POST-UPDATE MESSAGES
	 */
	printf('[INFO] <%s:%d> %s'."\n", __FILENAME__, __LINE__, 'Templates die eine "Sidebar" haben: entsprechend manuell updaten!');

	/** Execution time (total) */
	printf('[INFO] <%s:%d> Execution completed within %g s'."\n", __FILENAME__, __LINE__, microtime(true) - $startAll);
}

/** Password mismatch */
else {
	user_error('Zauberwörtli bitte', E_USER_NOTICE);
}
