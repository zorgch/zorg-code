<?php
/**
 * Migration Script to collect and move all "{menu name=...}" from `tpl`-row content to new `tpl_menus`
 *
 * Required for relationship between templates (templates) and menu-templates (tpl_menus) data
 *
 * @author IneX
 * @package zorg
 * @subpackage Scripts
 * @version 1.0
 * @since 1.0 <inex> 29.05.2019 Script added
 */

/**
 * Add data to table `tpl_menus`
 *
 * @version 1.0
 * @since 1.0 <inex> 11.06.2019 Function added
 *
 * @uses db_add_table_tplmenus()
 * @uses self::db_query_templates_menus()
 * @param boolean $dryrun Disable to actually make the changes. Default: TRUE
 * @global object $db Globales Class-Object mit allen MySQL-Methoden
 * @return boolean Result of executing the function
*/
function db_add_content_tplmenus($dryrun=true)
{
	global $db;

	/** Start execution time measurement (function) */
	$startAll = microtime(true);

	/** Initialize helper vars */
	$menusNum = 0;
	$menusInsertCount = 0;

	/** Fetch Array with Template-Menus Mapping from Helper function */
	$extractedTplMenusMapping = db_query_templates_menus();
	$menusNum = count($extractedTplMenusMapping);

	/** Validate extracted Template-Menus Mapping (Array) */
	if ($extractedTplMenusMapping === false || !is_array($extractedTplMenusMapping) || $menusNum <= 0) return false;

	/**
	 * All good! process extracted Template-Menus Mapping...
	 * 
	 * $extractedTplMenusMapping['menus'] is an array whose elements are the
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
		/**
		 * $extractedTplMenusMapping['menus'][1] is an array whose elements are only
		 * the capture-group matches (like $1) from the regex pattern.
		 * Example:
		 * 
		 * 1 => 
		 *   array (size=2)
		 *     0 => string 'zorg' (length=4)
		 *     1 => string 'utilities' (length=9)
		 */
		foreach($templateMenuMatches['menus'][1] as $menuName)
		{
			$startRecord = microtime(true); // Start execution time measurement (record)

			/** Query `menus` table to find correct `(menu)id` */
			$menuIdSql = sprintf('SELECT `id` FROM `menus` WHERE `name` = "%s" LIMIT 0,1', $menuName);
			$menuIdQuery = $db->query($menuIdSql, __FILE__, __LINE__, __FUNCTION__);
			$menuNameFound = $db->fetch($menuIdQuery);

			if (!empty($menuNameFound['id']))
			{
				if ($dryrun === false) // Only if Dry-run is --OFF--
				{
					/** Add new row to `tpl_menus` table */
					$menu_added = $db->insert('tpl_menus', [ 'tpl_id'=>$templateMenuMatches['tpl'], 'menu_id'=>$menuNameFound['id'] ], __FILE__, __LINE__, __FUNCTION__);
	
					if ($menu_added !== FALSE || $menu_added > 0)
					{
						error_log(sprintf('[INFO] <%s:%d> $db->insert(tpl_menus[tpl_id=>%d, menu_id=>%d]): SUCCESS', __FUNCTION__, __LINE__, $templateMenuMatches['tpl'], $menuNameFound['id']));
						$menusInsertCount++;
					} else {
						error_log(sprintf('[WARN] <%s:%d> $db->insert(tpl_menus[tpl_id=>%d, menu_id=>%d]): FAILED on iteration %d', __FUNCTION__, __LINE__, $templateMenuMatches['tpl'], $menuNameFound['id'], $menusInsertCount));
					}

				} else { // Dry-run mode is --ON--
					error_log(sprintf('[INFO] <%s:%d> $db->insert(tpl_menus[tpl_id=>%d, menu_id=>%d]): DRY-RUN', __FUNCTION__, __LINE__, $templateMenuMatches['tpl'], $menuNameFound['id']));
				}
			} else {
				error_log(sprintf('[WARN] <%s:%d> No menu "id" found / empty for "%s"!', __FUNCTION__, __LINE__, $menuName));
			}
			error_log(sprintf('[INFO] <%s:%d> Record (tpl#%d) "%s" processed within %g s', __FUNCTION__, __LINE__, $menuNameFound['id'], $menuName, microtime(true) - $startRecord)); // Stop execution time measurement (record)
		}
	}

	/** Finalise */
	if ($menusInsertCount >= $menusNum)
	{
		/** SUCCESSFUL FUNCTION EXECUTION */
		error_log(sprintf('[INFO] <%s:%d> %d x $db->insert(tpl_menus): SUCCESS x %d', __FUNCTION__, __LINE__, $menusNum, $menusInsertCount));

		/** Execution time (function) */
		printf('[INFO] <%s:%d> Function execution completed within %g s'."\n", __FUNCTION__, __LINE__, microtime(true) - $startAll);

		return true;
	} else {
		/** CANCEL FUNCTION EXECUTION */
		error_log(sprintf('[ERROR] <%s:%d> %d x $db->insert(tpl_menus): FAILED with %d inserts', __FUNCTION__, __LINE__, $menusNum, $menusInsertCount));

		/** Execution time (function) */
		printf('[INFO] <%s:%d> Function execution completed within %g s'."\n", __FUNCTION__, __LINE__, microtime(true) - $startAll);

		return false;
	}
}

