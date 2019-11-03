<?php
/**
 * Migration Script to add 'sidebar_tpl' row to existing `templates` table
 *
 * Required for displaying a Smarty template as sidebar in a Smarty page template
 *
 * @author IneX
 * @package zorg
 * @subpackage Scripts
 * @version 1.0
 * @since 1.0 <inex> 12.06.2019 Script added
 */

/**
 * Add `sidebar_tpl` row to existing `templates` table in zorg DB
 *
 * @version 1.0
 * @since 1.0 <inex> 12.06.2019 Function added
 *
 * @param boolean $dryrun Disable to actually make the changes. Default: TRUE
 * @global object $db Globales Class-Object mit allen MySQL-Methoden
 * @return boolean Result of executing the function
*/
function db_update_table_templates_sidebartpl($dryrun=true)
{
	global $db;

	/** Start execution time measurement (function) */
	$startAll = microtime(true);

	/** SQL-Query */
	if ($dryrun === false) // Only if Dry-run is --OFF--
	{
		$modify_table_query = 'ALTER TABLE `templates` ADD COLUMN `sidebar_tpl` smallint(5) unsigned DEFAULT NULL;';
		$modify_table_backup_query = 'ALTER TABLE `templates_backup` ADD COLUMN `sidebar_tpl` smallint(5) unsigned DEFAULT NULL;';

		/** Modify `templates` table */
		$table_modified = $db->query($modify_table_query, __FILE__, __LINE__, __FUNCTION__);
		$table_backup_modified = $db->query($modify_table_backup_query, __FILE__, __LINE__, __FUNCTION__);

	} else { // Dry-run mode is --ON--
		error_log(sprintf('[INFO] <%s:%d> ALTER TABLE `templates` & `templates_backup`: DRY-RUN EXIT', __FUNCTION__, __LINE__));
		printf('[INFO] <%s:%d> Function execution completed within %g s'."\n", __FUNCTION__, __LINE__, microtime(true) - $startAll);
		return false;
	}

	if ($table_modified !== FALSE)
	{
		/** SUCCESSFUL FUNCTION EXECUTION */
		error_log(sprintf('[INFO] <%s:%d> ALTER TABLE `templates` ADD COLUMN `sidebar_tpl`: SUCCESS', __FUNCTION__, __LINE__));
		error_log(sprintf('[INFO] <%s:%d> ALTER TABLE `templates_backup` ADD COLUMN `sidebar_tpl`: SUCCESS', __FUNCTION__, __LINE__));

		/** Execution time (function) */
		printf('[INFO] <%s:%d> Function execution completed within %g s'."\n", __FUNCTION__, __LINE__, microtime(true) - $startAll);

		return true;
	} else {
		/** CANCEL FUNCTION EXECUTION */
		error_log(sprintf('[ERROR] <%s:%d> ALTER TABLE `templates` ADD COLUMN `sidebar_tpl`: FAILED', __FUNCTION__, __LINE__));
		error_log(sprintf('[ERROR] <%s:%d> ALTER TABLE `templates_backup` ADD COLUMN `sidebar_tpl`: FAILED', __FUNCTION__, __LINE__));

		/** Execution time (function) */
		printf('[INFO] <%s:%d> Function execution completed within %g s'."\n", __FUNCTION__, __LINE__, microtime(true) - $startAll);

		return false;
	}
}

