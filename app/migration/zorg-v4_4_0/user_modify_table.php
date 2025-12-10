<?php
/**
 * Migration Script to modify IP column in existing `user` table
 *
 * @author IneX
 * @package zorg\Scripts
 * @version 1.0
 * @since 1.0 `03.12.2021` `IneX` Script added
 */

/**
 * Remove column `last_ip` from existing `user` table in zorg DB
 *
 * @version 1.0
 * @since 1.0 `03.12.2021` `IneX` Function added
 *
 * @param boolean $dryrun Disable to actually make the changes. Default: TRUE
 * @global object $db Globales Class-Object mit allen MySQL-Methoden
 * @return boolean Result of executing the function
 */
function db_table_user_drop_column_lastip($dryrun=true)
{
	global $db;

	/** Start execution time measurement (function) */
	$startAll = microtime(true);

	if ($dryrun === false) // Only if Dry-run is --OFF--
	{
		/** Remove column 'last_ip' from `user` table */
		$dropTableColumnQuery = 'ALTER TABLE `user` DROP COLUMN `last_ip`';
		$dropTableColumnResult = $db->query($dropTableColumnQuery, __FILE__, __LINE__, __FUNCTION__);
	} else { // Dry-run mode is --ON--
		error_log(sprintf('[INFO] <%s:%d> ALTER TABLE `user` DROP COLUMN `last_ip`: DRY-RUN EXIT', __FUNCTION__, __LINE__));
		printf('[INFO] <%s:%d> Function execution completed within %g s'."\n", __FUNCTION__, __LINE__, microtime(true) - $startAll);
		return false;
	}

	if ($dropTableColumnResult !== FALSE)
	{
		/** SUCCESSFUL FUNCTION EXECUTION */
		error_log(sprintf('[INFO] <%s:%d> `user` DROP COLUMN `last_ip`: SUCCESS', __FUNCTION__, __LINE__));
		printf('[INFO] <%s:%d> Function execution completed within %g s'."\n", __FUNCTION__, __LINE__, microtime(true) - $startAll);
		return true;
	} else {
		/** CANCEL FUNCTION EXECUTION */
		error_log(sprintf('[INFO] <%s:%d> `user` DROP COLUMN `last_ip`: ERROR', __FUNCTION__, __LINE__));
		printf('[INFO] <%s:%d> Function execution completed within %g s'."\n", __FUNCTION__, __LINE__, microtime(true) - $startAll);
		return false;
	}
}
