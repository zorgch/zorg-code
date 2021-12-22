<?php
/**
 * Migration Script to remove no longer used tables to match IP to Country.
 *
 * DROP TABLES:
 *    - country_coords
 *    - country_ip
 *    - country_utc
 *
 * @author IneX
 * @package zorg\Scripts
 * @version 1.0
 * @since 1.0 `03.12.2021` `IneX` Script added
 */

/**
 * DROP TABLE `country_coords` from the zorg DB
 *
 * @version 1.0
 * @since 1.0 `03.12.2021` `IneX` Function added
 *
 * @param boolean $dryrun Disable to actually make the changes. Default: TRUE
 * @global object $db Globales Class-Object mit allen MySQL-Methoden
 * @return boolean Result of executing the function
 */
function db_drop_table_country_coords($dryrun=true)
{
	global $db;

	/** Start execution time measurement (function) */
	$startAll = microtime(true);

	if ($dryrun === false) // Only if Dry-run is --OFF--
	{
		/** Remove table 'country_coords' from the Database */
		$dropTableQuery = 'DROP TABLE `country_coords`';
		$dropTableResult = $db->query($dropTableQuery, __FILE__, __LINE__, __FUNCTION__);
	} else { // Dry-run mode is --ON--
		error_log(sprintf('[INFO] <%s:%d> DROP TABLE `country_coords`: DRY-RUN EXIT', __FUNCTION__, __LINE__));
		printf('[INFO] <%s:%d> Function execution completed within %g s'."\n", __FUNCTION__, __LINE__, microtime(true) - $startAll);
		return false;
	}

	if ($dropTableResult !== FALSE)
	{
		/** SUCCESSFUL FUNCTION EXECUTION */
		error_log(sprintf('[INFO] <%s:%d> DROP TABLE `country_coords`: SUCCESS', __FUNCTION__, __LINE__));
		printf('[INFO] <%s:%d> Function execution completed within %g s'."\n", __FUNCTION__, __LINE__, microtime(true) - $startAll);
		return true;
	} else {
		/** CANCEL FUNCTION EXECUTION */
		error_log(sprintf('[INFO] <%s:%d> DROP TABLE `country_coords`: ERROR', __FUNCTION__, __LINE__));
		printf('[INFO] <%s:%d> Function execution completed within %g s'."\n", __FUNCTION__, __LINE__, microtime(true) - $startAll);
		return false;
	}
}

/**
 * DROP TABLE `country_coords` from the zorg DB
 *
 * @version 1.0
 * @since 1.0 `03.12.2021` `IneX` Function added
 *
 * @param boolean $dryrun Disable to actually make the changes. Default: TRUE
 * @global object $db Globales Class-Object mit allen MySQL-Methoden
 * @return boolean Result of executing the function
 */
function db_drop_table_country_ip($dryrun=true)
{
	global $db;

	/** Start execution time measurement (function) */
	$startAll = microtime(true);

	if ($dryrun === false) // Only if Dry-run is --OFF--
	{
		/** Remove table 'country_ip' from the Database */
		$dropTableQuery = 'DROP TABLE `country_ip`';
		$dropTableResult = $db->query($dropTableQuery, __FILE__, __LINE__, __FUNCTION__);
	} else { // Dry-run mode is --ON--
		error_log(sprintf('[INFO] <%s:%d> DROP TABLE `country_ip`: DRY-RUN EXIT', __FUNCTION__, __LINE__));
		printf('[INFO] <%s:%d> Function execution completed within %g s'."\n", __FUNCTION__, __LINE__, microtime(true) - $startAll);
		return false;
	}

	if ($dropTableResult !== FALSE)
	{
		/** SUCCESSFUL FUNCTION EXECUTION */
		error_log(sprintf('[INFO] <%s:%d> DROP TABLE `country_ip`: SUCCESS', __FUNCTION__, __LINE__));
		printf('[INFO] <%s:%d> Function execution completed within %g s'."\n", __FUNCTION__, __LINE__, microtime(true) - $startAll);
		return true;
	} else {
		/** CANCEL FUNCTION EXECUTION */
		error_log(sprintf('[INFO] <%s:%d> DROP TABLE `country_ip`: ERROR', __FUNCTION__, __LINE__));
		printf('[INFO] <%s:%d> Function execution completed within %g s'."\n", __FUNCTION__, __LINE__, microtime(true) - $startAll);
		return false;
	}
}

/**
 * DROP TABLE `country_utc` from the zorg DB
 *
 * @version 1.0
 * @since 1.0 `03.12.2021` `IneX` Function added
 *
 * @param boolean $dryrun Disable to actually make the changes. Default: TRUE
 * @global object $db Globales Class-Object mit allen MySQL-Methoden
 * @return boolean Result of executing the function
 */
function db_drop_table_country_utc($dryrun=true)
{
	global $db;

	/** Start execution time measurement (function) */
	$startAll = microtime(true);

	if ($dryrun === false) // Only if Dry-run is --OFF--
	{
		/** Remove table 'country_ip' from the Database */
		$dropTableQuery = 'DROP TABLE `country_utc`';
		$dropTableResult = $db->query($dropTableQuery, __FILE__, __LINE__, __FUNCTION__);
	} else { // Dry-run mode is --ON--
		error_log(sprintf('[INFO] <%s:%d> DROP TABLE `country_utc`: DRY-RUN EXIT', __FUNCTION__, __LINE__));
		printf('[INFO] <%s:%d> Function execution completed within %g s'."\n", __FUNCTION__, __LINE__, microtime(true) - $startAll);
		return false;
	}

	if ($dropTableResult !== FALSE)
	{
		/** SUCCESSFUL FUNCTION EXECUTION */
		error_log(sprintf('[INFO] <%s:%d> DROP TABLE `country_utc`: SUCCESS', __FUNCTION__, __LINE__));
		printf('[INFO] <%s:%d> Function execution completed within %g s'."\n", __FUNCTION__, __LINE__, microtime(true) - $startAll);
		return true;
	} else {
		/** CANCEL FUNCTION EXECUTION */
		error_log(sprintf('[INFO] <%s:%d> DROP TABLE `country_utc`: ERROR', __FUNCTION__, __LINE__));
		printf('[INFO] <%s:%d> Function execution completed within %g s'."\n", __FUNCTION__, __LINE__, microtime(true) - $startAll);
		return false;
	}
}
