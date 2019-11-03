<?php
/**
 * Migration Script to collect and add existing template "packages" from table `templates` to new `tpl_packages`
 *
 * Required for relationship between templates and php-package files (include)
 *
 * @author IneX
 * @package zorg
 * @subpackage Scripts
 * @version 1.0
 * @since 1.0 <inex> 16.05.2019 Script added
 */

/**
 * Add data to table `tpl_packages`
 *
 * @version 1.0
 * @since 1.0 <inex> 16.05.2019 Function added
 *
 * @see db_add_table_tplpackages()
 * @param boolean $dryrun Disable to actually make the changes. Default: TRUE
 * @global object $db Globales Class-Object mit allen MySQL-Methoden
 * @return boolean Result of executing the function
*/
function db_add_content_tplpackages($dryrun=true)
{
	global $db;

	/** Start execution time measurement (function) */
	$startAll = microtime(true);

	/** Initialize helper vars */
	define('template_packages_separator', ';');
	$tplpackagesInsertCount = 0;
	$templatePackagesNum = 0;

	/** Query all Templates containing `packages`-content */
	error_log(sprintf('[INFO] <%s:%d> Query Templates containing `packages`-content', __FUNCTION__, __LINE__));
	$startQuery = microtime(true); // Start execution time measurement (query)
	$templatePackagesSql = 'SELECT `id`, `title`, `packages` FROM templates WHERE `packages` <> "" ORDER BY `id` ASC';
	$templatePackagesQuery = $db->query($templatePackagesSql, __FILE__, __LINE__, __FUNCTION__);
	error_log(sprintf('[INFO] <%s:%d> SQL-query completed within %g s', __FUNCTION__, __LINE__, microtime(true) - $startQuery)); // Stop execution time measurement (query)

	/** Process query result matched */
	while ($templatePackagesResult = $db->fetch($templatePackagesQuery))
	{
		$startRecord = microtime(true); // Start execution time measurement (record)
		$templatePackagesNum++;
		$template_id = $templatePackagesResult['id'];
		error_log(sprintf('[INFO] <%s:%d> (tpl#%d) Fetched template for processing: "%s"', __FUNCTION__, __LINE__, $template_id, $templatePackagesResult['title']));
		error_log(sprintf('[INFO] <%s:%d> (tpl#%d) Template packages: "%s"', __FUNCTION__, __LINE__, $template_id, $templatePackagesResult['packages']));

		/** Make sure we can resolve old String to an Array (so it's not empty) */
		if (!empty($templatePackagesResult['packages']) && is_array(explode(template_packages_separator, $templatePackagesResult['packages'])))
		{
			/**
			 * Convert "packages" string to PHP-Array
			 */
			error_log(sprintf('[INFO] <%s:%d> (tpl#%d) Converting "packages" String to Array...', __FUNCTION__, __LINE__, $template_id));
			$tpl_packages_phparray = explode(template_packages_separator, $templatePackagesResult['packages']);
			error_log(sprintf('[INFO] <%s:%d> (tpl#%d) DONE - converted "packages" string to Array: %s', __FUNCTION__, __LINE__, $template_id, print_r($tpl_packages_phparray,true)));
		}
		/** We already have an Array for the "packages", so reuse it */
		elseif (is_array($templatePackagesResult['packages']))
		{
			$tpl_packages_phparray = $templatePackagesResult['packages'];
			error_log(sprintf('[INFO] <%s:%d> (tpl#%d) "packages" is already an Array: %s', __FUNCTION__, __LINE__, $template_id, print_r($tpl_packages_phparray,true)));
		}
		/** "packages" String is missing / empty */
		elseif (empty($templatePackagesResult['packages']))
		{
			error_log(sprintf('[WARN] <%s:%d> (tpl#%d) "packages" are missing / empty!', __FUNCTION__, __LINE__, $template_id));
		}

		/**
		 * Process each "package" from "packages" Array
		 */
		$tpl_packages_phparray = array_filter($tpl_packages_phparray, 'strlen'); // array_filter() = removes empty Array-elements
		foreach ($tpl_packages_phparray as $package_name)
		{
			/** Query `packages` table to find correct `(package)id` */
			$packageIdSql = sprintf('SELECT `id` FROM `packages` WHERE `name` = "%s" LIMIT 0,1', $package_name);
			$packageIdQuery = $db->query($packageIdSql, __FILE__, __LINE__, __FUNCTION__);
			$packagenameFound = $db->fetch($packageIdQuery);

			if (!empty($packagenameFound['id']))
			{
				if ($dryrun === false) // Only if Dry-run is --OFF--
				{
					/** Insert row to `tpl_packages` */
					$package_added = $db->insert('tpl_packages', [ 'tpl_id' => $template_id, 'package_id' => $packagenameFound['id'] ], __FILE__, __LINE__, __FUNCTION__);

					if ($package_added !== FALSE || $package_added > 0)
					{
						error_log(sprintf('[INFO] <%s:%d> $db->insert(tpl_packages[tpl_id=>%d, package_id=>%d]): SUCCESS', __FUNCTION__, __LINE__, $template_id, $packagenameFound['id']));
						$tplpackagesInsertCount++; // Increase insert counter
					} else {
						error_log(sprintf('[WARN] <%s:%d> $db->insert(tpl_packages[tpl_id=>%d, package_id=>%d]): FAILED on iteration %d', __FUNCTION__, __LINE__, $template_id, $packagenameFound['id'], $tplpackagesInsertCount));
					}

				} else { // Dry-run mode is --ON--
					error_log(sprintf('[INFO] <%s:%d> $db->insert(tpl_packages[tpl_id=>%d, package_id=>%d]): DRY-RUN', __FUNCTION__, __LINE__, $template_id, $packagenameFound['id']));
				}
			} else {
				error_log(sprintf('[WARN] <%s:%d> No package "id" found / empty for "%s"!', __FUNCTION__, __LINE__, $package_name));
			}
		}
		error_log(sprintf('[INFO] <%s:%d> (tpl#%d) Record processed within %g s', __FUNCTION__, __LINE__, $template_id, microtime(true) - $startRecord)); // Stop execution time measurement (record)
	}

	/** Finalise */
	if ($tplpackagesInsertCount >= $templatePackagesNum)
	{
		/** SUCCESSFUL FUNCTION EXECUTION */
		error_log(sprintf('[INFO] <%s:%d> %d x $db->insert(tpl_packages): SUCCESS x %d', __FUNCTION__, __LINE__, $templatePackagesNum, $tplpackagesInsertCount));

		/** Execution time (function) */
		printf('[INFO] <%s:%d> Function execution completed within %g s'."\n", __FUNCTION__, __LINE__, microtime(true) - $startAll);

		return true;
	} else {
		/** CANCEL FUNCTION EXECUTION */
		error_log(sprintf('[ERROR] <%s:%d> %d x $db->insert(tpl_packages): FAILED with %d inserts', __FUNCTION__, __LINE__, $templatePackagesNum, $tplpackagesInsertCount));

		/** Execution time (function) */
		printf('[INFO] <%s:%d> Function execution completed within %g s'."\n", __FUNCTION__, __LINE__, microtime(true) - $startAll);

		return false;
	}
}