/**
 * Helper function to fetch and return Array relation with menu-names from templates
 *
 * @version 1.0
 * @since 1.0 <inex> 11.06.2019 Function added
 *
 * @global object $db Globales Class-Object mit allen MySQL-Methoden
 * @return array|bool Fetched result as Array containing [ 'tpl' => tpl_id, [ menu-name1, menu-name2,.. ] ] - or false if no results
*/
function db_query_templates_menus()
{
	global $db;

	/** Start execution time measurement (function) */
	$startAll = microtime(true);

	/** Initialize helper vars */
	$arrayIndex = 0;

	/** Regex-Pattern with capture-group, matching all {menu name=>>>...<<<} in given String */
	$regexToMatch = '/{menu name=(.*?)}/';

	/** Query all templates 'tpl' fields, relevant for processing, from the database */
	$templatesQuery = 'SELECT `id`, `tpl` FROM `templates` WHERE `tpl` LIKE "%{menu name%"';
	$templatesQueryResult = $db->query($templatesQuery, __FILE__, __LINE__, __FUNCTION__);
	error_log(sprintf('[INFO] <%s:%d> $db->query("%s"): DONE', __FUNCTION__, __LINE__, templatesQuery));

	while ($matchedTemplates = $db->fetch($templatesQueryResult))
	{
		/** Cleanup string; e.g. we have stuff like: \\\"main\\\" and "main" */
		$templateContentRaw = $matchedTemplates['tpl'];
		$templateContentClean = $templateContentRaw;
		$templateContentClean = stripslashes($templateContentClean); // Strip all Slashes
		$templateContentClean = str_replace(array('\'', '"'), '', $templateContentClean); // Strip all Quotes
		$templateMenuMatches[$arrayIndex] = ['tpl' => $matchedTemplates['id']];
		preg_match_all($regexToMatch, $templateContentClean, $templateMenuMatches[$arrayIndex]['menus']); // Extract {menu name=>>>...<<<} from String & build Array elements

		$arrayIndex++; // Increase Array index counter
	}
	error_log(sprintf('[INFO] <%s:%d> Recordes matched with preg_match_all(%s): %d', __FUNCTION__, __LINE__, $regexToMatch, $arrayIndex));

	/** Count Array elements to return only a non-empty Array (otherwise NULL) */
	error_log(sprintf('[INFO] <%s:%d> $extractedTplMenusMapping-Array elements: %d', __FUNCTION__, __LINE__, count($templateMenuMatches)));
	
	/** Execution time (function) */
	printf('[INFO] <%s:%d> Function execution completed within %g s'."\n", __FUNCTION__, __LINE__, microtime(true) - $startAll);
	
	return (count($templateMenuMatches[0]['tpl']) >= 1 ? $templateMenuMatches : false);
}