/**
 * Add `allow_comments` row to existing `templates` table in zorg DB
 *
 * @version 1.0
 * @since 1.0 <inex> 02.11.2019 Function added
 *
 * @param boolean $dryrun Disable to actually make the changes. Default: TRUE
 * @global object $db Globales Class-Object mit allen MySQL-Methoden
 * @return boolean Result of executing the function
*/
function db_update_table_templates_allow_comments($dryrun=true)
{
	global $db;

	/** Start execution time measurement (function) */
	$startAll = microtime(true);

	/** SQL-Query */
	if ($dryrun === false) // Only if Dry-run is --OFF--
	{
		$modify_table_query = 'ALTER TABLE `templates` ADD COLUMN `allow_comments` enum("0","1") NOT NULL DEFAULT "0";';
		$modify_table_backup_query = 'ALTER TABLE `templates_backup` ADD COLUMN `allow_comments` enum("0","1") NOT NULL DEFAULT "0"';

		/** Modify `templates` table */
		$table_modified = $db->query($modify_table_query, __FILE__, __LINE__, __FUNCTION__);
		$table_backup_modified = $db->query($modify_table_backup_query, __FILE__, __LINE__, __FUNCTION__);

	} else { // Dry-run mode is --ON--
		error_log(sprintf('[INFO] <%s:%d> ALTER TABLE `templates` & `templates_backup`: DRY-RUN EXIT', __FUNCTION__, __LINE__));
		printf('[INFO] <%s:%d> Function execution completed within %g s'."\n", __FUNCTION__, __LINE__, microtime(true) - $startAll);
		return false;
	}

	if ($table_modified !== FALSE)
	{
		/** SUCCESSFUL FUNCTION EXECUTION */
		error_log(sprintf('[INFO] <%s:%d> ALTER TABLE `templates` ADD COLUMN `allow_comments`: SUCCESS', __FUNCTION__, __LINE__));
		error_log(sprintf('[INFO] <%s:%d> ALTER TABLE `templates_backup` ADD COLUMN `allow_comments`: SUCCESS', __FUNCTION__, __LINE__));

		/** Execution time (function) */
		printf('[INFO] <%s:%d> Function execution completed within %g s'."\n", __FUNCTION__, __LINE__, microtime(true) - $startAll);

		return true;
	} else {
		/** CANCEL FUNCTION EXECUTION */
		error_log(sprintf('[ERROR] <%s:%d> ALTER TABLE `templates` ADD COLUMN `allow_comments`: FAILED', __FUNCTION__, __LINE__));
		error_log(sprintf('[ERROR] <%s:%d> ALTER TABLE `templates_backup` ADD COLUMN `allow_comments`: FAILED', __FUNCTION__, __LINE__));

		/** Execution time (function) */
		printf('[INFO] <%s:%d> Function execution completed within %g s'."\n", __FUNCTION__, __LINE__, microtime(true) - $startAll);

		return false;
	}
}

/**
 * Remove `packages` column from existing `templates` table in zorg DB
 *
 * @version 1.0
 * @since 1.0 <inex> 16.06.2019 Function added
 *
 * @param boolean $dryrun Disable to actually make the changes. Default: TRUE
 * @global object $db Globales Class-Object mit allen MySQL-Methoden
 * @return boolean Result of executing the function
*/
function db_update_table_templates_remove_packages($dryrun=true)
{
	global $db;

	/** Start execution time measurement (function) */
	$startAll = microtime(true);

	if ($dryrun === false) // Only if Dry-run is --OFF--
	{
		/** Remove templates column 'packages' */
		$dropTemplatesTableColumnQuery = 'ALTER TABLE `templates` DROP COLUMN `packages`';
		$dropTemplatesTableColumnResult = $db->query($dropTemplatesTableColumnQuery, __FILE__, __LINE__, __FUNCTION__);
		
		/** Remove templates_backup column 'packages' */
		$dropTemplatesBackupTableColumnQuery = 'ALTER TABLE `templates_backup` DROP COLUMN `packages`';
		$dropTemplatesBackupTableColumnResult = $db->query($dropTemplatesBackupTableColumnQuery, __FILE__, __LINE__, __FUNCTION__);

	} else { // Dry-run mode is --ON--
		error_log(sprintf('[INFO] <%s:%d> ALTER TABLE `templates` & `templates_backup` DROP COLUMN `packages`: DRY-RUN EXIT', __FUNCTION__, __LINE__));
		printf('[INFO] <%s:%d> Function execution completed within %g s'."\n", __FUNCTION__, __LINE__, microtime(true) - $startAll);
		return false;
	}

	if ($dropTemplatesTableColumnResult !== FALSE)
	{
		/** SUCCESSFUL FUNCTION EXECUTION */
		error_log(sprintf('[INFO] <%s:%d> `templates` DROP COLUMN `packages`: SUCCESS', __FUNCTION__, __LINE__));
		error_log(sprintf('[INFO] <%s:%d> `templates_backup` DROP COLUMN `packages`: SUCCESS', __FUNCTION__, __LINE__));

		/** Execution time (function) */
		printf('[INFO] <%s:%d> Function execution completed within %g s'."\n", __FUNCTION__, __LINE__, microtime(true) - $startAll);

		return true;
	} else {
		/** CANCEL FUNCTION EXECUTION */
		error_log(sprintf('[INFO] <%s:%d> `templates` DROP COLUMN `packages`: ERROR', __FUNCTION__, __LINE__));
		error_log(sprintf('[INFO] <%s:%d> `templates_backup` DROP COLUMN `packages`: ERROR', __FUNCTION__, __LINE__));

		/** Execution time (function) */
		printf('[INFO] <%s:%d> Function execution completed within %g s'."\n", __FUNCTION__, __LINE__, microtime(true) - $startAll);

		return false;
	}
}

