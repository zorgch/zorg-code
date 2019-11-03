<?php
/**
 * Migration Script to collect and convert each "{comments}"-Smarty function to ENUM(1) = true in `allow_comments`-row
 *
 * Required to allow including the {comments}-Smarty function programmatically on a template base according to true/false value.
 *
 * @author IneX
 * @package zorg
 * @subpackage Scripts
 * @version 1.0
 * @since 1.0 <inex> 02.11.2019 Script added
 */

/**
 * Add data to table `tpl_menus`
 *
 * @version 1.0
 * @since 1.0 <inex> 11.06.2019 Function added
 *
 * @see db_add_table_tplmenus(), db_query_templates_menus()
 * @param boolean $dryrun Disable to actually make the changes. Default: TRUE
 * @global object $db Globales Class-Object mit allen MySQL-Methoden
 * @return boolean Result of executing the function
*/
function db_add_content_allow_comments($dryrun=true)
{
	global $db;

	/** Start execution time measurement (function) */
	$startAll = microtime(true);

	/** Initialize helper vars */
	$templatesRecordsNum = 0;
	$templatesUpdatedCount = 0;

	/** Query all Templates containing `packages`-content */
	error_log(sprintf('[INFO] <%s:%d> Query Templates containing `{comments}`-content', __FUNCTION__, __LINE__));
	$startQuery = microtime(true); // Start execution time measurement (query)
	$templateCommentsFunctionSql = 'SELECT `id`, `title`, `tpl` FROM templates WHERE `tpl` LIKE "%{comments}%" ORDER BY `id` ASC';
	$templateCommentsFunctionQuery = $db->query($templateCommentsFunctionSql, __FILE__, __LINE__, __FUNCTION__);
	error_log(sprintf('[INFO] <%s:%d> SQL-query completed within %g s', __FUNCTION__, __LINE__, microtime(true) - $startQuery)); // Stop execution time measurement (query)

	/** Process query result matched */
	while ($templateCommentsFunctionResult = $db->fetch($templateCommentsFunctionQuery))
	{
		$startRecord = microtime(true); // Start execution time measurement (record)
		$templatesRecordsNum++;
		$template_id = $templateCommentsFunctionResult['id'];
		error_log(sprintf('[INFO] <%s:%d> (tpl#%d) Fetched template for processing: "%s"', __FUNCTION__, __LINE__, $template_id, $templateCommentsFunctionResult['title']));

		/**
		 * Update row to set `allow_comments` ENUM()
		 */
		if ($dryrun === false) // Only if Dry-run is --OFF--
		{
			/** UPDATE-Query with SQL-side REPLACE of `templates` record's `tpl` string */
			$templateUpdateCommentsSql = sprintf('UPDATE templates SET
													 allow_comments="1", 
													 tpl=REPLACE(tpl, "{comments}", "")
												  WHERE `id` = %1$d', $template_id);
			$templateUpdateCommentsResult = $db->query($templateUpdateCommentsSql, __FILE__, __LINE__, __FUNCTION__);

			if ($templateUpdateCommentsResult !== FALSE && $templateUpdateCommentsResult >= 0)
			{
				error_log(sprintf('[INFO] <%s:%d> UPDATE templates SET tpl (allow_comments & REPLACE("{comments}") from id %d: SUCCESS (%d)', __FUNCTION__, __LINE__, $template_id, $templatesRecordsNum));
				$templatesUpdatedCount++;
			} else {
				error_log(sprintf('[WARN] <%s:%d> UPDATE templates SET tpl (allow_comments & REPLACE("{comments}") from id %d: FAILED on iteration %d', __FUNCTION__, __LINE__, $menuName, $templateRecordFound['id'], $templatesRecordsNum));
			}
		} else { // Dry-run mode is --ON--
			error_log(sprintf('[INFO] <%s:%d> UPDATE templates SET tpl (allow_comments & REPLACE("{comments}"): DRY-RUN', __FUNCTION__, __LINE__, $template_id, $packagenameFound['id']));
		}
		error_log(sprintf('[INFO] <%s:%d> (tpl#%d) Record processed within %g s', __FUNCTION__, __LINE__, $template_id, microtime(true) - $startRecord)); // Stop execution time measurement (record)
	}

	/** Finalise */
	if ($templatesUpdatedCount >= $templatesRecordsNum)
	{
		/** SUCCESSFUL FUNCTION EXECUTION */
		error_log(sprintf('[INFO] <%s:%d> %d x UPDATE templates: SUCCESS x %d', __FUNCTION__, __LINE__, $templatesRecordsNum, $templatesUpdatedCount));

		/** Execution time (function) */
		printf('[INFO] <%s:%d> Function execution completed within %g s'."\n", __FUNCTION__, __LINE__, microtime(true) - $startAll);

		return true;
	} else {
		/** CANCEL FUNCTION EXECUTION */
		error_log(sprintf('[ERROR] <%s:%d> %d x UPDATE templates: FAILED with %d inserts', __FUNCTION__, __LINE__, $templatesRecordsNum, $templatesUpdatedCount));

		/** Execution time (function) */
		printf('[INFO] <%s:%d> Function execution completed within %g s'."\n", __FUNCTION__, __LINE__, microtime(true) - $startAll);

		return false;
	}
}
