<?php
/**
 * Migration Script to add new database table "tpl_packages"
 *
 * Required for relationship between templates and php-package files (include)
 *
 * @author IneX
 * @package zorg
 * @subpackage Scripts
 * @version 1.0
 * @since 1.0 <inex> 16.06.2019 Script added
 */

/**
 * Add new `tpl_packages` table to zorg DB
 *
 * @version 1.0
 * @since 1.0 <inex> 29.05.2019 Function added
 *
 * @param boolean $dryrun Disable to actually make the changes. Default: TRUE
 * @global object $db Globales Class-Object mit allen MySQL-Methoden
 * @return boolean Result of executing the function
*/
function db_add_table_tplpackages($dryrun=true)
{
	global $db;

	/** Start execution time measurement (function) */
	$startAll = microtime(true);

	/** SQL-Query */
	if ($dryrun === false) // Only if Dry-run is --OFF--
	{
		$add_table_query =
			'CREATE TABLE IF NOT EXISTS `tpl_packages` (
			  `tpl_id` int(11) unsigned NOT NULL,
			  `package_id` tinyint(5) unsigned NOT NULL
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;';

		/** Add new table */
		$table_added = $db->query($add_table_query, __FILE__, __LINE__, __FUNCTION__);
	} else { // Dry-run mode is --ON--
		error_log(sprintf('[INFO] <%s:%d> CREATE TABLE `tpl_packages`: DRY-RUN EXIT', __FUNCTION__, __LINE__));
		printf('[INFO] <%s:%d> Function execution completed within %g s'."\n", __FUNCTION__, __LINE__, microtime(true) - $startAll);
		return false;
	}

	if ($table_added !== FALSE)
	{
		/** SUCCESSFUL FUNCTION EXECUTION */
		error_log(sprintf('[INFO] <%s:%d> CREATE TABLE `tpl_packages`: SUCCESS', __FUNCTION__, __LINE__));

		/** Execution time (function) */
		printf('[INFO] <%s:%d> Function execution completed within %g s'."\n", __FUNCTION__, __LINE__, microtime(true) - $startAll);

		return true;
	} else {
		/** CANCEL FUNCTION EXECUTION */
		error_log(sprintf('[ERROR] <%s:%d> CREATE TABLE `tpl_packages`: FAILED', __FUNCTION__, __LINE__));

		/** Execution time (function) */
		printf('[INFO] <%s:%d> Function execution completed within %g s'."\n", __FUNCTION__, __LINE__, microtime(true) - $startAll);

		return false;
	}
}