/**
 * Remove "{menu name=...}" from `tpl`-rows content in `templates` table
 *
 * @version 1.0
 * @since 1.0 <inex> 16.06.2019 Function added
 *
 * @param boolean $dryrun Disable to actually make the changes. Default: TRUE
 * @global object $db Globales Class-Object mit allen MySQL-Methoden
 * @return boolean Result of executing the function
*/
function db_update_table_templates_remove_menus($dryrun=true)
{
	global $db;

	/** Start execution time measurement (function) */
	$startAll = microtime(true);

	/** Initialize helper vars */
	$menusNum = 0;
	$templatesUpdatedCount = 0;

	/**
	 * Fetch Array with Template-Menus Mapping from Helper function
	 * @see templates_convert_menus.php
	 */
	$extractedTplMenusMapping = db_query_templates_menus();
	$menusNum = count($extractedTplMenusMapping);

	/** Validate extracted Template-Menus Mapping (Array) */
	if ($extractedTplMenusMapping === false || !is_array($extractedTplMenusMapping) || $menusNum <= 0) return false;

	/**
	 * All good! process extracted Template-Menus Mapping...
	 * 
	 * $extractedTplMenusMapping is an array whose elements are the
	 * multiple matches found by the regex pattern (WHOLE matches).
	 * Example:
	 * 
	 * array (size=2)
     * 'tpl' => string '17' (length=2)
     * 'menus' => 
     *   array (size=2)
     *     0 => 
     *       array (size=2)
     *         0 => string '{menu name=zorg}' (length=16)
     *         1 => string '{menu name=utilities}' (length=21)
     *     1 => 
     *       array (size=2)
     *         0 => string 'zorg' (length=4)
     *         1 => string 'utilities' (length=9)
	 */
	foreach ($extractedTplMenusMapping as $templateMenuMatches)
	{
		/** Query `templates` table to load db-record to update based on `tpl(id)` */
		//$templatesBaseSql = sprintf('SELECT `id`, REPLACE(`tpl`,"\\\\\\\\","") as `tpl` FROM `templates` WHERE `id` = %d LIMIT 0,1', $templateMenuMatches['tpl']);
		$templatesBaseSql = sprintf('SELECT `id` FROM `templates` WHERE `id` = %d LIMIT 0,1', $templateMenuMatches['tpl']);
		$templatesBaseQuery = $db->query($templatesBaseSql, __FILE__, __LINE__, __FUNCTION__);
		$templateRecordFound = $db->fetch($templatesBaseQuery);

		/** Template Record found */
		if (!empty($templateRecordFound['id']))
		{
			/**
			 * $extractedTplMenusMapping['menus'][1] is an array whose elements are only
			 * the capture-group matches (like $1) of each Menu from the regex pattern.
			 * Example:
			 * 
			 * 1 => 
			 *   array (size=2)
			 *     0 => string 'zorg' (length=4)
			 *     1 => string 'utilities' (length=9)
			 */
			foreach($templateMenuMatches['menus'][1] as $menuName)
			{
				error_log(sprintf('[INFO] <%s:%d> Query & SQL-side REPLACE of template ID %d (%s)', __FUNCTION__, __LINE__, $templateRecordFound['id'], $menuName));
				$startRecord = microtime(true); // Start execution time measurement (record)

				/**
				 * Only if Dry-run is --OFF--
				 */
				if ($dryrun === false)
				{
					/** UPDATE-Query with SQL-side REPLACE of `templates` record's `tpl` string */
					$templateTplCleanupSql = sprintf('UPDATE templates 
													  SET tpl=TRIM(LEADING "\r\n" FROM 
															TRIM(LEADING "<br />" FROM 
																TRIM(LEADING "<br>" FROM 
																	TRIM(
																		REPLACE(
																			REPLACE(
																				REPLACE(tpl, "{menu name=%1$s}", ""),
																			"{menu name=\"%1$s\"}", ""),
																		"\\\\", "")
																	)
																)
															)
														) WHERE `id` = %2$d',
														$menuName, $templateRecordFound['id']);
					$templateTplCleanupResult = $db->query($templateTplCleanupSql, __FILE__, __LINE__, __FUNCTION__);
	
					if ($templateTplCleanupResult !== FALSE && $templateTplCleanupResult >= 0)
					{
						error_log(sprintf('[INFO] <%s:%d> UPDATE templates SET tpl (remove menu "%s" from id %d): SUCCESS (%d)', __FUNCTION__, __LINE__, $menuName, $templateRecordFound['id'], $templatesUpdatedCount));
						$templatesUpdatedCount++;
					} else {
						error_log(sprintf('[WARN] <%s:%d> UPDATE templates SET tpl (remove menu "%s" from id %d): FAILED on iteration %d', __FUNCTION__, __LINE__, $menuName, $templateRecordFound['id'], $templatesUpdatedCount));
					}

				/**
				 * Dry-run mode is --ON--
				 */
				} else {
					/** SELECT-only Query & SQL-side REPLACE of `templates` record's `tpl` string */
					error_log(sprintf('[INFO] <%s:%d> $db->fetch(tpl_id=>%d): DRY-RUN', __FUNCTION__, __LINE__, $templateRecordFound['id']));
					$templateTplCleanupSelectSql = sprintf('SELECT tpl, 
															TRIM(LEADING "\r\n" FROM 
																TRIM(LEADING "<br />" FROM 
																	TRIM(LEADING "<br>" FROM 
																		TRIM(
																			REPLACE(
																				REPLACE(
																					REPLACE(tpl, "{menu name=%1$s}", ""),
																				"{menu name=\"%1$s\"}", ""),
																			"\\\\", "")
																		)
																	)
																)
															) as tpl_clean 
															FROM templates WHERE `id` = %2$d LIMIT 0,1',
														$menuName, $templateRecordFound['id']);
					$templateTplCleanupSelectQuery = $db->query($templateTplCleanupSelectSql, __FILE__, __LINE__, __FUNCTION__);
					$templateTplCleanupSelectRecordFound = $db->fetch($templateTplCleanupSelectQuery);
					$templatesUpdatedCount++;
					//error_log(sprintf('[INFO] <%s:%d> (Iteration #%d) $db->fetch(tpl_id=>%d) Record: %s', __FUNCTION__, __LINE__, $templatesUpdatedCount, $templateRecordFound['id'], $templateTplCleanupSelectRecordFound['tpl_clean']));
					error_log(sprintf('[INFO] <%s:%d> (Iteration #%d) $db->fetch(tpl_id=>%d) Record: retrieved', __FUNCTION__, __LINE__, $templatesUpdatedCount, $templateRecordFound['id']));
				}
				error_log(sprintf('[INFO] <%s:%d> Record (tpl#%d) "%s" processed within %g s', __FUNCTION__, __LINE__, $templateRecordFound['id'], $menuName, microtime(true) - $startRecord)); // Stop execution time measurement (record)
			}
		} else {
			error_log(sprintf('[WARN] <%s:%d> No menu "id" found / empty for template "%d"!', __FUNCTION__, __LINE__, $templateMenuMatches['id']));
		}
	}

	/** Finalise */
	if ($templatesUpdatedCount >= $menusNum)
	{
		/** SUCCESSFUL FUNCTION EXECUTION */
		error_log(sprintf('[INFO] <%s:%d> %d x UPDATE templates SET tpl: SUCCESS x %d', __FUNCTION__, __LINE__, $menusNum, $templatesUpdatedCount));

		/** Execution time (function) */
		printf('[INFO] <%s:%d> Function execution completed within %g s'."\n", __FUNCTION__, __LINE__, microtime(true) - $startAll);

		return true;
	} else {
		/** CANCEL FUNCTION EXECUTION */
		error_log(sprintf('[ERROR] <%s:%d> %d x UPDATE templates SET tpl: FAILED with only %d updated records', __FUNCTION__, __LINE__, $menusNum, $templatesUpdatedCount));

		/** Execution time (function) */
		printf('[INFO] <%s:%d> Function execution completed within %g s'."\n", __FUNCTION__, __LINE__, microtime(true) - $startAll);

		return false;
	}
}

