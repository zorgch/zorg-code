<?php
/**
 * Migration Script to add new folder, file and contents
 *
 * @author IneX
 * @package zorg\Scripts
 * @version 1.0
 * @since 1.0 `22.12.2021` `IneX` Script added
 */

/**
 * Add new folder 'ipinfo' to 'keys/'-directory
 *
 * @version 1.0
 * @since 1.0 `22.12.2021` `IneX` Function added
 *
 * @uses ENV_ROOT
 * @param boolean $dryrun Disable to actually make the changes. Default: TRUE
 * @return boolean Result of executing the function
 */
function filesystem_mkdir_ipinfo($dryrun=true)
{
	/** Start execution time measurement (function) */
	$startAll = microtime(true);

	/** Configs for new folder */
	$dirName = 'ipinfo';
	$dirPath = ENV_ROOT.'/../keys/'.$dirName;
	$dirChmod = 0775;

	if ($dryrun === false) // Only if Dry-run is --OFF--
	{
		/** Add new folder to filesystem (if not exists yet) */
		if (is_dir($dirPath.'/..') && !is_dir($dirPath))
		{
			$mkdirResult = mkdir($dirPath, $dirChmod);
		}
		/** In case Parent-Dir is wrong, or $dirPath already exists... */
		else {
			error_log(sprintf('[INFO] <%s:%d> mkdir(%s, %d): ERROR (Parent dir missing or dir already exists)', __FUNCTION__, __LINE__, $dirPath, $dirChmod));
			$mkdirResult = false;
		}
	} else { // Dry-run mode is --ON--
		error_log(sprintf('[INFO] <%s:%d> mkdir(%s, %d): DRY-RUN EXIT', __FUNCTION__, __LINE__, $dirPath, $dirChmod));
		printf('[INFO] <%s:%d> Function execution completed within %g s'."\n", __FUNCTION__, __LINE__, microtime(true) - $startAll);
		return false;
	}

	if ($mkdirResult !== FALSE)
	{
		/** SUCCESSFUL FUNCTION EXECUTION */
		error_log(sprintf('[INFO] <%s:%d> mkdir(%s, %d): SUCCESS', __FUNCTION__, __LINE__, $dirPath, $dirChmod));
		printf('[INFO] <%s:%d> Function execution completed within %g s'."\n", __FUNCTION__, __LINE__, microtime(true) - $startAll);
		return true;
	} else {
		/** CANCEL FUNCTION EXECUTION */
		error_log(sprintf('[INFO] <%s:%d> mkdir(%s, %d): ERROR', __FUNCTION__, __LINE__, $dirPath, $dirChmod));
		printf('[INFO] <%s:%d> Function execution completed within %g s'."\n", __FUNCTION__, __LINE__, microtime(true) - $startAll);
		return false;
	}
}

/**
 * Add new file 'ipinfo_key.inc.php' with contents to '/keys/ipinfo/'-directory
 *
 * @version 1.0
 * @since 1.0 `22.12.2021` `IneX` Function added
 *
 * @uses ENV_ROOT
 * @param boolean $dryrun Disable to actually make the changes. Default: TRUE
 * @return boolean Result of executing the function
 */
function filesystem_fileputcontents_ipinfokey($dryrun=true)
{
	/** Start execution time measurement (function) */
	$startAll = microtime(true);

	/** Configs for new folder */
	$fileName = 'ipinfo_key.inc.php';
	$filePath = ENV_ROOT.'/../keys/ipinfo/'.$fileName;
	$fileContents = <<<'NOWDOC'
	<?php
	/**
	 * IPinfo API Token for zorg
	 *
	 * Subscription coverage
	 * - You're on the Free plan
	 * - 50k lookups per month (No additional lookups after that)
	 *    - If you exceed that limit, we'll return a 429 HTTP status code to you.
	 * - Geolocation data
	 *
	 * NOTE: requests are only allowed from whitelisted Domains, as configured
	 *       in «Whitelist Referring Domains» at https://ipinfo.io/account/token
	 *       - construct.zorg.ch
	 *       - zorg.ch
	 *       - zooomclan.org
	 *
	 * @link https://ipinfo.io/developers
	 * @example https://ipinfo.io/172.225.27.69/json?token=...
	 * @example ipv4: curl ipinfo.io/172.225.27.69/json?token=...
	 * @example ipv6: curl ipinfo.io/2001:4860:4860::8888?token=...
	 */
	return null;

	NOWDOC;

	if ($dryrun === false) // Only if Dry-run is --OFF--
	{
		/** Add new file with contents (if file does not exist yet) */
		if (is_dir($filePath.'/..') && !is_file($filePath))
		{
			$file_put_contentsResult = file_put_contents($filePath, $fileContents);
		}
		/** In case Parent-Dir is wrong, or $filePath already exists... */
		else {
			error_log(sprintf('[INFO] <%s:%d> file_put_contents(%s): ERROR (Parent dir missing or file alredy exists)', __FUNCTION__, __LINE__, $filePath));
			$file_put_contentsResult = false;
		}

	} else { // Dry-run mode is --ON--
		error_log(sprintf('[INFO] <%s:%d> file_put_contents(%s, %s): DRY-RUN EXIT', __FUNCTION__, __LINE__, $filePath, $fileContents));
		printf('[INFO] <%s:%d> Function execution completed within %g s'."\n", __FUNCTION__, __LINE__, microtime(true) - $startAll);
		return false;
	}

	if ($file_put_contentsResult !== FALSE)
	{
		/** SUCCESSFUL FUNCTION EXECUTION */
		error_log(sprintf('[INFO] <%s:%d> file_put_contents(%s, %s): SUCCESS', __FUNCTION__, __LINE__, $filePath, $fileContents));
		printf('[INFO] <%s:%d> Function execution completed within %g s'."\n", __FUNCTION__, __LINE__, microtime(true) - $startAll);
		return true;
	} else {
		/** CANCEL FUNCTION EXECUTION */
		error_log(sprintf('[INFO] <%s:%d> file_put_contents(%s, %s): ERROR', __FUNCTION__, __LINE__, $filePath, $fileContents));
		printf('[INFO] <%s:%d> Function execution completed within %g s'."\n", __FUNCTION__, __LINE__, microtime(true) - $startAll);
		return false;
	}
}
