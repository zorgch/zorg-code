<?php
/**
 * Migration Script to add primary-key field to existing `menus` table
 *
 * Required for relationship between templates (templates) and menu-templates (tpl_menus) data
 *
 * @author IneX
 * @package zorg
 * @subpackage Scripts
 * @version 1.0
 * @since 1.0 <inex> 12.06.2019 Script added
 */

/**
 * Add primary-key field to existing `menus` table in zorg DB
 *
 * @version 1.0
 * @since 1.0 <inex> 12.06.2019 Function added
 *
 * @param boolean $dryrun Disable to actually make the changes. Default: TRUE
 * @global object $db Globales Class-Object mit allen MySQL-Methoden
 * @return boolean Result of executing the function
*/
function db_update_table_menus_primarykey($dryrun=true)
{
	global $db;

	/** Start execution time measurement (function) */
	$startAll = microtime(true);

	/** SQL-Query */
	if ($dryrun === false) // Only if Dry-run is --OFF--
	{
		$modify_table_query = 'ALTER TABLE `menus` ADD COLUMN `id` int(11) unsigned NOT NULL AUTO_INCREMENT FIRST, ADD PRIMARY KEY (`id`);';

		/** Modify `menus` table */
		$table_modified = $db->query($modify_table_query, __FILE__, __LINE__, __FUNCTION__);

	} else { // Dry-run mode is --ON--
		error_log(sprintf('[INFO] <%s:%d> ALTER TABLE `menus`: DRY-RUN EXIT', __FUNCTION__, __LINE__));
		printf('[INFO] <%s:%d> Function execution completed within %g s'."\n", __FUNCTION__, __LINE__, microtime(true) - $startAll);
		return false;
	}

	if ($table_modified !== FALSE)
	{
		/** SUCCESSFUL FUNCTION EXECUTION */
		error_log(sprintf('[INFO] <%s:%d> ALTER TABLE `menus`: SUCCESS', __FUNCTION__, __LINE__));

		/** Execution time (function) */
		printf('[INFO] <%s:%d> Function execution completed within %g s'."\n", __FUNCTION__, __LINE__, microtime(true) - $startAll);

		return true;
	} else {
		/** CANCEL FUNCTION EXECUTION */
		error_log(sprintf('[ERROR] <%s:%d> ALTER TABLE `menus`: FAILED', __FUNCTION__, __LINE__));

		/** Execution time (function) */
		printf('[INFO] <%s:%d> Function execution completed within %g s'."\n", __FUNCTION__, __LINE__, microtime(true) - $startAll);

		return false;
	}
}