/**
 * Set "force_compile" flag on all templates
 *
 * @version 1.0
 * @since 1.0 <inex> 12.06.2019 Function added
 *
 * @param boolean $dryrun Disable to actually make the changes. Default: TRUE
 * @global object $db Globales Class-Object mit allen MySQL-Methoden
 * @return boolean Result of executing the function
*/
function db_update_table_templates_forcecompile($dryrun=true)
{
	global $db;

	/** Start execution time measurement (function) */
	$startAll = microtime(true);

	/** SQL-Query */
	if ($dryrun === false) // Only if Dry-run is --OFF--
	{
		/** Update all templates records */
		$updateTemplatesQuery = 'UPDATE templates SET force_compile="1"';
		$updateTemplatesDbRecords = $db->query($updateTemplatesQuery, __FILE__, __LINE__, __FUNCTION__);

	} else { // Dry-run mode is --ON--
		error_log(sprintf('[INFO] <%s:%d> UPDATE templates `templates`: DRY-RUN EXIT', __FUNCTION__, __LINE__));
		printf('[INFO] <%s:%d> Function execution completed within %g s'."\n", __FUNCTION__, __LINE__, microtime(true) - $startAll);
		return false;
	}

	if ($updateTemplatesDbRecords !== FALSE)
	{
		/** SUCCESSFUL FUNCTION EXECUTION */
		error_log(sprintf('[INFO] <%s:%d> UPDATE templates `templates`: SUCCESS', __FUNCTION__, __LINE__));

		/** Execution time (function) */
		printf('[INFO] <%s:%d> Function execution completed within %g s'."\n", __FUNCTION__, __LINE__, microtime(true) - $startAll);

		return true;
	} else {
		/** CANCEL FUNCTION EXECUTION */
		error_log(sprintf('[INFO] <%s:%d> UPDATE templates `templates`: ERROR', __FUNCTION__, __LINE__));

		/** Execution time (function) */
		printf('[INFO] <%s:%d> Function execution completed within %g s'."\n", __FUNCTION__, __LINE__, microtime(true) - $startAll);

		return false;
	}
}
