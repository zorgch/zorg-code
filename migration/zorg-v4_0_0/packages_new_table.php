<?php
/**
 * Migration Script to add new database table "packages" incl. data
 *
 * Required for relationship between templates and php-package files (include)
 *
 * @author IneX
 * @package zorg
 * @subpackage Scripts
 * @version 1.0
 * @since 1.0 <inex> 24.05.2019 Script added
 */

/**
 * Step 1) Add new `packages` table to zorg DB
 *
 * @version 1.0
 * @since 1.0 <inex> 24.05.2019 Function added
 *
 * @param boolean $dryrun Disable to actually make the changes. Default: TRUE
 * @global object $db Globales Class-Object mit allen MySQL-Methoden
 * @return boolean Result of executing the function
*/
function db_add_table_packages($dryrun=true)
{
	global $db;

	/** Start execution time measurement (function) */
	$startAll = microtime(true);

	/** SQL-Query */
	if ($dryrun === false) // Only if Dry-run is --OFF--
	{
		$add_table_query =
			'CREATE TABLE IF NOT EXISTS `packages` (
			  `id` tinyint(5) unsigned NOT NULL AUTO_INCREMENT,
			  `name` tinytext COLLATE utf8mb4_unicode_ci NOT NULL,
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;';

		/** Add new table */
		$table_added = $db->query($add_table_query, __FILE__, __LINE__, __FUNCTION__);

	} else { // Dry-run mode is --ON--
		error_log(sprintf('[INFO] <%s:%d> CREATE TABLE `packages`: DRY-RUN EXIT', __FUNCTION__, __LINE__));
		printf('[INFO] <%s:%d> Function execution completed within %g s'."\n", __FUNCTION__, __LINE__, microtime(true) - $startAll);
		return false;
	}

	if ($table_added !== FALSE)
	{
		/** SUCCESSFUL FUNCTION EXECUTION */
		error_log(sprintf('[INFO] <%s:%d> CREATE TABLE `packages`: SUCCESS', __FUNCTION__, __LINE__));

		/** Execution time (function) */
		printf('[INFO] <%s:%d> Function execution completed within %g s'."\n", __FUNCTION__, __LINE__, microtime(true) - $startAll);

		return true;
	} else {
		/** CANCEL FUNCTION EXECUTION */
		error_log(sprintf('[ERROR] <%s:%d> CREATE TABLE `packages`: FAILED', __FUNCTION__, __LINE__));

		/** Execution time (function) */
		printf('[INFO] <%s:%d> Function execution completed within %g s'."\n", __FUNCTION__, __LINE__, microtime(true) - $startAll);

		return false;
	}
}

/**
 * Step 2) Add data to table `packages`
 *
 * - packages
 *   - doku
 *   - anficks
 *   - activities
 * @version 1.0
 * @since 1.0 <inex> 29.05.2019 Function added
 *
 * @see db_add_content_packages()
 * @param boolean $dryrun Disable to actually make the changes. Default: TRUE
 * @global object $db Globales Class-Object mit allen MySQL-Methoden
 * @return boolean Result of executing the function
*/
function db_add_content_packages($dryrun=true)
{
	global $db;

	/** Start execution time measurement (function) */
	$startAll = microtime(true);

	/** Packages data to insert */
	$packagesNames = [ 'doku', 'anficks', 'activities' ];

	/** Initialize helper vars */
	$packagesInsertCount = 0;
	$numPackages = count($packagesNames);

	foreach ($packagesNames as $package_name)
	{
		if ($dryrun === false) // Only if Dry-run is --OFF--
		{
			/** Add new row to table */
			$package_added = $db->insert('packages', [ 'name'=>$package_name ], __FILE__, __LINE__, __FUNCTION__);

			if ($package_added !== FALSE || $package_added > 0)
			{
				error_log(sprintf('[INFO] <%s:%d> $db->insert(packages, [name=>"%s"]): SUCCESS', __FUNCTION__, __LINE__, $package_name));			
				$packagesInsertCount++; // Increase counter of inserted rows
			} else {
				error_log(sprintf('[ERROR] <%s:%d> $db->insert(packages, [name=>"%s"]): FAILED on iteration %d', __FUNCTION__, __LINE__, $package_name, $packagesInsertCount));
			}
		} else {
			error_log(sprintf('[INFO] <%s:%d> $db->insert(packages, [name=>"%s"]): DRY-RUN', __FUNCTION__, __LINE__, $package_name));
		}		
	}

	/** Finalise */
	if ($packagesInsertCount === $numPackages)
	{
		/** SUCCESSFUL FUNCTION EXECUTION */
		error_log(sprintf('[INFO] <%s:%d> %d x $db->insert(packages): SUCCESS', __FUNCTION__, __LINE__, $numPackages));

		/** Execution time (function) */
		printf('[INFO] <%s:%d> Function execution completed within %g s'."\n", __FUNCTION__, __LINE__, microtime(true) - $startAll);

		return true;

	} else {
		/** CANCEL FUNCTION EXECUTION */
		error_log(sprintf('[ERROR] <%s:%d> %d x $db->insert(packages): FAILED with %d inserts', __FUNCTION__, __LINE__, $numPackages, $packagesInsertCount));

		/** Execution time (function) */
		printf('[INFO] <%s:%d> Function execution completed within %g s'."\n", __FUNCTION__, __LINE__, microtime(true) - $startAll);

		return false;
	}
}
